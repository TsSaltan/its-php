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