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

Hook::register('plugin.load', function(){
	Config::set('access.meta', UserAccess::Admin);
});

Hook::registerOnce('plugin.load', function(){
	Plugins::required('dashboard', 'database');
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('menu.render.dashboard-admin-sidebar', function(MenuItem $menu){
	$menu->add(new MenuItem('Meta', ['url' => Http::makeURI('/dashboard/meta'), 'fa' => 'table', 'access' => UserAccess::getAccess('meta')]));
});