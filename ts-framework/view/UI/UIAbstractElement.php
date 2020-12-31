<?php
namespace tsframe\view\UI;

use tsframe\view\Template;


abstract class UIAbstractElement {
	abstract function render();

	protected $tpl;

	public function setTemplate(Template $tpl){
		$this->tpl = $tpl;
	}

	protected $id;

	public function setId(string $id){
		$this->id = $id;
	}

	public function getId(): string {
		if(strlen($this->id) == 0){
			$this->id = uniqid('ui-');
		}

		return $this->id;
	}

	protected function getContent($content): ?string {
		if(is_string($content)){
			return $content;
		}

		elseif(is_callable($content)){
			ob_start();
			echo call_user_func($content);
			return ob_get_clean();
		}

		else {
			ob_start();
			echo $content;
			return ob_get_clean();
		}
	}

	protected function getClassString($classes): ?string {
		$string = '';
		$args = func_get_args();
		foreach ($args as $arg) {
			if(is_array($arg)){
				$string .= implode(' ', $arg);
			}
			elseif(is_string($arg)){
				$string .= trim($arg);
			} 
			else {
				ob_start();
				echo $arg;
				$string .= trim(ob_get_clean());
			}

			$string .= ' ';
		}

		return trim($string);
	}

	public function __toString(){
		return $this->getContent(function(){ 
			return $this->render(); 
		});
	}
}