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

Hook::registerOnce('plugin.install.required', function(){
	return [
		'smsc.login' => ['type' => 'text', 'placeholder' => 'Your login for smsc'],
		'smsc.password' => ['type' => 'text', 'placeholder' => 'Your password for smsc']
	];
});

Hook::registerOnce('plugin.load', function(){
	Plugins::required('sms-base');
});