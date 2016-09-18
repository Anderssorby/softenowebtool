<?php
class Menu extends HtmlList {
	protected $links = array();
	public $logger;
	public function __construct($class = "") {
		parent::__construct($class ? $class : "Menu", "menu".HtmlElement::$instance);
		$this -> logger = new Logger();
	}

	public function addMenuArray($arr, $parent = false) {
		$link = new Link($arr["attr"]["link"]?$arr["attr"]["link"]:"#", $arr["attr"]["name"]);
		if (!($parent instanceof Node)) {
			$li = $this->add($link);
			if (is_array($arr["childs"])) {
				$subList = new Div("", "sub".Menu::$instance);
				$subList -> onmouseover = "mcancelclosetime()";
				$subList -> onmouseout = "mclosetime()";
				foreach ($arr["childs"] as $child){
					$this->addMenuArray($child, $subList);
				}
				$li -> addChild($subList);
				$link -> onmouseover = "mopen('" . $subList -> id . "')";
				$link -> onmouseout = "mclosetime()";
			}

		} else {
			$parent->addChild($link);
			if (is_array($arr['childs'])) {
				$subList = new Div("", "sub".Menu::$instance);
				$subList -> onmouseover = "mcancelclosetime()";
				$subList -> onmouseout = "mclosetime()";
					
				foreach ($arr['childs'] as $child) {
					$this->addMenuArray($child, $subList);
				}
				$link -> addChild($subList);
				$link -> onmouseover = "mopen('" . $subList -> id . "')";
				$link -> onmouseout = "mclosetime()";
			}
		}
	}



	public function loadFromXML($file) {
		$doc = new DOMDocument();
		$doc->load($file);
		$node = $doc->documentElement;
		foreach ($node->childNodes as $child) {
			$this->processMenu($child, $this);
		}
	}

	/**
	 * Fetches the menuobjects as defined in the xml document
	 * @param Node $element
	 */
	protected function processMenu(DOMNode $element, Node $node) {
		if ($element->nodeType == XML_ELEMENT_NODE) {
			$link = new Link(utf8_decode($element->getAttribute("link")), utf8_decode($element->getAttribute("name")));
			if ($node instanceof HtmlList)
			$li = $node->add($link);
			else {
				$node->addChild($link);
				$li = $node;
			}
			if ($element->hasChildNodes()) {
				$subList = new Div("", "sub".Menu::$instance);
				$subList -> onmouseover = "mcancelclosetime()";
				$subList -> onmouseout = "mclosetime()";
				foreach ($element->childNodes as $childNode) {
					$this->processMenu($childNode, $subList);
				}
				$li -> addChild($subList);
				$link -> onmouseover = "mopen('" . $subList -> id . "')";
				$link -> onmouseout = "mclosetime()";
			}
		}
	}
}