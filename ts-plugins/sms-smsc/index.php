<?
/**
 * SMSC
 * Система отправки смс через https://smsc.ru/
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\UserAccess;
use tsframe\module\user\User;
use tsframe\view\TemplateRoot;
use tsframe\view\Template;
use tsframe\view\HtmlTemplate;

Hook::register('app.install', function(){
	if(is_null(Config::get('smsc'))){
		Config::set('smsc.login', "INPUT_YOUR_LOGIN");
		Config::set('smsc.password', "INPUT_YOUR_PASSWORD");
	}
});

Hook::registerOnce('plugin.load', function(){
	Plugins::required('sms-base');
});