<?php
/** * */
class HtmlList extends HtmlElement {
	function __construct($class = "", $id = "", $ord = false) {
		parent::__construct($ord ? "ol" : "ul");
		$this -> setAttribute("class", $class);
		$this -> id = $id;
	}

	function add($item) {
		$li = new HtmlElement("li");
		$li -> addChild(func_get_args());
		return $this -> addChild($li);
	}
}