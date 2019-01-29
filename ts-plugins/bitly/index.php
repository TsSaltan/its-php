<?php
/**
 * Поддежрка сокращённых ссылок bitly
 * @url https://app.bitly.com/
 */
namespace tsframe;

use tsframe\Hook;

Hook::registerOnce('plugin.install', function(){
	return [
		'bitly.accessToken' => ['type' => 'text', 'placeholder' => "Your token for app.bitly.com"],
	];
});