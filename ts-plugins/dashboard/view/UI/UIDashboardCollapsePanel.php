<?php
namespace tsframe\view\UI;

use tsframe\view\HtmlTag;
use tsframe\view\UI\UIAbstractElement;
use tsframe\view\UI\UIDashboardPanel;

class UIDashboardCollapsePanel extends UIAbstractElement {

	protected $panel, $header, $body, $footer;

	protected $collapsed = true;

	public function __construct(string $panelType = null){
		$this->panel = HtmlTag::createElement('div');
		$this->panel->addClass('panel');
		$this->panel->addClass('panel-collapsable');

		if(!is_null($panelType)){
			$this->panel->addClass('panel-' . $panelType);
		}
	}

	public function setCollapsed(bool $state){
		$this->collapsed = $state;
	}

	public function header($content): HtmlTag {
		$header = HtmlTag::createElement('div');
		$header->addClass('panel-heading');
        
		$title = $header->addElement('h4');
		$title->addClass('panel-title');

		$a = $title->addElement('a');
		$a->set('data-toggle', 'collapse');
		$a->set('href', '#' . $this->getId());
		$a->text($this->getContent($content));
        

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
		$this->panel->id($this->getId() . '-parent');
		$this->panel->addElement($this->header);

		$content = $this->panel->addElement('div');
		$content->addClass('panel-collapse');
		$content->id($this->getId());

		if($this->collapsed){
			$content->addClass('collapse');
		}

		$content->addElement($this->body);
		$content->addElement($this->footer);

		return $this->panel;
	}
}