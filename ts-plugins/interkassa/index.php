<?
/**
 * Система приёма платежей
 *
 * @hook template.dashboard.cash.global
 * @hook cash.pay (int $userId, $amount, $description, $payId)
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
			Config::set('access.cash.global', UserAccess::Admin);
			Config::set('access.cash.self', UserAccess::Guest);
		}
	}

	public static function load(){
		Plugins::required('database', 'user', 'dashboard');
		TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
		TemplateRoot::add('interkassa', __DIR__ . DS . 'template' . DS . 'interkassa');
		self::$cash = Cash::currentUser();
	}

	public static function addMenuSidebar(MenuItem $menu){
		$menu->add(new MenuItem('Финансовые операции', ['url' => Http::makeURI('/dashboard/cash'), 'fa' => 'money', 'access' => UserAccess::getAccess('cash.global')]), -1);
	}
	
	public static function addMenuTop(MenuItem $menu){
		$menu->add(new MenuItem('Баланс: ' . self::$cash->getBalance() . ' ' . Config::get('interkassa.currency'), ['url' => Http::makeURI('/dashboard/user/me/edit?balance'), 'fa' => 'money', 'access' => UserAccess::getAccess('user.self')]), -2);
	}

	public static function addEditTab(Template $tpl, array &$configTabs, int &$activeTab){
		if(is_null($tpl->selectUser)) return;
		$selectUser = $tpl->selectUser;

		if($tpl->self || UserAccess::checkCurrentUser('user.edit')){
			if(isset($_GET['balance'])){
				$activeTab = sizeof($configTabs);

				switch($_GET['balance']){
					case 'frompay':
						$tpl->vars(['cashAlert' => ['info' => 'Платёж совершён! Средства поступят на счёт после проверки платежа, обычно для этого требуется не более 5-10 минут.']]);
					break;
				}
			}

			$configTabs['Баланс'] = function() use ($tpl, $selectUser){
				$cash = new Cash($selectUser);
				$tpl->var('balance', $cash->getBalance());
				$tpl->var('balanceCurrency', Config::get('interkassa.currency'));
				$tpl->var('balanceHistory', $cash->getHistory());

				$payment = new Payment($selectUser);
				$payment->calculateAmount('0');
				$tpl->var('payFormAction', $payment->getProcessURI());
				$tpl->var('payFormFields', $payment->getForm(true, true));

				$tpl->inc('balance');
			};
		}
	}

	public static function showUserBalance(Template $tpl, SingleUser $user){
		if(!UserAccess::checkCurrentUser('cash.view')) return;

		$balance = (new Cash($user))->getBalance();
		$currency = Cash::getCurrency();

		?>
		<p>Текущий счёт: <b><?=$balance?></b> <?=$currency?></p>
		<?
	}
}

Hook::registerOnce('plugin.load', [CashInstaller::class, 'load']);
Hook::registerOnce('app.install', [CashInstaller::class, 'install']);
Hook::register('menu.render.dashboard-top', [CashInstaller::class, 'addMenuTop']);
Hook::register('menu.render.dashboard-admin-sidebar', [CashInstaller::class, 'addMenuSidebar']);
Hook::register('template.dashboard.user.edit', [CashInstaller::class, 'addEditTab']);
Hook::register('template.dashboard.user.profile', [CashInstaller::class, 'showUserBalance']);