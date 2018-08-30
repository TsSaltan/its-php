<?php
namespace tsframe\module\twilio;

use tsframe\Config;
use tsframe\exception\SMSException;
use tsframe\module\Log;

/**
 * Отправка SMS через API Twilio
 */
class SMS{
	/**
	 * Отправить SMS
	 * @param  string  $phone
	 * @param  string  $message
	 * @return array
	 */
	public static function send(string $phone, string $message, string $from = null): array {
		$from = is_null($from) ? Config::get('twilio.phone') : $from;

		try{
			$query = API::query('Messages', [
				'To' => $phone, 
				'From' => $from, 
				'Body' => $message
			]);

			Log::sms('Send message to ' . $phone, [
				'phone' => $phone,
				'message_text' => $message,
				'api_answer' => $query
			]);

		} catch(SMSException $e){
			Log::sms('Send sms error', [
				'phone' => $phone,
				'message_text' => $message,
				'errorDebug' => $e->getDebug()
			]);

			throw $e;
		}

		return $query;
	}
}