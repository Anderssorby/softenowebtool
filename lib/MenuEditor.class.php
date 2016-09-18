<?php
class MenuEditor implements Action, ModuleApp {
	protected $key;
	public function __construct($key) {
		$this->key = $key;
	}

	protected function getIndex($index, DOMElement $parent) {
		$in = explode(":", $index);
		$el = $parent;
		foreach ($in as $i => $n) {
			$ch = $el->childNodes;
			if (is_numeric($n) && $n < $ch->length && $n>=0) {
				$c = $ch->item($n);
				$el = $c;
			} else {
				return false;
			}
		}
		return $el;
	}

	public function performAction($command, $args) {
		switch ($this->key) {
			case 'menu-edit':
				$name = $args["name"];
				$link = $args["link"];
				$mod = $args['mod'];
				$num = $args['num'];
				$index = $args['index'];
				if ($mod&&is_numeric($num)&&$name) {
					chdir("..");
					$page = new Page();
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					$menu = $this->getIndex($index, $element);
					if ($menu) {
						$menu->setAttribute("link", $link);
						$menu->setAttribute("name", $newname);
					} else {
						$menuobj = $page->getXMLDoc()->createElement("menuobject");
						$menuobj->setAttribute("link", $link);
						$menuobj->setAttribute("name", $newname);
						$element->appendChild($menuobj);
					}
					$page->saveTemplate();
					chdir("softenowebtool");
					return array("message" => "Menyelement er oppdatert (".$index.")");
				}
				break;
			case 'menu-del':
				$mod = $args['mod'];
				$num = $args['num'];
				$index = $args['index'];
				if ($mod&&is_numeric($num)&&$index) {
					chdir("..");
					$page = new Page();
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					$menu = $this->getIndex($index);
					if ($menu) {
						$par = $menu->parent;
						$par->removeChildNode($menu);
						$page->saveTemplate();
						chdir("softenowebtool");
						return array("message" => "Menyelement ble slettet (".$index.")");
					}
				}
				break;
			case 'menu-reorder':
				$mod = $args['mod'];
				$num = $args['num'];
				$menu = $args['menu'];
				if ($mod&&is_numeric($num)&&$menu) {
					chdir("..");
					$page = new Page();
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					//empty $element first
					if ($element->hasChildNodes()) {
						for ($i = $element->childNodes->length-1; $i >= 0; $i--) {
							$element->removeChild($element->childNodes->item($i));
						}
					}
					$result = $this->reorderMenu($page->getXMLDoc(), $element, $menu);
					if ($result) $page->saveTemplate();
					return array("message" => $result?utf8_encode("Rekkefølgen ble lagret"):utf8_encode("Rekkefølgen ble ikke lagret"));
				}
				break;
			case 'infosites':
				$arr = array();
				$query = "SELECT `name`, `id` FROM `info`";
				$resource = mysql_query($query);
				while ($row = mysql_fetch_assoc($resource)) {
					$arr[$row['name']] = "?site=info&id=".$row['id'];
				}
				return array("message" => "Fullført innlasting av infosider.", "values" => $arr);
				break;
		}
	}

	private function loadMenu(DOMElement $node, HtmlList $list) {
		foreach ($node->childNodes as $key => $element) {
			if ($element->tagName === "menuobject") {
				$li = $list->add(new Span("_"));
				$li->addChild(new Input("text", "name", utf8_decode($element->getAttribute("name"))));
				$li->addChild(new Input("text", "link", utf8_decode($element->getAttribute("link"))));
				$li->addChild($sub = new HtmlList());
				if ($element->hasChildNodes()) {
					$this->loadMenu($element, $sub);
				}
			}
		}
	}

	private function reorderMenu(DOMDocument $doc, DOMElement $element, $menu) {
		if (is_array($menu)) {
			$result = true;
			foreach ($menu as $key => $m) {
				$upd = $doc->createElement("menuobject");
				$upd->setAttribute("name", $m["name"]);
				$upd->setAttribute("link", $m["link"]);
				$element->appendChild($upd);
				if (is_array($m["children"])) {
					$result = $result && $this->reorderMenu($doc, $upd, $m["children"]);
				}
			}
			return $result;
		}
	}

	public function createInstance($args) {
		$content = $args["_element"];
		$document = $args["_document"];
		switch ($this->key) {
			case 'editmenu':
				$document->head->linkStyleSheet("css/MenuList.css");
				$document->head->addScript("javascript/MenuLister.js");
				$mod = $args['mod'];
				$num = $args['num'];
				if ($mod&&is_numeric($num)) {
					$content->addChild($hold = new Div("menu-lister-wrapper"));
					$hold->style = "text-align: center;";
					chdir("..");
					$page = new Page();
					$list = $page->listModuleApplications($mod);
					$node = $list[$num]["element"];
					$hold->addChild($h1 = new HtmlElement("h1", false, "Menyoversikt"));
					$hold->addChild($p = new HtmlElement("p", false, ""));
					$hold->addChild($links = new HtmlList("menu-lister", "menu-list"));
					$this->loadMenu($node, $links);
					$hold->addChild($new = new HtmlList("menu-lister-new"));
					$hold->addChild(new HtmlList("menu-lister-bin"));
					$hold->addChild(new HtmlElement("button", array("class" => "button button-big menu-lister-lagre", "type" => "button", "id" => "lagre"), "Lagre"));
					$hold->addChild(new script("", "$('#$links->id').menulister();$('#lagre').action({action: 'menu-reorder', args: {menu: function () {return $('#$links->id').menulister('save');}, mod: '".$mod."', num: ".$num."}});"));
					chdir("softenowebtool");
				}
				break;
			case 'menu-edit':
				$menuLister = new MenuLister("../menu.xml", Data::get("name", "s", 40), true);
				$content -> addChild($menuLister);
				$document -> head -> linkStyleSheet("css/MenuList.css");
				break;
		}
	}
}