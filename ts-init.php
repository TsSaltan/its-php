<?php  
define('DS', DIRECTORY_SEPARATOR);
define('CD', __DIR__ . DS);

if(!defined('STORAGE')) define('STORAGE', CD . 'storage' . DS);
if(!defined('TEMP')) define('TEMP', STORAGE . 'temp' . DS);

require 'ts-framework/Autoload.php';
require 'vendor/autoload.php';

use tsframe\Config;
use tsframe\App;
use tsframe\Autoload;

Autoload::init();
Autoload::addRoot('ts-framework');

Config::load('ts-config.json');