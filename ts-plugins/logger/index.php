<?
/**
 * Логирование
 * @todo  clear logs
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\UserAccess;
use tsframe\view\TemplateRoot;

Hook::register('plugin.install.required', function(){
	return [
		'access.log' => ['type' => 'numeric', 'value' => UserAccess::Admin, 'title' => 'Просмотр системных логов']
	];
});

Hook::registerOnce('plugin.load', function(){
	Plugins::required('dashboard');
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Логи', ['url' => Http::makeURI('/dashboard/logs'), 'fa' => 'list-alt', 'access' => UserAccess::getAccess('log')]));
});