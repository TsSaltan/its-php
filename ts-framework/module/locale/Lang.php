<?php
namespace tsframe\module\locale;

/**
 * Набор функций для определения и выбора текущего языка
 */
class Lang {

	/**
	 * Список доступных языков
	 * @var array
	 */
	public static $list = ['en', 'ru'];

	/**
	 * Имя куки, где хранятся данные о текущем языке
	 */
	const COOKIE_NAME = 'lang';

	/**
	 * Язык по умолчанию
	 * @var string
	 */
	private static $default = 'en';

	/**
	 * Текущий язык
	 * @var string
	 */
	private static $current;

	/**
	 * Массив с путями, где хранятся файлы с переводами
	 * @var array
	 */
	private static $translationPaths = [];

	/**
	 * Установить язык по умолчанию
	 * @param string $lang
	 */
	public static function setDefault(string $lang): bool {
		self::$default = strtolower($lang);
	}

	public static function getCurrent(): ?string {
		return self::$current;
	}

	public static function setCurrent(string $lang){
		self::$current = $lang;
		setcookie(self::COOKIE_NAME, $lang, time()+60*60*24, '/');
	}

	public static function addTranslationPath(string $path): bool {
		if(is_dir($path)){
			self::$translationPaths[] = realpath($path) . DS;
			return true;
		}

		return false;
	}

	/**
	 * Определить текущий язык пользователя
	 * @return string
	 */
	public static function detect(): string {
		$lang = null;

		// 1. Смотрим запись в $_GET
		if(isset($_GET[self::COOKIE_NAME])){
			$lang = $_GET[self::COOKIE_NAME];
		}

		// 2. Ниже приоритет у куков
		elseif(isset($_COOKIE[self::COOKIE_NAME])){
			$lang = $_COOKIE[self::COOKIE_NAME];
		}
		
		// Если ничего нет, ищем в заголовках браузера
		elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		}

		if(is_null($lang) || !in_array($lang, self::$list)){
			$lang = self::$defaultLoc;
		}

		self::setCurrent($lang);

		// Загрузить языковые файлы из путей
		Translation::importFiles(self::$translationPaths, $lang);
		
		return $lang;
	}

/////////////

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
}