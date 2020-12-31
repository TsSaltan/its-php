<?php
namespace tsframe\view\UI;

use tsframe\view\UI\UIAbstractElement;


class UIDashboardPanel extends UIAbstractElement {
	protected $panelClass;
	protected $panelId;
	protected $html = '';

	public function __construct($panelClass = null, ?string $panelId = null){
		$this->panelClass = $panelClass;
		$this->panelId = (strlen($panelId) == 0) ? uniqid('panel-'): $panelId;
	}

	public function header($contentLeft = null, $contentRight = null, ?string $classes = null, ?string $contentLeftClasses = "panel-title", ?string $contentRightClasses = "panel-title"){
		$this->html .= $this->getContent(function() use ($contentLeft, $contentRight, $classes, $contentLeftClasses, $contentRightClasses){
			?><div class="<?=$this->getClassString('panel-heading', 'clearfix', $classes)?>"><?php
            	if(!is_null($contentLeft)):?><div class="<?=$this->getClassString('pull-left', $contentLeftClasses)?>"><?=$this->getContent($contentLeft)?></div><?php endif;
            	if(!is_null($contentRight)):?><div class="<?=$this->getClassString('pull-right', $contentRightClasses)?>"><?=$this->getContent($contentRight)?></div><?php endif;
        	?></div><?php
		});

		return $this;
	}

	public function body($content = null, ?string $classes = null){
		$this->html .= $this->getContent(function() use ($content, $classes){
			?><div class="panel-body <?=$classes?>"><?=$this->getContent($content)?></div><?php
		});

		return $this;
	}

	public function footer($content = null, ?string $classes = null){
		$this->html .= $this->getContent(function() use ($content, $classes){
			?><div class="panel-footer <?=$classes?>"><?=$this->getContent($content)?></div><?php
		});

		return $this;
	}

	public function render(){
		return $this->getContent(function(){ 
			?>
			<!-- .panel --><div id="<?=$this->panelId?>" class="<?=$this->getClassString('panel', $this->panelClass)?>"><?=$this->html?></div><!-- /.panel -->
			<?php
		});
	}
}