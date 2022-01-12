<?php
namespace tsframe\module\database;

trait DataTrait{
	public function setData(array $data) : bool {
		$q = [];
		foreach ($data as $key => $value) {
    		if(!property_exists($this, $key) || $key == 'id') continue;
    		$this->{$key} = $value;
    		$q[] = '`'.$key.'` = :' . $key;
    	}

    	$sql = Database::prepare('UPDATE `'. $this->tableName .'` SET ' . implode(', ', $q) . ' WHERE `id` = :id');

    	foreach ($data as $key => $value) {
    		if(!property_exists($this, $key) || $key == 'id') continue;
    		$sql->bind($key, $value);
    	}

    	$sql->bind('id', $this->id);
    	return $sql->exec()->affectedRows() > 0;
	}
}