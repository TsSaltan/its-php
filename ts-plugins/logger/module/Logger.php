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

	public static function getCount(string $section = '*', int $level = -1): int {
		if($section == '*'){
			$q = Database::prepare('SELECT COUNT(*) c FROM `logger` WHERE `level` >= :level');
		} 
		else {
			$q = Database::prepare('SELECT COUNT(*) c FROM `logger` WHERE `section` = :section AND `level` >= :level')->bind('section', $section);
		}
		
		$q->bind('level', $level);
		$logs = $q->exec()->fetch();
		return $logs[0]['c'] ?? -1;
	}

	public static function getList(string $section = '*', int $level = -1, int $offset = 0, int $count = 0): array {
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
		$levels = array_flip(self::getLevels());
		foreach ($logs as $k => $log) {
			$logs[$k]['data'] = json_decode($log['data'], true);
			$logs[$k]['levelName'] = $levels[$log['level']] ?? -1;
		}

		return $logs;
	}


	/**
	 * Очистить логи
	 * @param  string      $section 	'*' = все
	 * @param  int|integer $level 		уровень ошибки, c которым будет удалены логи
	 * @param  int|integer $timestamp 	Метка времени, ДО которой логи будут очищены
	 * @return bool
	 */
	public static function delete(string $section = '*', int $level = -1, int $timestamp = -1): bool {
		$sql = 'DELETE FROM `logger` WHERE 1=1';

		if($section != '*') $sql .= ' AND `section` = :section';
		if($level > -1) $sql .= ' AND `level` = :level';
		if($timestamp > -1) $sql .= ' AND `date` <= from_unixtime(:ts)';

		$query = Database::prepare($sql); 

		if($section != '*') $query->bind('section', $section);
		if($level > -1) $query->bind('level', $level);
		if($timestamp > -1) $query->bind('ts', $timestamp);

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

	/**
	 * Получить размер логов в базе данных
	 * @return int
	 */
	public static function getSize(): int {
		return Database::getSize('logger');
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
			return Database::prepare('INSERT INTO `logger` (`id`, `level`, `section`, `data`) VALUES (UUID(), :level, :section, :data)')
				->bind('level', $level)
				->bind('section', strtolower($this->section))
				->bind('data', json_encode($data))
				->exec()
				->affectedRows() > 0;
		} catch (\Exception $e){
			throw new BaseException('Cannot add log entry: ' . $e->getMessage(), 0, ['level' => $level, 'section' => strtolower($this->section), 'message' => $message, 'data' => $data]);
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