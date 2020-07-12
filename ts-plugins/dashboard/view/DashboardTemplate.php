<?php
namespace tsframe\view;

use tsframe\module\DashboardDesigner;
use tsframe\module\Meta;
use tsframe\module\menu\Menu;

class DashboardTemplate extends HtmlTemplate {
	/**
	 * @var DashboardDesigner
	 */
	protected $designer;

	public function __construct(string $part, string $name){
		parent::__construct($part, $name);
		$this->designer = new DashboardDesigner;
	}

	/**
	 * Добавить уведомление
	 * @param  string $message [description]
	 * @param  string $type    info|danger|warning|error|success
	 */
	public function alert(string $message, string $type = 'info'){
		$this->vars['alert'][$type][] = $message;
	}

	public function getDesigner(): DashboardDesigner {
		return $this->designer;
	}

	public function themeCSS(){
		$theme = $this->designer->getCurrentTheme();
		if(!is_null($theme)){
			$this->css("themes/" . $theme . ".css");
		}
	}
}