<?
/**
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('dashboard');
	return [
		PluginInstaller::withKey('access.support.client')
					->setType('select')
					->setDescription("Права доступа: возможность создавать сообщение в поддержке")
					->setDefaultValue(UserAccess::User)
					->setValues(array_flip(UserAccess::getArray())),

		PluginInstaller::withKey('access.support.operator')
					->setType('select')
					->setDescription("Права доступа: права оператора в поддержке")
					->setDefaultValue(UserAccess::Admin)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('menu.render.dashboard-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Поддержка', ['url' => Http::makeURI('/dashboard/support'), 'fa' => 'comments', 'access' => -1]));
});

Hook::register('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Заявки в поддержку', ['url' => Http::makeURI('/dashboard/support-operator'), 'fa' => 'support', 'access' => UserAccess::getAccess('support.operator')]));
});