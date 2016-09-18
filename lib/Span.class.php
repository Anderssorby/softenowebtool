<?php
/**
 * 
 */
class Span extends HtmlElement {
	public function __construct($content = "", $class = "", $id = "", $attrs = false) {
		parent::__construct("span", $attrs, $content);
		if ($class) $this->class = $class;
		if ($id) $this->id = $id;
	}
}

