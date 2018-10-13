<?
/**
 * Реферальная система пользователей 
 * Данные о пригласившем пользователе будут храниться в мета пользователя, ключ referrer
 * + поддержка сокращённых ссылок из https://app.bitly.com/
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

Hook::registerOnce('plugin.install.required', function(){
	return [
		'access.referrer.self' => ['type' => 'numeric', 'value' => UserAccess::Guest],
		'access.referrer.view' => ['type' => 'numeric', 'value' => UserAccess::Moderator],
		'bitly.accessToken' => ['type' => 'text', 'placeholder' => "Your token for app.bitly.com"],
	];
});

Hook::registerOnce('plugin.load', function(){
	Plugins::required('database', 'user', 'dashboard');
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