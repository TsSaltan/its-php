<?php
namespace tsframe\module\twilio;

use tsframe\Config;
use tsframe\exception\SMSException;
use tsframe\module\Log;

/**
 * API Twilio
 */
class API{
	const API_DATE = '2010-04-01';
	public static function query(string $method, array $params = []): array {
		$account = Config::get('twilio.account');
		$token = Config::get('twilio.token');
		$url = 'https://api.twilio.com/'.self::API_DATE.'/Accounts/'.$account.'/'.$method.'.json';
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_USERPWD => $account . ":" . $token
		]);

		if(sizeof($params) > 0){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);			
		}

		$result = curl_exec($ch);
		$decoded = json_decode($result, true);

		if(!$result || is_null($decoded) || $decoded['status'] >= 400){
			throw new SMSException('SMS API error', $decoded['status'], [
				'url' => $url,
				'answer' => $result,
				'curl_errno' => curl_errno($ch),
				'curl_error' => curl_error($ch),
				'curl_getinfo' => curl_getinfo($ch),
			]);
		}

		return $decoded;
	}
}