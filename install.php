<?php  
require 'ts-init.php';

use tsframe\App;
use tsframe\Http;
use tsframe\Log;

Log::add('Installing ts-framework...');
App::install();
Http::sendBody(Log::get(), 200, 'text/plain');

if(!App::isDev()){
	rename(CD . 'install.php', CD . uniqid('install-') . '.php');
}