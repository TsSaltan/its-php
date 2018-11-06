<?php
namespace tsframe;

use tsframe\exception\BaseException;
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
	 * Проходится по всем плагинам и вызывает хук plugin.install
	 * Если произошла ошибка, плагин будет отключён
	 * @return array Массив необходимых параметров, которые должен заполнить пользователь в процессе установки [key => params[]]
	 */
	public static function install(): array {
		// Функция, которая будет отключать плагины, если в процессе утсановки произошли какие-либо ошибки
		$disabledPlugin = function($e){
			foreach ($e->getTrace() as $item) {
				if(isset($item['file']) && basename($item['file']) == 'index.php' && strstr($item['file'], 'ts-plugins') !== false){
					$pluginName = basename(dirname($item['file']));
					self::disable($pluginName);
				}
			}
		};

		// 1. Загружаем плагины
		foreach (self::getList() as $pluginName => $pluginPath) {
			try{
				self::loadPlugin($pluginName, $pluginPath);
			} catch(\Exception $e){
				self::disable($pluginName);
			}
		}

		// 2. Получаем необходимые плагинам параметры
		$requiredParams = [];
		Hook::call('plugin.install', [], function($params) use (&$requiredParams){
			if(is_array($params)){
				foreach ($params as $paramPath => $data) {
					if(!Config::isset($paramPath)){
						$requiredParams[$paramPath] = $data;
					}
				}
			}
		}, $disabledPlugin);

		return $requiredParams;
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
		$disabled = self::getDisabled();
		Config::set('plugins.disabled', array_unique(array_merge($disabled, func_get_args())));

		foreach(func_get_args() as $pluginName){
			if(in_array($pluginName, self::$loaded)){
				unset(self::$loaded[array_search($pluginName, self::$loaded)]);
			}
		}
	}

	/**
	 * Позволяет включить плагины
	 * @param string $pluginName1
	 * @param string $pluginName2
	 * @param string ...
	 */
	public static function enable(){
		$disabled = self::getDisabled();
		Config::set('plugins.disabled', array_unique(array_diff($disabled, func_get_args())));
	}

	/**
	 * Является ли плагин отключенным
	 * @param string $pluginName
	 * @return bool
	 */
	public static function isDisabled(string $pluginName): bool {
		$disabled = self::getDisabled();
		return in_array($pluginName, $disabled);
	}

	/**
	 * Получить список отключенных плагинов
	 * @return array
	 */
	public static function getDisabled(): array {
		$disabled = Config::get('plugins.disabled');
		return !is_array($disabled) ? [] : $disabled;
	}
}