<?php
/**
 * Страница со сводкой и статистикой для админов
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\PluginInstaller;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('dashboard', 'database', 'meta');

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