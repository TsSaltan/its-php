<?
/**
 * Плагин для работы с https://anti-captcha.com/
 * Github https://github.com/AdminAnticaptcha/anticaptcha-php
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;

Hook::register('plugin.install.required', function(){
	return [
		'anticaptcha.apiKey' => ['type' => 'text', 'placeholder' => 'Anticapthca API key'],
	];
});

Hook::registerOnce('plugin.load', function(){
	
});