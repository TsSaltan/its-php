<?php
namespace tsframe;

class Cache{
	protected static $vars = [];

	public static function variable(string $varName, callable $getValue, bool $update = false){
		if(!isset(self::$vars[$varName]) || $update){
			self::$vars[$varName] = call_user_func($getValue);
		}

		return self::$vars[$varName];
	}
}