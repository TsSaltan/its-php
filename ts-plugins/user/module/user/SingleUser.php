<?php
namespace tsframe\module\user;

use tsframe\exception\AccessException;
use tsframe\module\Crypto;
use tsframe\module\IP;
use tsframe\module\Meta;
use tsframe\module\database\Database;
use tsframe\module\user\UserConfig;


class SingleUser {
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
	 * Хеш от пароля
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var Meta
	 */
	protected $meta;

	public static function unauthorized(){
		return new self(-1);
	}	

	/**
	 * Use User::current() instead this method!
	 */
	public static function current(?string $sessionKey = null): SingleUser {
		$sessionKey = (strlen($sessionKey) == 0) ? ($_COOKIE[self::SESSION_KEY] ?? null) : $sessionKey;
		if(strlen($sessionKey) > 0){
			$data = Database::prepare('SELECT * FROM `sessions` WHERE `key` = :key AND `expires` > CURRENT_TIMESTAMP')
					->bind('key', $sessionKey )
					->exec()
					->fetch();

			if(isset($data[0])){
				return new self($data[0]['user_id']);
			}
		}
		return self::unauthorized();
	}

	public function __construct(int $uid, string $login = null, string $email = null, int $access = UserAccess::Guest, string $password = null){
		$this->id = $uid;
		$this->login = $login;
		$this->email = $email;
		$this->access = $access;
		$this->accessText = array_flip(UserAccess::getArray())[$this->access];
		$this->password = $password;

		if(is_null($this->login)){
			$this->update();
		}
	}

	public function isAuthorized() : bool {
		return $this->id >= 0;
	}

	public function get(string $key){
		if(property_exists($this, $key)){
			return $this->{$key};
		} 
		elseif(isset($this->data[$key])) {
			return $this->data[$key];
		}

		return null;
	}

	public function set(string $key, $value) : bool {
		if($key == 'password'){
			// Для пароля генерируем хеш
			$value = User::getPasswordHash($this->id, $value);
		}
		elseif($key == 'id' || $key == 'accessText'){
			// ID не меняем
			return false;
		}
		elseif($key == 'access'){
			$this->accessText = array_flip(UserAccess::getArray())[$value];
		}
		
		if(property_exists($this, $key)){
			$this->{$key} = $value;
		} else {
			$this->data[$key] = $value;
		}

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
			$this->password = $data[0]['password'];

			unset($data[0]['login']);
			unset($data[0]['email']);
			unset($data[0]['access']);
			unset($data[0]['password']);
			$this->data = $data[0];
		} else {
			// Если данных нет в бд, значит пользователь не авторизован
			$this->id = -1;
		}
	}

	/**
	 * Создать новую сессию
	 * @param  bool|boolean $setCookies Установить авторизационные куки
	 * @return array [session_key, expires]
	 * @throws UserException
	 */
	public function createSession(bool $setCookies = true) : array {
		$sessionId = Crypto::generateString(100);

		if($setCookies){
			$_COOKIE[self::SESSION_KEY] = $sessionId;
			setcookie(self::SESSION_KEY, $sessionId, time()+self::SESSION_EXPIRES, '/');
		}

		$res = Database::prepare('INSERT INTO `sessions` (`key`, `user_id`, `expires`, `ip`) VALUES (:key, :id, CURRENT_TIMESTAMP + INTERVAL :expires SECOND, :ip)')
			->bind('key', $sessionId)
			->bind('id', $this->id)
			->bind('expires', self::SESSION_EXPIRES, TYPE_INT)
			->bind('ip', IP::current())
			->exec()
			->affectedRows();

		if($res == 0){
			throw new UserException('Cannot create session', -1, [
				'user' => $this
			]);
		}

		return [
			'session_key' => $sessionId,
			'expires' => time() + self::SESSION_EXPIRES
		];
	}

	/**
	 * Закрыть текущую сессию пользователя и удалить авторизационные куки
	 * @return bool
	 */
	public function closeSession() : bool {
		setcookie(self::SESSION_KEY, null, -1, '/');		
		return Database::prepare('DELETE FROM `sessions` WHERE `user_id` = :id AND `key` = :key')
				->bind('id', $this->id)
				->bind('key', $_COOKIE[self::SESSION_KEY])
				->exec()
				->affectedRows() > 0;
	}

	/**
	 * Закрыть все сессии пользователя
	 * @return bool
	 */
	public function closeAllSessions() : bool {
		return Database::prepare('DELETE FROM `sessions` WHERE `user_id` = :id OR `expires` < CURRENT_TIMESTAMP')
				->bind('id', $this->id)
				->exec()
				->affectedRows() > 0;
	}

	public function delete(): bool {
		$this->closeAllSessions();
		return Database::prepare('DELETE FROM `users` WHERE `id` = :id')
				->bind('id', $this->id)
				->exec()
				->affectedRows() > 0;
	}

	public function getSessions(): array {
		if(!$this->isAuthorized()) return [];

		return Database::prepare('SELECT *, (`expires` - INTERVAL :expires SECOND) \'start\' FROM `sessions` WHERE `user_id` = :id')
				->bind('id', $this->id)
				->bind('expires', self::SESSION_EXPIRES, TYPE_INT)
				->exec()
				->fetch();
	}

	public function isAccess($rule): bool {
		return UserAccess::checkUser($this, $rule);
	}

	public function getMeta(): Meta {
		return !is_object($this->meta) ? $this->meta = new Meta('user', $this->id) : $this->meta ;
	}
}