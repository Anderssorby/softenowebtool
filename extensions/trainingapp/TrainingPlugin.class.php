<?php
/**
*
*/
class TrainingPlugin implements Action, ModuleApp {

	protected $key;
	
	public function __construct($key) {
		$this->key = $key;
	}
	
	public function performAction($command, $args) {
			switch ($this->key) {
						case "":
						break;
		}
	}
								
	public function createInstance($args) {
		$content = $args["_element"];
		$document = $args["_document"];		
		$style = $args["_style"]; 
		$source = $_GET["src"];
		switch ($this->key) {
			case "trainingapp":
$content->addChild(new HtmlElement("h1", "hello"));
				$content->addChild($cal = new Div("", ""));
				$cal->generateId();
				$content->addChild(new script("", "$('#".$cal->id."').calendar({large: false, link: true, selected: function() {}});"));
				$entry = new HtmlElement("textarea", array("name" => "entry"));
				
				$content->addChild($entry);
			break;		
		}	
	}
}