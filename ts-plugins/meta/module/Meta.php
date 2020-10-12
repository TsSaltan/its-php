<?php
namespace tsframe\module;

use tsframe\module\database\Database;

class Meta {

	protected $parent;
	protected $data = [];

	public function __construct(...$args){
		$this->parent = implode('_', $args);
	}	

	/**
	 * Чтоб сэкономить запросы, можно указать уже загруженные данные
	 */
	public function setLoadedData(array $data){
		$this->data = $data;
		$this->isLoaded = true;
	}

	/**
	 * Флаг - были ли загружены данные ранее
	 */
	protected $isLoaded = false;

	/**
	 * Если данные не были загружены - грузим их из бд
	 */
	protected function loadData(){
		if($this->isLoaded) return;

		$data = Database::prepare('SELECT * FROM `meta` WHERE `parent` = :parent_id')
				->bind('parent_id', $this->parent)
				->exec()
				->fetch();

		foreach ($data as $value) {
			$this->data[$value['key']] = $value['value'];
		}

		$this->isLoaded = true;
	}

	public function set(string $key, $value){
		$this->getData();

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
		return $this->getData()[$key] ?? null;
	}

	public function getData(): array {
		$this->loadData();
		return $this->data;
	}

	public function isExists(): bool {
		return sizeof($this->getData()) > 0;
	}

	public function delete(): bool {
		return Database::prepare('DELETE FROM `meta` WHERE `parent` = :parent')
						->bind('parent', $this->parent)
						->exec()
						->affectedRows() > 0;
	}
	
	public static function findByParent(string $parentMask): array {
		return self::find('*', '*', $parentMask);
	}

	public static function find(string $key = '*', string $value = '*', string $parentMask = '*'): array {
		$found = [];
		
		$keyQuery = ($key == '*') ? '1 = 1' : '`key` = :key';
		$valQuery = ($value == '*') ? '1 = 1' : '`value` = :value';
		$parentQuery = ($parentMask == '*') ? '1 = 1' : '`parent` LIKE :parent';

		$query = Database::prepare('SELECT * FROM `meta` WHERE ' . $keyQuery . ' AND ' . $valQuery . ' AND ' . $parentQuery);

		if($key != '*') $query->bind('key', $key);
		if($value != '*') $query->bind('value', $value);
		if($parentMask != '*') $query->bind('parent', $parentMask);
				
		
		$data = $query->exec()
					  ->fetch();

		foreach ($data as $meta) {
			$found[$meta['parent']][$meta['key']] = $meta['value'];
		}

		foreach ($found as $parent => $data) {
			$found[$parent] = new self($parent);
			$found[$parent]->setLoadedData($data);
		}

		return $found;
	}

	public static function getParentList(?string $filter = null): array {
		if(is_null($filter)){
			$query = Database::prepare('SELECT DISTINCT `parent` p FROM `meta` ORDER BY p ASC');
		} else {
			$query = Database::prepare('SELECT DISTINCT `parent` p FROM `meta` WHERE `parent` LIKE :filter ORDER BY p ASC');
			$query->bind('filter', $filter . '%');
		}
		$parents = $query->exec()->fetch();
		return array_column($parents, 'p');
	}

	/**
	 * Создаёт Meta элемент с уникальным значением родителя
	 * @return Meta
	 */
	public static function create(): Meta {
		$args = func_get_args();
		$append = 0;
		do{
			$argList = $args;
			$argList[] = $append;
			$meta = new self(...$argList);
			$append++;
		} while($meta->isExists());

		return $meta;
	}
}