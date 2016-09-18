<?php 
class chooser extends HtmlElement {
	public function __construct($class = "", $name = "", $id = "", $smal = true) {
	parent::__construct("select");
	if (!$smal)
		$this -> setAttribute("size", "2");
		$this -> setAttribute("class", $class);
		$this -> setAttribute("name", $name);
		$this -> setAttribute("id", $id);
	}

	public function add_option($title, $value) {
		$option = new HtmlElement("option");
		$option -> setAttribute("value", $value);
		$option -> addChild($title);
		$this -> addChild($option);
	}

	public function addOption($title = "", $value = "") {
		$option = new HtmlElement("option");
		$option -> setAttribute("value", $value);
		$option -> addChild($title);
		return $this -> addChild($option);
	}
	
	public function hasValue($value) {
		$options = $this->getElementsByTagName("option");
		foreach ($options as $k => $option) {
			if ($option->value == $value) {
				return true;
			}
		}
		return false;
	}
	
	public function hasOption($title) {
		$options = $this->getElementsByTagName("option");
		foreach ($options as $k => $option) {
			if ($option->children[0]->equals($title)) {
				return true;
			}
		}
		return false;
	}
	
	public function setSelectedOption($option) {
		$options = $this->getElementsByTagName("option");
		foreach ($options as $k => $option) {
			if ($option->children[0]->equals($option)) {
				$option->selected = "selected";
				return true;
			}
		}
		return false;
	}
	
	public function setSelectedValue($value) {
		$options = $this->getElementsByTagName("option");
		foreach ($options as $k => $option) {
			if ($option->value == $option) {
				$option->selected = "selected";
				return true;
			}
		}
		return false;
	}
	
	public function generateRange($max, $min = 0, $leap = 1) {
		for ($i = $min; $i <= $max; $i+=$leap) {
			$this->addOption($i, $i);
		}
	}
	
}