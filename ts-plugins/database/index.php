<?
/**
 * База данных
 */

namespace tsframe;

use tsframe\Config;
use tsframe\module\database\Database;
use tsframe\exception\DatabaseException;

Hook::registerOnce('plugin.install.required', function(){
	$fields = [
		'database.host' => ['type' => 'text', 'placeholder' => 'Хост базы данных', 'value' => Config::get('database.host')],
		'database.user' => ['type' => 'text', 'placeholder' => 'Имя пользователя', 'value' => Config::get('database.user')],
		'database.pass' => ['type' => 'text', 'placeholder' => 'Пароль', 'value' => Config::get('database.pass')],
		'database.name' => ['type' => 'text', 'placeholder' => 'Имя базы данных', 'value' => Config::get('database.name')],
	];

	if(Config::get('database') !== null){
		try{
			Database::connect(
				Config::get('database.host'), 
				Config::get('database.user'), 
				Config::get('database.pass'), 
				Config::get('database.name')
			);
		} catch(DatabaseException $e){
			Config::set('database.host', null);
			Config::set('database.user', null); 
			Config::set('database.pass', null); 
			Config::set('database.name', null);
			$fields['database.error'] = ['type' => 'error', 'text' => 'Database connection error!'];
		}

	}

	return $fields;
});


Hook::registerOnce('plugin.load', function(){
	Database::connect(
		Config::get('database.host'), 
		Config::get('database.user'), 
		Config::get('database.pass'), 
		Config::get('database.name')
	);
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
		Database::exec(file_get_contents($sql));
	}
}