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

Hook::registerOnce('plugin.install', function(){
	Plugins::required('sms-base');
	return [
		PluginInstaller::withKey('smsc.login')
					->setType('text')
					->setDescription("Логин от кабинета <a href='https://smsc.ru/' target='_blank'>smsc.ru</a>")
					->setRequired(true),

		PluginInstaller::withKey('smsc.password')
					->setType('text')
					->setDescription("Пароль от кабинета <a href='https://smsc.ru/' target='_blank'>smsc.ru</a>")
					->setRequired(true),
	];
});