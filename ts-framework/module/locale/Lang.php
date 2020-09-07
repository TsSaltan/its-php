<?php
namespace tsframe\module;

/**
 * Реализация мультиязычности
 */
class Lang {
	/**
	 * Имя куки, где хранятся данные о текущем языке
	 */
	const COOKIE_LOC = 'locale';
	
	/**
	 * Директория с языковыми пакетами
	 */
	const TRANSTALES_ROOT = STORAGE . 'translates' . DS;

	/**
	 * Язык по умолчанию
	 * @var string
	 */
	private static $defaultLoc = 'ru';

	/**
	 * Текущий язык
	 * @var string
	 */
	private static $currentLoc;

	/**
	 * Установить язык по умолчанию
	 * @param string $lang
	 */
	public static function setDefaultLocale(string $lang): bool {
		self::$defaultLoc = strtolower($lang);
	}

	public static function getCurrentLocale(): ?string {
		return self::$currentLoc;
	}

	public static function setCurrentLocale(string $lang){
		self::$current = $lang;
		setcookie(self::COOKIE_LOC, $lang, time()+60*60*24, '/');
	}

	/**
	 * Определить текущий язык пользователя
	 * @return string
	 */
	public static function detectLocale(): string {
		$lang = null;

		// 1. Смотрим запись в $_GET
		if(isset($_GET[self::COOKIE_LOC])){
			$lang = $_GET[self::COOKIE_LOC];
		}

		// 2. Ниже приоритет у куков
		elseif(isset($_COOKIE[self::COOKIE_LOC])){
			$lang = $_COOKIE[self::COOKIE_LOC];
		}
		
		// Если ничего нет, ищем в заголовках браузера
		elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		}

		if(is_null($lang) || !self::isTranslateExists($lang)){
			$lang = self::$default;
		}

		self::setCurrentLanguage($lang);
		self::loadTranslateFile();
		return $lang;
	}

	/**
	 * Импорт файлов перевода в основной файл
	 * @param  string $path Путь к файлам
	 */
	public static function importTranslations(string $path){
		$files = glob($path . DS . '*.json');
		foreach ($files as $file) {
			$lang = explode('.', basename($file))[0];
			Translate::loadLangFile($lang);
			Translate::importFile($file);
		}

		self::loadTranslateFile(); // После импорта необходимо вернуть исходный языковой пакет
	}

	private static function loadTranslateFile(){
		Translate::loadLangFile(self::getCurrentLanguage());
	}

	/**
	 * Проверить, существует ли языковой пакет
	 * @param  string  $lang 
	 * @return boolean
	 */
	private static function isTranslateExists(string $lang): bool {
		return file_exists(self::LANG_DIR . $lang . '.json');
	}

	/**
	 * Получить список языковых пакетов
	 * @return array
	 */
	private static function getTranslateList(): array {
		$list = glob(self::LANG_DIR . '*');
		$langs = [];
		foreach($list as $item){
			$langs[] = explode('.', basename($item))[0];
		}

		return $langs;
	}
}