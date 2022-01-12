<?php 
/**
 * Система поддержки
 * @hook template.dashboard.support.menu (HtmlTemplate $tpl, int $chatId, string $chatRole="operator|client")
 * @hook template.dashboard.support.header (HtmlTemplate $tpl, int $chatId, string $chatRole="operator|client")
 * @hook template.dashboard.support.footer (HtmlTemplate $tpl, int $chatId, string $chatRole="operator|client")
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\menu\MenuItem;
use tsframe\module\support\Message;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('dashboard');
	return [
		PluginInstaller::withKey('access.support.client')
					->setType('select')
					->setDescription("Права доступа: возможность создавать сообщение в поддержке")
					->setDefaultValue(UserAccess::User)
					->setValues(array_flip(UserAccess::getArray())),

		PluginInstaller::withKey('access.support.operator')
					->setType('select')
					->setDescription("Права доступа: права оператора в поддержке")
					->setDefaultValue(UserAccess::Admin)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

Hook::registerOnce('app.start', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('menu.render.dashboard-sidebar', function(MenuItem $menu){
	$unread = Message::getUnreadCountForUser(User::current());
	$menu->add(new MenuItem('Поддержка', [
		'url' => Http::makeURI('/dashboard/support'), 
		'fa' => 'comments', 
		'access' => UserAccess::getAccess('support.client'), 
		'counter' => $unread
	]));
});

Hook::register('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$unread = Message::getUnreadCountForOperator();
	$menu->add(new MenuItem('Оператор поддержки', [
		'url' => Http::makeURI('/dashboard/operator'), 
		'fa' => 'support', 
		'access' => UserAccess::getAccess('support.operator'), 
		'counter' => $unread
	]));
});