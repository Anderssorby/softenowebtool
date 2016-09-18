<?php
/**
 *
 */
class MenuLister extends Div {
	protected $file;
	protected $script;
	protected $tar;
	protected $name;
	protected $doc;

	public function __construct($file, $name = "", $edit = false) {
		parent::__construct("", "menu-lister");
		$this->file = $file;
		$this->name = urldecode($name);
		$this->doc = new DOMDocument();
		$this->doc->load($this->file);
		$this->addChild($this -> script = new script());
		if (!$name&&!$edit) {
			$this->makeContent();
		} else {
			$this->makeEditContent();
		}
	}

	public function removeMenuObject($name) {
		$this->name = $name;
		$result = $this->searchMenu();
		if ($result) $result->parentNode->removeChild($result);
		else Logger::getDefaultLogger()->app("fant ikke menyobjektet $name");
		$this->doc->save($this->file);
	}

	public function reorderMenu() {
		global $error;
		$parentName = Data::get("parent", "s", 40);
		if ($parentName) {
			$this->name = $parentName;
			$parent = $this->searchMenu();
			$error->app("parent: ".$parentName);
		} else {
			$parent = $this->doc->documentElement;
		}
		$keys = array_keys($_POST);

		$menuObj = array();
		foreach ($keys as $key) {
			$value = Data::post($key, "i", 5);
			$key = utf8_decode(str_replace("_", " ", $key));
			if (is_numeric($value)) {
				$this->name = $key;
				$result = $this->searchMenu();
				if ($result) {
					$menuObj[$value] = $result->cloneNode(true);
				} else {
					$error->app("wrong key: ".$key);
				}
			} else {
				$error->app("not numeric ".$value);
			}
		}
		$len = 0;
		$menuList = array();
		foreach ($parent->childNodes as $c) {
			if ($c->nodeName == "menuobject") {
				$menuList[] = $c;
				$len++;
			}
		}
		if (count($menuObj) == $len) {
			foreach ($menuObj as $index => $men) {
				$item = $menuList[$index];
				$parent->removeChild($item);
				$parent->appendChild($men);
			}
		} else {
			$error->app("lenght $len not fully represented ".implode(", ", $keys)." for ".$parentName);
			return false;
		}
		return $this->doc->save($this->file);
	}

	public function makeEditContent() {
		$doc = new DOMDocument();
		$doc->load($this->file);
		$node = $doc->documentElement;
		$this->addChild($h1 = new HtmlElement("h1", false, "Endre element"));
		if ($this->name) {
			foreach ($node->childNodes as $child) {
				$this->processEdit($child);
			}
		} else {
			$form = new Form("?site=menu&action=menu-edit", false);
			$form->addInput("navn", new Input("text", "newname"));
			$form->addInput("link", new Input("text", "link"));
			$form->addChild($this->makeSiteChooser());
			$parent = Data::get("parent", "s", 40);
			if ($parent) $form->addChild(new Input("hidden", "parent", $parent));
			$form->addChild(new Input("submit", "edit", "lagre"));
			$this->addChild($form);
		}
		$this->addChild($back = new Link("?site=menu", "Tilbake"));
	}

	public function editMenu() {
		$this->name = urldecode($_POST["name"]);
		$link = Data::post("link", "s", 200);
		$newname = Data::post("newname", "s", 40);
		$parent = Data::post("parent", "s", 40);
		$doc = $this->doc;
		$menuList = $this->doc->getElementsByTagName("menuobject");
		if ($this->name) {
			foreach ($menuList as $child) {
				$this->menuEdit($child, utf8_encode($link), utf8_encode($newname));
			}
		} else {
			if ($parent) {
				$this->name = $parent;
				$el = $this->searchMenu();
			}
			else $el = $doc->documentElement;
			$menuobj = $this->doc->createElement("menuobject");
			$menuobj->setAttribute("link", utf8_encode($link));
			$menuobj->setAttribute("name", utf8_encode($newname));
			$el->appendChild($menuobj);
		}
		$bytes = $doc->save($this->file);
	}

	public function makeContent() {
		$node = $this->doc->documentElement;
		$this->addChild(new HtmlElement("h1", false, "Menyoversikt"));
		$this->addChild($ol = new HtmlList("menu-lister", "menu-list", true));
		$ol->generateId();
		$this->script->addChild(new JavaScript("call", "$(document)", "ready", $this->tar = new JavaScript("function", "")));
		foreach ($node->childNodes as $child) {
			$this->processMenu($child, $ol);
		}
		$this->tar->addChild("$('#$ol->id').menulister();");
		$this -> addChild($lbut = new Link("?site=menu-edit", "Legg til meny"));
		$this->addChild(new HtmlElement("button", array("id" => "lagre"), "Lagre ny rekkefølge"));
		$this->addChild($side = new Div("", "side"));
		$this->tar->addChild("$('#lagre').click(function () {\n  $('#$ol->id').menulister('save');\n});");

	}

	protected function processEdit(DOMNode $element) {
		if ($element->nodeType == XML_ELEMENT_NODE) {
			$link = utf8_decode($element->getAttribute("link"));
			$name = utf8_decode($element->getAttribute("name"));
			$parent = utf8_decode($element->parentNode->getAttribute("name"));
			if ($name == $this->name) {
				$form = new Form("?site=menu&action=menu-edit", false);
				$form->addInput("navn", new Input("text", "newname", $name));
				$form->addInput("link", new Input("text", "link", $link));
				$form->addChild($this->makeSiteChooser());
				$form->addChild(new Input("hidden", "name", $name));
				if ($parent) $form->addChild(new Input("hidden", "parent", $parent));
				$form->addChild(new Input("submit", "edit", "lagre"));
				$this->addChild($form);

			} else {
				foreach ($element->childNodes as $child) {
					$this->processEdit($child);
				}
			}
		}
	}

	public function makeSiteChooser() {
		$chooser = new chooser();
		$chooser -> generateId();
		$dataReader = new DataReader("info", 0, 100);
		foreach ($dataReader -> assoc as $as) {
			$chooser->addOption($as['name'], "?site=info&id=".$as['id']);
		}
		$this->script->addChild("$(document).ready(function() { $('#$chooser->id').change(function() { $('#link').val($(this).val());});});");
		return $chooser;
	}

	protected function menuEdit(DOMNode $element, $link, $newname) {
		if ($element->nodeType == XML_ELEMENT_NODE) {
			$name = $element->getAttribute("name");
			if ($name == $this->name) {
				if (!$newname&&!$link)
				return;
				$element->setAttribute("link", $link);
				$element->setAttribute("name", $newname);
			} else {
				foreach ($element->childNodes as $child) {
					$this->menuEdit($child, $link, $newname);
				}
			}
		}
	}

	protected function searchMenu(DOMNode $element = null) {
		if (!$element) {
			$node = $this->doc->documentElement;
			foreach ($node->childNodes as $child) {
				if ($result = $this->searchMenu($child)) {
					return $result;
				}
			}
			return false;
		}
		if ($element->nodeType == XML_ELEMENT_NODE) {
			$name = $element->getAttribute("name");
			if ($name == $this->name) {
				return $element;
			} else {
				foreach ($element->childNodes as $child) {
					if ($result = $this->searchMenu($child)) {
						return $result;
					}
				}
			}
		}
		return false;
	}

	/**
	 *
	 * Fetches the menuobjects as defined in the xml document
	 * @param Node $element
	 */
	protected function processMenu(DOMNode $element, Node $node) {
		if ($element->nodeType == XML_ELEMENT_NODE) {
			$link = $element->getAttribute("link");
			$name = $element->getAttribute("name");
			$li = $node->add($t = new HtmlElement("span", false, utf8_decode($name)));
			$t -> generateId();
			$li -> generateId();
			$li -> link = $link;
			$li -> addChild($lbut = new Link("?site=menu-edit&name=".$name, "Endre"));
			$li -> addChild(new Link("?site=menu&action=menu-del&name=".$name, "Slett", true));
			$li -> addChild(new Link("?site=menu-edit&parent=".$name, "Legg til undermeny"));
			$li -> addChild($lagre = new HtmlElement("button", array("id" => "lagre"), "Lagre ny rekkefølge"));
			$li -> name = $name;

			//$this->tar->addChild("$('#$lbut->id').click(function() { $('#side').dialog();});");
			if ($element->hasChildNodes()) {
				$subList = new HtmlList("", "", true);
				$subList -> generateId();
				foreach ($element->childNodes as $childNode) {
					$this->processMenu($childNode, $subList);
				}
				
				$this->tar->addChild("$('#$lagre->id').click(function () {\n  $('#$subList->id').menulister('save');\n});");
				//$this -> script -> addChild("$('#$subList->id').sortable();");
				//$subList -> ondragover = "event.preventDefault()";
				//$li -> addChild($subList);
				$li -> addChild($subList);

				//$li -> draggable = "true";
				//$li -> ondragstart = "event.dataTransfer.setData('text/plain', this.id);".
				//"event.dataTransfer.effectAllowed = 'copy';";
			}
		}
	}
}