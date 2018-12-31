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
	 * Подключиться к базе данных
	 * @param  string $host    
	 * @param  string $user    
	 * @param  string $pass    
	 * @param  string $dbname  
	 * @param  string $charset 
	 * @throws DatabaseException     
	 */
	public static function connect(string $host, string $user, ?string $pass, string $dbname, string $charset = 'utf8'){
		try {
			$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
			self::$pdo = new \PDO($dsn, $user, $pass);
			self::$pdo->exec("set names ".$charset);
		} catch( PDOException $e ) {
			throw new DatabaseException( 
				'Connect error: '.$e->getMessage(), 
				$e->getCode(),
				['dsn' => $dsn]
			);
		}
	}

	/**
	 * Подготовить запрос
	 * @param  string $query Текст запроса
	 * @return Query
	 * @throws DatabaseException
	 */
	public static function prepare(string $query): Query {
		try {
			return new Query($query);
		} catch( \PDOException $e ) {
			throw new DatabaseException( 
				$e->getMessage(), 
				$e->getCode(),
				[
					'query' => $query,
					'vars' => $vars,
				]
			);
		}
	}
	
	/**
	 * Выполнить запрос
	 * @param  string $query Текст запроса
	 * @param  array  $vars  Переменные для подготовленного запроса
	 * @return Query
	 * @throws DatabaseException
	 */
	public static function exec(string $query, array $vars = []): Query {
		try {
			$q = new Query($query);
			$q->exec($vars);
			return $q;

		}catch(\PDOException $e) {
			throw new DatabaseException( 
				$e->getMessage(), 
				$e->getCode(),
				[
					'$query' => $query,
					'$vars' => $vars,
				]
			);
		}
	}
	
	/**
	 * Возвращает ID последней вставленной строки
	 * @return [type] [description]
	 */
	public static function lastInsertId(){
		return self::$pdo->lastInsertId();
	}	

	/**
	 * Возвращает имя текущей базы данных
	 * @return string
	 */
	public static function getCurrentDatabase(): ?string {
		return self::exec('SELECT database() "db"')->fetch()[0]['db'];
	}

	/**
	 * Получить размер занимаемых данных
	 * @param  string|null $table Имя таблицы (или null - размер всей базы)
	 * @return int Размер занимаемых данных в байтих
	 */
	public static function getSize(?string $table = null): int {
		if(is_null($table)){
			$query = self::exec('SELECT table_schema, SUM(data_length + index_length) AS "size"
				FROM information_schema.TABLES
				WHERE table_schema = :database
				GROUP BY table_schema;', 
			['database' => self::getCurrentDatabase()]);
		} else {
			$query = self::exec('SELECT table_name,
				(data_length + index_length) AS "size"
				FROM information_schema.TABLES
				WHERE table_schema = :database AND table_name = :table
				ORDER BY (data_length + index_length) DESC;', 
			[
				'database' => self::getCurrentDatabase(),
				'table' => $table,
			]);
		}

		$result = $query->exec()->fetch();
		return $result[0]['size'] ?? 0;
	}
}