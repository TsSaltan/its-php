<?
/**
 * Dashboard: админ-панель
 *
 * @hook template.dashboard.index
 * @hook template.dashboard.header
 * @hook template.dashboard.footer
 */
namespace tsframe;

use tsframe\module\database\Database;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\module\user\SocialLogin;
use tsframe\module\menu\Menu;
use tsframe\module\menu\MenuItem;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.load', function(){
	Plugins::required('database', 'user');

	TemplateRoot::addDefault(__DIR__ . DS . 'template');	
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');	
});

Hook::registerOnce('app.start', function(){
	Menu::create('dashboard-sidebar')
		->add(new MenuItem('Профиль', ['url' => Http::makeURI('/dashboard/'), 'fa' => 'user', 'access' => UserAccess::Guest]))
		->add(new MenuItem('Системные настройки', ['url' => Http::makeURI('/dashboard/config'), 'fa' => 'wrench', 'access' => UserAccess::getAccess('user.editConfig')]), -1);

	Menu::create('dashboard-top')
		->add(new MenuItem('Выход', ['url' => Http::makeURI('/dashboard/logout'), 'fa' => 'sign-out', 'access' => UserAccess::Guest]));

});