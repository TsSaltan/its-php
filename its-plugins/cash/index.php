<?php
/**
 * Система приёма платежей
 * @hook template.dashboard.user.edit.balance (Template $tpl, SingleUser $selectUser)
 * @hook cash.pay (int $userId, $amount, $description, $payId) - Поступление средств на счёт
 * @hook cash.balance.change (SingleUser $user, string $amountChange, ?string $description, ?string $payId) - Изменение средств на счёт
 * @hook cash.balance.add (SingleUser $user, string $amount, ?string $description, ?string $payId) - Поступление средств на счёт
 * @hook cash.balance.sub (SingleUser $user, string $amount, ?string $description, ?string $payId) - Списание средств со счёта
 */
namespace tsframe;

use tsframe\Config;
use tsframe\module\Logger;
use tsframe\module\menu\Menu;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\Cash;
use tsframe\module\user\SingleUser;
use tsframe\module\user\SocialLogin;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;
use tsframe\view\UI\UIDashboardTabPanel;

class CashInstaller {
	public static function install(){
		Plugins::required('database', 'user', 'dashboard', 'logger');
		
		return [
			PluginInstaller::withKey('cash.currency')
							->setType('select')
							->setDescription("Валюта, используемая в системе")
							->setValues(['RUB'=>'RUB', 'UAH'=>'UAH', 'USD'=>'USD'])
							->setDefaultValue('RUB'),

			PluginInstaller::withKey('access.cash.payment')
							->setType('select')
							->setDescription("Права доступа: возможность изменять баланс пользователей")
							->setDefaultValue(UserAccess::Admin)
							->setValues(array_flip(UserAccess::getArray())),

			PluginInstaller::withKey('access.cash.view')
							->setType('select')
							->setDescription("Права доступа: просмотр баланса любого пользователя")
							->setDefaultValue(UserAccess::Admin)
							->setValues(array_flip(UserAccess::getArray())),

			PluginInstaller::withKey('access.cash.global')
							->setType('select')
							->setDescription("Права доступа: доступ к истории финансовых операций")
							->setDefaultValue(UserAccess::Admin)
							->setValues(array_flip(UserAccess::getArray())),

			PluginInstaller::withKey('access.cash.self')
							->setType('select')
							->setDescription("Права доступа: просмотр своего баланса")
							->setDefaultValue(UserAccess::Guest)
							->setValues(array_flip(UserAccess::getArray())),
		];
	}

	public static function load(){
		TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
		Logger::setUnremovableSection('cash');
	}
	
	public static function addMenuTop(MenuItem $menu){
		$menu->add(new MenuItem('Баланс: ' . Cash::currentUser()->getBalance() . ' ' . Cash::getCurrency(), ['url' => Http::makeURI('/dashboard/user/me/edit?balance'), 'fa' => 'money', 'access' => UserAccess::getAccess('user.self')]), -2);
	}

	public static function addEditTab(Template $tpl, UIDashboardTabPanel $configTabs){
		if(is_null($tpl->selectUser)) return;
		$selectUser = $tpl->selectUser;

		if(($tpl->self && UserAccess::checkCurrentUser('cash.self')) || (!$tpl->self && UserAccess::checkCurrentUser('cash.view'))){
			if(isset($_GET['balance'])){
				$configTabs->setActiveTab('balance');

				switch($_GET['balance']){
					case 'frompay':
					case 'completed':
					case 'success':
						$tpl->vars(['cashAlert' => ['success' => 'Платёж совершён. Средства зачислены на счёт.']]);
						break;

					case 'pending':
						$tpl->vars(['cashAlert' => ['info' => 'Платёж обрабатывается. Средства поступят на счёт после проверки, обычно для этого требуется не более 5-10 минут.']]);
						break;

					case 'fail':
					case 'failed':
						$tpl->vars(['cashAlert' => ['danger' => 'Произошла ошибка во время платежа!']]);
						break;

					case 'cancel':
					case 'cancelled':
						$tpl->vars(['cashAlert' => ['warning' => 'Платёж был отменён.']]);
						break;
				}
			}

			$configTabs->tab('balance', 'Баланс', function() use ($tpl, $selectUser){
				$cash = new Cash($selectUser);
				$tpl->var('balance', $cash->getBalance());
				$tpl->var('balanceCurrency',  Cash::getCurrency());
				$tpl->var('balanceHistory', $cash->getHistory());

				$tpl->inc('balance');
			});
		}
	}

	public static function showUserBalance(Template $tpl, SingleUser $user){
		if(!UserAccess::checkCurrentUser('cash.view')) return;

		$balance = (new Cash($user))->getBalance();
		$currency = Cash::getCurrency();

		?>
		<p>Текущий счёт: <b><?=$balance?></b> <?=$currency?></p>
		<?php 
	}

	public static function showUserBalanceApi(SingleUser $user, array &$data){
		$currentUser = User::current();
		if(
			($currentUser->get('id') == $user->get('id') && UserAccess::checkCurrentUser('cash.self')) ||
			($currentUser->get('id') != $user->get('id') && UserAccess::checkCurrentUser('cash.view')) 
		){
			$data['balance'] = (new Cash($user))->getBalance();
			$data['balance_currency'] = Cash::getCurrency();

			//return $data;
		}
	}

	public static function userBalance(Template $tpl, SingleUser $selectUser){
		if(UserAccess::checkCurrentUser('cash.payment')){
			$tpl->inc('edit-balance');
		}
	}

	public static function userListColumn(Template $tpl){
		?><th>Баланс</th><?php
	}

	public static function userListItem(Template $tpl, SingleUser $selectUser){
		$cash = new Cash($selectUser);
		$balance = $cash->getBalance();
		$currency = Cash::getCurrency();
		?><td><b><?=$balance?></b> <?=$currency?></td><?php
	}
}

Hook::registerOnce('app.init', [CashInstaller::class, 'load']);
Hook::registerOnce('plugin.install', [CashInstaller::class, 'install']);
Hook::register('menu.render.dashboard-top', [CashInstaller::class, 'addMenuTop']);
Hook::register('template.dashboard.user.edit', [CashInstaller::class, 'addEditTab']);
Hook::register('template.dashboard.user.profile', [CashInstaller::class, 'showUserBalance']);
Hook::register('api.user.data', [CashInstaller::class, 'showUserBalanceApi']);
Hook::register('template.dashboard.user.edit.balance', [CashInstaller::class, 'userBalance']);
Hook::register('template.dashboard.user.list.column', [CashInstaller::class, 'userListColumn']);
Hook::register('template.dashboard.user.list.item', [CashInstaller::class, 'userListItem']);



/**
 * Логирование изменения баланса пользователей
 */
Hook::register('cash.balance.add', function(SingleUser $user, $sum, $description, $payId){
	Logger::cash()->info($description, [
		'operation_type' => 'add',
		'user' => $user->get('id'),
		'balance' => '+' . $sum,
		'pay_id' => $payId
	]);
});

Hook::register('cash.balance.sub', function(SingleUser $user, $sum, $description, $payId){
	Logger::cash()->info($description, [
		'operation_type' => 'sub',
		'user' => $user->get('id'),
		'balance' => '-' . $sum,
		'pay_id' => $payId
	]);
});