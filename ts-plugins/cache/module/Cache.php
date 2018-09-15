<?php
namespace tsframe\module;

use tsframe\module\database\Database;

class Cache{
	const TYPE_VAR = "var";
	const TYPE_FILE = "file";
	const TYPE_DATABASE = "db";

	/**
	 * Максимальный срок хранения - 5 лет
	 */
	const MAX_TIME = 60*60*24*365*5;

	/**
	 * Хранимые данные в формате: [key][data=>,update=>]
	 * @var array
	 */
	protected static $cachedVars = [];

	/**
	 * Load cached data
	 * @param  string       	$type     Cache::TYPE_FILE | Cache::TYPE_DATABASE | Cache::TYPE_VAR
	 * @param  string       	$key      Field name
	 * @param  callable|mixed  	$getValue Callback function, returns value
	 * @param  int     			$liveTime Time to live | null - MAX_TIME
	 * @param  bool    			$update   is update required
	 * @return [type]                 [description]
	 */
	public static function load(string $type, string $key, $getValue, int $liveTime = null, bool $update = false){
		$now = time();
		$liveTime = $now + (is_null($liveTime) ? self::MAX_TIME : $liveTime);
		$updateRequired = $now > $liveTime || $update;

		// Если обновление не нужно, прочитаем данные из кеша
		if(!$updateRequired){
			$cached = self::getCached($type, $key);
			if(is_array($cached)){
				if($now < $cached['update']){
					return $cached['data'];
				}
			}
		}

		// Если выполнение функции не прервалось return, нужно обновлять данные
		if(is_callable($getValue)){
			$data = call_user_func($getValue);
		} else {
			$data = $getValue;
		}
		self::setCached($type, $key, $data, $liveTime);

		return $data;
	}

	/**
	 * Cache to variable
	 * @param  string   $key     
	 * @param  callable $getValue 
	 * @param  int|null $liveTime Time to live | null - MAX_TIME
	 * @param  bool  	$update
	 * @return mixes
	 */
	public static function toVar(string $key, callable $getValue, int $liveTime = null, bool $update = false){
		return self::load(self::TYPE_VAR, $key, $getValue, $liveTime, $update);
	}

	/**
	 * Cache to database
	 * @param  string   $key     
	 * @param  callable $getValue 
	 * @param  int|null $liveTime Time to live | null - MAX_TIME
	 * @param  bool  	$update
	 * @return mixes
	 */
	public static function toDatabase(string $key, callable $getValue, int $liveTime = null, bool $update = false){
		return self::load(self::TYPE_DATABASE, $key, $getValue, $liveTime, $update);
	}

	/**
	 * Cache to file
	 * @param  string   $key     
	 * @param  callable $getValue 
	 * @param  int|null $liveTime Time to live | null - MAX_TIME
	 * @param  bool  	$update
	 * @return mixes
	 */
	public static function toFile(string $key, callable $getValue, int $liveTime = null, bool $update = false){
		return self::load(self::TYPE_FILE, $key, $getValue, $liveTime, $update);
	}

	/**
	 * Save data to cache
	 * @param string $type   
	 * @param string $key    
	 * @param [type] $data   
	 * @param int    $update = Live time + now
	 */
	protected static function setCached(string $type, string $key, $data, int $update){
		switch($type) {
			case self::TYPE_VAR :
				self::$cachedVars[$key] = [
					'data' => $data,
					'update' => $update
				];
				break;
			
			case self::TYPE_FILE :
				$cacheFile = TEMP . 'cached_' . md5($key) . '.json';
				$save = json_encode(['data' => $data, 'update' => $update]);
				file_put_contents($cacheFile, $save);
				break;

			case self::TYPE_DATABASE :
				Database::prepare('INSERT INTO `cache` VALUES (:key, :value, :update) ON DUPLICATE KEY UPDATE `value` = :value, `update` = :update')
						->bind('key', $key)
						->bind('value', json_encode($data))
						->bind('update', $update)
						->exec();

				break;
		}
	}	

	/**
	 * Get data from cache
	 * @param  string $type 
	 * @param  string $key  
	 * @return array(data, update) | false
	 */
	protected static function getCached(string $type, string $key){
		switch($type) {
			case self::TYPE_VAR :
				if(!isset(self::$cachedVars[$key])) break;

				return [
					'data' => self::$cachedVars[$key]['data'] ?? null,
					'update' => self::$cachedVars[$key]['update'] ?? self::MAX_TIME
				];
			
			case self::TYPE_FILE :
				$cacheFile = TEMP . 'cached_' . md5($key) . '.json';
				if(!file_exists($cacheFile)) break;

				$data = json_decode(file_get_contents($cacheFile), true);
				if(!is_array($data)) break;

				return [
					'data' => $data['data'] ?? null,
					'update' => $data['update'] ?? self::MAX_TIME
				];

			case self::TYPE_DATABASE :
				$db = Database::prepare('SELECT * FROM `cache` WHERE `key` = :key')
						->bind('key', $key)
						->exec()
						->fetch();

				if(!isset($db[0])) break;

				return [
					'data' => json_decode($db[0]['value'], true),
					'update' => $db[0]['update']
				];
		}

		return false;
	}
}