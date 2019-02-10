<?
/**
 * Интеграция с платежной системой paysera
 */
namespace tsframe;

use tsframe\PluginInstaller;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;


Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::registerOnce('plugin.install', function(){
	/*	return [
			PluginInstaller::withKey('interkassa.accountId')
							->setType('text')
							->setDescription("ID аккаунта Interkassa")
							->setRequired(true),

			PluginInstaller::withKey('interkassa.cashId')
							->setType('text')
							->setDescription("ID кошелька Interkassa")
							->setRequired(true),

			PluginInstaller::withKey('interkassa.key')
							->setType('text')
							->setDescription("Секретный (или тестовый) ключ от кошелька Interkassa<br/>".
											"<p>В настройках кассы укажите:</p>".
											"<p><b>URL успешной оплаты:</b> <u>".$host."interkassa/success</u></p>".
											"<p><b>URL неуспешной оплаты:</b> <u>".$host."interkassa/fail</u></p>".
											"<p><b>URL ожидания проведения платежа:</b> <u>".$host."interkassa/pending</u></p>".
											"<p><b>URL взаимодействия:</b> <u>".$host."interkassa/pay</u></p>"
							)
							->setRequired(true),
		];*/
});