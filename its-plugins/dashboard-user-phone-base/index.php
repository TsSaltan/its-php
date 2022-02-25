<?php
/**
 * Номер телефона в аккаунте пользователя
 * Основа для смс-провайдеров
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\Geo\PhoneData;
use tsframe\module\io\Input;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\User;
use tsframe\module\user\UserAccess;
use tsframe\view\HtmlTemplate;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;
use tsframe\view\UI\UIDashboardTabPanel;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('dashboard', 'user', 'database', 'logger', 'geodata', 'phone-input');
});

Hook::registerOnce('app.init', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('template.dashboard.user.edit', function(Template $tpl, UIDashboardTabPanel $configTabs){
	if(is_null($tpl->selectUser)) return;
	$selectUser = $tpl->selectUser;

	if($tpl->self || UserAccess::checkCurrentUser('user.edit')){
		if(isset($_GET['phone'])){
			$configTabs->setActiveTab('phone');
		}

		$configTabs->tab('phone', 'Телефон', function() use ($tpl, $selectUser){
			$tpl->inc('user_phone');
		});
	}
});

Hook::register('template.render', function(Template $tpl){
	$tpl->var('userPhone', User::current()->getMeta()->get('phone'));
});

Hook::register('template.dashboard.header', function(HtmlTemplate $tpl){
	$tpl->hook('template.phone-input.script');
});

Hook::register('template.dashboard.config', function(HtmlTemplate $tpl){
	$tpl->inc('sms_config');
});