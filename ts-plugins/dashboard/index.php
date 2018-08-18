<?
/**
 * Dashboard: админ-панель
 *
 * @hook template.dashboard.index
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

	Menu::create('dashboard-sidebar')
		->add(new MenuItem('Профиль', ['url' => Http::makeURI('/dashboard/'), 'fa' => 'user', 'access' => UserAccess::User]));

	Menu::create('dashboard-top')
		->add(new MenuItem('Выход', ['url' => Http::makeURI('/dashboard/logout'), 'fa' => 'sign-out', 'access' => UserAccess::User]));
});