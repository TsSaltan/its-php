<?php
namespace tsframe\module\Geo;

use tsframe\module\database\Database;

class City extends GeoItem {
	/**
	 * Получить список городов для определенного региона
	 * @return City[]
	 */
	public static function getList(int $region): array {
		$query = Database::exec('SELECT * FROM `city` WHERE `region_id` = :rid', ['rid' => $region])->fetch();
		$cities = [];

		foreach ($query as $value) {
			$cities[] = new self($value['id'], $value['name']);
		}

		return $cities;
	}

	public static function find(string $name): City {
		$query = Database::exec('SELECT * FROM `city` WHERE `name` LIKE :q', ['q' => "%name%"])->fetch();
		if(sizeof($query) == 0){
			return new self(-1, $name);
		}
		return new self($query[0]['id'], $query[0]['name']);
	}
}