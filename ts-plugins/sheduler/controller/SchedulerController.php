<?php
namespace tsframe\controller;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\Plugins;
use tsframe\module\Logger;
use tsframe\module\scheduler\Scheduler;

/**
 * Контроллер для планировщика задач
 * @route GET /scheduler-task/
 */
class SchedulerController extends AbstractController{
	protected static $log = true;

	public static function setLog(bool $log){
		self::$log = $log;
	}

	public function response(){
		// Для браузера - никакого доступа
		if(Http::isBrowser()){
			$this->responseCode = Http::CODE_ACCESS_DENIED;
			$this->responseBody = "Access denied";
			return;
		}

		$tasks = Scheduler::getTasks();
		Hook::call('scheduler.start', [$tasks], null, function(){});

		foreach ($tasks as $task){
			if($task->runRequired()){
				$logData = [
					'now' => time(), 
					'last-exec' => date('Y-m-d h:i:s', $task->getLastExec()),
					'period' => $task->getPeriod(), 
				];

				Hook::call('scheduler.task.' . $task->getName(), [$task], function($return) use ($task, $logData){
					if($return !== false){
						$lresult = 'successfully';
						$task->update();
					} else {
						$lresult = 'unsuccessfully';
					}

					if(self::$log) Logger::scheduler()->debug('Task "'.$task->getName().'" '.$lresult.' runned', $logData);

				}, function($error) use ($task, $logData){
					$logData['error_message'] = $error->getMessage();
					$logData['error_code'] = $error->getCode();
					if(self::$log) Logger::scheduler()->error('Catch exception ('.get_class($error).') on running task "'.$task->getName().'"', $logData);
				});
			}
		}

		$this->responseCode = Http::CODE_OK;
		$this->responseBody = "Tasks updated";
	}
}