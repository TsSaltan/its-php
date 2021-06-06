<?php
namespace tsframe\module\database;

use tsframe\exception\DatabaseException;
use PDOException;

class Connection {
	/**
	 * @var PDO
	 */
	public $pdo;

	/**
	 * Подключиться к базе данных
	 * @param  string $host    
	 * @param  string $user    
	 * @param  string $pass    
	 * @param  string $dbname  
	 * @param  string $charset 
	 * @throws DatabaseException     
	 */
	public function __construct(string $host, string $user, ?string $pass, string $dbname, string $charset = 'utf8'){
		try {
			$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
			$this->pdo = new \PDO($dsn, $user, $pass);
			$this->pdo->exec("set names ".$charset);
		} catch( PDOException $e ) {
			throw new DatabaseException( 
				'Connect error: '.$e->getMessage(), 
				$e->getCode(),
				['dsn' => $dsn]
			);
		}
	}

	public function getPDO(): \PDO {
		if(!is_object($this->pdo)) throw new DatabaseException('Database connection does not initialized');
		return $this->pdo;
	}

	/**
	 * Подготовить запрос
	 * @param  string $query Текст запроса
	 * @return Query
	 * @throws DatabaseException
	 */
	public function prepare(string $query): Query {
		try {
			return new Query($this, $query);
		} catch( \PDOException $e ) {
			throw new DatabaseException( 
				$e->getMessage(), 
				$e->getCode(),
				[
					'query' => $query,
					'vars' => $vars,
					'exception_class' => get_class($e)
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
	public function exec(string $query, array $vars = []): Query {
		try {
			$q = new Query($this, $query);
			$q->exec($vars);
			return $q;

		}catch(\PDOException $e) {
			throw new DatabaseException( 
				$e->getMessage(), 
				$e->getCode(),
				[
					'query' => $query,
					'vars' => $vars,
					'exception_class' => get_class($e)
				]
			);
		}
	}
	
	/**
	 * Возвращает ID последней вставленной строки
	 */
	public function lastInsertId(){
		return $this->pdo->lastInsertId();
	}	

	/**
	 * Возвращает имя текущей базы данных
	 * @return string
	 */
	public function getCurrentDatabase(): ?string {
		return $this->exec('SELECT database() "db"')->fetch()[0]['db'];
	}

	/**
	 * Получить размер занимаемых данных
	 * @param  string|null $table Имя таблицы (или null - размер всей базы)
	 * @return int Размер занимаемых данных в байтих
	 */
	public function getSize(?string $table = null): int {
		if(is_null($table)){
			$query = $this->exec('SELECT table_schema, SUM(data_length + index_length) AS "size"
				FROM information_schema.TABLES
				WHERE table_schema = :database', 
			['database' => $this->getCurrentDatabase()]);
		} else {
			$query = $this->exec('SELECT table_name,
				(data_length + index_length) AS "size"
				FROM information_schema.TABLES
				WHERE table_schema = :database AND table_name = :table', 
			[
				'database' => $this->getCurrentDatabase(),
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
	public function dump($tables = '*', bool $deleteQueries = false, $filePath = false): ?string {
	    $out = '';

	    try {
	        if ($tables == '*') {
	            $tables = [];
	            $query = $this->pdo->query('SHOW TABLES');
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
	            $query = $this->pdo->query('SELECT * FROM `' . $table . '`');
	            $numColumns = $query->columnCount();
	            
	            // Add DROP TABLE statement
	            if($deleteQueries){
	            	$out .= 'DROP TABLE `' . $table . '`;' . "\n\n";
	            }
	            
	            // Add CREATE TABLE statement
	            $query2 = $this->pdo->query('SHOW CREATE TABLE `' . $table . '`');
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