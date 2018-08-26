<?php
namespace tsframe;

/**
 * @deprecated
 */
class Log{
	public static function __callStatic($a, $b){
		throw new \Exception('class ' . __CLASS__ . ' deprecated!');
	}
}