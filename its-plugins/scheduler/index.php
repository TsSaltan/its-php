<?php
/**
 * Планировщик задач (по типу крона)
 * - Необходимо добавить задачу в cron: curl https://site.com/scheduler-task/
 */
namespace tsframe;

use tsframe\App;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;
use tsframe\module\scheduler\Scheduler;

Hook::registerOnce('plugin.install', function(){
	Plugins::required('database', 'logger');
});