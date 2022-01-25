<?php
namespace tsframe;

use tsframe\Hook;

class Http {

	const CODE_OK = 			200;
	const CODE_CREATED = 		201;
	const CODE_MOVED_PERMANENTLY = 		301;
	const CODE_MOVED_TEMPORARILY = 		302;
	const CODE_BAD_REQUEST = 	400;
	const CODE_UNAUTHORIZED = 	401;
	const CODE_ACCESS_DENIED = 	403;
	const CODE_NOT_FOUND = 		404;
	const CODE_SERVER_ERROR = 	500;

	const TYPE_HTML = 'text/html';
	const TYPE_PLAIN = 'text/plain';
	const TYPE_JSON = 'application/json';
	const TYPE_JAVASCRIPT = 'text/javascript';

	public static function sendBody(?string $body, int $code = 200, string $type = 'text/html', string $charset = 'utf-8', array $headers = []){
		Hook::call('http.send', [&$body, &$headers, $code, $type]);
		header('Content-type: ' . $type . '; charset=' . $charset, $code);
		foreach ($headers as $key => $value) {
			header(str_replace(["\r\n", "\n", "\r"], " ", $key . ': ' . $value));
		}
		echo $body;
	}

	public static function redirectURI(string $uriPath, int $code = self::CODE_MOVED_TEMPORARILY){
		return self::redirect(self::makeURI($uriPath), $code);
	}

	public static function redirect(string $path, int $code = self::CODE_MOVED_TEMPORARILY){
		header('Location: '.$path, $code);
		?>
			<meta http-equiv="refresh" content="0; url=<?=$path?>">
			<script>window.location.replace('<?=$path?>');</script>
		<?php

		Hook::call('app.finish');
		die;
	}

	/**
	 * Accept Cross-Origin Resource Sharing (CORS)
	 * @url https://developer.mozilla.org/ru/docs/Web/HTTP/CORS
	 */
	public static function acceptCORS(string $domain = null, array $methods = ['GET', 'POST', 'HEAD']){
		$domain = is_null($domain) ? '//' . $_SERVER['HTTP_HOST'] : $domain;
		header('Access-Control-Allow-Origin: ' , $domain);
		header('Access-Control-Allow-Methods: ' . implode(', ', $methods));
	}

	/**
	 * Получить последний отправленный Content-type заголовок
	 * @return string
	 */
	public static function getContentType() : string {
		$list = headers_list();
		foreach($list as $h){
			if(preg_match('#Content-type:([^/]+\/[^;]+)\;#Ui',$h.';',$r)){
				return strtolower(trim($r[1]));
			}
		}

		return 'text/html';
	}

	/**
	 * Сгенерировать ссылку с учётом basePath
	 * @param  string $uri
	 * @return string
	 *
	 * /abs-path/ => /basedir/abs-path
	 * relative-path/ => relative-path/
	 * http://abs-path/ => http://abs-path/
	 */
	public static function makeURI(string $uri, array $queryParams = [], string $hashString = null): string {
		$postfix = (sizeof($queryParams) > 0) ? (strpos($uri, '?') === false ? '?' : '&') . http_build_query($queryParams) : null;
		$postfix .= strlen($hashString) > 0 ? '#' . $hashString : null;

		if(substr($uri, 0, 1) == '/'){
			return self::getProtocol() . '://' .  self::getHostName() . App::getBasePath() . substr($uri, 1) . $postfix;
		}

		return $uri . $postfix;
	}

	/**
	 * Получить текущий протокол
	 * @return string http|https
	 */
	public static function getProtocol(): string {
		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)){
			return 'https';
		} else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
			return 'https';
		} else if (isset($_SERVER['REQUEST_SCHEME'])){
			return $_SERVER['REQUEST_SCHEME'];
		} else if(isset($_SERVER["SERVER_PROTOCOL"])){
			return strtolower(explode('/', $_SERVER["SERVER_PROTOCOL"])[0]);
		}

		return 'http';
	}
	
	public static function getHostName(): string {
		return $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
	}

	/**
	 * Поступил ли текущий запрос от браузера
	 * @return boolean
	 */
	public static function isBrowser(): bool {
		return isset($_SERVER['HTTP_USER_AGENT']) && (
			   stripos($_SERVER['HTTP_USER_AGENT'], 'mozilla') || 
			   stripos($_SERVER['HTTP_USER_AGENT'], 'windows') ||
			   stripos($_SERVER['HTTP_USER_AGENT'], 'firefox') ||
			   stripos($_SERVER['HTTP_USER_AGENT'], 'opera') ||
			   stripos($_SERVER['HTTP_USER_AGENT'], 'chrome') ||
			   stripos($_SERVER['HTTP_USER_AGENT'], 'webkit'));
	}

	public static function getRequestMethod(): string {
		return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
	}
}