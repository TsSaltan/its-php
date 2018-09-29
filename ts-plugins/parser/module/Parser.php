<?php
namespace tsframe\module;

use tsframe\exception\ParserException;

class Parser{

	/**
	 * Curl resourse
	 */
	protected $ch = null;

	/**
	 * Curl params
	 * @var array
	 */
	protected $params = [];

	/**
	 * Queries log
	 * @var array[ ['GET|POST URL'] ]
	 */
	protected $queries = [];

	/**
	 * 0 - ничего не логировать
	 * 1 - не сохранять данные запросов, только заголовки
	 * 2 - полное логирование
	 * @var integer
	 */
	protected $logPolicy = 1;

	const LOG_MAX = 2;
	const LOG_MIN = 1;
	const LOG_NONE = 0;

	public function __construct(string $url = null){
		$this->init();
		$this->resetParams();
		if(!is_null($url)){
			$this->setURL($url);
		}
	}

	/**
	 * Инициализация парсера
	 */
	public function init(){
		$this->ch = curl_init();
		return $this;
	}

	/**
	 * Установить URL
	 * @param string $url
	 */
	public function setURL(string $url){
		return $this->setParams([CURLOPT_URL => $url]);
	}

	/**
	 * Установить User-Agent
	 */
	public function setUserAgent(string $ua = null){
		// если не указан - сгенерируем как у браузера
		if(is_null($ua)){ 
			$os = ['Windows NT 10.0; Win64; x64', 'Windows NT 10.0; WOW64', 'Windows NT 6.3; WOW64; rv:52.0', 'Macintosh; Intel Mac OS X 10_13_4', 'Macintosh; Intel Mac OS X 10_13_6'];
			$engine = [
				'Gecko/20100101 Firefox/52.0', 
				'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/' . rand(60,70) . '.0.' . rand(1000, 9999) . '.' . rand(100, 999) . ' Safari/537.36 OPR/43.0.' . rand(1000, 9999) . '.' . rand(100, 999),
				'AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1 Safari/605.1.' . rand(0,20),
				'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/' . rand(60,70) . '.0.' . rand(1000, 9999) . '.' . rand(100, 999) . ' Safari/537.36'
			];

			$ua = 'Mozilla/5.0 ('. $os[array_rand($os)] .') ' . $engine[array_rand($engine)];
		}
		
		return $this->setParams([CURLOPT_USERAGENT => $ua]);
	}

	/**
	 * Сбросить параметры curl по умолчанию
	 */
	public function resetParams(){
		$this->params = [];
		return $this->setParams([
			CURLOPT_CONNECTTIMEOUT => 60,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLINFO_HEADER_OUT => true
		]);
	}

	/**
	 * Получить список установленных параметров
	 * @return array [string_constant => value]
	 */
	public function getParams(bool $textKeys = true): array {
		if(!$textKeys) return $this->params;

		$params = [];
		$consts = get_defined_constants(true)['curl'] ?? [];
		foreach ($this->params as $key => $value) {
			// Сначала ищем curlopt
			foreach ($consts as $ckey => $cvalue) {
				if(strpos($ckey, 'CURLOPT_') === 0 && $cvalue == $key){
					$params[$ckey] = $value;
					continue 2;
				}
			}

			// Но также могут быть и curlinfo
			foreach ($consts as $ckey => $cvalue) {
				if(strpos($ckey, 'CURLINFO_') === 0 && $cvalue == $key){
					$params[$ckey] = $value;
					continue 2;
				}
			}
			
			$params[$key] = $value;
		}

		return $params;
	}

	/**
	 * Установить cur параметры
	 * @param array $params
	 */
	public function setParams(array $params = []){
		$this->params = $params + $this->params;
		curl_setopt_array($this->ch, $this->params);
		return $this;
	}

	/**
	 * Проверка SSL
	 */
	public function sslVerify(bool $value){
		return $this->setParams([
			CURLOPT_SSL_VERIFYHOST => ($value ? 2 : 0),
			CURLOPT_SSL_VERIFYPEER => $value
		]);
	}

	/**
	 * Установить файл для хранения кук
	 * @param string $saveName
	 */
	public function setCookieFile(string $saveName = null){
		$saveName = is_null($saveName) ? parse_url(($this->params[CURLOPT_URL] ?? null), PHP_URL_HOST) : $saveName;
		$cookieFile =  TEMP . 'cookies_' . $saveName. '.txt';
		if(!file_exists($cookieFile)){
			file_put_contents($cookieFile, "");
		}

		$this->setParams([
			CURLOPT_COOKIEJAR => realpath($cookieFile),
			CURLOPT_COOKIEFILE => realpath($cookieFile)
		]);
	
		return $this;
	}

	/**
	 * Установить куки
	 * @param array $cookies [key => value, ...]
	 */
	public function setCookies(array $cookies){
		return $this->setParams([CURLOPT_COOKIE => http_build_query($cookies)]);
	}

	/**
	 * Установить заголовки
	 * @param array $headers [header1, header2, ...]
	 */
	public function setHeaders(array $headers){
		return $this->setParams([CURLOPT_HTTPHEADER => $headers]);
	}

	/**
	 * Запустить выполнение запроса
	 * @param  int|integer $attempts Количество попыток
	 * @return ParserResponse
	 */
	public function exec(int $attempts = 1): ParserResponse {
		$attempt = 0;
		do{
			$response = new ParserResponse($this->ch);
			$this->logQuery($response);
		}
		while($attempt++ < $attempts && $response->hasError());

		if($response->hasError()){
			throw new ParserException('Invalid query', $response->getError(), [
				'attempts' => $attempts,
				'params' => $this->getParams(),
				'response' => $response
			]);
		}

		return $response;
	}

	/**
	 * Логирование запросов
	 * @param  ParserResponse $response
	 */
	protected function logQuery(ParserResponse $response){
		if($this->logPolicy == self::LOG_NONE) return;

		$responseData = [
			'code' => $response->getResponseCode(),
			'length' => $response->getResponseLength(),
			'contentType' => $response->getResponseContentType()
		];

		if($this->logPolicy == self::LOG_MAX){
			$responseData['body'] = $response->getResponseBody();
			$requestData = ['url' => ($this->params[CURLOPT_URL] ?? null), 'headers' => $response->getRequestHeader()];
		} else {
			$requestData = $response->getRequestMethod() . ' ' . ($this->params[CURLOPT_URL] ?? null);
		}

		if($response->isRedirected()){
			$responseData['redirect'] = $response->getRedirectedURI();
		}

		$query = [
			'request' => $requestData,
			'response' => $responseData
		];

		if($response->hasError()){
			$query['error'] = $response->getError();
		}

		return $this->queries[] = $query;
	}

	/**
	 * Получить логи
	 * @return array
	 */
	public function getLogs(): array {
		return $this->queries;
	}

	public function setLogPolicy(int $policy){
		$this->logPolicy = $policy;
	}


	/**
	 * Выполнить GET запрос
	 * @return PaserResponse
	 */
	public function get(int $attempts = 1){
		$this->setRequestMethod('GET');
		return $this->exec($attempts);
	}

	/**
	 * Выполнить POST запрос
	 * @return PaserResponse
	 */
	public function post($data, int $attempts = 1){
		$this->setRequestMethod('POST');
		$this->setParams([CURLOPT_POSTFIELDS => $data]);
		return $this->exec($attempts);
	}

	/**
	 * Установить метод HTTP запроса
	 * @param string $method GET|POST|PUT|etc...
	 */
	public function setRequestMethod(string $method){
		$method = strtoupper($method);

		unset($this->params[CURLOPT_CUSTOMREQUEST]);
		unset($this->params[CURLOPT_POST]);
		unset($this->params[CURLOPT_PUT]);
		unset($this->params[CURLOPT_POSTFIELDS]);

		switch ($method) {
			case 'GET':
				$this->setParams();
				break;
			
			case 'POST':
				$this->setParams([CURLOPT_POST => true]);

			case 'PUT':
				$this->setParams([CURLOPT_PUT => true]);
				break;

			default:
				$this->setParams([CURLOPT_CUSTOMREQUEST => $method]);
				break;
		}

		return $this;
	}
}