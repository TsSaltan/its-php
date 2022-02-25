<?php
namespace tsframe\view;

use tsframe\App;
use tsframe\Hook;
use tsframe\Http;
use tsframe\exception\TemplateException;
use tsframe\module\locale\Lang;

class Template {
	public static function error(){
		return new self('default', 'error');
	}

	/**
	 * Имя раздела с шаблонаами
	 * @var string
	 */
	protected $part;

	/**
	 * Имя файла шаблона
	 * @var string
	 */
	protected $name;

	/**
	 * Разрешено ли использовать хуки
	 * @var boolean
	 */
	protected $useHooks = true;

	/**
	 * Переменные для шаблона
	 * @var array
	 */
	public $vars = [];

	public function __construct(string $part, string $name){
		$this->part = $part;
		$this->name = $name;

		$this->var('currentLang', Lang::getCurrent());
	}

	/**
	 * Добавить переменные
	 * @param  array  $vars
	 */
	public function vars(array $vars){
		array_walk($vars, function($value, $key){
			$this->var($key, $value);
		});
	}

	public function var(string $key, $value){
		$this->vars[$key] = $value;
	}

	public function __get(string $varName){
		return $this->vars[$varName] ?? null ;
	}

	/**
	 * Получить содержимое шаблона
	 * @return string
	 */
	public function render() : string {
		if($this->useHooks) Hook::call('template.render', [$this]);

		ob_start();
			global $tpl;
			$tpl = $GLOBALS['tpl'] = $this;
			extract($this->vars);
			$tplFiles = TemplateRoot::getTemplateFiles($this->part, $this->name);
			foreach($tplFiles as $tplFile){
				require($tplFile);
			}
		return ob_get_clean();
	}

	/**
	 * Преобразует абсолютный путь в URI
	 * @return string
	 */
	public function toURI(string $path){
		$path = str_replace(
			[realpath($_SERVER['DOCUMENT_ROOT']), '\\'], 
			['//' . $_SERVER['SERVER_NAME'], '/'], 
			$path
		);

		if(substr($path, 0, 1) !== '/') $path = '/' . $path;

		return $path;
	}


	/**
	 * Получить ссылку на первый найденный ресурс 
	 * @return string | null
	 */
	public function getURI(string $path): ?string {
		$uri = $this->getURIs($path);
		return $uri[0] ?? null;
	}

	/**
	 * Получить ссылки на все доступные ресурсы, стили, скрипты в текущей директории 
	 * @return array
	 */
	public function getURIs(string $path): array {
		$files = [];

		if(substr($path, 0, 4) == 'http' || substr($path, 0, 2) == '//'){
			// Абсолютные ссылки оставляем без изменения
		} else {
			try{
				$files = TemplateRoot::findFiles($this->part, $path);
			} catch (TemplateException $e){
				$files[] = $path;
			}

			foreach ($files as $key => $fileOring) {
				$file = $this->toURI($fileOring);

				if(strpos($file, '?') !== false){
					$file .= '&';
				} else {
					$file .= '?';
				}

				
				// По умолчанию версия файла (=кеширование) равно текущему месяцу (менее ресурсозатратно)
				$version = date('my');

				// Если включен режим разработчика, добавляем версию файла, чтоб измененный файл не кешировался
				if(App::isDev()){
					$ftime = 0;
					if(file_exists($fileOring)){
						// В некоторых случаях невозможно получить точное время изменения файла, поэтому нужно подавление warning
						$ftime = @filemtime($fileOring);
					}

					if(!is_null($ftime) && $ftime > 0){
						$version = implode('-', str_split($ftime, 4));
					}
				}

				$files[$key] = $file . 'v=' . $version;
			}
		}
		return $files;
	}	

	/**
	 * Include resource file
	 * @param string $name
	 */
	public function inc(string $name){
		try{
			$tplFiles = TemplateRoot::getIncludeFiles($this->part, $name);
			extract($this->vars);
			foreach($tplFiles as $tplFile){
				require $tplFile;
			}
			if($this->useHooks) Hook::call('template.include', [$name, $this]);
		} catch (TemplateException $e){

		}
	}	

	/**
	 * incHeader
	 * incFooter
	 * etc...
	 */
	public function __call($name, $params){
		if(substr($name, 0, 3) == 'inc'){
			$pageName = strtolower(substr($name, 3));
			$this->inc($pageName);
		}
	}

	public function hook(string $name, array $params = []){
		if(!$this->useHooks) return;
		
		if(strpos($name, 'template.') === 0){
			return Hook::call($name, array_merge([$this], $params));
		} else {
			return Hook::call('template.' . $this->part . '.' . $name, array_merge([$this], $params));
		}
	}

	public function makeURI(string $uri, array $queryParams = [], string $hashString = null){
		return Http::makeURI($uri, $queryParams, $hashString);
	}

	public function setHooksUsing(bool $enable){
		$this->useHooks = $enable;
	}

	public function getTemplatePart(): string {
		return $this->part;
	}

	public function getTemplateName(): string {
		return $this->name;
	}
}