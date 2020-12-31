<?php
namespace tsframe\view\UI;

use tsframe\view\HtmlTag;
use tsframe\view\UI\UIAbstractElement;

class UIDashboardPanel extends UIAbstractElement {
	protected $panel, $header, $body, $footer;

	public function __construct(string $panelType = null){
		$this->panel = HtmlTag::createElement('div');
		$this->panel->addClass('panel');

		if(!is_null($panelType)){
			$this->panel->addClass('panel-' . $panelType);
		}
	}

	public function header($contentLeft = null, $contentRight = null): HtmlTag {
		$header = HtmlTag::createElement('div');
		$header->addClass('panel-heading');
		$header->addClass('clearfix');

        if(!is_null($contentLeft)){
			$left = $header->addElement('div');
			$left->addClass('pull-left');
			$left->addClass('panel-title');
			$left->text($this->getContent($contentLeft));
        }

        if(!is_null($contentRight)){
			$right = $header->addElement('div');
			$right->addClass('pull-right');
			$right->addClass('panel-title');
			$right->text($this->getContent($contentRight));
        }

		return $this->header = $header;
	}

	public function body($content = null): HtmlTag {
		$body = HtmlTag::createElement('div');
		$body->addClass('panel-body');
		$body->text($this->getContent($content));

		return $this->body = $body;
	}

	public function footer($content = null): HtmlTag {
		$footer = HtmlTag::createElement('div');
		$footer->addClass('panel-footer');
		$footer->text($this->getContent($content));

		return $this->footer = $footer;
	}

	public function render(): HtmlTag {
		$this->panel->set('id', $this->getId());
		$this->panel->addElement($this->header);
		$this->panel->addElement($this->body);
		$this->panel->addElement($this->footer);
		return $this->panel;
	}
}