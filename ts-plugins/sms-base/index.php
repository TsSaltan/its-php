<?
/**
 * Система отправки смс
 * Основа для провайдеров
 * Номер телефона в аккаунте пользователя
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\UserAccess;
use tsframe\module\user\User;
use tsframe\view\TemplateRoot;
use tsframe\view\Template;
use tsframe\view\HtmlTemplate;

Hook::registerOnce('plugin.load', function(){
	Plugins::required('dashboard', 'user', 'database', 'logger');
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
	TemplateRoot::addDefault(__DIR__ . DS . 'template');
	TemplateRoot::addDefault(CD . 'vendor' . DS . 'andr-04' . DS . 'jquery.inputmask-multi');
});

Hook::register('template.dashboard.user.edit', function(Template $tpl, array &$configTabs, int &$activeTab){
	if(is_null($tpl->selectUser)) return;
	$selectUser = $tpl->selectUser;

	if($tpl->self || UserAccess::checkCurrentUser('user.edit')){
		if(isset($_GET['phone'])){
			$activeTab = sizeof($configTabs);
		}

		$configTabs['Телефон'] = function() use ($tpl, $selectUser){
			$tpl->inc('user_phone');
		};

	}
});

Hook::register('template.render', function(Template $tpl){
	$tpl->var('userPhone', User::current()->getMeta()->get('phone'));
});

Hook::register('template.dashboard.header', function(HtmlTemplate $tpl){
	$tpl->js('js/jquery.inputmask.bundle.min.js');
	$tpl->js('js/jquery.inputmask-multi.js');
});

Hook::register('template.dashboard.config', function(HtmlTemplate $tpl){
	$tpl->inc('sms_config');
});