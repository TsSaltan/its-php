<?php
namespace tsframe\module\Geo;

use tsframe\module\database\Database;

class Location{
	/**
	 * @var Country
	 */
	protected $country;

	/**
	 * @var Region
	 */
	protected $region;

	/**
	 * @var City
	 */
	protected $city;

	public function __construct(string $country, string $region, string $city){
		try{
			$this->country = Country::getByAlias($country);
		} catch(GeoException $e){
			$this->country = Country::find($country);
		}

		$this->region = Region::find($region);
		$this->city = City::find($city);
	}

	public function getText(): string {
		$co = $this->country->getName();
		$re = $this->region->getName();
		$ci = $this->city->getName();

		$co .= (strlen($re) > 1 ? ', ' : '');
		$re .= (strlen($ci) > 1 ? ', ' : '');
		
		return $co . $re . $ci;
	}

	public function __toString(): string {
		return $this->getText();
	}

	/**
	 * @return Country
	 */
	public function getCountry(): Country {
	    return $this->country;
	}

	/**
	 * @return Region
	 */
	public function getRegion(): Region {
	    return $this->region;
	}

	/**
	 * @return City
	 */
	public function getCity(): City {
	    return $this->city;
	}
}