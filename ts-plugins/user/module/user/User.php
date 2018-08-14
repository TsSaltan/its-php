<?php
namespace tsframe\module\user;

use tsframe\module\database\Database;
use tsframe\module\database\Query;


class User{
	public static function register(string $login, string $email, ?string $password, int $access = null) : SingleUser {
		$access = is_null($access) ? UserAccess::getAccess('user.onRegister') : $access;
		$uid = Database::prepare('INSERT INTO `users` (`login`, `email`, `access`, `password`) VALUES (:login, :email, :access, :password)')
				->bind('login', $login)
				->bind('email', $email)
				->bind('password', self::getPasswordHash($login, $password))
				->bind('access', $access)
				->exec()
				->lastInsertId();

		return new SingleUser($uid, $login, $email, $access);
	}

	public static function login(string $loginOrMail, string $password) : SingleUser {
		$user = Database::prepare('SELECT * FROM `users` WHERE `login` = :login OR `email` = :login')
				->bind('login', $loginOrMail)
				->exec()
				->fetch();

		if(isset($user[0])){
			$pHash = self::getPasswordHash($user[0]['login'], $password);
			if($pHash == $user[0]['password']){
				return new SingleUser($user[0]['id'], $user[0]['login'], $user[0]['email'], $user[0]['access']);
			}
		}

		return SingleUser::unauthorized();
	}

	/**
	 * Cуществоет ли пользователь с заданными параметрами
	 * @param  array  $params f.e. ['login' => 'Admin', 'email' => 'mail@admin.ru']
	 * @return bool
	 */
	public static function exists(array $params, string $operator = 'OR') : bool {
		$result = self::get($params, $operator);
		return sizeof($result) > 0;
	}		

	/**
	 * Cуществоет ли пользователь с заданными параметрами
	 * @param  array  $params f.e. ['login' => 'Admin', 'email' => 'mail@admin.ru']
	 * @return bool
	 */
	public static function get(array $params = [], string $operator = 'OR') : array {
		if(sizeof($params) > 0){
			$queryCond = [];
			foreach ($params as $key => $value) {
				$queryCond[] = '`'.$key.'` = :'.$key;
			}

			$queryCond = implode(" $operator ", $queryCond);
			$query = Database::prepare('SELECT * FROM `users` WHERE ' . $queryCond);
		} else {
			$query = Database::prepare('SELECT * FROM `users`');
		}
		foreach ($params as $key => $value) {
			if($key == 'password' && isset($params['login'])){
				$value = self::getPasswordHash($params['login'], $value);
			}

			$query->bind($key, $value);
		}

		$result = $query->exec()->fetch();
		$users = [];
		foreach ($result as $user) {
			$users[] = new SingleUser($user['id'], $user['login'], $user['email'], $user['access']);
		}

		return $users;
	}	

	protected static $current = null;

	/**
	 * Возвращает текущего пользователя
	 * @return SingleUser
	 */
	public static function current() : SingleUser {
		if(is_null(self::$current)){
			self::$current = SingleUser::current();
		}
		
		return self::$current;
	}

	public static function getPasswordHash(string $login, ?string $password) : string {
		$salt = Config::get('appId');
		return hash('sha512', $password . $salt . $login);
	}
}