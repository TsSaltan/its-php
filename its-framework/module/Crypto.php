<?php
namespace tsframe\module;

use tsframe\Config;

class Crypto {
	/**
	 * Возвращает уникальный ID для текущего приложения
	 * @return [type] [description]
	 */
	public static function getAppId(): string {
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
		return substr($string, 0, $length);
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

	/**
	 * Прервать выполнение скрипта на случайный промежуток времени
	 * @param  int $min Минимум (в милисекундах)
	 * @param  int $max Максимум (в милисекундах)
	 */
	public static function wait(int $min = 500, int $max = 2500){
		usleep(self::randomInt($min, $max));
	}

	/**
	 * Генерирует рандомное число
	 * @param  int    $from 
	 * @param  int    $to   
	 * @return int
	 */
	public static function randomInt(int $from, int $to): int {
		if(function_exists('random_int')){
			return random_int($from, $to);
		}

		if(function_exists('mt_rand')){
			return mt_rand($from, $to);
		}

		return rand($from, $to);
	}

	/**
	 * Генерирует массив с символами a-z и 0-9
	 * используется в методе Crypto::getCodeByIndex()
	 */
	protected static function getCharList(): array {
	    $chars = [];
	    $ca = 97;
	    $cz = 122;
	    $c0 = 48;
	    $c9 = 57;

	    // a - z
	    for ($i=$ca; $i <= $cz; $i++) { 
	        $chars[] = chr($i);
	    }

	    // 0 - 9
	    for ($i=$c0; $i <= $c9; $i++) { 
	        $chars[] = chr($i);
	    }

	    return $chars; 
	}

	/**
	 * Генерирует последовательные идентификаторы в зависимости от индекса
	 * например, 1 - a, 2 - b, 3 - c, 4 - aa, 5 - ab, etc ...
	 * 
	 * @param int 	$i Индекс
	 * @param array $chars Массив с используемыми символами, если не указан по умолчанию символы a-z0-9
	 * 
	 * @return string
	 */
	public static function getCodeByIndex(int $i, array $chars = []): string {
	    if(sizeof($chars) == 0) $chars = self::getCharList();

	    $num = sizeof($chars);
	    $index = [];
	    $ii = 0;
	    $ci = 0;

	    for($a = 0; $a <= $i; ++$a){
	        if(!isset($chars[$ci])){
	            $ci = 0;

	            if(isset($index[$ii-1])){
	                $ni = $ii;
	                while($ni >= 0){
	                    if(++$index[$ni] >= $num){
	                        $index[$ni] = 0;
	                        $ni--;
	                    } else {
	                        break;
	                    }
	                }

	                if($ni < 0){
	                    $ii++;
	                }

	            } else {
	                $index[$ii] = 0;
	                $ii++;
	            }
	        }

	        $index[$ii] = $ci++;
	    }

	    foreach ($index as $key => $value) {
	        $index[$key] = $chars[$value];
	    }
	    return implode('', $index);
	} 
}