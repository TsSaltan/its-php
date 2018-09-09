<?php
namespace tsframe\view;

use tsframe\exception\TemplateException;
use tsframe\Log;

class TemplateRoot{
	const TPL_EXT = '.tpl.php';
	const INC_EXT = '.inc.php';

	const DEFAULT = 'default';

	protected static $roots = [
		'default' => [CD . 'ts-template' . DS]
	];

	public static function addDefault(string $path){
		return self::add(self::DEFAULT, $path);
	}

	public static function add(string $part, string $path){
		if(is_dir($path)){
			self::$roots[$part][] = realpath($path) . DS;
		}
	}

	/**
	 * @throws TemplateException
	 */
	public static function getTemplateFiles(string $part, string $path): array {
		return self::findFiles($part, $path, self::TPL_EXT);
	}

	/**
	 * @throws TemplateException
	 */
	public static function getIncludeFiles(string $part, string $path): array {
		return self::findFiles($part, $path, self::INC_EXT);
	}

	/**
	 * @throws TemplateException
	 */
	public static function findFiles(string $part, string $path, string $ext = null): array {
		$files = [];
		$roots = array_merge((self::$roots[$part] ?? []), self::$roots[self::DEFAULT]);
		$roots = array_unique($roots);
		
		foreach($roots as $root){
			$filePath = $root . $path . $ext;
			if(file_exists($filePath)){
				$files[] = $filePath;
			}
		}

		if(sizeof($files) == 0){
			throw new TemplateException("Template file does not found", 500, [
				'part' => $part,
				'path' => $path,
				'ext' => $ext,
				'roots' => self::$roots,
			]);
		}
		
		return $files;
	}
}