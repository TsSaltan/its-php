<?php
namespace tsframe;

use tsframe\Hook;
use tsframe\Http;
use tsframe\module\blog\Post;
use tsframe\module\menu\MenuItem;
use tsframe\module\user\SingleUser;
use tsframe\module\user\UserAccess;
use tsframe\view\DashboardTemplate;
use tsframe\view\TemplateRoot;

if(!defined('APP_MEDIA')){
	define('APP_MEDIA', APP_UPLOAD . DS . 'media');
}

Hook::registerOnce('plugin.install', function(){
	Plugins::required('blog', 'dashboard', 'dashboard-blog', 'api-base');

	if(!is_dir(APP_UPLOAD)){
		mkdir(APP_UPLOAD);
	}

	if(!is_dir(APP_MEDIA)){
		mkdir(APP_MEDIA);
	}
});

Hook::registerOnce('app.init', function(){
	TemplateRoot::add('dashboard', __DIR__ . DS . 'template' . DS . 'dashboard');
});

Hook::register('template.dashboard.post.edit-inner', function(DashboardTemplate $tpl, ?Post $post, ?SingleUser $user){
	$tpl->inc('media-uploader');
});