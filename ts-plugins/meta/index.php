<?
/**
 * META редактор
 */
namespace tsframe;

use tsframe\Config;
use tsframe\Hook;
use tsframe\Http;
use tsframe\Plugins;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('dashboard', 'database');
	return [
		PluginInstaller::withKey('access.meta')
					->setType('select')
					->setDescription("Права доступа: доступ к META-реестру")
					->setDefaultValue(UserAccess::Admin)
					->setValues(array_flip(UserAccess::getArray())),
	];
});

Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Meta реестр данных', ['url' => Http::makeURI('/dashboard/meta'), 'fa' => 'table', 'access' => UserAccess::getAccess('meta')]), -2);
});