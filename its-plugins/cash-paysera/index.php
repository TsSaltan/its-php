<?php
/**
 * Интеграция с платежной системой paysera
 * @link https://developers.paysera.com/ru/payments/current
 */
namespace tsframe;

use tsframe\PluginInstaller;
use tsframe\module\user\Cash;
use tsframe\module\user\SingleUser;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;


Hook::registerOnce('app.start', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::registerOnce('plugin.install', function(){
	return [
		PluginInstaller::withKey('paysera.projectid')
						->setType('text')
						->setDescription("Уникальный ID проекта Paysera")
						->setRequired(true),

		PluginInstaller::withKey('paysera.sign_password')
						->setType('text')
						->setDescription("Пароль от проекта Paysera")
						->setRequired(true),

		PluginInstaller::withKey('paysera.test')
						->setType('select')
						->setDescription("Режим работы")
						->setValues([1 => "Test mode", 0 => "Production mode"])
						->setRequired(true),
	];
});


Hook::register('template.dashboard.user.edit.balance', function(Template $tpl, SingleUser $selectUser){
	$tpl->var('currency', Cash::getCurrency());
	$tpl->inc('pay-form');
});