<?php
class Div extends HtmlElement {
	public function __construct($class = "", $id = "", $content = "") {
		parent::__construct("div", is_array($class)?$class:false);
		if (is_string($class)) $this->setAttribute("class", $class);
		if ($id) $this->setId($id);
		if ($content) $this->addChild($content);
	}
}