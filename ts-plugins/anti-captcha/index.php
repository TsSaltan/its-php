<?
/**
 * Плагин для работы с https://anti-captcha.com/
 * Github https://github.com/AdminAnticaptcha/anticaptcha-php
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;

Hook::register('plugin.install', function(){
	return ['anticaptcha.apiKey' => ['type' => 'text', 'placeholder' => 'Anticapthca API key']];
});