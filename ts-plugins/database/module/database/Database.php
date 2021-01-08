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
				WHERE table_schema = :database', 
			['database' => self::getCurrentDatabase()]);
		} else {
			$query = self::exec('SELECT table_name,
				(data_length + index_length) AS "size"
				FROM information_schema.TABLES
				WHERE table_schema = :database AND table_name = :table', 
			[
				'database' => self::getCurrentDatabase(),
				'table' => $table,
			]);
		}

		$result = $query->exec()->fetch();
		return $result[0]['size'] ?? 0;
	}

	/**
	 * Получить дамп / SQL-запрос создания таблиц и их содержимого
	 * 
	 * @param array|string 	$tables 		Список таблиц для дампа, * - все | таблица1, таблица2, ... | [table1, table2, ...]
	 * @param bool 			$deleteQueries 	Использовать в дампе хапросы с удалением таблиц перед сохданием новых
	 * @param bool|string 	$filePath 		Путь для сохранения SQL файла c дампом или false, если сохранение не нужно
	 */
	public static function dump($tables = '*', bool $deleteQueries = false, $filePath = false): ?string {
	    $out = '';

	    try {
	        if ($tables == '*') {
	            $tables = [];
	            $query = self::$pdo->query('SHOW TABLES');
	            while ($row = $query->fetch(\PDO::FETCH_NUM)) {
	                $tables[] = $row[0];
	            }
	        } else {
	            $tables = is_array($tables) ? $tables : explode(',', $tables);
	        }
	        
	        if (empty($tables)) {
	            return null;
	        }
	        
	        // Loop through the tables
	        foreach ($tables as $table) {
	            $query = self::$pdo->query('SELECT * FROM `' . $table . '`');
	            $numColumns = $query->columnCount();
	            
	            // Add DROP TABLE statement
	            if($deleteQueries){
	            	$out .= 'DROP TABLE `' . $table . '`;' . "\n\n";
	            }
	            
	            // Add CREATE TABLE statement
	            $query2 = self::$pdo->query('SHOW CREATE TABLE `' . $table . '`');
	            $row2 = $query2->fetch(\PDO::FETCH_NUM);
	            $out .= $row2[1] . ';' . "\n\n";
	            
	            // Add INSERT INTO statements
	            for ($i = 0; $i < $numColumns; $i++) {
	                while ($row = $query->fetch(\PDO::FETCH_NUM)) {
	                    $out .= "INSERT INTO `$table` VALUES(";
	                    for ($j = 0; $j < $numColumns; $j++) {
	                        $row[$j] = addslashes($row[$j]);
	                        $row[$j] = preg_replace("/\n/us", "\\n", $row[$j]);
	                        if (isset($row[$j])) {
	                            $out .= '"' . $row[$j] . '"';
	                        } else {
	                            $out .= '""';
	                        }
	                        if ($j < ($numColumns - 1)) {
	                            $out .= ',';
	                        }
	                    }
	                    $out .= ');' . "\n";
	                }
	            }
	            $out .= "\n\n\n";
	        }
	        
	        // Save file
	        if($filePath != false){
	        	$savePath = $filePath . time() . '-backup.sql';
	        	file_put_contents($savePath, $out);
	        }
	        
    	} catch (\Exception $e) {
        	throw new DatabaseException("Error on dumping database", 0, [
        		'error_class' => get_class($e),
        		'error' => $e->getMessage(),
        		'e' => $e,
        	]);
	        return null;
    	}	
    
	    return $out;
	}
}