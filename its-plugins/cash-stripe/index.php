<?php
namespace tsframe;
use tsframe\Config;
use tsframe\Hook;
use tsframe\PluginInstaller;
use tsframe\module\user\Cash;
use tsframe\module\user\SingleUser;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;

Hook::registerOnce('app.init', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template');
});

Hook::registerOnce('plugin.install', function(){
	Plugins::required('cash', 'database', 'user', 'dashboard', 'logger');

	return [
		PluginInstaller::withKey('stripe.public_key')
						->setType('text')
						->setDescription("Stripe public key")
						->setRequired(true),

		PluginInstaller::withKey('stripe.private_key')
						->setType('text')
						->setDescription("Stripe private key")
						->setRequired(true),

		/*PluginInstaller::withKey('stripe.test')
						->setType('select')
						->setDescription("Stripe mode")
						->setValues([1 => "Test mode", 0 => "Production mode"])
						->setRequired(true),*/
	];
});

Hook::register('template.dashboard.user.edit.balance', function(Template $tpl, SingleUser $selectUser){
	$tpl->var('currency', Cash::getCurrency());
	$tpl->inc('stripe');
});