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
	
	public static function install(){
		Plugins::required('database', 'user', 'dashboard');
		
		return [
			'interkassa.accountId' => ['type' => 'text', 'placeholder' => 'IK account ID'],
			'interkassa.cashId' => ['type' => 'text', 'placeholder' => 'IK cash ID'],
			'interkassa.key' => ['type' => 'text', 'placeholder' => 'IK private key'],
			'interkassa.currency' => ['type' => 'text', 'value' => 'RUB'],
			'access.cash.view' => ['type' => 'numeric', 'value' => UserAccess::Admin],
			'access.cash.global' => ['type' => 'numeric', 'value' => UserAccess::Admin],
			'access.cash.self' => ['type' => 'numeric', 'value' => UserAccess::Guest],
		];
	}

	public static function load(){
		TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
		TemplateRoot::add('interkassa', __DIR__ . DS . 'template' . DS . 'interkassa');
	}
	
	public static function addMenuTop(MenuItem $menu){
		$menu->add(new MenuItem('Баланс: ' . Cash::currentUser()->getBalance() . ' ' . Config::get('interkassa.currency'), ['url' => Http::makeURI('/dashboard/user/me/edit?balance'), 'fa' => 'money', 'access' => UserAccess::getAccess('user.self')]), -2);
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
Hook::registerOnce('plugin.install', [CashInstaller::class, 'install']);
Hook::register('menu.render.dashboard-top', [CashInstaller::class, 'addMenuTop']);
Hook::register('template.dashboard.user.edit', [CashInstaller::class, 'addEditTab']);
Hook::register('template.dashboard.user.profile', [CashInstaller::class, 'showUserBalance']);