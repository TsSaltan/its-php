<?php
namespace tsframe\module;

use tsframe\Config;
use tsframe\module\database\Database;

class Log{
	protected static $logs = [];

	public static function add(string $message, string $type = 'default', array $meta = []): bool {
		$meta['message'] = $message;
		self::$logs[$type][] = $meta;

		return Database::prepare('INSERT INTO `log` (`id`, `type`, `data`) VALUES (UUID(), :type, :data)')
				->bind('type', $type)
				->bind('data', json_encode($meta))
				->exec()
				->lastInsertId() > 0;
	}

	public static function getCurrentLogs(): array {
		return self::$logs;
	}

	public static function getLogs(string $type): array {
		$return = [];
		$logs = Database::prepare('SELECT * FROM `log` WHERE `type` = :type ORDER BY `date` DESC')
					->bind('type', $type)
					->exec()
					->fetch();

		foreach ($logs as $log) {
			$return[] = ['date' => $log['date'], 'data' => json_decode($log['data'], true)];
		}

		return $return;
	}

	public static function getTypes(): array {
		$return = [];
		$types = Database::exec('SELECT DISTINCT `type` FROM `log`')
						->fetch();

		foreach ($types as $key => $value) {
			$return[] = $value['type'];
		}

		return $return;		
	}
}