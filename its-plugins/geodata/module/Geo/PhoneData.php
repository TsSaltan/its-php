<?php
namespace tsframe\module\Geo;

use tsframe\module\Geo\GeoIP;
use tsframe\module\Geo\Location;

class PhoneData {
	/**
	 * Загрузить список телефонных префиксов и кодов
	 * @param bool $detectDefault - Определит текущую страну по IP
	 */
	public static function load(bool $detectDefault = false){
		$countries = Country::getList();
		foreach($countries as $k => $country){
			$phone = $country->getPhone();
			if(!is_array($phone) || is_null($phone['mask']) || is_null($phone['prefix'])){
				unset($countries[$k]);
			}
		}

		$data = new self($countries);

		if($detectDefault){
			$location = GeoIP::getLocation();
			$data->setCurrentLocation($location);
		}

		return $data;
	}

	protected $countryList = [];
	protected $currentLoc;

	public function __construct(array $countryList){
		$this->countryList = $countryList;
	}

	public function setCurrentLocation(Location $loc){
		$this->currentLoc = $loc;
	}

	public function getData(): array {
		$data = [];
		foreach($this->countryList as $country){
			$item = $country->getPhone();
			$item['country'] = $country;

			if($this->currentLoc instanceof Location){
				$item['current'] = $country->getId() == $this->currentLoc->getCountry()->getId();
			} else {
				$item['current'] = false;
			}

			$data[] = $item;
		}

		if($this->currentLoc instanceof Location){
			usort($data, function($a, $b){
				if($a['current']){
					return -1;
				}

				if($b['current']){
					return 1;
				}
			
				return 0;
			});
		}
		return $data;
	}

	public function __toString(){
		$data = $this->getData();
		foreach($data as $k => $item){
			$country = $item['country'];

			$data[$k]['cc'] = $country->getAlias();
			$data[$k]['name'] = $country->getName();
			$data[$k]['flag'] = $country->getFlag()['url'];
			unset($data[$k]['country']);
		}

		return json_encode($data);
	}
}