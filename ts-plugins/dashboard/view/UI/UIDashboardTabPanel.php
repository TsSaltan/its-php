<?php
namespace tsframe\view\UI;

use tsframe\view\HtmlTag;
use tsframe\view\UI\UIAbstractElement;
use tsframe\view\UI\UIDashboardPanel;


class UIDashboardTabPanel extends UIAbstractElement {

	/**
	 * @var UIDashboardPanel
	 */
	protected $panel;

	/**
	 * @var HtmlTag
	 */
	protected $header;

	/**
	 * @var HtmlTag
	 */
	protected $uiTabBase = null;

	/**
	 * @var array
	 */
	protected $tabs = [];

	/**
	 * @var ?string
	 */
	protected $activeTab = null;

	public function __construct(string $panelType = null){
		$this->panel = new UIDashboardPanel($panelType);
	}

	public function header($header): HtmlTag {
		$this->header = $this->panel->header($header, $this->uiTabBase());	
		return $this->header;
	}

	public function footer($footer): HtmlTag {
		return $this->panel->footer($footer);	
	}

	public function setActiveTab(string $id){
		$this->activeTab = $id;
		return $this;
	}

	public function tab(string $id, $title, $content){
		$this->tabs[$id] = [
			'title' => $title,
			'content' => $content
		];

		// Активным будет первый таб по умолчанию
		if(is_null($this->activeTab)){
			$this->activeTab = $id;
		}

		return $this;
	}

	public function render(): HtmlTag {
		if(is_null($this->uiTabBase)){
			$this->uiTabBase();
			$this->header = $this->panel->header($this->uiTabBase);
		}

		foreach ($this->tabs as $tabId => $tabData){
			$li = $this->uiTabBase->addElement('li');
			$a = $li->addElement('a');

			if($tabId == $this->activeTab){
				$li->addClass('active');
			}

			$a->set('href', '#' . $tabId);
			$a->set('data-toggle', 'tab');
			$a->text($this->getContent($tabData['title']));
		}

		$headerItems = $this->header->getChild();
		$tabHeader = end($headerItems);
		$tabHeader->removeClass('panel-title');

		$body = $this->panel->body()->addElement('div');
		$body->addClass('tab-content');

		foreach ($this->tabs as $tabId => $tabData){
			$pane = $body->addElement('div');
			$pane->addClass('tab-pane');
			$pane->addClass('fade');

			if($tabId == $this->activeTab){
				$pane->addClass('in');
				$pane->addClass('active');
			}

			$pane->id($tabId);
			$pane->text($this->getContent($tabData['content']));
		}

		$panel = $this->panel->render();
		$panel->addClass('tabbed-panel');

		return $panel;
	}

	protected function uiTabBase(): HtmlTag {
		$this->uiTabBase = HtmlTag::createElement('ul');
		$this->uiTabBase->addClass('nav');
		$this->uiTabBase->addClass('nav-tabs');

		return $this->uiTabBase;
	}	
}