<?php
use tsframe\App;
use tsframe\Autoload;
use tsframe\Config;
use tsframe\Hook;
use tsframe\Plugins;
use tsframe\exception\BaseException;
use tsframe\module\locale\Lang;

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

define('ITS_ROOT', 			__DIR__ . DS);						// Корневая директория фреймворка
define('ITS_FRAME', 		ITS_ROOT . 'its-framework' . DS); 	// Директория с базовыми функциями фрейма
define('ITS_PLUGINS', 		ITS_ROOT . 'its-plugins' . DS); 	// Директория с базовыми плагинами
define('ITS_TEMPLATES', 	ITS_ROOT . 'its-templates' . DS); 	// Директория с базовыми шаблонами
define('ITS_STORAGE', 		ITS_ROOT . 'storage' . DS); 		// Директория c временной папкой, переводами и пр.
define('ITS_TEMP', 			ITS_STORAGE . 'temp' . DS); 		
define('ITS_UPLOAD', 		ITS_STORAGE . 'upload' . DS); 		
define('ITS_TRANSLATIONS', 	ITS_STORAGE . 'translations' . DS); 

class itsFrame {
	/**
	 * Инициализация путей фреймврока
	 * @param array $paths Массив с путями, доступные ключи: 
	 * - root, 
	 * - plugins, 
	 * - storage, 
	 * - upload, 
	 * - temp, 
	 * - translations
	 * - basePath
	 */
	public static function init(array $paths = []){
		$basePath = '/';
		foreach($paths as $key => $path){
			if(strtolower($key) == 'basepath'){
				$basePath = $path;
				continue;
			}

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
			define('APP_STORAGE', APP_ROOT . 'storage' . DS);

		if(!defined('APP_UPLOAD'))		
			define('APP_UPLOAD', APP_STORAGE . 'upload' . DS);

		if(!defined('APP_TEMP'))		
			define('APP_TEMP', APP_STORAGE . 'temp' . DS);

		if(!defined('APP_TRANSLATIONS'))
			define('APP_TRANSLATIONS', APP_STORAGE . 'translations' . DS);

		// Aliases for roots
		define('CD', APP_ROOT);	// Alias "current dir"

		if(is_dir(APP_STORAGE)){
			define('STORAGE', APP_STORAGE);
		}
		else {
			define('STORAGE', ITS_STORAGE);
		}

		if(is_dir(APP_TEMP)){
			define('TEMP', APP_TEMP);
		}
		else {
			define('TEMP', ITS_TEMP);
		}

		if(is_dir(APP_UPLOAD)){
			define('UPLOAD', APP_UPLOAD);
		}
		else {
			define('UPLOAD', ITS_UPLOAD);
		}
		
		require ITS_FRAME . 'Autoload.php';
		
		// Include composer
		if(file_exists(ITS_ROOT . 'vendor/autoload.php')){
			require ITS_ROOT . 'vendor/autoload.php';
		}
		
		Autoload::init();
		Autoload::addRoot(ITS_FRAME);

		// Путь к файлу настрек
		Config::load(APP_ROOT . 'its-config.json');

		// Базовая директория скрипта
		App::setBasePath($basePath);

		// Путь к директории с переводами
		if(APP_TRANSLATIONS != ITS_TRANSLATIONS){
			Lang::addTranslationPath(APP_TRANSLATIONS);
		}
		Lang::addTranslationPath(ITS_TRANSLATIONS);

		self::registerMigrateHooks();

		return new self;
	}

	private static function registerMigrateHooks(){
		Hook::registerOnce('app.install', function(){
			// Migrate from ts-frame
			$oldCfg = APP_ROOT . 'ts-config.json';

			if(file_exists($oldCfg)){
				$data = json_decode(file_get_contents($oldCfg), true);
				Config::set('*', $data);
				@unlink($oldCfg);
				Config::set('install_mode', true);
			}
		});

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

	public function setLanguages(array $langs = [], ?string $default = null){
		Lang::setList($langs, $default);
		return $this;
	}

	public function addPlugin(string $path){
		Plugins::addCustom($path);
		return $this;
	}

	public function start(){
		App::start();
	}
}