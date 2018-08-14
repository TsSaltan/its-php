<?php
namespace tsframe\view;

use tsframe\exception\TemplateException;
use tsframe\utils\io\Filter;
use tsframe\module\menu\Menu;

class HtmlTemplate extends Template {
	protected function css(){
		foreach(func_get_args() as $arg){
			echo '<link rel="stylesheet" type="text/css" href="'. $this->getURI($arg) .'">' . "\n";
		}
	}

	protected function js(){
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
}