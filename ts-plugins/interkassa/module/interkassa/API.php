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

	public static function checkPayment(array $data) {
		$sign = self::genSign($data);
		$data['ik_sign'] = $sign;
		$ch = curl_init('https://sci.interkassa.com/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        return $result;
	}

	protected static function genSign(array $data){
		$secretKey = Config::get('interkassa.key');
		if(isset($data['ik_sign'])) unset($data['ik_sign']);
		foreach ($data as $key => $value) {
	        if (!preg_match('/ik_/', $key)){
	        	unset($data[$key]);
	        }
	    }

	    ksort($data, SORT_STRING);
	    array_push($data, $secretKey);
	    $arg = implode(':', $data);
	    return base64_encode(md5($arg, true));
	}
}