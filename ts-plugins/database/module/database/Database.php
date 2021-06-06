<?php
namespace tsframe\module\database;

use tsframe\exception\DatabaseException;
use PDOException;

/*
DataBase::Connect('localhost', 'root', '', 'api');
DataBase::Execute('SELECT * FROM  `accounts` Where `appId` = :appi');
DataBase::Query('SELECT * FROM  `accounts` Where `appId` = :appi', ['appi'=>100]);

	var_dump(
		//DataBase::Query('SELECT * FROM  `accounts` Where `appId` = :appi', ['appi'=>100]),
		
		DataBase::Prepare('SELECT * FROM  `accounts` Where `appId` > :appid OR `appId` = :appid')	
				->Bind('appid', 100, VAR_INT)
				->Execute()

	);
	*/

class Database{
	/**
	 * @var PDO
	 */
	public static $pdo;

	/**
	 * @var Connection
	 */
	public static $db;

	/**
	 * Подключиться к базе данных
	 * @param  string $host    
	 * @param  string $user    
	 * @param  string $pass    
	 * @param  string $dbname  
	 * @param  string $charset 
	 * @throws DatabaseException     
	 */
	public static function connect(string $host, string $user, ?string $pass, string $dbname, string $charset = 'utf8'){
		self::$db = new Connection($host, $user, $pass, $dbname, $charset);
		self::$pdo = self::$db->getPDO();
	}

	/**
	 * Подготовить запрос
	 * @param  string $query Текст запроса
	 * @return Query
	 * @throws DatabaseException
	 */
	public static function prepare(string $query): Query {
		return self::$db->prepare($query);
	}
	
	/**
	 * Выполнить запрос
	 * @param  string $query Текст запроса
	 * @param  array  $vars  Переменные для подготовленного запроса
	 * @return Query
	 * @throws DatabaseException
	 */
	public static function exec(string $query, array $vars = []): Query {
		return self::$db->exec($query, $vars);
	}
	
	/**
	 * Возвращает ID последней вставленной строки
	 * @return [type] [description]
	 */
	public static function lastInsertId(){
		return self::$db->lastInsertId();
	}	

	/**
	 * Возвращает имя текущей базы данных
	 * @return string
	 */
	public static function getCurrentDatabase(): ?string {
		return self::$db->getCurrentDatabase();
	}

	/**
	 * Получить размер занимаемых данных
	 * @param  string|null $table Имя таблицы (или null - размер всей базы)
	 * @return int Размер занимаемых данных в байтих
	 */
	public static function getSize(?string $table = null): int {
		return self::$db->getSize($table);
	}

	/**
	 * Получить дамп / SQL-запрос создания таблиц и их содержимого
	 * 
	 * @param array|string 	$tables 		Список таблиц для дампа, * - все | таблица1, таблица2, ... | [table1, table2, ...]
	 * @param bool 			$deleteQueries 	Использовать в дампе хапросы с удалением таблиц перед сохданием новых
	 * @param bool|string 	$filePath 		Путь для сохранения SQL файла c дампом или false, если сохранение не нужно
	 */
	public static function dump($tables = '*', bool $deleteQueries = false, $filePath = false): ?string {
	    return self::$db->dump($tables, $deleteQueries, $filePath);
	}
}