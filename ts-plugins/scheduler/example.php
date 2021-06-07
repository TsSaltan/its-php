<?php 
die;

/**
 * Пример использования
 */

// Добавление новой задачи (например при установке плагина)
// app.install выполняется после установки всех плагинов !!!
// Если нужно прописать задание в планировщик Scheduler, то гнеобходимо указать минимальный уровень приоритета !!!
Hook::registerOnce('app.install', function(){
	Scheduler::addTask('my-super-task', '@daily');
}, Hook::MIN_PRIORITY);

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