<?php
namespace tsframe\module\Geo;

use tsframe\exception\GeoException;
use tsframe\module\database\Database;

class City extends GeoItem {
	/**
	 * Получить город по id в базе
	 * @return City
	 */
	public static function getById(int $id): City {
		$query = Database::exec('SELECT * FROM `city` WHERE `id` = :id', ['id' => $id])->fetch();

		foreach ($query as $value) {
			$city = new self($value['id'], $value['name']);
			$city->regionId = $value['region_id'];

			return $city;
		}

		throw new GeoException("Invalid city id = '" . $id . "'");
	}

	/**
	 * Получить список городов для определенного региона
	 * @return City[]
	 */
	public static function getList(int $region): array {
		$query = Database::exec('SELECT * FROM `city` WHERE `region_id` = :rid', ['rid' => $region])->fetch();
		$cities = [];

		foreach ($query as $value) {
			$city = new self($value['id'], $value['name']);
			$city->regionId = $value['region_id'];
			$cities[] = $city;
		}

		return $cities;
	}

	public static function find(string $name): City {
		$query = Database::exec('SELECT * FROM `city` WHERE `name` LIKE :q', ['q' => "%name%"])->fetch();
		if(sizeof($query) == 0){
			return new self(-1, $name);
		}
		$city = new self($query[0]['id'], $query[0]['name']);
		$city->regionId = $value['region_id'];

		return $city;
	}

	public $regionId = -1;

	public function getParentRegion(): Region {
		return Region::getById($this->regionId);
	}
}