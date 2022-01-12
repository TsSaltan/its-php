<?php 
/**
 * API для работы с VK API
 *
 * Данные о Callback API хранятся в конфигурационном файле
 * vk.groups = {
 * 		%group_id%: {confirm: %confirm_code%, secret: %secret_key%}
 * }
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\module\Crypto;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;

/**
 * Загрузка плагина
 */
Hook::registerOnce('plugin.load', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('template.dashboard.config', function(Template $tpl){
	$tpl->var('vkGroups', Config::get('vk.groups'));
	$tpl->var('vkRandom', Crypto::generateString(16));
	$tpl->inc('vk-groups-config');
}, 20);