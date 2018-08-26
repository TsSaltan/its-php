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

Hook::register('app.install', function(){
	Config::set('access.log', UserAccess::Admin);
});

Hook::registerOnce('plugin.load', function(){
	Plugins::required('dashboard');
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('menu.render.dashboard-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Логи', ['url' => Http::makeURI('/dashboard/logs'), 'fa' => 'list-alt', 'access' => UserAccess::getAccess('log')]));
});