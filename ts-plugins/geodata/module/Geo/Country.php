<?php
namespace tsframe\module\Geo;

use tsframe\exception\GeoException;
use tsframe\module\database\Database;

class Country extends GeoItem {
	/**
	 * Получить список стран
	 * @return Country[]
	 */
	public static function getList(): array {
		$query = Database::exec('SELECT * FROM `country`')->fetch();
		$countries = [];

		foreach ($query as $value) {
			$countries[] = new self($value['id'], $value['name'], $value['alias']);
		}

		return $countries;
	}

	/**
	 * Получить страну по буквенному коду ISO
	 * @param  string $code RU, BY, etc...
	 * @return Country[]
	 * @throws GeoException
	 */
	public static function getByAlias(string $code): Country {
		$query = Database::exec('SELECT * FROM `country` WHERE `alias` = :alias', ['alias' => $code])->fetch();
		if(sizeof($query) == 0){
			throw new GeoException("Can not found country by alias '" . $code . "'");
		}
		return new self($query[0]['id'], $query[0]['name'], $query[0]['alias']);
	}


	public static function find(string $name): Country {
		$query = Database::exec('SELECT * FROM `country` WHERE `name` LIKE :q', ['q' => "%name%"])->fetch();
		if(sizeof($query) == 0){
			return new self(-1, $name);
		}
		return new self($query[0]['id'], $query[0]['name'], $query[0]['alias']);
	}

	/**
	 * ISO алиас для страны
	 * @var string
	 */
	protected $alias;

	public function __construct(int $id, string $name, ?string $alias = null){
		$this->id = $id;
		$this->name = $name;
		$this->alias = $alias;
	}

	/**
	 * @return string
	 */
	public function getAlias(): string {
	    return $this->alias;
	}

	public function getRegions(): array {
		return Region::getList($this->getId());
	}
}