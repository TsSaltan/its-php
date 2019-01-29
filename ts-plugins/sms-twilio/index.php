<?
/**
 * Система отправки смс через https://twilio.com
 */
namespace tsframe;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('sms-base');
	Plugins::conflict('sms-smsc');
	return [
		PluginInstaller::withKey('twilio.account')
					->setType('text')
					->setDescription("ID аккаунта <a href='https://twilio.com/' target='_blank'>twilio</a>")
					->setRequired(true),

		PluginInstaller::withKey('twilio.token')
					->setType('text')
					->setDescription("Ключ доступа от <a href='https://twilio.com/' target='_blank'>twilio</a>")
					->setRequired(true),

		PluginInstaller::withKey('twilio.phone')
					->setType('text')
					->setDescription("Телефон по умолчанию для <a href='https://twilio.com/' target='_blank'>twilio</a>")
					->setRequired(true)
	];
});