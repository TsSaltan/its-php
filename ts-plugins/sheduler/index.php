<?php
/**
 * Планировщик задач (по типу крона)
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\scheduler\Scheduler;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('database', 'logger');
});

/**
 * Пример использования
 */
function(){
	// Добавление новой задачи (например при установке плагина)
	Hook::registerOnce('plugin.install', function(){
		Scheduler::addTask('my-super-task', '@daily');
	});

	// Выполнение задачи
	// Ручной вариант:
	$task = Scheduler::getTask('my-super-task');
	if($task->runRequired()){
		// todo my task
		$task->update();
	}

	// Автоматический вариант, когда настроено выполнение SchedulerController
	Hook::register('scheduler.task.my-super-task', function(Task $task){
		// todo my task
		return true;

		// if error
		return false;
	});
};