<?php
namespace tsframe\module\database;

use tsframe\exception\DatabaseException;

/**
 * @todo
 */
class MySQL{
	/**
	 * Query text
	 * @var string
	 */
	protected $query = "";

	/**
	 * Vars for preparing
	 * @var array
	 */
	protected $vars = [];

	/**
	 * Setected table
	 * @var string
	 */
	protected $table;

	/**
	 * Is query executed
	 * @var bool
	 */
	protected $executed = false;

	/**
	 * @var Query
	 */
	protected $dbQuery;

	public function __construct(string $table){
		$this->table = $table;
	}

	/**
	 * Insert data
	 * @param  array  $data  Ассоциативный массив поле => значение
	 */
	public function insert(array $data): MySQL {
		$this->query = "INSERT INTO `" . $this->table . "` ";
		$cols = $this->getColumnList(array_keys($data));
		$this->query .= '(' . $cols . ') VALUES ';
		$vals = $this->getValuesList($data);
		$this->query .= '(' . $vals . ')';

		return $this->exec();
	}

	public function exec(): Query {
		if($this->executed) return $this->dbQuery;

		$this->dbQuery = Database::prepare($this->query);
		foreach ($this->vars as $key => $value) {
			$this->dbQuery->bind($key, $value);
		}
		$this->dbQuery->exec();

		$this->executed = true;
		return $this->dbQuery;
	}

	protected function getColumnList(array $cols): string {
		$list = [];
		foreach ($cols as $col) {
			$list[] = (substr($col, 0, 1) == '`') ? $col : '`' . $col . '`';
		}

		return implode(', ', $list);
	}

	protected function getValuesList(array $data): string {
		$list = [];
		foreach ($data as $key => $value) {
			$key = is_string($key) ? $key : md5($value);
			$list[] = ':' . $key;
			$this->vars[$key] = $value;
		}

		return implode(', ', $list);
	}
}

// MySQL::selectTable('table')
// 		->insert(['a' => 'b', 'c' => 'd']);
// 		
// 		->insertOrUpdate(['a' => 'b', 'c' => 'd']);
// 		
// 		->select('*')
// 		->where('a', '=', $b)
// 		->or('c', '=', $d)
// 		->and('e', '<', $f)
// 		->fetch();
// 		
// 		->update(['a' => 'b'])
// 		->where('a', '=', 'b')
// 		->exec();
// 		