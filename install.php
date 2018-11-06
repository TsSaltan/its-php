<?php  
require 'ts-init.php';

use tsframe\App;
use tsframe\Http;
use tsframe\Config;
use tsframe\Hook;

if(App::install()){
	if(!App::isDev()){
		rename(CD . 'install.php', CD . uniqid('install-') . '.php');
	}
}