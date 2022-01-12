<?php
use tsframe\App;
use tsframe\Autoload;
use tsframe\Config;
use tsframe\Hook;
use tsframe\exception\BaseException;
use tsframe\module\locale\Lang;

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

define('ITS_ROOT', 		__DIR__ . DS);						// Корневая директория фреймворка
define('ITS_FRAME', 	ITS_ROOT . 'its-framework' . DS); 	// Директория с базовыми функциями фрейма
define('ITS_PLUGINS', 	ITS_ROOT . 'its-plugins' . DS); 	// Директория с базовыми плагинами
define('ITS_TEMPLATES', ITS_ROOT . 'its-templates' . DS); 	// Директория с базовыми шаблонами

class itsFrame {
	/**
	 * Инициализация путей фреймврока
	 * @param array $paths Массив с путями, доступные ключи: root, plugins, storage, upload, temp, translations
	 */
	public static function init(array $paths = []){
		foreach($paths as $key => $path){
			$pathKey = strtoupper('APP_' . $key);
			if(!defined($pathKey)){
				if(substr($path, -1, 1) !== DS){
					$path .= DS;
				}
				define($pathKey, $path);
			}
		}

		// Default roots
		if(!defined('APP_ROOT')) 		
			define('APP_ROOT', ITS_ROOT);

		if(!defined('APP_PLUGINS')) 		
			define('APP_PLUGINS', APP_ROOT . 'its-plugins' . DS);

		if(!defined('APP_STORAGE'))		
			define('APP_STORAGE', ITS_ROOT . 'storage' . DS);

		if(!defined('APP_UPLOAD'))		
			define('APP_UPLOAD', APP_STORAGE . 'upload' . DS);

		if(!defined('APP_TEMP'))		
			define('APP_TEMP', APP_STORAGE . 'temp' . DS);

		if(!defined('APP_TRANSLATIONS'))
			define('APP_TRANSLATIONS', APP_STORAGE . 'translations' . DS);

		// Aliases for roots
		define('CD', APP_ROOT);	// Alias "current dir"
		define('STORAGE', APP_STORAGE);
		define('TEMP', APP_TEMP);
		define('UPLOAD', APP_UPLOAD);
		
		require ITS_FRAME . 'Autoload.php';
		
		// Include composer
		if(file_exists(ITS_ROOT . 'vendor/autoload.php')){
			require ITS_ROOT . 'vendor/autoload.php';
		}
		
		Autoload::init();
		Autoload::addRoot(ITS_FRAME);

		// Путь к файлу настрек
		Config::load(APP_ROOT . 'ts-config.json');

		// Путь к директории с переводами
		Lang::addTranslationPath(APP_TRANSLATIONS);

		self::registerMigrateHooks();
	}

	public static function launch(array $paths = [], string $basePath = '/'){
		self::init($paths);
		App::setBasePath($basePath);
		App::start();
	}

	private static function registerMigrateHooks(){
		Hook::registerOnce('app.installed', function(){
			// Migrate from ts-framework v1.0
			$canReg = Config::get('user.canRegister');
			if(!is_null($canReg)){
				Config::set('user.auth.register', $canReg);
				Config::unset('user.canRegister');
			}

			$canSocial = Config::get('user.canSocial');
			if(!is_null($canSocial)){
				Config::set('user.auth.social', $canSocial);
				Config::unset('user.canSocial');
			}

			$loginUsed = Config::get('user.loginUsed');
			if(!is_null($loginUsed)){
				Config::set('user.auth.login', $loginUsed);
				Config::unset('user.loginUsed');
			}
		});
	}
}