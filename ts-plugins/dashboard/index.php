<?php
/**
 * Dashboard: админ-панель
 *
 * @hook template.dashboard.index
 * @hook template.dashboard.config
 * @hook template.dashboard.header
 * @hook template.dashboard.footer
 * @hook template.dashboard.auth
 */
namespace tsframe;

use tsframe\controller\Dashboard;
use tsframe\module\Meta;
use tsframe\module\database\Database;
use tsframe\module\io\Output;
use tsframe\module\menu\Menu;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\SocialLogin;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

define('DASHBOARD_THEMES', __DIR__ . DS . 'template' . DS . 'dashboard' . DS . 'themes' . DS);

Hook::registerOnce('plugin.install', function(){
	Plugins::required('database', 'user');
});

Hook::registerOnce('plugin.load', function(){
	TemplateRoot::addDefault(__DIR__ . DS . 'template');	
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');	
});

Hook::registerOnce('app.start', function(){
	Menu::create('dashboard-sidebar');
	
	Menu::create('dashboard-admin-sidebar')
		->add(new MenuItem('Системные настройки', ['url' => Http::makeURI('/dashboard/config'), 'fa' => 'wrench', 'access' => UserAccess::getAccess('user.editConfig')]), 0);

	Menu::create('dashboard-top')
		->add(new MenuItem('Выход', ['url' => Http::makeURI('/dashboard/logout'), 'fa' => 'sign-out', 'access' => UserAccess::Guest]));
});

Hook::register('template.render', function($tpl){
	$tpl->var('siteName', Dashboard::getSiteName());
	$tpl->var('siteHome', Dashboard::getSiteHome());
	$tpl->var('siteIcon', Dashboard::getSiteIcon());
});