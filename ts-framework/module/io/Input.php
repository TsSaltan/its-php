<?php
namespace tsframe\module\io;

use tsframe\exception\InputException;

/**
 * Валидатор входящих данных
 */
class Input extends Filter {
	/**
	 * Ссылка на обрабатываемые данные 
	 * @var &array
	 */
	protected $data;

	/**
	 * Текущий ключ
	 * @var string
	 */
	protected $currentKey;

	/**
	 * Данные, не прошедшие проверку
	 * @var array key => [value=>, filter => []]
	 */
	protected $invalid = [];

	/**
	 * Функция для постобработки переменной
	 * @var callable|null
	 */
	protected $varPostProcess;
	
	/**
	 * Конструкторы
	 */
	public function __construct(array &$data){
		$this->data = &$data;
	}

	public static function of(array &$data){
		return new self($data);
	}

	public static function get(){
		return new self($_GET);
	}	

	public static function post(){
		return new self($_POST);
	}

	public static function request(){
		return new self($_REQUEST);
	}

	public static function files(){
		return new self($_FILES);
	}

	/**
	 * Установка текущего ключа
	 * @param  string $key
	 */
	public function key(string $key){
		$this->currentKey = $key;
		$this->varPostProcess = null;
		return $this;
	}
	/**
	 * @alias key
	 */
	public function name(string $key){
		return $this->key($key);
	}

	/**
	 * Установить функцию для постобработки переменной
	 * @param  callable $callback
	 */
	public function varProcess(callable $callback){
		$this->varPostProcess = $callback;
		return $this;
	}

	/**
	 * Существуют ли данные для текущего ключа
	 * @return boolean
	 */
	public function isCurrentExists(): bool {
		return isset($this->data[$this->currentKey]);
	}

	/**
	 * Получить текущие данные
	 * @return mixed | null
	 */
	public function getCurrentData(){
		return $this->data[$this->currentKey] ?? null;
	}

	/**
	 * Получить текущий ключ
	 * @return string|null
	 */
	public function getCurrentKey(){
		return $this->currentKey;
	}

	/**
	 * Получить ключи, не прошедшие проверку
	 * @return array
	 */
	public function getInvalidKeys() : array {
		return array_keys($this->invalid);
	}

	/**
	 * Получить данные, их ключи и фильтры, не прошедшие проверку
	 * @return array
	 */
	public function getInvalid() : array {
		return $this->invalid;
	}	

	public function clearInvalidKey($key){
		unset($this->invalid[$key]);
	}

	/**
	 * Обращение к конкретному фильтру
	 * @param  string $method Имя фильтры
	 * @param  array  $params Дополнительные аргументы
	 */
	public function __call(string $method, array $params = []){
		$args = array_merge([$this], $params);		

		$result = $this->callFilter($method, $args);

		if($result !== true){
			$this->invalid[$this->currentKey]['filter'][] = $method;
			$this->invalid[$this->currentKey]['value'] = $this->getCurrentData();
		}

		if(is_callable($this->varPostProcess)){
			$this->data[$this->currentKey] = call_user_func($this->varPostProcess, $this->getCurrentData());
		}

		return $this;
	}

	/**
	 * Прошли ли данные валидацию
	 * @return boolean
	 */
	public function isValid(): bool {
		return sizeof($this->invalid) == 0;
	}

	public function assert(){
		if(!$this->isValid()) throw new InputException('Invalid input data', -1, ['invalid' => $this->getInvalid(), 'data' => $this->data]);
		return $this->data;
	}
}

/**
 * Добавление фильтров
 */

Input::addFilter(['referer', 'referrer'], function(Input $input, string $current = null){
	$referer = $_SERVER['HTTP_REFERER'] ?? null ;
	$refDomain = parse_url($referer, PHP_URL_HOST);

	if(is_null($current)){
		$current = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
	}
	$currentDomain = explode(':', $current)[0]; // Убираем порт, если он есть
	return $refDomain == $currentDomain;	
});

Input::addFilter('optional', function(Input $input){
	if(is_null($input->getCurrentData()) || strlen($input->getCurrentData()) == 0){
		$input->varProcess(function() use ($input){
			$input->clearInvalidKey($input->getCurrentKey());
		});
	}
	return true;
});

Input::addFilter('required', function(Input $input){
	return $input->isCurrentExists();
});

Input::addFilter('minLength', function(Input $input, int $length){
	return strlen($input->getCurrentData()) >= $length;
});

Input::addFilter('maxLength', function(Input $input, int $length){
	return strlen($input->getCurrentData()) <= $length;
});

Input::addFilter('length', function(Input $input, int $length){
	return strlen($input->getCurrentData()) == $length;
});

Input::addFilter('regexp', function(Input $input, string $reg){
	return preg_match($reg, $input->getCurrentData()) > 0;
});

Input::addFilter(['numeric', 'number'], function(Input $input){
	return is_numeric($input->getCurrentData());
});

Input::addFilter('double', function(Input $input){
	return is_double($input->getCurrentData());
});

Input::addFilter('float', function(Input $input){
	return is_float($input->getCurrentData());
});

Input::addFilter(['int', 'integer'], function(Input $input){
	return is_int($input->getCurrentData()) || preg_match('#^[0-9]+$#Ui', $input->getCurrentData());
});

Input::addFilter('array', function(Input $input){
	return is_array($input->getCurrentData());
});

Input::addFilter('string', function(Input $input){
	return is_string($input->getCurrentData());
});

Input::addFilter('json', function(Input $input){
	json_decode($input->getCurrentData());
	return json_last_error() == JSON_ERROR_NONE;
});

Input::addFilter('email', function(Input $input){
	return substr_count($input->getCurrentData(), '@') == 1;
});

Input::addFilter('ip', function(Input $input){
	return filter_var($input->getCurrentData(), FILTER_VALIDATE_IP) !== false;
});

Input::addFilter('ipv4', function(Input $input){
	return filter_var($input->getCurrentData(), FILTER_FLAG_IPV4) !== false;
});

Input::addFilter('ipv6', function(Input $input){
	return filter_var($input->getCurrentData(), FILTER_FLAG_IPV6) !== false;
});