<?
/**
 * База данных
 */

namespace tsframe;

use tsframe\Config;
use tsframe\module\Log;
use tsframe\module\database\Database;

Hook::register('plugin.load', function(){
	if(Config::get('database') == null){
		Config::set('database.host', 'localhost');
		Config::set('database.user', 'root');
		Config::set('database.pass', '');
		Config::set('database.name', 'tsframe');
	}
	
	$host = Config::get('database.host');
	$user = Config::get('database.user');
	$pass = Config::get('database.pass');
	$dbname = Config::get('database.name');
	Database::connect($host, $user, $pass, $dbname);
});

/**
 * install.sql из папок с плагинами
 */
Hook::register('plugin.install', function(string $name, string $path){
	importSql($path);
});

/**
 * install.sql из корня
 */
Hook::register('app.install', function(){
	importSql(CD);
});

/**
 * Хук для плагинов, работающих с базой
 * Установщик автоматически выполнит запросы из файлов install.sql
 */
function importSql(string $parentDir){
	$sql = $parentDir . DS . 'install.sql';
	if(file_exists($sql)){
		Log::add('[Database] Install from: ' . $sql, 'install');
		Database::exec(file_get_contents($sql));
	}
}