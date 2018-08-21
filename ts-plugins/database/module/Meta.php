<?php
namespace tsframe\module;

use tsframe\module\database\Database;

class Meta{

	protected $parent;
	protected $data = [];

	public function __construct(){
		$this->parent = implode('_', func_get_args());
		$data = Database::prepare('SELECT * FROM `meta` WHERE `parent` = :parent_id')
				->bind('parent_id', $this->parent)
				->exec()
				->fetch();

		foreach ($data as $value) {
			$this->data[$value['key']] = $value['value'];
		}
	}	

	public function set(string $key, $value){
		if(is_null($value) || strlen($value) == 0){
			$query = Database::prepare('DELETE FROM `meta` WHERE (`parent` = :parent AND `key` = :key) OR `value` = null');
		} else if(isset($this->data[$key])){
			$query = Database::prepare('UPDATE `meta` SET `value` = :value WHERE `key` = :key AND `parent` = :parent');
			$query->bind('value', $value);
		} else {
			$query = Database::prepare('INSERT INTO `meta` (`parent`, `key`, `value`) VALUES (:parent, :key, :value)');
			$query->bind('value', $value);
		}

		$this->data[$key] = $value;
		$query->bind('parent', $this->parent)
			  ->bind('key', $key)
			  ->exec();
	}

	public function getParent(): string {
		return $this->parent;
	}

	public function get(string $key){
		return $this->data[$key] ?? null;
	}

	public function getData(): array {
		return $this->data;
	}
	
	public static function find(string $key = '*', string $value = '*'): array {
		$return = [];
		
		$keyQuery = ($key == '*') ? '1 = 1' : '`key` = :key';
		$valQuery = ($value == '*') ? '1 = 1' : '`value` = :value';

		$query = Database::prepare('SELECT * FROM `meta` WHERE ' . $keyQuery . ' AND ' . $valQuery);

		if($key != '*') $query->bind('key', $key);
		if($value != '*') $query->bind('value', $value);
				
		
		$data = $query->exec()
					  ->fetch();

		foreach($data as $item){
			$parent = $item['parent'];
			if(!isset($return[$parent])){
				$return[$parent] = new self($parent);
			}
		}
		return $return;
	}
}