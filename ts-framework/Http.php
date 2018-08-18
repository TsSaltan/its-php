<?php
namespace tsframe;

use tsframe\Hook;

class Http{

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

	public static function sendBody(string $body, int $code = 200, string $type = 'text/html', string $charset = 'utf-8', array $headers = []){
		Hook::call('http.send', [&$body, &$headers]);
		header('Content-type: ' . $type . '; charset=' . $charset, $code);
		foreach ($headers as $key => $value) {
			header($key . ': ' . $value);
		}
		echo $body;
	}

	public static function redirect(string $path, int $code = self::CODE_MOVED_TEMPORARILY){
		header('Location: '.$path, $code);
		?>
			<meta http-equiv="refresh" content="0; url=<?=$path?>">
			<script>window.location.replace('<?=$path?>');</script>
		<?
		die;
	}

	/**
	 * Accept Cross-Origin Resource Sharing (CORS)
	 * @url https://developer.mozilla.org/ru/docs/Web/HTTP/CORS
	 */
	public static function acceptCORS(string $domain = null, array $methods = ['GET', 'POST', 'HEAD']){
		$domain = is_null($domain) ? '//' . $_SERVER['HTTP_HOST'] : $domain;
		header('Access-Control-Allow-Origin: ' , $domain);
		header('Access-Control-Allow-Methods: '.implode($methods, ', '));
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
	public static function makeURI(string $uri): string {
		if(substr($uri, 0, 1) == '/'){
			return App::getBasePath() . $uri;
		}

		return $uri;
	}
}