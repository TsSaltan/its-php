<?php
namespace tsframe\module\Geo;

class PhoneData {
	public static function load(){
		$countries = Country::getList();
		foreach($countries as $k => $country){
			$phone = $country->getPhone();
			if(!is_array($phone) || is_null($phone['mask']) || is_null($phone['prefix'])){
				unset($countries[$k]);
			}
		}

		return new self($countries);
	}

	protected $countryList = [];

	public function __construct(array $countryList){
		$this->countryList = $countryList;
	}

	public function getData(): array {
		$data = [];
		foreach($this->countryList as $country){
			$item = $country->getPhone();
			$item['country'] = $country;
			$data[] = $item;
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