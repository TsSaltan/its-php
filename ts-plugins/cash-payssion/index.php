<?php
/**
 * Интеграция с платежной системой Payssion
 * @link https://www.payssion.com/ru/index.html
 * @link https://github.com/payssion/payssion-php/
 */
namespace tsframe;

use tsframe\PluginInstaller;
use tsframe\module\user\Cash;
use tsframe\module\user\SingleUser;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;


Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::registerOnce('plugin.install', function(){
	return [
		PluginInstaller::withKey('payssion.api_key')
						->setType('text')
						->setDescription("Payssion API key")
						->setRequired(true),

		PluginInstaller::withKey('payssion.secret_key')
						->setType('text')
						->setDescription("Payssion secrey key")
						->setRequired(true),

		PluginInstaller::withKey('payssion.production_mode')
						->setType('select')
						->setDescription("Production mode")
						->setValues([1 => "Disabled / Test mode", 0 => "Enabled / Production mode"])
						->setRequired(true),
	];
});


Hook::register('template.dashboard.user.edit.balance', function(Template $tpl, SingleUser $selectUser){
	$tpl->var('currency', Cash::getCurrency());
	// $tpl->inc('pay-form');
	// @todo
});