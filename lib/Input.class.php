<?php class Input extends HtmlElement {
	public function __construct($type = "text", $name = "", $value = "") {
		parent::__construct("input", true);
		$this -> setAttribute("type", $type);
		$this -> setAttribute("name", $name);
		$this -> setAttribute("id", $name);
		$this -> setAttribute("value", $value);
	}

	public function setValue($value) {
		$this -> setAttribute("value", $value);
	}
}