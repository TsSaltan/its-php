<?php
namespace tsframe;

class Log{
	protected static $logs = [];
	public static function add($data){
		if(func_num_args() > 1){
			$data = func_get_args();
		}

		if(!is_string($data)){
			$data = trim(var_export($data, true));
		}

		self::$logs[] = $data;
	}

	public static function get(bool $asString = true){
		return $asString ? implode("\n", self::$logs) : self::$logs;
	}

	public static function save(string $file){
		file_put_contents($file, self::get(true), FILE_APPEND);
	}
}