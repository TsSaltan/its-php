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
		'twilio.account' => ['type' => 'text', 'placeholder' => 'INPUT_YOUR_ACCOUNT_ID'],
		'twilio.token' => ['type' => 'text', 'placeholder' => 'INPUT_YOUR_SECRET_TOKEN'],
		'twilio.phone' => ['type' => 'text', 'placeholder' => 'INPUT_YOUR_DEFAULT_PHONE_NUMBER_+123456...'],
	];
});