<?php
namespace tsframe\view;

class HtmlTag extends \HtmlGenerator\HtmlTag {
	public function getChild(): array {
		return $this->content;
	}
}