<?php
/**
 * META реестр в базе данных
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('database');
});
