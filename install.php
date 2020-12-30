<?php  
require 'ts-init.php';

use tsframe\App;
use tsframe\Http;
use tsframe\Config;
use tsframe\Hook;

if(App::install()){

	// Migrate scripts

	// Migrate from v 1.0
	$canReg = Config::get('user.canRegister');
	if(!is_null($canReg)){
		Config::set('user.auth.register', $canReg);
		Config::unset('user.canRegister');
	}

	$canSocial = Config::get('user.canSocial');
	if(!is_null($canSocial)){
		Config::set('user.auth.social', $canSocial);
		Config::unset('user.canSocial');
	}

	$loginUsed = Config::get('user.loginUsed');
	if(!is_null($loginUsed)){
		Config::set('user.auth.login', $loginUsed);
		Config::unset('user.loginUsed');
	}

	if(!App::isDev()){
		rename(CD . 'install.php', CD . uniqid('install-') . '.php');
	}
}