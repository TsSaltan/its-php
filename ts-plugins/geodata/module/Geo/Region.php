<?php
namespace tsframe\module\Geo;

use tsframe\module\database\Database;

class Region extends GeoItem {
	/**
	 * Получить список регионов определенной страны
	 * @return Region[]
	 */
	public static function getList(int $country): array {
		$query = Database::exec('SELECT * FROM `region` WHERE `country_id` = :cid', ['cid' => $country])->fetch();
		$regions = [];

		foreach ($query as $value) {
			$regions[] = new self($value['id'], $value['name']);
		}

		return $regions;
	}
	
	public static function find(string $name): Region {
		$query = Database::exec('SELECT * FROM `region` WHERE `name` LIKE :q', ['q' => "%name%"])->fetch();
		if(sizeof($query) == 0){
			return new self(-1, $name);
		}
		return new self($query[0]['id'], $query[0]['name']);
	}


	public function getCities(): array {
		return City::getList($this->getId());
	}
}