<?php 
/**
 * Dashboard для WebPush Клиентов
 * 
 * - Необходимо добавить в крон выполнение sheduler, т.к. очередь зависит от крона 
 */

namespace tsframe;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use tsframe\App;
use tsframe\Config;
use tsframe\Http;
use tsframe\module\menu\MenuItem;
use tsframe\module\push\WebPushQueue;
use tsframe\module\scheduler\Scheduler;
use tsframe\module\scheduler\Task;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('geodata', 'scheduler', 'user', 'dashboard', 'web-push');
	
	return [
		PluginInstaller::withKey('access.webpush')
					->setType('select')
					->setDescription("Права доступа: доступ к базе данных web-push клиентов")
					->setDefaultValue(UserAccess::Admin)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

/**
 * Загрузка плагина
 */
Hook::registerOnce('app.init', function(){
	TemplateRoot::add('web-push', __DIR__ . DS . 'template' . DS . 'push');
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

/**
 * Добавляем пункт меню
 */
Hook::registerOnce('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Web-Push клиенты', ['url' => Http::makeURI('/dashboard/web-push-clients'), 'fa' => 'commenting', 'access' => UserAccess::getAccess('webpush')]));
});

/**
 * Очередь для рассылки пушей
 */
Hook::registerOnce('app.installed', function() {
	Scheduler::addTask('web-push-send', '*/5 * * * *');
}, Hook::MIN_PRIORITY);


/**
 * Разбор очереди пушей
 */
Hook::register('scheduler.task.web-push-send', function(Task $task) {
	$queues = WebPushQueue::getList();
	foreach ($queues as $queue) {
		$queue->send();
	}

	return true;
});