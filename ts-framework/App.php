<?php
namespace tsframe;

use tsframe\exception\BaseException;
use tsframe\view\Template;

class App{

	/**
	 * Загрузка приложения
	 */
	public static function load(){
		Plugins::load();
	}

	/**
	 * Путь к директории на сервере, откуда будет брать начало роутер, ссылки и т.д.
	 * @var string
	 */
	protected static $basePath = '';

	/**
	 * Возможность установить базовую директорию для работы скрипта
	 * Влияет на роутер (для роутера необходим путь вида %path%/ - слеш в конце, но не в начале!)
	 * Влияет на генерацию ссылок (метод Http::makeURI), к абсолютным ссылкам в начало добавляет часть пути
	 * @param string $path
	 */
	public static function setBasePath(string $path){
		$path = str_replace(['\\', '|'], '/', $path);

		// Без слэша в начале
		if(substr($path, 0, 1) == '/') $path = substr($path, 1);

		// Но слэш в конце
		if(substr($path, -1, 1) != '/') $path .= '/';

		self::$basePath = $path;
	}

	public static function getBasePath(): string {
		return self::$basePath;
	}

	/**
	 * Запуск приложения
	 */
	public static function start(){
		Hook::call('app.start');
		try{
			$controller = Router::findController();
			$controller->send();

		} catch(BaseException $e) {
			$dump = $e->dump(true);
			$code = $e->getCode();
			$tpl = Template::error();
			$tpl->vars(['dump' => $dump, 'errorCode' => $code]);
			$body = $tpl->render();
			Http::sendBody($body, $code, Http::TYPE_HTML, 'utf-8');
		} 
		Hook::call('app.finish');
	}

	/**
	 * Включен ли режим разработчика
	 */
	public static function isDev() : bool {
		return Config::get('dev_mode') === true; // мб null
	}

	/**
	 * Установка компонентов
	 */
	public static function install(){
		Plugins::install();
		Hook::call('app.install');
	}
}