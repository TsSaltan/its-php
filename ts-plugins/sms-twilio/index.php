<?
/**
 * Система отправки смс через https://twilio.com
 */
namespace tsframe;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;

Hook::registerOnce('app.install', function(){
	if(is_null(Config::get('twilio'))){
		Config::set('twilio.account', "INPUT_YOUR_ACCOUNT_ID");
		Config::set('twilio.token', "INPUT_YOUR_SECRET_TOKEN");
		Config::set('twilio.phone', "INPUT_YOUR_DEFAULT_PHONE_NUMBER_+123456...");
	}
});

Hook::registerOnce('plugin.load', function(){
	Plugins::required('sms-base');
	Plugins::conflict('sms-smsc');
});