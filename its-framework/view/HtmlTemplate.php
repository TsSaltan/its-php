<?php
namespace tsframe\view;

use tsframe\module\menu\Menu;

class HtmlTemplate extends Template {
	public function css(){
		foreach(func_get_args() as $arg){
			$uris = $this->getURIs($arg);
			foreach ($uris as $uri) {
				echo '<link rel="stylesheet" type="text/css" href="'. $uri .'">' . "\n";
			}
		}
	}

	public function js(){
		foreach(func_get_args() as $arg){
			$uris = $this->getURIs($arg);
			foreach ($uris as $uri) {
				echo '<script src="'. $uri .'"></script>' . "\n";
			}
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