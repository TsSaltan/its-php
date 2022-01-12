<?php
namespace tsframe;

use tsframe\PluginInstaller;
use tsframe\controller\InstallController;
use tsframe\exception\BaseException;
use tsframe\exception\PluginException;

class Plugins {
	/**
	 * Loaded plugins name => path
	 * @var array
	 */
	protected static $loaded = [];

	public static function load(){
		foreach (self::getList() as $pluginName => $pluginPath) {
			if(!isset(self::$loaded[$pluginName])){
				self::loadPlugin($pluginName, $pluginPath);
			}
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
	 * @return PluginInstaller[] Массив необходимых параметров, которые должен заполнить пользователь в процессе установки [key => params[]]
	 */
	public static function install(): array {
		$pluginErrors = [];

		// Функция, которая будет отключать плагины, если в процессе установки произошли какие-либо ошибки
		$disabledPlugin = function($e) use (&$pluginErrors){
			foreach ($e->getTrace() as $item) {
				if(isset($item['file']) && basename($item['file']) == 'index.php' && strstr($item['file'], 'ts-plugins') !== false){
					$pluginName = basename(dirname($item['file']));
					$pluginErrors[$pluginName] = $e->getMessage();
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
					if($data instanceof PluginInstaller){
						$installer = $data;
					} 
					elseif(is_array($data)) {
						$installer = PluginInstaller::fromArray($paramPath, $data);
					} 
					else {
						continue;
					}

					if(Config::isset($installer->getKey())){
						$installer->setCurrentValue(Config::get($installer->getKey()));
					}
					
					$requiredParams[] = $installer;
				}
			}
		}, $disabledPlugin);

		return ['errors' => $pluginErrors, 'params' => $requiredParams];
	}

	/**
	 * Получить список доступных плагинов
	 * @return array [pluginName => path, ]
	 */
	public static function getList(): array {
		$return = [];
		//$files = glob(CD . 'ts-plugins' . DS . '*' . DS . 'index.php');
		$plugins = glob(ITS_PLUGINS . '*' . DS . 'index.php');
		if(is_dir(APP_PLUGINS)){
			$appPlugins = glob(APP_PLUGINS . '*' . DS . 'index.php');
			$plugins = array_merge($plugins, $appPlugins);
		}
		
		foreach ($plugins as $path) {
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
		$enabled = self::getEnabled();

		$enabled = array_unique(array_diff($enabled, func_get_args()));
		self::$loaded = array_unique(array_diff(self::$loaded, func_get_args()));

		Config::set('plugins.enabled', $enabled);
	}

	/**
	 * Позволяет включить плагины
	 * @param string $pluginName1
	 * @param string $pluginName2
	 * @param string ...
	 */
	public static function enable(){
		$enabled = self::getEnabled();
		$enabled = array_merge($enabled, func_get_args());
		Config::set('plugins.enabled', array_unique($enabled));
	}

	/**
	 * Является ли плагин отключенным
	 * @param string $pluginName
	 * @return bool
	 */
	public static function isDisabled(string $pluginName): bool {
		$enabled = self::getEnabled();
		return !in_array($pluginName, $enabled);
	}

	/**
	 * Является ли плагин включенным
	 * @param string $pluginName
	 * @return bool
	 */
	public static function isEnabled(string $pluginName): bool {
		$enabled = self::getEnabled();
		return in_array($pluginName, $enabled);
	}

	/**
	 * Получить список отключенных плагинов
	 * @return array
	 */
	public static function getDisabled(): array {
		return array_diff(array_keys(self::getList()), self::getEnabled());
	}

	/**
	 * Получить список включенных плагинов
	 * @return array
	 */
	public static function getEnabled(): array {
		$enabled = Config::get('plugins.enabled');
		if((!is_array($enabled) || sizeof($enabled) == 0) && Config::isset('plugins.disabled')){
			// migrate from disabled list to enabled
			$disabled = Config::get('plugins.disabled');
			$list = self::getList();
			foreach ($list as $plugin => $path) {
				if(!in_array($plugin, $disabled)){
					$enabled[] = $plugin;
				}
			}

			Config::unset('plugins.disabled');
			Config::set('plugins.enabled', $enabled);
		}

		return !is_array($enabled) ? [] : $enabled;
	}
}