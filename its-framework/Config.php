<?php
namespace tsframe;

/**
 * Класс для хранения конфигов в формате json
 * Config::get('param');
 * Config::get('param.path');
 * Config::set('param', 'value');
 * Config::set('param,path', 'value');
 */
class Config {
	/**
	 * Разделитель для ключей
	 * @var string
	 */
	protected static $separator = '.';

	/**
	 * Файл с настройками
	 * @var string
	 */
	protected static $file;
	
	/**
	 * Закешированные данные
	 * @var array
	 */
	protected static $cache = [];

	public static function load(string $file){
		static::$file = $file;

		if(file_exists(static::$file)){
			static::$cache = json_decode(file_get_contents(static::$file), true);
		}
	}

	protected static function save(){
		$data = json_encode(static::$cache, JSON_PRETTY_PRINT);
		file_put_contents(static::$file, $data);
	}

	/**
	 * Получить ссылку на раздел настроек
	 */
	protected static function &getPath(string $path){
		$path = explode(static::$separator, $path);
		$data = &static::$cache;

		foreach ($path as $p) {
			$data = &$data[$p];
		}

		return $data;
	}

	public static function get(string $path = '*'){
		if($path == '*') return static::$cache;

		$data = static::getPath($path);
		return $data;
	}	

	public static function set(string $path = '*', $value){
		if($path == '*') $data =& static::$cache;
		else $data =& static::getPath($path);
		$data = $value;
		static::save();
	}

	public static function isset(string $path) {
		$path = explode(static::$separator, $path);
		$data = &static::$cache;

		foreach ($path as $p) {
			if(!isset($data[$p])) return false;
			$data = &$data[$p];
		}

		return true;
	}

	public static function unset(string $path): bool {
		$path = explode(static::$separator, $path);
		$data = &static::$cache;
		$len = sizeof($path);

		foreach ($path as $k => $p) {
			if(!isset($data[$p])) return false;

			if($k == $len-1){
				unset($data[$p]);
			} else {
				$data = &$data[$p];
			}
		}

		static::save();
		return true;
	}
}