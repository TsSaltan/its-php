<?php
namespace tsframe;

use tsframe\Config;
use tsframe\controller\ErrorController;
use tsframe\controller\InstallController;
use tsframe\exception\BaseException;
use tsframe\exception\PluginException;
use tsframe\module\Crypto;
use tsframe\module\locale\Lang;
use tsframe\view\Template;

class App {

	/**
	 * Версия приложения
	 * major.minor.release
	 */
	const VERSION = "1.0-dev";

	/**
	 * Путь к директории на сервере, откуда будет брать начало роутер, ссылки и т.д.
	 * @var string
	 */
	protected static $basePath = '/';

	/**
	 * Возможность установить базовую директорию для работы скрипта
	 * Влияет на роутер (для роутера необходим путь вида %path%/ - слеш в конце, но не в начале!)
	 * Влияет на генерацию ссылок (метод Http::makeURI), к абсолютным ссылкам в начало добавляет часть пути
	 * @param string $path
	 */
	public static function setBasePath(string $path){
		$path = str_replace(['\\', '|'], '/', $path);

		// Cлэш в начале
		if(substr($path, 0, 1) != '/') $path = '/' . $path;

		// Cлэш в конце
		if(substr($path, -1, 1) != '/') $path .= '/';

		self::$basePath = $path;
	}


	public static function getBasePath(): string {
		return self::$basePath;
	}

	/**
	 * Проверяет, установлена ли необходимая версия фреймворка
	 * @param  string $version Требуемая версия
	 * @param  string $rule    Правило для сравнения, по умлочанию >=
	 * @throws BaseException
	 */
	public static function requiredVersion(string $version, string $rule = ">=", bool $throwException = true): bool {
		if(!version_compare(self::VERSION, $version, $rule)){
			if($throwException) throw new BaseException("Required framework version " . $rule . " " . $version . ". Current version: " . self::VERSION);
			return false;
		}

		return true;
	}

	/**
	 * Загрузка приложения
	 */
	public static function init(){
		Lang::detect();
		Plugins::load();
		Hook::call('app.init');
	}
	
	/**
	 * Запуск приложения
	 * Поиск подходящего контроллера
	 */
	public static function start(){
		try {		
			if(!App::isInstalled()){
				$controller = App::install();
			} else {
				self::init();
				$controller = Router::findController();
			}

			$controller->send();
		} catch(BaseException $e) {
			$controller = new ErrorController($e);
			Hook::call('app.error', [$e, &$controller]);
			$controller->send();
		} 
	}

	/**
	 * Включен ли режим разработчика
	 */
	public static function isDev() : bool {
		return Config::get('dev_mode') !== false;
	}

	/**
	 * Установка компонентов приложения
	 */
	public static function install(): InstallController {
		Hook::call('app.install');
		$controller = new InstallController;
		$controller->checkPost();

		$install = Plugins::install();
		$controller->setErrors($install['errors']);
		$controller->setRequiredFields($install['params']);

		if($controller->isInstalled()){
			if(strlen(Config::get('appId')) < 64){
				Config::set('appId', Crypto::generateString(64));
			}

			// После загрузки плагинов необходимо вызвать plugins::load, чтоб сработал хук для выполнения кода внутри каждого плагина
			Plugins::load();	
			Hook::call('app.installed');
			Config::set('install_mode', false);
		}

		return $controller;
	}

	public static function isInstalled(): bool {
		return (strlen(Config::get('appId')) > 0) && (Config::get('install_mode') !== true);
	}
}