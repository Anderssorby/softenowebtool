<?php
/**
 *
 */
class StyleHandler implements Action, ModuleApp {
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
	
	public function readStructure($structure) {
		$list = new HtmlList("sow-styleedit-list");
		foreach ($structure as $i => $struct) {
			$li = $list->add(new Span($struct['type'], "sow-styleedit-list-type"));
			if ($struct['name']) $li->addChild(new Span($struct['name'], "sow-styleedit-list-name"));
			if ($struct['value']) $li->addChild(new Span($struct['value'], "sow-styleedit-list-value"));
			if ($struct['children']) $li->addChild($this->readStructure($struct['children']));
		}
		return $list;
	}

	public function createInstance($args) {
		$content = $args["_element"];
		$document = $args["_document"];
		$style = $args["_style"];
		switch ($this->key) {
			case "style":
				$content->addChild(new HtmlElement("h1", array("align" => "center"), "Stil"));
				chdir("..");
				$page = new Page();
				chdir("softenowebtool");
				$style = $page->getStyle();
				$content->addChild($left = new Div(""));
				$left->style = "width:50%;float:left;";
				$left->addChild(new HtmlElement("h2", false, "Struktur"));
				$left->addChild($this->readStructure($style->getStructure()));
				$content->addChild($right = new Div(""));
				$right->style = "width:50%;float:right;";
				$right->addChild(new HtmlElement("h2", false, "Stilset"));
				$right->addChild($css = new HtmlElement("textarea", false, $style->getCSS()));
				$css->style = "width: 450px;height:400px;";
				break;
		}
	}
}