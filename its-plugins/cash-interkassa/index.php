<?php
/**
 * Интеграция с платежной системой interkassa
 */
namespace tsframe;

use tsframe\Config;
use tsframe\module\menu\Menu;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\User;
use tsframe\module\user\Cash;
use tsframe\module\interkassa\Payment;
use tsframe\module\user\SingleUser;
use tsframe\module\user\UserAccess;
use tsframe\module\user\SocialLogin;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;

/**
 * @todo  Проверка уникальности платежей
 */
class CashInterkassaInstaller {
	public static function install(){
		Plugins::required('cash');
		
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    	$domainName = $_SERVER['HTTP_HOST'].'/';	
    	$host = $protocol . $domainName;

		return [
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
		];
	}

	public static function load(){
		TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
		TemplateRoot::add('interkassa', __DIR__ . DS . 'template' . DS . 'interkassa');
	}
	
	public static function userBalance(Template $tpl, SingleUser $selectUser){
		$payment = new Payment($selectUser);
		$payment->calculateAmount('0');
		$tpl->var('payFormAction', $payment->getProcessURI());
		$tpl->var('payFormFields', $payment->getForm(true, true));
		$tpl->inc('put-balance');
	}
}

Hook::registerOnce('plugin.load', [CashInterkassaInstaller::class, 'load']);
Hook::registerOnce('plugin.install', [CashInterkassaInstaller::class, 'install']);
Hook::register('template.dashboard.user.edit.balance', [CashInterkassaInstaller::class, 'userBalance']);