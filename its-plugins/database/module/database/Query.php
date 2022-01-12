<?php
namespace tsframe\module\database;

use tsframe\Hook;
use tsframe\exception\DatabaseException;
use tsframe\module\database\Database;

define('TYPE_INT', \PDO::PARAM_INT);
define('TYPE_STRING', \PDO::PARAM_STR);
define('TYPE_BOOL', \PDO::PARAM_BOOL);

class Query {
	public $sth;
	public $pdo;
	public $connection;

	public function __construct(Connection $db, string $query){
		$this->connection = $db;
		$this->pdo = $db->getPDO();
		$this->sth = $this->pdo->prepare($query);
	}

	public function exec($vars = false){
		if(is_array($vars))$this->sth->execute($vars);
		else $this->sth->execute();
		
		Hook::call('database.query', [$this]);
		return $this;
	}

	public function fetch($mode = \PDO::FETCH_ASSOC){
		return $this->sth->fetchAll($mode);
	}		
		
	public function bind($var, $value = null, $type = TYPE_STRING){
		if(is_array($var)){
			foreach($var as $v){
				$this->sth->bindParam($v[0], $v[1], (isset($v[2])?$v[2]:TYPE_STRING));
			}
		}
		else $this->sth->bindParam($var, $value, $type);

		return $this;
	}	
		
	public function affectedRows(){
		return $this->sth->rowCount();
	}
		
	public function lastInsertId(){
		return Database::$pdo->lastInsertId();
	}	

	public function getDebug() : string {
		ob_start();
		$this->sth->debugDumpParams();
		$data = ob_get_contents();
  		ob_end_clean();

  		return $data;
	}	
}