<?php
namespace tsframe\utils\io;

use tsframe\exception\ValidateException;

/**
 * Валидатор входящих данных
 */
class Validator{

	/**
	 * Переменные для проверки
	 * @var array
	 */
	protected $vars = [];

	/**
	 * Текущий ключ
	 * @var string
	 */
	protected $key;

	/**
	 * Ключи, не прошедшие проверку
	 * @var array
	 */
	protected $invalidKeys = [];

	public static function of(array &$inputVars){
		return new self($inputVars);
	}

	public static function get(bool $checkReferer = true){
		return new self($_GET, $checkReferer);
	}	

	public static function post(bool $checkReferer = true){
		return new self($_POST, $checkReferer);
	}

	public static function request(bool $checkReferer = true){
		return new self($_REQUEST, $checkReferer);
	}

	public static function files(bool $checkReferer = true){
		return new self($_FILES, $checkReferer);
	}

	public function __construct(array &$inputVars, bool $checkReferer = false){
		$this->vars = &$inputVars;

		if($checkReferer){
			$this->assertReferer();
		}
	}

	protected function assertReferer(){
		$referer = $_SERVER['HTTP_REFERER'] ?? null ;
		$refDomain = parse_url($referer, PHP_URL_HOST);
		$current = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
		$currentDomain = explode(':', $current)[0]; // Убираем порт, если он есть

		if($refDomain != $currentDomain){
			throw new ValidateException('Query security error', 2, [
				'referer' => $referer,
				'referer_domain' => $refDomain,
				'current' => $current,
				'current_domain' => $currentDomain,
			]);
		}
	}

	public function name(string $key){
		$this->key = $key;
		return $this;
	}	

	public function validateID(string $name = 'id'){
		return $this->name($name)
					->required()
					->int();
	}

	public function validateLogin(string $name = 'login'){
		return $this->name($name)
					->required()
					->regexp('#[A-Za-z0-9-_\.]+#Ui');
	}

	public function validatePassword(string $name = 'password'){
		return $this->name($name)
					->required()
					->minLength(1);
	}

	public function validateEmail(string $name = 'email'){
		return $this->name($name)
					->required()
					->email();
	}

	public function validateUploadImage(string $name = 'image'){
		return $this->name($name)
					->fileRequired()
					->fileMinSize(10) // min 10 bytes
					->fileMaxSize(15 * 1024 * 1024) // max 15 MiB
					->fileAcceptTypes('image/jpeg', 'image/jpg', 'image/png', 'image/gif')
					->fileAcceptExts('jpeg', 'jpg', 'png', 'apng', 'gif');
	}

	public function __call(string $method, array $params = []){
		$var = $this->vars[$this->key] ?? null;
		$continue = true;
		$error = 'Invalid "'. $this->key .'" format for filter "'.$method.'".';

		switch ($method) {
			// for string data
			case 'required':
				$continue = isset($this->vars[$this->key]);
				break;			

			case 'minLength':
				$continue = strlen($var) >= $params[0];
				break;			

			case 'maxLength':
				$continue = strlen($var) <= $params[0];
				break;

			case 'regexp':
				$continue = preg_match($params[0], $var);
				break;			

			case 'numeric':
				$continue = is_numeric($var);
				break;

			case 'double':
				$continue = is_double($var);
				break;

			case 'float':
				$continue = is_float($var);
				break;

			case 'int':
				$continue = preg_match('#^[0-9]+$#Ui', $var) || is_int($var);
				break;

			case 'uriString':
				$continue = preg_match('#^[A-Za-z0-9\-\.]+$#Ui', $var);
				break;

			case 'array':
				$continue = is_array($var);
				break;			

			case 'json':
				json_decode($var);
				$continue = strlen($var) == 0 || (json_last_error() == JSON_ERROR_NONE);
				break;		

			case 'string':
				$continue = is_string($var);
				break;

			case 'email':
				$continue = substr_count($var, '@') == 1;
				break;

			case 'equals':
				$values = is_array($params[0]) ? $params[0] : $params;
				$continue = in_array($var, $values);
				break;

			case 'phone':
				$var = str_replace(['+', ' ', '-', '(', ')', '.', "\t"], '', $var);
				$continue = is_numeric($var);
				$var = '+' . $var;
				break;

			// for upload files
			case 'file':
			case 'fileRequired':
				$continue = isset($this->vars[$this->key]) && 
							isset($this->vars[$this->key]['name']) && 
							isset($this->vars[$this->key]['type']) && 
							isset($this->vars[$this->key]['tmp_name']) && 
							isset($this->vars[$this->key]['size']);
				break;			

			case 'fileMinSize':
				$continue = ($var['size'] ?? 0) >= $params[0];
				break;

			case 'fileMaxSize':
				$continue = ($var['size'] ?? 0) <= $params[0];
				break;

			case 'fileAcceptType':
			case 'fileAcceptTypes':
				$types = is_array($params[0]) ? $params[0] : $params;
				$continue = in_array($var['type'], $types);
				break;

			case 'fileAcceptExt':
			case 'fileAcceptExts':
				$exts = is_array($params[0]) ? $params[0] : $params;
				$ext = explode('.', $var['name']);
				$ext = strtolower(end($ext));
				$continue = in_array($ext, $exts);
				break;

			// @togo image size	(min, max)
			
			default:
				$continue = false;
				$error = 'Invalid filter name "'.$method.'"';
		}

		if(!$continue){
			$this->invalidKeys[$this->key] = $error;
		} else {
			$this->vars[$this->key] = $var;
		}

		return $this;
	}

	public function assert() : array {
		if(sizeof($this->invalidKeys) > 0){
			throw new ValidateException('Validate error', 0, ['invalidKeys' => $this->invalidKeys]);
		}

		return $this->vars;
	}

	public function assertFiles(string $path = null) : array {
		$path = is_null($path) ? DS : $path;
		$files = $this->assert();
		$saved = [];

		foreach ($files as $key => $value) {
			if(!isset($value['name'])) continue;
			$ext = explode('.', $value['name']);
			$ext = strtolower(end($ext));
			$filename = uniqid($key . '_') . '.' . $ext;
			$savePath = $path . '/' . $filename;
			if(!move_uploaded_file($value['tmp_name'], $savePath)){
				throw new ValidateException('Can not save uploaded file', 1, [
					'files' => $files, 
					'key' => $key, 
					'savePath' => $savePath, 
				]);
			}

			$saved[$key] = $savePath;
		}
		

		return $saved;
	}

	public function getInvalidKeys() : array {
		return array_keys($this->invalidKeys);
	}
}