<?php
namespace tsframe\module;

use tsframe\Config;
use tsframe\module\database\Database;

/**
 * Used for old-compatibility
 * @deprecated
 */
class Log {
	public static function __callStatic(string $name , array $args){
		throw new Exception('Log class deprecated! Use Logger class!');
	}
}