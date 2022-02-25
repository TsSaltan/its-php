<?php
namespace tsframe\module\Geo;

use tsframe\exception\GeoException;
use tsframe\exception\TemplateException;
use tsframe\module\database\Database;
use tsframe\view\Template;
use tsframe\view\TemplateRoot;

class Country extends GeoItem {
	/**
	 * Получить страну по id в базе
	 * @return Country
	 */
	public static function getById(int $id): Country {
		$query = Database::exec('SELECT * FROM `country` WHERE `id` = :id', ['id' => $id])->fetch();

		foreach ($query as $value) {
			return new self($value['id'], $value['name'], $value['alias']);
		}

		throw new GeoException("Invalid country id = '" . $id . "'");
	}

	/**
	 * Получить список стран
	 * @return Country[]
	 */
	public static function getList(): array {
		$query = Database::exec('SELECT * FROM `country`')->fetch();
		$countries = [];

		foreach ($query as $value) {
			$countries[] = new self($value['id'], $value['name'], $value['alias'], $value['phone_prefix'], $value['phone_mask']);
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

	/**
	 * Данные о префиксе и маске номера телефона страны 
	 * @var array ['prefix' => '+123', 'mask' => '+123(###)-##-##']
	 */
	protected $phone;

	public function __construct(int $id, string $name, ?string $alias = null, ?string $phonePrefix = null, ?string $phoneMask = null){
		$this->id = $id;
		$this->name = $name;
		$this->alias = $alias;
		$this->phone = ['prefix' => $phonePrefix, 'mask' => $phoneMask];
	}

	/**
	 * @return string
	 */
	public function getAlias(): string {
	    return $this->alias;
	}

	public function getPhone(): ?array {
	    return $this->phone;
	}

	public function getRegions(): array {
		return Region::getList($this->getId());
	}

	/**
	 * Возвращает URL и путь к файлу с флагом
	 * @return array ['url' => ..., 'filepath' => ...]
	 */
	public function getFlag(): array {
		$alias = $this->getAlias();
		try {
			$files = TemplateRoot::findFiles('geodata', 'flags' . DS . strtolower($alias) . '.png');
		} catch (TemplateException $e){
			try {
				$files = TemplateRoot::findFiles('geodata', 'flags' . DS . 'empty.png');
			} catch (TemplateException $e){
				return ['filepath' => null, 'url' => null];
			}
		}

		$tpl = new Template('geo', 'geo');

		if(isset($files[0])){
			return [
				'filepath' => $files[0],
				'url' => $tpl->toURI($files[0])
			];
		}
	}
}