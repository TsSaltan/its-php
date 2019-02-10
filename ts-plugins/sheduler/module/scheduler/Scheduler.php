<?php 
namespace tsframe\module\scheduler;

use tsframe\exception\BaseException;
use tsframe\module\database\Database;
use tsframe\module\scheduler\Task;

class Scheduler {
	/**
	 * Добавление новой задачи
	 * @param string $taskName Уникальное имя задачи
	 * @param string $period   Период (в формате crontab)
	 *	-    -    -    -    -
	 * 	|    |    |    |    |
	 *	|    |    |    |    |
	 *	|    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
	 *	|    |    |    +---------- month (1 - 12)
	 *	|    |    +--------------- day of month (1 - 31)
	 *	|    +-------------------- hour (0 - 23)
	 *	+------------------------- min (0 - 59)
	 *	или @daily
	 *	или @monthly
	 *	
	 *	@return Task
	 */
    public static function addTask(string $taskName, string $period): Task {
        Database::exec("INSERT INTO `tasks` (`name`, `period`) VALUES (:name, :period) ON DUPLICATE KEY UPDATE `period` = :period", ['name' => $taskName, 'period' => $period]);
        return new Task($taskName, $period);
    }

    /**
     * Получить задачу по её имени
     * @param  string $taskName Имя задачи
     * @return Task
     * @throws BaseException
     */
    public static function getTask(string $taskName): Task {
        $query = Database::exec("SELECT *, UNIX_TIMESTAMP(`last-exec`) ts FROM `tasks` WHERE `name` = :name", ['name' => $taskName])->fetch();
        if(!isset($query[0])) throw new BaseException('Invalid task name "' . $taskName . '"');

        return new Task($query[0]['name'], $query[0]['period'], $query[0]['ts']);
    }

    /**
     * Получить список задач для выполнения
     * @return Task[]
     */
    public static function getTasks(): array {
        $query = Database::exec("SELECT *, UNIX_TIMESTAMP(`last-exec`) ts FROM `tasks`")->fetch();
        $tasks = [];
        foreach ($query as $task) {
        	$tasks[] = new Task($task['name'], $task['period'], $task['ts']);
        }

        return $tasks;
    }
}