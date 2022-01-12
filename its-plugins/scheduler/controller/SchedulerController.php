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
 * @route GET /scheduler-task
 */
class SchedulerController extends AbstractController {
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
			$task->refreshParams();
			$task->run();
		}

		$this->responseCode = Http::CODE_OK;
		$this->responseBody = "Tasks updated";
	}
}