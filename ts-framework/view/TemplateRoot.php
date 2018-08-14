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
	public static function getTemplateFile(string $part, string $path): string {
		return self::findFile($part, $path, self::TPL_EXT);
	}

	/**
	 * @throws TemplateException
	 */
	public static function getIncludeFile(string $part, string $path): string {
		return self::findFile($part, $path, self::INC_EXT);
	}

	/**
	 * @throws TemplateException
	 */
	public static function findFile(string $part, string $path, string $ext = null): string {
		$roots = array_merge((self::$roots[$part] ?? []), self::$roots[self::DEFAULT]);

		foreach($roots as $root){
			$filePath = $root . $path . $ext;
			Log::add(['path' => $filePath, 'exists' => file_exists($filePath)]);
			if(file_exists($filePath)){
				return $filePath;
			}
		}

		throw new TemplateException("Template does not found", 500, [
			'part' => $part,
			'path' => $path,
			'ext' => $ext,
			'roots' => self::$roots,
		]);
	}
}