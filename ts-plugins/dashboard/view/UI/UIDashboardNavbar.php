<?php
namespace tsframe\view\UI;

use tsframe\view\HtmlTag;
use tsframe\view\UI\UIAbstractElement;

class UIDashboardNavbar extends UIAbstractElement {
	protected $nav;

	public function __construct(){
		$this->nav = HtmlTag::createElement('nav');
		$this->nav->addClass('navbar');
		$this->nav->addClass('navbar-inverse');
		$this->nav->addClass('navbar-fixed-top');
	}

	public function navtop(){
		$this->nav->addElement('')->text($this->getContent(function(){ $this->tpl->incNavtop(); }));
		return $this;
	}

	public function navside(){
		$this->nav->addElement('')->text($this->getContent(function(){ $this->tpl->incNavside(); }));
		return $this;
	}

	public function render(): HtmlTag {
		$this->nav->set('id', $this->getId());
		return $this->nav;
	}
}