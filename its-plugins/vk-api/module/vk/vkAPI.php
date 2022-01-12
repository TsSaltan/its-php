<?php 
namespace tsframe\module\vk;

use tsframe\exception\VKException;

class vkAPI {
	const BASE_URL = 'https://api.vk.com/method/';
	const VERSION = 5.92;

	public static function getTokenURL(string $appId, string $scope, ?string $groups = null, string $redirectUri = 'https://oauth.vk.com/blank.html', string $responseType = 'code', string $display = 'page'){
		$params = [
			'client_id' => $appId,
			'scope' => $scope,
			'redirect_uri' => $redirectUri,
			'response_type' => $responseType,
			'display' => $display,
			'v' => self::VERSION
		];

		if(strlen($groups) > 0){
			$params['group_ids'] = $groups;
		}

		return 'https://oauth.vk.com/authorize?' . http_build_query($params);
	}

	/**
	 * Получить access_token по временному коду 
	 * @return array [access_token, expires_in, user_id]
	 */
	public static function getAccessToken(string $appId, string $secretKey, string $userCode, string $redirectURI = 'https://oauth.vk.com/blank.html'){
		$vkApi = new self;
		return $vkApi->query('access_token', ['client_id' => $appId, 'client_secret' => $secretKey, 'code' => $userCode, 'redirect_uri' => $redirectURI], 'GET', 'https://oauth.vk.com/');
	}

	/**
	 * Токен для совершения запросов к API
	 * @var string
	 */
	protected $accessToken;

	public function __construct(?string $accessToken = null){
		if(!is_null($accessToken)){
			$this->setAccessToken($accessToken);
		}
	}

	/**
	 * @param string $accessToken
	 */
	public function setAccessToken(string $accessToken){
	    $this->accessToken = $accessToken;
	    return $this;
	}

	public function query(string $method, array $params = [], string $httpMethod = 'POST', ?string $baseURI = null): array {
		$base = !is_null($baseURI) ? $baseURI : self::BASE_URL;
		$params['v'] = self::VERSION;

		if(strlen($this->accessToken) > 0){
			$params['access_token'] = $this->accessToken;
		}

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_CUSTOMREQUEST => $httpMethod,
		]);

		if($httpMethod == 'GET'){
			curl_setopt($ch, CURLOPT_URL, $base . $method . '?' . http_build_query($params) );
		}
		else{
			curl_setopt_array($ch, [
				CURLOPT_URL => $base . $method ,
				CURLOPT_POSTFIELDS => $params,
			]);
		}

		$result = curl_exec($ch);
		$error = curl_error($ch);
		$data = json_decode($result, true);

		if(strlen($error) > 1 || $result === false || !is_array($data)){
			throw new VKException('VK query error', 0, [
				'method' => $method,
				'params' => $params,
				'httpMethod' => $httpMethod,
				'result' => $result,
				'error' => $error,
				'curlinfo' => curl_getinfo($ch)
			]);
		}

		if(isset($data['error'])){
			throw new VKException('VK API error', 1, [
				'method' => $method,
				'params' => $params,
				'httpMethod' => $httpMethod,
				'result' => $result,
				'error' => $data['error'],
				'curlinfo' => curl_getinfo($ch)
			]);
		}

		return $data;
	}

	public function __call(string $method, array $args = []){
		$methodName = preg_replace_callback('#([a-z])([A-Z])#U', 
			function($matches){
				return $matches[1] . '.' . strtolower($matches[2]);
			}, $method, 1);

		return $this->query($methodName, ($args[0] ?? []), ($args[1] ?? 'GET'));
	}
}