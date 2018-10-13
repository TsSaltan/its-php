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
		foreach (self::getList() as $pluginName => $pluginPath) {
			self::loadPlugin($pluginName, $pluginPath);
		}

		foreach (self::$loaded as $name => $path) {
			Hook::call('plugin.load', [$name, $path]);
		}
	}

	protected static function loadPlugin(string $pluginName, string $pluginPath){
		if(self::isDisabled($pluginName)) return;

		Autoload::addRoot($pluginPath);
		require $pluginPath . "/index.php";
		self::$loaded[$pluginName] = $pluginPath;
	}

	/**
	 * Установка плагинов
	 * @return array|null возвращает либо массив необходимых полей в настройках, либо null 
	 */
	public static function install(){
		// 1. Загружаем плагины
		foreach (self::getList() as $pluginName => $pluginPath) {
			self::loadPlugin($pluginName, $pluginPath);
		}

		// 2. Получаем необходимые параметры
		$requiredParams = [];
		Hook::call('plugin.install.required', [], function($params) use (&$requiredParams){
			if(is_array($params)){
				foreach ($params as $paramPath => $data) {
					if(!Config::isset($paramPath)){
						$requiredParams[$paramPath] = $data;
					}
				}
			}
		});

		if(sizeof($requiredParams) > 0){
			return $requiredParams;
		}

		// 3. Загружаем плагины
		foreach (self::$loaded as $name => $path) {
			try{
				Hook::call('plugin.install', [$name, $path]);
			} catch(PluginException $e){
				self::disable($e->getPluginName());
			}
		}
	}

	/**
	 * Получить список доступных плагинов
	 * @return array [pluginName => path, ]
	 */
	public static function getList(): array {
		$return = [];
		$files = glob(CD . 'ts-plugins' . DS . '*' . DS . 'index.php');
		foreach ($files as $path) {
			$parent = dirname($path);
			$pluginName = basename($parent);
			$return[$pluginName] = $parent;
		}
		return $return;
	}

	/**
	 * Позволяет указать на требуемые плагины
	 * @throws PluginException
	 * @param строки с названиями необходимых плагинов
	 */
	public static function required(){
		foreach(func_get_args() as $pluginName){
			if(!isset(self::$loaded[$pluginName])){
				throw new PluginException('Plugin "'. $pluginName .'" does not loaded', 500, [
					'pluginName' => $pluginName,
					'loaded' => self::$loaded,
					'disabled' => self::$disabled,
				]);
			}
		}
	}

	/**
	 * Позволяет указать на конфликтующие плагины
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

	/**
	 * Позволяет отключить плагины
	 * @param string $pluginName1
	 * @param string $pluginName2
	 * @param string ...
	 */
	public static function disable(){
		$disabled = Config::get('plugins.disabled');
		$disabled = !is_array($disabled) ? [] : $disabled;
		Config::set('plugins.disabled', array_unique(array_merge($disabled, func_get_args())));
	}

	/**
	 * Позволяет включить плагины
	 * @param string $pluginName1
	 * @param string $pluginName2
	 * @param string ...
	 */
	public static function enable(){
		$disabled = Config::get('plugins.disabled');
		$disabled = !is_array($disabled) ? [] : $disabled;
		Config::set('plugins.disabled', array_unique(array_diff($disabled, func_get_args())));
	}

	/**
	 * Является ли плагин отключенным
	 * @param string $pluginName
	 * @return bool
	 */
	public static function isDisabled(string $pluginName): bool {
		$disabled = Config::get('plugins.disabled');
		return (is_array($disabled) && in_array($pluginName, $disabled));
	}
}