<?php
namespace tsframe\module;

use tsframe\Config;

class Crypto{
	/**
	 * Возвращает уникальный ID для текущего приложения
	 * @return [type] [description]
	 */
	protected static function getAppId(): string {
		return Config::get('appId');
	}

	/**
	 * Генерирует случайную строку заданной длины
	 * @param  int|integer $length 
	 * @return string
	 */
	public static function generateString(int $length = 32): string {
		$string = null;
		if (function_exists("random_bytes")) {
			$bytes = random_bytes(ceil($length/2));
			$string = bin2hex($bytes);
		} elseif(function_exists("openssl_random_pseudo_bytes")){
			$bytes = openssl_random_pseudo_bytes(ceil($length/2));
			$string = bin2hex($bytes);
		} else {
			while(strlen($string) < $length){
				$string .= sha1(uniqid(microtime()));
			}
		}
		return substr($str, 0, $length);
	}

	/**
	 * Сгенерировать "солёный" хэш
	 * @param  string      $data   
	 * @param  string      $method 
	 * @param  string|null $salt   
	 * @return string
	 */
	public static function saltHash(string $data, string $method = 'sha512', string $salt = null): string {
		$salt = is_null($salt) ? self::getAppId() : $salt;
		return hash($method, $data . $salt);
	}
}