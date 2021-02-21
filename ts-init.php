<?php  
define('DS', DIRECTORY_SEPARATOR);

// Define roots
define('APP_ROOT', 		__DIR__ . DS);
define('APP_STORAGE', 	APP_ROOT . 'storage' . DS);
define('APP_TEMP', 		APP_STORAGE . 'temp' . DS);

// Aliases for roots
define('CD', APP_ROOT);	// Alias "current dir"
define('STORAGE', APP_STORAGE);
define('TEMP', APP_TEMP);

require 'ts-framework/Autoload.php';
require 'vendor/autoload.php';

use tsframe\Config;
use tsframe\App;
use tsframe\Autoload;

Autoload::init();
Autoload::addRoot(APP_ROOT . 'ts-framework');

Config::load(APP_ROOT . 'ts-config.json');