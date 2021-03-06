<?php
/**
 * Отображение логов в Dashboard
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\Logger;
use tsframe\module\database\Database;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\SingleUser;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('logger', 'dashboard');

	return [
		PluginInstaller::withKey('access.log')
					->setType('select')
					->setDescription("Права доступа: просмотр системных логов")
					->setDefaultValue(UserAccess::Admin)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

Hook::registerOnce('app.init', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Логи', ['url' => Http::makeURI('/dashboard/logs'), 'fa' => 'list-alt', 'access' => UserAccess::getAccess('log')]));
});