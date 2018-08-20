<?php
namespace tsframe\view;

use tsframe\exception\TemplateException;
use tsframe\utils\io\Filter;
use tsframe\module\menu\Menu;
use PHPHtmlParser\Dom;

class HtmlTemplate extends Template {
	public function css(){
		foreach(func_get_args() as $arg){
			echo '<link rel="stylesheet" type="text/css" href="'. $this->getURI($arg) .'">' . "\n";
		}
	}

	public function js(){
		foreach(func_get_args() as $arg){
			echo '<script src="'. $this->getURI($arg) .'"></script>' . "\n";
		}
	}

	public function menu(string $menuName, callable $onParent, callable $onItem): string {
		return Menu::render($menuName, $onParent, $onItem);
	}

	public function build(array $before = ['functions', 'header'], array $after = ['footer']){
		ob_start();

		foreach ($before as $inc) {
			$this->inc($inc);
		}

		echo $this->render();

		foreach ($after as $inc) {
			$this->inc($inc);
		}

		return ob_get_clean();
	}

	/**
	 * Var filter
	 */
	public function filter($var, array $filters){
		$filter = Filter::of($var);
		$filters = array_unique($filters);
		foreach($filters as $f){
			if(method_exists($filter, $f)){
				$filter = call_user_func([$filter, $f]);
			}
		}

		return $filter->getData();
	}
}