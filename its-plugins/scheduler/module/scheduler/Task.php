<?php 
namespace tsframe\module\scheduler;

use Cron\CronExpression;
use tsframe\Hook;
use tsframe\module\Logger;
use tsframe\module\database\Database;

class Task {
	protected $name;
	protected $period;
	protected $lastExec;

    public function __construct(string $taskName, string $period, int $lastExec = 0){
        $this->name = $taskName;
        $this->period = $period;
        $this->lastExec = ($lastExec == 0) ? time() : $lastExec;
    }

    /**
     * Обновить параметры из базы данных
     */
    public function refreshParams(): bool {
        $query = Database::exec("SELECT *, UNIX_TIMESTAMP(`last-exec`) ts FROM `tasks` WHERE `name` = :name")
                        ->bind('name', $this->name)
                        ->fetch();

        foreach ($query as $task) {
            $this->period = $task['period'];
            $this->lastExec = ($task['ts'] == 0) ? time() : $task['ts'];
            return true;
        }

        return false;
    }

    /**
     * Получить следующее время запуска
     * @return [type] [description]
     */
    public function getRunDate(): int {
    	$cron = CronExpression::factory($this->period);
		return $cron->getNextRunDate(date('Y-m-d H:i:s', $this->lastExec))->format('U');
    }

    /**
     * Необходим ли запуск текущей задачи
     * @return bool
     */
    public function runRequired(): bool {
    	return $this->getRunDate() <= time();
    }

    /**
     * Обновить время запуска
     */
    public function update(){
    	Database::exec('UPDATE `tasks` SET `last-exec` = CURRENT_TIMESTAMP() WHERE `name` = :name', ['name' => $this->name]);
    	$this->lastExec = time();
    }

    /**
     * Удалить задачу
     */
    public function delete(){
    	Database::exec('DELETE FROM `tasks` WHERE `name` = :name', ['name' => $this->name]);
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getLastExec(): int {
        return $this->lastExec;
    }

    /**
     * @return string
     */
    public function getPeriod(): string {
        return $this->period;
    }

    /**
     * Запустить задачу
     * 
     * @param   bool $ignoreLastExec    Если true, то не будет проверки даты запуска и задача будет запущена в любом случае
     * @return  bool
     */
    public function run(bool $ignoreLastExec = false): bool {
        if($this->runRequired() || $ignoreLastExec){
            $this->update();

            $logData = [
                'now' => time(), 
                'last-exec' => date('Y-m-d h:i:s', $this->getLastExec()),
                'period' => $this->getPeriod(), 
                'ignore-last-exec' => $ignoreLastExec
            ];

            Hook::call('scheduler.task.' . $this->getName(), [$this], function($return) use ($logData){
                if($return !== false){
                    $lresult = 'successfully';
                } else {
                    $lresult = 'unsuccessfully';
                }
                Logger::scheduler()->debug('Task "'.$this->getName().'" started ' . $lresult, $logData);

            }, function($error) use ($logData){
                $logData['error_message'] = $error->getMessage();
                $logData['error_code'] = $error->getCode();
                Logger::scheduler()->error('Task "' . $this->getName() . '" throws exception [' . get_class($error) . ']', $logData);
            });

            return true;
        }

        return false;
    }
}