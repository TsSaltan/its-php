<?php
namespace tsframe\module;

use tsframe\Config;


class Bitly{
	const API_URL = 'http://api.bit.ly/v3/';

	private $accessToken;

	public function __construct(){
		$this->accessToken = Config::get('bitly.accessToken');
	}

	public function api(string $method, $postData = null){
		$ch = curl_init('https://api-ssl.bitly.com/v4/' . $method);
		$headers = ['Authorization: Bearer ' . $this->accessToken];

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => true
		]);

		if(!is_null($postData)){
			curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            $headers[] = 'Content-Type: application/json';
		}
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		return json_decode(curl_exec($ch), true);
	}

	public function shortUrl(string $url){
		$guid = null;
		$groups = $this->api('groups');
		foreach($groups as $group){
			if(isset($group['guid'])){
				$guid = $group['guid'];
				break;
			}
		}

		$shorten = $this->api('shorten', ['long_url' => $url, 'group_guid' => $guid, 'domain' => "bit.ly"]);
		return $shorten['link'] ?? null;
	}

}