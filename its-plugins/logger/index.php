<?php
/**
 * Логирование
 * @todo  clear logs
 */
namespace tsframe;

use tsframe\Hook;
use tsframe\Plugins;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('database');
});