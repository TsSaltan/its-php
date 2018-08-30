<?php
namespace tsframe;

use tsframe\exception\PluginException;

class Plugins{
	/**
	 * Loaded plugins name => path
	 * @var array
	 */
	protected static $loaded = [];

	public static function load(){
		$list = self::getList();
		foreach ($list as $plugin) {
			$parent = dirname($plugin);
			Autoload::addRoot($parent);
			require $plugin;
			self::$loaded[basename($parent)] = $parent;
		}

		foreach (self::$loaded as $name => $path) {
			Hook::call('plugin.load', [$name, $path]);
		}
	}

	public static function install(){
		foreach (self::$loaded as $name => $path) {
			Hook::call('plugin.install', [$name, $path]);
		}
	}

	protected static function getList(){
		$files = glob(CD . 'ts-plugins' . DS . '*' . DS . 'index.php');
		return $files;
	}

	/**
	 * @throws PluginException
	 * @param строки с названиями необходимых плагинов
	 */
	public static function required(){
		foreach(func_get_args() as $pluginName){
			if(!isset(self::$loaded[$pluginName])){
				throw new PluginException('Plugin "'. $pluginName .'" does not loaded', 500, [
					'pluginName' => $pluginName,
					'loaded' => self::$loaded
				]);
			}
		}
	}

	/**
	 * @throws PluginException
	 * @param строки с названиями конфликтующих плагинов
	 */
	public static function conflict(){
		foreach(func_get_args() as $pluginName){
			if(isset(self::$loaded[$pluginName])){
				throw new PluginException('Plugin conflict with "'. $pluginName .'"', 500, [
					'pluginName' => $pluginName,
					'loaded' => self::$loaded
				]);
			}
		}
	}
}