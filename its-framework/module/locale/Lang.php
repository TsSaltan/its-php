<?php
namespace tsframe\module\locale;

use tsframe\Http;

/**
 * Набор функций для определения и выбора текущего языка
 */
class Lang {

	/**
	 * Список доступных языков
	 * @var array
	 */
	protected static $list = ['en', 'ru'];

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
	 * Автоматический редирект на поддомен с языком
	 * Например localhost -> ru.localhost
	 */
	private static $autoRedirectToSubdomain = false;
	
	public static function setAutoRedirectToSubdomain(bool $redirect){
		self::$autoRedirectToSubdomain = $redirect;
	}

	/**
	 * Установить язык по умолчанию
	 * @param string $lang
	 */
	public static function setDefault(?string $lang = null): bool {
		if(is_null($lang)){
			self::$default = current(self::$list);
		}

		$lang = strtolower($lang);
		if(!in_array($lang, self::$list)) return false;

		self::$default = $lang;
		return true;
	}

	public static function setList(array $list = [], ?string $default = null): bool {
		if(sizeof($list) == 0) return false;

		self::$list = $list;
		return self::setDefault($default);
	}

	public static function getList(): array {
		return self::$list;
	}

	public static function getCurrent(): ?string {
		return self::$current;
	}

	public static function setCurrent(string $lang): bool {
		if(!in_array($lang, self::$list)) return false;

		self::$current = $lang;

		$host = Http::getHostName();
		$parts = explode('.', $host);
		$redirectRequired = false;
		
		if(in_array($parts[0], self::$list)){
			// if subdomain eq lang.domain.com
			if($parts[0] == $lang){
				// if lang subdomain eq current language
				$redirectRequired = false;
				$domain = $host;
			}
			else {
				$parts[0] = $lang;
				$domain = implode('.', $parts);
				$redirectRequired = true;
			}
			unset($parts[0]);
			$baseDomain = implode('.', $parts);
		} else {
			// if subdomain eq domain.com
			$redirectRequired = true;
			$baseDomain = $host;
			$domain = $lang . '.' . $host;
		}

		Http::setCookie(self::COOKIE_NAME, $lang, ['expires' => time() + 60*60*24, 'domain' => '.' . $baseDomain]);

		if(!filter_var($host, FILTER_VALIDATE_IP) && $redirectRequired && self::$autoRedirectToSubdomain){
			Http::redirect(Http::getProtocol() . '://' . $domain . $_SERVER['REQUEST_URI']);
		}

		return true;
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
	public static function detect(): ?string {
		$langs = [];

		// 0. Опредеяем по домену (например ru.localhost)
		$host = Http::getHostName();
		$parts = explode('.', $host);
		$langs[] = $parts[0];


		// 1. Смотрим запись в $_GET
		if(isset($_GET[self::COOKIE_NAME])){
			$langs[] = $_GET[self::COOKIE_NAME];
		}

		// 2. Ниже приоритет у куков
		if(isset($_COOKIE[self::COOKIE_NAME])){
			$langs[] = $_COOKIE[self::COOKIE_NAME];
		}
		
		// 3. Если ничего нет, ищем в заголовках браузера
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$langs[] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		}

		// Если ничего не найдено - берём язык по умолчанию
		$langs[] = self::$default;

		foreach($langs as $lang){
			if(self::setCurrent($lang)){
				// Загрузить языковые файлы из путей
				Translation::importFiles(self::$translationPaths, $lang);
				return $lang;
			}
		}

		return null;
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