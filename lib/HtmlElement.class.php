<?php
class HtmlElement extends Node {
	public function __construct($type, $noEndTag = false, $value = "") {
		parent::__construct($type, $noEndTag);
		if ($value) {
			$this->addChild($value);
		}
	}
	public function addClass($class) {
		if ($this->attributes['class']) {
			$this->setAttribute("class", $this->class." ".$class);
		} else {
			$this->setAttribute("class", $class);
		}
	}
	public function setId($id) {
		$this -> setAttribute("id", $id);
	}

	public function generateId() {
		$this->setAttribute("id", $this->type.$this->ins);
	}

	public function script($script = "") {
		$js = new script("", $script);
		return $this->addChild($js);
	}
}
