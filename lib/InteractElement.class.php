<?php
class InteractElement extends Form {
	
	public function __construct($action, $title, $confirm = false) {
		parent::__construct($action, FALSE);
		$this->addChild(new Button($title, "", TRUE));
		$this->class = "inline";
		if ($confirm) {
			$this->onsubmit = "return confirm('Er du sikker på at du vil utføre denne handlingen?');";
		}
	}
	
	public function addInput($label, Input $input) {
		$lab = new HtmlElement("label");
		$lab -> addChild($label);
		$lab -> addChild($input);
		$this -> addChild($lab);
	}
}
	