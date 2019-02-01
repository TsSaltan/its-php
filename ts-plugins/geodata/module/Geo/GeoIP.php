<?php
namespace tsframe\module\Geo;

use tsframe\exception\GeoException;

class GeoIP{
	public static function getLocation(string $ip): Location {
		$json = @file_get_contents('http://ipinfo.io/' . $ip . '/json');
		$data = json_decode($json, true);
		if(!is_array($data) || !isset($data['country'])){
			throw new GeoException('Invalid geo query');
		}

		return new Location($data['country'], $data['region'], $data['city']);
	}
}