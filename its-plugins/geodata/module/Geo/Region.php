<?php
namespace tsframe\module\Geo;

use tsframe\exception\GeoException;
use tsframe\module\database\Database;

class Region extends GeoItem {
	/**
	 * Получить регион по его id в базе
	 * @return Region
	 */
	public static function getById(int $id): Region {
		$query = Database::exec('SELECT * FROM `region` WHERE `id` = :id', ['id' => $id])->fetch();

		foreach ($query as $value) {
			$region = new self($value['id'], $value['name']);
			$region->countryId = $value['country_id'];
			return $region;
		}

		throw new GeoException("Invalid region id = '" . $id . "'");
	}
		/**
	 * Получить список регионов определенной страны
	 * @return Region[]
	 */
	public static function getList(int $country): array {
		$query = Database::exec('SELECT * FROM `region` WHERE `country_id` = :cid', ['cid' => $country])->fetch();
		$regions = [];

		foreach ($query as $value) {
			$region = new self($value['id'], $value['name']);
			$region->countryId = $value['country_id'];
			$regions[] = $region;
		}

		return $regions;
	}
	
	public static function find(string $name): Region {
		$query = Database::exec('SELECT * FROM `region` WHERE `name` LIKE :q', ['q' => "%name%"])->fetch();
		
		if(sizeof($query) == 0){
			return new self(-1, $name);
		}

		$region = new self($query[0]['id'], $query[0]['name']);
		$region->countryId = $query[0]['country_id'];
		return $region;
	}


	public function getCities(): array {
		return City::getList($this->getId());
	}

	public $countryId = -1;

	public function getParentCountry(): Country {
		return Country::getById($this->countryId);
	}
}