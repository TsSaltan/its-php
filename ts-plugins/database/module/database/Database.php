<?php
namespace tsframe\module\database;

use tsframe\exception\DatabaseException;

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
	public static $pdo;

	public static function connect($host, $user, $pass, $dbname, $charset = 'utf8'){
		try {
			$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
			self::$pdo = new \PDO($dsn, $user, $pass);
			self::$pdo->exec("set names ".$charset);
		} catch( \PDOException $e ) {
			throw new DatabaseException( 
				'Connect error: '.$e->getMessage(), 
				$e->getCode(),
				['$dsn' => $dsn]
			);
		}
	}

	public static function prepare($query){
		try {
			return new Query($query);
		} catch( \PDOException $e ) {
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
	
	
	public static function exec($query, $vars = array()){
		try {
			$q = new Query($query);
			$q->exec($vars);
			return $q;

		}catch( \PDOException $e ) {
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
	
	public static function lastInsertId(){
		return self::$pdo->lastInsertId();
	}	
}