<?php
namespace tsframe;

use tsframe\exception\AutoloadException;
use tsframe\Logger;

class Autoload{
	/**
	 * Расширение скриптов
	 * @var string
	 */
	protected static $ext = 'php';

	/**
	 * Пути для поиска файлов
	 * @var array
	 */
	protected static $paths = [];

	public static function init(){
		spl_autoload_extensions("." . self::$ext);
		spl_autoload_register([__CLASS__, 'load']);
	}

	public static function addRoot(string $path){
		if(substr($path, -1) != DS) $path .= DS;
		self::$paths[] = str_replace(['\\', '/', '|'], DS, $path);
	}

	public static function getPaths(string $namespace) : array {
		$subPath = explode('tsframe', $namespace);
		$subPath = end($subPath);
		$paths = [];

		foreach (self::$paths as $path){
			$currentPath = str_replace("\\", '/', $path . '/' . $subPath);
			if(is_dir($currentPath)){
				$paths[] = $currentPath;
			} elseif (file_exists($currentPath .= '.' . self::$ext)) {
				$paths[] = $currentPath;
			}
		}

		return $paths;
	}

	public static function getRootPaths() : array {
		return self::$paths;
	}

	public static function load(string $className){
		$paths = self::getPaths($className);
		foreach ($paths as $path) {
			// Используем первый найденный файл
			require $path;
			return;
		}

		throw new AutoloadException('Class "'. $className .'" does not loaded', 404, [
			'className' => $className,
			'paths' => self::$paths
		]);
	
	}
}