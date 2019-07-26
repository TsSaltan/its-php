<?php
namespace tsframe\module\user;

use tsframe\module\database\Database;
use tsframe\module\database\Query;
use tsframe\module\Crypto;
use tsframe\Config;
use tsframe\Hook;
use tsframe\exception\UserException;
use tsframe\module\Cache;

class User{
	/**
	 * Регистрация пользователя
	 * @param  string   	 $login    
	 * @param  string   	 $email    
	 * @param  string|null   $password (optional) Если null, будет установлен случайный пароль
	 * @param  int|null 	 $access (optional) Если null, будут установлены права доступа по умолчанию (Config: access.user.onRegister)
	 * @return SingleUser
	 * @throws UserException
	 */
	public static function register(?string $login, string $email, ?string $password, int $access = null) : SingleUser {
		Crypto::wait();
		
		if(!UserConfig::canRegister()) throw new UserException('Registration disabled', 403);

		// Если логин не используется, сгенерируем его на основе email
		if(!UserConfig::isLoginUsed() && is_null($login))$login = explode('@', $email)[0] . '_' . strrev(uniqid()); // Токены, сгенерированные через uniqid уж очень похожи один на другого, переверну их через strrev

		$access = is_null($access) ? UserAccess::getAccess('user.onRegister') : $access;
		$query = Database::prepare('INSERT INTO `users` (`login`, `email`, `access`) VALUES (:login, :email, :access)')
				->bind('login', $login)
				->bind('email', $email)
				->bind('access', $access)
				->exec();

		if($query->affectedRows() == 0) throw new UserException('User registration error', 400, ['login' => $login, 'email' => $email, 'password' => $password, 'access' => $access]);

		$uid = $query->lastInsertId();
		$user = new SingleUser($uid, $login, $email, $access);
		$user->set('password', $password); // Пароль устанавливается отдельно, чтоб сгенерировался его хеш
		Hook::call('user.register', [$user]);
		return $user;
	}

	public static function login(string $loginOrMail, string $password) : SingleUser {
		Crypto::wait();
		$users = self::get(['login' => $loginOrMail, 'email' => $loginOrMail], 'OR');
	
		foreach($users as $user){
			if(self::getPasswordHash($user->get('id'), $password) == $user->get('password')){
				Hook::call('user.login', [$user]);
				return $user;
			}
		}

		throw new UserException('User login error', 400, ['loginOrMail' => $loginOrMail, 'password' => $password]);
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
	 * @return SingleUser[]
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
			if($key == 'password' && isset($params['id'])){
				$value = self::getPasswordHash($params['id'], $value);
			}

			$query->bind($key, $value);
		}

		$result = $query->exec()->fetch();
		$users = [];
		foreach ($result as $user) {
			$users[$user['id']] = new SingleUser($user['id'], $user['login'], $user['email'], $user['access'], $user['password']);
		}

		return $users;
	}	

	/**
	 * Возвращает текущего пользователя
	 * @return SingleUser
	 */
	public static function current(?string $sessionKey = null) : SingleUser {
		return Cache::toVar('currentUser', function() use ($sessionKey){
			return SingleUser::current($sessionKey);
		});
	}

	public static function getPasswordHash(int $userId, string $password) : string {
		return Crypto::saltHash($userId . $password, 'sha512');
	}
}