<?php
/**
 * Страница со сводкой и статистикой для админов
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Http;
use tsframe\PluginInstaller;
use tsframe\Plugins;
use tsframe\controller\SummaryDashboard;
use tsframe\module\Logger;
use tsframe\module\Mailer;
use tsframe\module\menu\MenuItem;
use tsframe\module\push\WebPushClient;
use tsframe\module\push\WebPushQueue;
use tsframe\module\scheduler\Scheduler;
use tsframe\module\scheduler\Task;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('dashboard', 'database', 'meta', 'scheduler');

	return [
		PluginInstaller::withKey('access.summary')
					->setType('select')
					->setDescription("Права доступа: просмотр страницы со сводкой и статистикой")
					->setDefaultValue(UserAccess::Admin)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('menu.render.dashboard-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Сводка', ['url' => Http::makeURI('/dashboard/summary'), 'fa' => 'dashboard', 'access' => UserAccess::getAccess('summary')]));
});

// Задача для рассылки уведомлений (1 раз в полчаса)
Hook::registerOnce('app.install', function() {
	Scheduler::addTask('summary-notify', '30 * * * *');
});

// Уведомление админа о критических ошибках
Hook::register('scheduler.task.summary-notify', function(Task $task) {
	$fromTs = SummaryDashboard::getErrorTs();
	$errorCount = Logger::getCount('*', Logger::LEVEL_CRITICAL, $fromTs);

	if($errorCount == 0) return true;

	$access = UserAccess::getAccess('summary');
	$link = Http::makeURI('/dashboard/summary');

	// 1. Уведомление на почту
	if(Plugins::isEnabled('mailer')){
		try {
			$users = User::get(['access', $access]);
			$mail = new Mailer;
			foreach ($users as $user) {
				$mail->addAddress($user->get('email'), $user->get('login'));
			}
			$mail->isHTML(true);
			$mail->Subject = 'Уведомление об ошибках | ' . $_SERVER['HTTP_HOST'];
	     	$mail->Body = "<p>На сайте обнаружены ошибки, пожалуйста, проверьте их: <a href='$link'>$link</a></p>";
	     	$mail->send();
     	}
     	catch(\Exception $e){

     	}
	}

	// 2. Web-Push уведомления
	if(Plugins::isEnabled('web-push')){
		try {
			$clients = WebPushClient::findIdsByParams('*', '*', $access);
			WebPushQueue::add($clients, 'Обнаружены ошибки | ' . $_SERVER['HTTP_HOST'], "На сайте обнаружены ошибки, пожалуйста проверьте их!", $link, 'https://www.iconsdb.com/icons/download/red/warning-256.png');
		}
		catch(\Exception $e){

     	}
	}

	return true;
});