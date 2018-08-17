<?php
namespace tsframe\module\interkassa;

use tsframe\Config;

class API{
	const API_VERSION = 'v1';

	public static function query(string $method){
		$url = 'https://api.interkassa.com/'. self::API_VERSION .'/'.$method;
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => true, 
			CURLOPT_HTTPHEADER => [
				'Authorization: ' . Config::get('interkassa.key'), 
				'Ik-Api-Account-Id: ' . Config::get('interkassa.accountId'),
			]
		]);

		return json_decode(curl_exec($ch), true);
	}

	public static function checkPayment(array $data): bool {
		if(
			isset($data['ik_co_id']) &&
		)
	}
}