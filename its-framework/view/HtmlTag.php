<?php
namespace tsframe\view;

use HtmlGenerator\HtmlTag as HtmlGenTag;

class HtmlTag extends HtmlGenTag {
	public function getChild(): array {
		return $this->content;
	}

	/**
     * Добавляет элемент, но не клонирует его, как это указано в родительском классе
     * @param Markup|string $tag
     * @return static instance
     * @override
     */
    public function addElement($tag = '')
    {
        $htmlTag = (is_object($tag) && ($tag instanceof self || $tag instanceof HtmlGenTag)) ? $tag : new static($tag);
        $htmlTag->top = $this->getTop();
        $htmlTag->parent = &$this;

        $this->content[] = $htmlTag;
        return $htmlTag;
    }
}