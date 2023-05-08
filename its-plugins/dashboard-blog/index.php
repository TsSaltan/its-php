<?php
namespace tsframe;

use tsframe\Hook;
use tsframe\Http;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('blog', 'dashboard');

	return [
		PluginInstaller::withKey('access.blog')
					->setType('select')
					->setDescription("Права доступа: Публикация и редактирование записей в блоге")
					->setDefaultValue(UserAccess::Moderator)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

Hook::registerOnce('app.init', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('menu.render.dashboard-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem(__('menu/blog-writes'), ['url' => Http::makeURI('/dashboard/blog/posts'), 'fa' => 'list', 'access' => UserAccess::getAccess('blog')], 'blog'), 10);
	$menu->add(new MenuItem(__('menu/blog-categories'), ['url' => Http::makeURI('/dashboard/blog/categories'), 'fa' => 'list-ul', 'access' => UserAccess::getAccess('blog')], 'blog-categories'), 11);
});
