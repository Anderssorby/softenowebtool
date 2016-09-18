<?php
/** * */
class Button extends HtmlElement {

	function __construct($label = "", $id = "", $submit = false) {
		parent::__construct("button", array("type" => $submit?"submit":"button", "id" => $id), $label);
		$this->class = "sow-button";
		if (!$id) $this->generateId();
	}

}