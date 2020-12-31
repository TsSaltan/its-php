<?php
namespace tsframe\view\UI;

use tsframe\view\UI\UIAbstractElement;
use tsframe\view\UI\UIDashboardPanel;


class UIDashboardTabPanel extends UIAbstractElement {

	/**
	 * @var UIDashboardPanel
	 */
	protected $panel;

	/**
	 * @var callable|string|null
	 */
	protected $header = null;

	/**
	 * @var callable|string|null
	 */
	protected $footer = null;

	/**
	 * @var array
	 */
	protected $tabs = [];

	/**
	 * @var ?string
	 */
	protected $activeTab = null;

	public function __construct($panelClass = null){
		$this->panel = new UIDashboardPanel($this->getClassString('tabbed-panel', $panelClass));
	}

	public function header($header){
		$this->header = $header;
		return $this;
	}

	public function footer($footer){
		$this->footer = $footer;
		return $this;
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

		return $this;
	}


	public function render(){
		$tabHeader = function(){ 
			?>
			<ul class="nav nav-tabs">
                <?php foreach ($this->tabs as $tabId => $tabData):?>
                <li<?php if($tabId == $this->activeTab):?> class="active"<?php endif ?>>
                    <a href="#<?=$tabId?>" data-toggle="tab"><?=$this->getContent($tabData['title'])?></a>
                </li>
                <?php endforeach?>
            </ul>
			<?php
		};

		if(is_null($this->header)){
			$this->panel->header($tabHeader, null, null, null, null);
		} 
		else {
			$this->panel->header($this->header, $tabHeader, null, "panel-title", null);
		}


		$tabBody = function(){ 
			?><div class="tab-content"><?php 
				foreach ($this->tabs as $tabId => $tabData):
                	?><div class="tab-pane fade<?php if($tabId == $this->activeTab):?> in active<?php endif ?>" id="<?=$tabId?>"><?php
     				echo $this->getContent($tabData['content']);
            		?></div><?php 
            	endforeach; 
            ?></div><?php
		};
		$this->panel->body($tabBody);

		if(!is_null($this->footer)){
			$this->panel->footer($this->footer);
		} 

		return $this->getContent($this->panel);
	}
	
}