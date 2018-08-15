<?php
namespace tsframe\module\user;

use tsframe\module\Meta;
use tsframe\module\database\Database;
use tsframe\exception\AccessException;
use tsframe\utils\IP;


class SingleUser{
	/**
	 * Время действия пользовательской сессии (сек)
	 */
	const SESSION_EXPIRES = 60*60*24*366;	

	/**
	 * Ключ для сессионной куки
	 */
	const SESSION_KEY = 'session';

	/**
	 * Если ID == -1, значит пользователь не авторизован 
	 * @var int
	 */
	protected $id;		

	/**
	 * @var int
	 */
	protected $access;

	/**
	 * @var string
	 */
	protected $accessText;	

	/**
	 * @var string
	 */
	protected $login;

	/**
	 * @var string
	 */
	protected $email;

	public static function unauthorized(){
		return new self(-1);
	}	

	/**
	 * Use User::current() instead this method!
	 */
	public static function current(): SingleUser {
		if(isset($_COOKIE[self::SESSION_KEY])){
			$data = Database::prepare('SELECT * FROM `sessions` WHERE `key` = :key AND `expires` > CURRENT_TIMESTAMP')
					->bind('key', $_COOKIE[self::SESSION_KEY])
					->exec()
					->fetch();

			if(isset($data[0])){
				return new self($data[0]['user_id']);
			}
		}
		return self::unauthorized();
	}

	public function __construct(int $uid, string $login = null, string $email = null, int $access = UserAccess::Guest){
		$this->id = $uid;
		$this->login = $login;
		$this->email = $email;
		$this->access = $access;

		if(is_null($this->login)){
			$this->update();
		}
	}

	public function isAuthorized() : bool {
		return $this->id >= 0;
	}

	public function get(string $data){
		$this->accessText = array_flip(UserAccess::getArray())[$this->access];
		return $this->{$data} ?? null;
	}

	public function set(string $key, $value) : bool {
		if($key == 'password'){
			// Для пароля генерируем хеш
			$value = User::getPasswordHash($this->login, $value);
		}
		elseif($key == 'id' || !property_exists($this, $key)){
			// ID и несуществующие поля не меняем
			return false;
		}
		
		$this->{$key} = $value;
		return Database::prepare('UPDATE `users` SET `'.$key.'` = :value WHERE `id` = :id')
						->bind('value', $value)
						->bind('id', $this->id)
						->exec()
						->affectedRows() > 0;
	}

	protected function update(){
		if(!$this->isAuthorized()) return;

		$data = Database::prepare('SELECT * FROM `users` WHERE `id` = :id')
				->bind('id', $this->id)
				->exec()
				->fetch();

		if(isset($data[0])){
			$this->login = $data[0]['login'];
			$this->email = $data[0]['email'];
			$this->access = $data[0]['access'];
		}		
	}

	public function createSession() : bool {
		$sessionId = hash('sha384', time() . uniqid($this->login));

		$_COOKIE[self::SESSION_KEY] = $sessionId;
		setcookie(self::SESSION_KEY, $sessionId, time()+self::SESSION_EXPIRES, '/');

		return Database::prepare('INSERT INTO `sessions` (`key`, `user_id`, `expires`, `ip`) VALUES (:key, :id, CURRENT_TIMESTAMP + INTERVAL :expires SECOND, :ip)')
				->bind('key', $sessionId)
				->bind('id', $this->id)
				->bind('expires', self::SESSION_EXPIRES, TYPE_INT)
				->bind('ip', IP::get())
				->exec()
				->affectedRows() > 0;
	}

	public function closeSession(bool $deleteCookies = false) : bool {
		if($deleteCookies){
			setcookie(self::SESSION_KEY, null, -1, '/');
		}
		
		return Database::prepare('DELETE FROM `sessions` WHERE `user_id` = :id OR `expires` < CURRENT_TIMESTAMP')
				->bind('id', $this->id)
				->exec()
				->affectedRows() > 0;
	}

	public function delete(): bool {
		$this->closeSession();
		return Database::prepare('DELETE FROM `users` WHERE `id` = :id')
				->bind('id', $this->id)
				->exec()
				->affectedRows() > 0;
	}

	public function getSessions(): array {
		if(!$this->isAuthorized()) return [];

		return Database::prepare('SELECT * FROM `sessions` WHERE `user_id` = :id')
				->bind('id', $this->id)
				->exec()
				->fetch();
	}

	public function isAccess($rule): bool {
		return UserAccess::checkUser($this, $rule);
	}

	public function getMeta(): Meta {
		return new Meta('user', $this->id);
	}
}