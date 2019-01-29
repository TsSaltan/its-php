<?php
namespace tsframe\module;

use tsframe\Config;

class Bitly{
	const API_URL = 'https://api-ssl.bitly.com/v4/';

	/**
	 * Секретный токен для API запросов
	 * @var string
	 */
	private $accessToken;

	public function __construct(?string $token = null){
		$this->accessToken = strlen($token) > 1 ? $token : Config::get('bitly.accessToken');
	}

	/**
	 * Есть ли корректный токен для API
	 * @return boolean
	 */
	public function isTokenCorrect(): bool {
		return strlen($this->accessToken) > 1;
	}

	/**
	 * Запрос к API
	 * @param  string $method  
	 * @param  array $postData 
	 * @return array|null
	 */
	public function api(string $method, ?array $postData = []): ?array {
		if(!$this->isTokenCorrect()){
			return ['error' => 'Invalid access token'];
		}

		$ch = curl_init(self::API_URL . $method);
		$headers = ['Authorization: Bearer ' . $this->accessToken];

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => true
		]);

		if(is_array($postData) && sizeof($postData) > 0){
			curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            $headers[] = 'Content-Type: application/json';
		}
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		return json_decode(curl_exec($ch), true);
	}

	/**
	 * Сократить ссылку
	 * @param  string $url
	 * @return string|null Если не удалось сократить ссылку, вернёт null
	 */
	public function shortUrl(string $url): ?string {
		$guid = null;
		$groups = $this->api('groups');
		if(is_array($groups)){
			foreach($groups as $group){
				if(isset($group['guid'])){
					$guid = $group['guid'];
					break;
				}
			}

			$shorten = $this->api('shorten', ['long_url' => $url, 'group_guid' => $guid, 'domain' => "bit.ly"]);
		}
		return $shorten['link'] ?? null;
	}

}