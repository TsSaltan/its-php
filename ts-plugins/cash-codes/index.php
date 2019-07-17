<?
/**
 * Система пополнения баланс, используя платёжные коды
 */
namespace tsframe;

use tsframe\Config;
use tsframe\module\menu\Menu;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\Cash;
use tsframe\module\user\SingleUser;
use tsframe\module\user\SocialLogin;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\DashboardTemplate;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::registerOnce('plugin.install', function(){
	Plugins::required('cash', 'database', 'user', 'dashboard', 'crypto');

	return [
		PluginInstaller::withKey('access.cash.codes')
						->setType('select')
						->setDescription("Права доступа: возможность создавать платёжные коды")
						->setDefaultValue(UserAccess::Admin)
						->setValues(UserAccess::getArray(true)),
	];
});

Hook::registerOnce('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Платёжные коды', ['url' => Http::makeURI('/dashboard/cash-codes'), 'fa' => 'rub', 'access' => UserAccess::getAccess('access.cash.codes')]));
});


Hook::register('template.render', function(Template $tpl){
	if($tpl instanceof DashboardTemplate){
		if(isset($_GET['cash-code'])){
			switch ($_GET['cash-code']) {
				case 'error':
					$tpl->alert('Введён некорректный код', 'danger');
					break;

				case 'success':
					$tpl->alert('Ваш баланс успешно пополнен через платёжный код', 'success');
					break;
			}
		}
	}
});

Hook::register('template.dashboard.user.edit.balance', function(Template $tpl, SingleUser $selectUser){
	$tpl->inc('balance-codes');
});