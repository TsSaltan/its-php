<?php
/**
 * База данных
 */

namespace tsframe;

use tsframe\Config;
use tsframe\module\database\Database;
use tsframe\exception\DatabaseException;

Hook::registerOnce('plugin.install', function(){
	$fields = [
		PluginInstaller::withKey('database.host')->setDescription('Хост базы данных')->setRequired(true),
		PluginInstaller::withKey('database.user')->setDescription('Имя пользователя')->setRequired(true),
		PluginInstaller::withKey('database.pass')->setDescription('Пароль базы данных')->setRequired(false),
		PluginInstaller::withKey('database.name')->setDescription('Имя базы данных')->setRequired(true),
	];

	if(Config::get('database') !== null && Config::get('database.host') !== null && Config::get('database.user') !== null && Config::get('database.name') !== null){
		try{
			Database::connect(
				Config::get('database.host'), 
				Config::get('database.user'), 
				Config::get('database.pass'), 
				Config::get('database.name')
			);
		} catch(DatabaseException $e){
			$fields[] = PluginInstaller::error('Ошибка при подключении к базе данных. Проверьте указанные настройки!');
		}

	}

	return $fields;
});

Hook::registerOnce('app.init', function(){
	if(Config::get('database') !== null && Config::get('database.host') !== null && Config::get('database.user') !== null && Config::get('database.name') !== null){
		Database::connect(
			Config::get('database.host'), 
			Config::get('database.user'), 
			Config::get('database.pass'), 
			Config::get('database.name')
		);
	}
});

/**
 * Выполнение SQL запроса из install.sql
 */
Hook::registerOnce('app.installed', function(){
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

	// Из каждой папки плагина
	foreach (Plugins::getList() as $name => $path) {
		if(Plugins::isEnabled($name)){
			importSql($path);
		}
	}

	// Из корневой папки
	importSql(CD);

});