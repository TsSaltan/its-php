<?php
namespace tsframe;
use tsframe\Config;
use tsframe\Hook;
use tsframe\PluginInstaller;
use tsframe\view\TemplateRoot;

Hook::registerOnce('app.init', function(){

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

		PluginInstaller::withKey('stripe.test')
						->setType('select')
						->setDescription("Stripe mode")
						->setValues([1 => "Test mode", 0 => "Production mode"])
						->setRequired(true),
	];
});