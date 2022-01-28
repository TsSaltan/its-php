<?php
namespace tsframe\module;

use tsframe\Config;
use tsframe\Reflect;
use tsframe\exception\BaseException;
use tsframe\module\database\Database;

/**
 * @see https://habr.com/ru/post/456676/
 * @example (new Logger('logs-section'))->add(Logger::LEVEL_DEBUG, $message, ['debug' => 'data']);
 * @example Logger::section()->add(Logger::LEVEL_DEBUG, $message, ['debug' => 'data']);
 * @example Logger::section()->debug($message, ['debug' => 'data']);
 * @example Logger::section()->notice($message, ['debug' => 'data']);
 * @example Logger::section()->error($message, ['debug' => 'data']);
 */
class Logger {
	/**
	 * Подробная информация для отладки
	 */
	CONST LEVEL_DEBUG = 0;
    
    /**
     * Интересные события
     */
    CONST LEVEL_INFO = 1;
    
    /**
     * Существенные события, но не ошибки
     */
    CONST LEVEL_NOTICE = 2;
    
    /**
     * Исключительные случаи, но не ошибки
     */
    CONST LEVEL_WARNING = 3;
    
    /**
     * Ошибки исполнения, не требующие сиюминутного вмешательства
     */
    CONST LEVEL_ERROR = 4;
    
    /**
     * Критические состояния (компонент системы недоступен, неожиданное исключение)
     * Cобытие, когда сбой даёт компонент системы, который очень важен и всегда должен работать. Это уже сильно зависит от того, чем занимается система. Подходит для событий, о которых важно оперативно узнать, даже если оно произошло всего раз.
     */
    CONST LEVEL_CRITICAL = 5;

    /**
     * Действие требует безотлагательного вмешательства
     * Система сама может продиагностировать своё состояние, например, задачей по расписанию, и в результате записать событие с этим уровнем. Это могут быть проверки подключаемых ресурсов или что-то конкретное, например, баланс на счету используемого внешнего ресурса.
     */
    CONST LEVEL_ALERT = 6;

    /**
     * Система не работает
     * Уровень для внешних систем, которые могут посмотреть на вашу систему и точно определить, что она полностью не работает, либо не работает её самодиагностика.
     */
    CONST LEVEL_EMERGENCY = 7;

    /**
     * Список неудаляемых типов
     * @var array
     */
    protected static $unremovableSections = [];

    public static function getUnremovableSections(): array {
    	return self::$unremovableSections;
    }

    public static function isUnremovableSection(string $section): bool {
    	return in_array(strtolower($section), self::$unremovableSections);
    }

    public static function setUnremovableSection(string $section){
    	self::$unremovableSections[] = strtolower($section);
    	self::$unremovableSections = array_unique(self::$unremovableSections);
    }

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

    /**
     * Получить количество запичей
     * @param  string      $section 
     * @param  int|integer $level   Минимальный уровень
     * @param  int|integer $fromTs  Метка времени, с которой считтать логи
     * @param  int|integer $toTs  Метка времени, до которой считтать логи
     * @return int
     */
	public static function getCount(string $section = '*', int $level = -1, int $fromTs = -1, int $toTs = -1): int {
		$sql = 'SELECT COUNT(*) c FROM `logger` WHERE `level` >= :level';
		
		if($section != '*'){
			$sql .= ' AND `section` = :section';
		}

		if($fromTs > -1){
			$sql .= ' AND `date` >= from_unixtime(:from_ts)';
		}

		if($toTs >= $fromTs && $toTs > 0){
			$sql .= ' AND `date` <= from_unixtime(:to_ts)';
		}

		$q = Database::prepare($sql);
		$q->bind('level', $level);

		if($section != '*'){
			$q->bind('section', $section);
		}

		if($fromTs > -1){
			$q->bind('from_ts', $fromTs);
		}


		if($toTs >= $fromTs && $toTs > 0){
			$q->bind('to_ts', $toTs);
		}

		$logs = $q->exec()->fetch();
		return $logs[0]['c'] ?? -1;
	}

	public static function getList(string $section = '*', int $level = -1, int $fromTs = -1, int $toTs = -1, int $offset = 0, int $count = 0): array {
		$limits = (($count > 0) ? 'LIMIT ' . $count : '') . ' ' . (($offset > 0) ? 'OFFSET ' . $offset : '');

		$sqlWhere = '`level` >= :level';

		if($section != '*'){
			$sqlWhere .= ' AND `section` = :section';
		} 

		if($fromTs > -1){
			$sqlWhere .= ' AND `date` >= from_unixtime(:from_ts)';
		}

		if($toTs >= $fromTs && $toTs > 0){
			$sqlWhere .= ' AND `date` <= from_unixtime(:to_ts)';
		}

		$q = Database::prepare('SELECT * FROM `logger` WHERE ' . $sqlWhere . ' ORDER BY `date` DESC ' . $limits);

		if($section != '*'){
			$q->bind('section', $section);
		} 

		if($fromTs > -1){
			$q->bind('from_ts', $fromTs);
		}

		if($toTs >= $fromTs && $toTs > 0){
			$q->bind('to_ts', $toTs);
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
	public static function delete(string $section, int $level = -1, int $timestamp = -1): bool {
		if(self::isUnremovableSection($section)){
			throw new BaseException('Try to delete unremovable section "'. $section .'"');
			return false;
		}

		$sql = 'DELETE FROM `logger` WHERE `section` = :section';

		if($level > -1) $sql .= ' AND `level` = :level';
		if($timestamp > -1) $sql .= ' AND `date` <= from_unixtime(:ts)';

		$query = Database::prepare($sql); 
		$query->bind('section', $section);

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
		sort($return);
		return $return;		
	}

	/**
	 * Получить размер логов в базе данных
	 * @return int
	 */
	public static function getSize(): int {
		return Database::getSize('logger');
	}

	/**
	 * Выбрать раздел логов для их дальнейшего добавления (алиас для конструктора)
	 */
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

	/**
	 * Сделать текущий раздел неудаляемым (т.е. нельзя удалить через админку)
	 */
	public function makeUnremovable(){
		self::setUnremovableSection($this->section);
	}

	public function isUnremovable(): bool {
		return self::isUnremovableSection($this->section);
	}
}