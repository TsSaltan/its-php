<?php
namespace tsframe\module;

use tsframe\Config;
use tsframe\exception\SMSException;
use tsframe\module\Log;

/**
 * Отправка SMS через API
 * https://smsc.ru/api/http/
 */
class SMS{
	/**
	 * Отправить SMS
	 * @param  string|array $phones
	 * @param  string  $message [description]
	 * @return array
	 */
	public static function send($phones, string $message){
		$phones = is_array($phones) ? implode(';', $phones) : $phones;

		try{
			$query = self::apiQuery('send', ['phones' => $phones, 'mes' => $message, 'id' => uniqid('sms_')]);
			
			Log::sms('Send message to ' . $phones, [
				'phone' => $phones,
				'message_text' => $message,
				'api_answer' => $query
			]);
		} catch(SMSException $e){
			Log::sms('Send sms error', [
				'phone' => $phones,
				'message_text' => $message,
				'errorDebug' => $e->getDebug()
			]);

			throw $e;
			
		}
		
		return $query;
	}

	protected static function apiQuery(string $method, array $fields = []){
		$url = 'https://smsc.ru/sys/' . $method . '.php';
		$fields['login'] = Config::get('smsc.login');
		$fields['psw'] = Config::get('smsc.password');
		$fields['fmt'] = 3;
		$fields['charset'] = 'utf-8';

		$url .= '?' . http_build_query($fields);

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => true
		]);

		$result = curl_exec($ch);
		$decode = json_decode($result, true);

		if(strpos($result, 'error') !== false || !$result || is_null($decode) || isset($decode['error'])){
			throw new SMSException('SMS API error', 0, [
				'url' => $url,
				'answer' => $result,
				'curl_errno' => curl_errno($ch),
				'curl_error' => curl_error($ch),
				'curl_getinfo' => curl_getinfo($ch),
			]);
		}

		return $decode;
	}
}