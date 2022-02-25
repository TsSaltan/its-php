<?php
namespace tsframe\module\Geo;

use tsframe\exception\GeoException;
use tsframe\module\Cache;

class GeoIP {
	public static function getServerIP(){
		return $_SERVER['SERVER_ADDR'];
	}

	public static function getClientIP(){
		return $_SERVER['REMOTE_ADDR'];
	}

	public static function getLocation(?string $ip = null): Location {
		$ip = (is_null($ip) || strlen($ip) < 7) ? self::getClientIP() : $ip;

		$data = Cache::toDatabase('geoip-' . $ip, function() use ($ip){
			if(rand(1, 2) == 1){
				$data = self::ipinfo($ip);
			} else {
				$data = self::ipapi($ip);
			}

			return $data;
		});

		return new Location($data['country'], $data['region'], $data['city'], $data['lat'], $data['lon']);
	}

	/**
	 * @link http://ipinfo.io/
	 */
	public static function ipinfo(?string $ip = null){
		$url = 'http://ipinfo.io/' . (!is_null($ip) ? $ip . '/' : '') . 'json';
		$data = file_get_contents($url);
		$json = json_decode($data, true);

		if(!is_array($json) || !isset($json['ip'])){
			throw new GeoException('Invalid geo query (via ipinfo.io)', 0, [
				'url' => $url,
				'answer' => $data
			]);
		}

		if(isset($json['loc'])){
			$coord = explode(',', $json['loc']);
		}

		return [
			'ip' => $json['ip'],
			'country' => $json['country'] ?? null,
			'city' => $json['city'] ?? null,
			'region' => $json['region'] ?? null,
			'lat' => $coord[0] ?? -1,
			'lon' => $coord[1] ?? -1,
			'org' => $json['org'] ?? null,
			'source' => 'ipinfo.io',
		];
	}

	/**
	 * @link http://ip-api.com/
	 */
	public static function ipapi(?string $ip = null){
		$url = 'http://ip-api.com/json/' . (!is_null($ip) ? $ip : '');
		$data = file_get_contents($url);
		$json = json_decode($data, true);

		if(!is_array($json) || !isset($json['query'])){
			throw new GeoException('Invalid geo query (via ip-api.com)', 0, [
				'url' => $url,
				'answer' => $data
			]);
		}

		return [
			'ip' => $json['query'],
			'country' => $json['countryCode'] ?? null,
			'city' => $json['city'] ?? null,
			'region' => $json['regionName'] ?? null,
			'lat' => $json['lat'] ?? -1,
			'lon' => $json['lon'] ?? -1,
			'org' => $json['isp'] ?? null,
			'source' => 'ip-api.com',
		];
	}
}