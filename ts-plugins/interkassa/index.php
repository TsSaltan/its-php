<?
/**
 * Система приёма платежей
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

class CashInstaller {
	protected static $cash;
	
	public static function install(){
		if(Config::get('interkassa') == null){
			Config::set('interkassa.accountId', 'input_your_account_id');
			Config::set('interkassa.cashId', 'input_your_cash_id');
			Config::set('interkassa.key', 'input_your_key');
			Config::set('interkassa.currency', 'USD');
		}	

		if(Config::get('access.cash') == null){
			Config::set('access.cash.view', UserAccess::Admin);
			Config::set('access.cash.self', UserAccess::Guest);
		}
	}

	public static function load(){
		Plugins::required('database', 'user', 'dashboard');
		TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
		self::$cash = Cash::currentUser();
	}

	public static function addMenuTop(MenuItem $menu){
		$menu->add(new MenuItem('Баланс: ' . self::$cash->getBalance() . ' ' . Config::get('interkassa.currency'), ['url' => '/dashboard/user/me/edit?balance', 'fa' => 'money', 'access' => UserAccess::getAccess('user.self')]), -2);
	}

	public static function addEditTab(Template $tpl, array &$configTabs, int &$activeTab){
		if(is_null($tpl->selectUser)) return;
		$selectUser = $tpl->selectUser;

		if($tpl->self || UserAccess::checkCurrentUser('user.edit')){
			$configTabs['Баланс'] = function() use ($tpl, $selectUser){
				$cash = new Cash($selectUser);
				$pay = new Payment($selectUser);
				$tpl->var('balance', $cash->getBalance());
				$tpl->var('balanceCurrency', Config::get('interkassa.currency'));
				$tpl->var('balanceHistory', $cash->getHistory());
				$tpl->var('balancePayForm', $pay->getForm());
				$tpl->inc('balance');
			};

			if(isset($_GET['balance'])){
				$activeTab = sizeof($configTabs)-1;
			}
		}
	}
}

Hook::registerOnce('plugin.load', [CashInstaller::class, 'load']);
Hook::registerOnce('app.install', [CashInstaller::class, 'install']);
Hook::register('menu.render.dashboard-top', [CashInstaller::class, 'addMenuTop']);
Hook::register('template.dashboard.user.edit', [CashInstaller::class, 'addEditTab']);