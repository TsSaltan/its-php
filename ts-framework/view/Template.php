<?php
namespace tsframe\view;

use tsframe\App;
use tsframe\Http;
use tsframe\Hook;
use tsframe\exception\TemplateException;

class Template{
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
	 * Полный путь к файлу шаблона
	 * @var string
	 */
	// protected $file;

	/**
	 * Переменные для шаблона
	 * @var array
	 */
	protected $vars = [];

	public function __construct(string $part, string $name){
		$this->part = $part;
		$this->name = $name;
		// $this->file = TemplateRoot::getTemplateFile($part, $name);
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
		Hook::call('template.render', [$this]);

		ob_start();
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
	protected function toURI(string $path){
		$path = str_replace(
			[realpath($_SERVER['DOCUMENT_ROOT']), '\\'], 
			['//' . $_SERVER['SERVER_NAME'], '/'], 
			$path
		);

		if(substr($path, 0, 1) !== '/') $path = '/' . $path;

		return $path;
	}


	/**
	 * Получить ссылку (на ресурсы, стили, скрипты)
	 * @return string
	 */
	protected function getURI(string $path){
		if(substr($path, 0, 4) == 'http' || substr($path, 0, 2) == '//'){
			// Абсолютные ссылки оставляем без изменения
		} else {
			try{
				$files = TemplateRoot::findFiles($this->part, $path);
				$file = $files[0];
			} catch (TemplateException $e){
				$file = $path;
			}

			$path = $this->toURI($file);
		}

		// Если включен режим разработчика, убираем кеширование ресурсов
		if(App::isDev()){
			if(strpos($path, '?') !== false){
				$path .= '&';
			} else {
				$path .= '?';
			}
			$path .= '__nocache=' . (time().rand(0,100));
		}
		return $path;
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
			Hook::call('template.include', [$name, $this]);
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
		return Hook::call('template.' . $this->part . '.' . $name, array_merge([$this], $params));
	}

	public function makeURI($uri){
		return Http::makeURI($uri);
	}
}