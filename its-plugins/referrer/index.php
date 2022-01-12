<?php
/**
 * Реферальная система пользователей 
 * Данные о пригласившем пользователе будут храниться в мета пользователя, ключ referrer
 * 
 * @hook template.dashboard.referrer
 * @hook referrer.invite (int $referrerId, SingleUser $referral)
 * @hook referrer.makeURI (string &$referrerURI, Referrer $referer)
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\user\Referrer;
use tsframe\module\user\UserAccess;
use tsframe\module\user\SingleUser;
use tsframe\module\user\User;
use tsframe\module\menu\MenuItem;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('database', 'user', 'dashboard');
	return [
		PluginInstaller::withKey('access.referrer.self')
					->setType('select')
					->setDescription("Права доступа: просмотр своего реферала")
					->setDefaultValue(UserAccess::Guest)
					->setValues(array_flip(UserAccess::getArray())),

		PluginInstaller::withKey('access.referrer.view')
					->setType('select')
					->setDescription("Права доступа: просмотр реферала другого пользователя")
					->setDefaultValue(UserAccess::Moderator)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('menu.render.dashboard-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Партнёрская программа', ['url' => Http::makeURI('/dashboard/referrer'), 'fa' => 'hand-o-right', 'access' => UserAccess::getAccess('referrer.self')]));
});

Hook::register('template.dashboard.auth', function(){
	if(isset($_GET['ref'])){
		setcookie('ref', $_GET['ref'], time()+60*60*24, '/');
	}
});

Hook::register('user.register', function(SingleUser $user){
	$refKey = $_REQUEST['ref'] ?? $_COOKIE['ref'] ?? null;
	if(!is_null($refKey)){
		$ref = new Referrer($user);
		$id = $ref->decodeID($refKey);
		if($id > 0){
			$ref->setReferrer($id);
			Hook::call('referrer.invite', [$id, $user]);
		}

		setcookie('ref', null, -1, '/');
	}	
});