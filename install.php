<?php  
require 'ts-init.php';

use tsframe\App;
use tsframe\Http;
use tsframe\module\Log;

Log::add('Installing ts-framework...', 'install');
App::install();
Http::sendBody(var_export(Log::getCurrentLogs()['install'], true), 200, 'text/plain');

if(!App::isDev()){
	rename(CD . 'install.php', CD . uniqid('install-') . '.php');
}