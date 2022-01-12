<?php
/**
 * Планировщик задач (по типу крона)
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\TelegramBot;
use tsframe\module\io\Output;
use tsframe\module\locale\Lang;
use tsframe\module\scheduler\Scheduler;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){

});

Hook::registerOnce('app.init', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template');
	Lang::addTranslationPath( __DIR__ . DS . 'translates');
});

Hook::register('template.dashboard.config', function(Template $tpl){
	$tgbotapi_token = TelegramBot::getDefaultToken();
	$tgbotapi_token = Output::of($tgbotapi_token)->quotes()->getData();
	$tpl->var('tgbotapi_token', $tgbotapi_token);

	$tgbotapi_uri = Http::makeURI('/telegram-bot-api');
	$tpl->var('tgbotapi_uri', $tgbotapi_uri);
	$tpl->inc('tgbotapi-config');
});