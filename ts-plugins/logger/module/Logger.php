<?php
namespace tsframe\module;

use tsframe\Config;
use tsframe\Reflect;
use tsframe\exception\BaseException;
use tsframe\module\database\Database;

/**
 * @see https://habr.com/ru/post/456676/
 */
class Logger {
	CONST LEVEL_DEBUG = 0; 		// Подробная информация для отладки
    CONST LEVEL_INFO = 1; 		// Интересные события
    CONST LEVEL_NOTICE = 2; 	// Существенные события, но не ошибки
    CONST LEVEL_WARNING = 3; 	// Исключительные случаи, но не ошибки
    CONST LEVEL_ERROR = 4; 		// Ошибки исполнения, не требующие сиюминутного вмешательства
    CONST LEVEL_CRITICAL = 5; 	// Критические состояния (компонент системы недоступен, неожиданное исключение)
    CONST LEVEL_ALERT = 6; 		// Действие требует безотлагательного вмешательства
    CONST LEVEL_EMERGENCY = 7; 	// Система не работает

    public static function getLevels(): array {
    	$constants = Reflect::getConstants(__CLASS__);
    	$levels = [];

    	foreach ($constants as $key => $value) {
    		if(stripos($key, 'LEVEL_') === 0){
    			$name = strtolower(str_replace('LEVEL_', '', $key));
				$levels[$name] = $value;    			
    		}
    	}
    	return $levels;
    }

	public static function getCount(string $section = '*'): int {
		if($section == '*'){
			$q = Database::prepare('SELECT COUNT(*) c FROM `logger`');
		} 
		else {
			$q = Database::prepare('SELECT COUNT(*) c FROM `logger` WHERE `section` = :section')->bind('section', $section);
		}
		
		$logs = $q->exec()->fetch();
		return $logs[0]['c'] ?? -1;
	}

	public static function getList(string $section = '*', int $level = -1, int $offset = 0, int $count = 0): array {
		$return = [];
		$limits = (($count > 0) ? 'LIMIT ' . $count : '') . ' ' . (($offset > 0) ? 'OFFSET ' . $offset : '');

		if($section == '*'){
			$q = Database::prepare('SELECT * FROM `logger` WHERE `level` >= :level ORDER BY `date` DESC ' . $limits);
		} 
		else {
			$q = Database::prepare('SELECT * FROM `logger` WHERE `section` = :section AND `level` >= :level ORDER BY `date` DESC ' . $limits);
			$q->bind('section', $section);
		} 
		$q->bind('level', $level);
		$logs = $q->exec()->fetch();

		foreach ($logs as $log) {
			$return[] = ['date' => $log['date'], 'data' => json_decode($log['data'], true)];
		}

		return $return;
	}


	/**
	 * Очистить логи
	 * @param  string      $type      Тип логов ('*' = все)
	 * @param  int|integer $timestamp Метка времени, ДО которой логи будут очищены
	 * @return bool
	 */
	public static function delete(string $type = '*', int $timestamp = 0): bool {
		$query = ($type == '*') 
					? Database::prepare('DELETE FROM `log` WHERE 1 = 1')
					: Database::prepare('DELETE FROM `log` WHERE `type` = :type AND `date` <= from_unixtime(:ts)');
		
		$query->bind('ts', $timestamp);
		if($type != '*') $query->bind('type', $type);

		return $query->exec()->affectedRows() > 0;
	}

	public static function getSections(): array {
		$return = [];
		$types = Database::exec('SELECT DISTINCT `section` FROM `logger`')->fetch();

		foreach ($types as $key => $value) {
			$return[] = $value['section'];
		}

		return $return;		
	}

	public static function __callStatic(string $name, array $args){
		return new self($name);
	}

	protected $section;

	public function __construct(string $section = 'default'){
		$this->section = $section;
	}

	public function add(int $level, string $message, array $data = []): bool {
		$data['message'] = $message;

		try {
			return Database::prepare('INSERT INTO `logger` (`id`, `level`, `section`. `data`) VALUES (UUID(), :level, :section, :data)')
				->bind('level', $level)
				->bind('section', strtolower($this->section))
				->bind('data', json_encode($data))
				->exec()
				->lastInsertId() > 0;
		} catch (\Exception $e){
			return false;
		}
	}

	public function __call(string $method, array $params){
		$method = strtolower($method);
		$levels = self::getLevels();
		if(!isset($levels[$method])){
			throw new BaseException('Invalid log level "'.$method.'"', 0, ['levels' => $levels]);
		}

		$level = $levels[$method];
		$message = (string) $params[0];
		$data = (array) ($params[1] ?? []);
		return $this->add($level, $message, $data);
	}
}