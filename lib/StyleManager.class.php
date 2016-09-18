<?php
/**
 *
 */
class StyleManager {
	protected $doc;
	protected $document;
	protected $structure = array();
	protected $css;
	protected $stylesets = array();
	protected static $indent = 0;

	public function __construct($file) {
		$this->doc = new DOMDocument();
		$this->doc->load($file);
		$this->document = new Document();
		$this->css = new CSS();
		$this->loadStyles();
		$style = new HtmlElement("style", array("type" => "text/css"), $this->css);
		$this->document->head->addChild($style);
	}

	protected function loadStyles() {
		$structure = $this->doc->getElementsByTagName("structure")->item(0);
		$class = $this->generateClassName();
		$this->document->body->class = $class;
		foreach ($structure->childNodes as $child) {
			if ($child->nodeType == XML_ELEMENT_NODE) {
				switch ($child->tagName) {
					case 'style':
						$i = count($this->structure);
						$sel = $child->getAttribute("selector");
						$name = $child->getAttribute("name");
						$value = $child->getAttribute("value");
						$this->structure[$i]['type'] = "style";
						$this->structure[$i]['name'] = $name;
						$this->structure[$i]['value'] = $value;
						$this->structure[$i]['selector'] = $sel;
						if ($sel) {
							$this->css->applyStyle($sel, $name, $value);
						} else {
							$this->applyStyle($child, ".".$class);
						}
						break;
					case 'wrapper':
						$i = count($this->structure);
						$this->structure[$i]['type'] = "wrapper";
						$this->structure[$i]['children'] = $this->loadWrapper($child, $this->document->body);
						break;
					case "module":
						$i = count($this->structure);
						$this->structure[$i]['type'] = "module";
						$this->document->body->addChild($mod = new Div());
						$name = $child->getAttribute("name");
						$this->structure[$i]['name'] = $name;
						$mod->class = $this->generateClassName($name);
						$this->document->setModule($name, $mod);
						$styles = $child->getElementsByTagName("style");
						foreach ($styles as $style) {
							$c = count($this->structure[$i]['children']);
							$sel = $child->getAttribute("selector");
							$name = $child->getAttribute("name");
							$value = $child->getAttribute("value");
							$this->structure[$i]['children'][$c]['type'] = "style";
							$this->structure[$i]['children'][$c]['name'] = $name;
							$this->structure[$i]['children'][$c]['value'] = $value;
							$this->structure[$i]['children'][$c]['selector'] = $sel;
							if ($sel) {
								$this->applyStyle($style, ".".$mod->class." ".$sel);
							} else {
								$this->applyStyle($style, ".".$mod->class);
							}
						}
						break;
				}
			}
		}
		$styles = $this->doc->getElementsByTagName("styles")->item(0);
		foreach ($styles->childNodes as $child) {
			if ($child->tagName == 'set') {
				$stylename = $child->getAttribute("name");
				$class = $this->generateClassName();
				$this->stylesets[$stylename]['class'] = $class;
				foreach ($child->childNodes as $chi) {
					switch ($chi->tagName) {
						case "style":
							$this->applyStyle($chi, ".".$class);
							break;
						case "sub":
							$this->loadSubSet($chi, ".".$class, $stylename);
							break;
					}
				}
			}
		}
	}

	protected function loadSubSet(DOMElement $sub, $selector, $stylename) {
		$sel = $sub->getAttribute("selector");
		if ($sel) {
			$ex = explode(",", $sel);
			$subclass = "";
			$l = count($ex);
			foreach ($ex as $k => $e) {
				$ex2 = explode(",", $selector);
				$l2 = count($ex2);
				foreach ($ex2 as $k2 => $e2) {
					$subclass .= $e2." ".$e;
					if (!($k2>=$l2-1)) {
						$subclass .= ", ";
					}
				}
				if (!($k>=$l-1)) {
					$subclass .= ", ";
				}
			}
			$elname = $sub->getAttribute("element");
			$this->stylesets[$stylename]['subclass'][$elname] = $subclass;
		} else {
			$subclass = $this->generateClassName();
			$elname = $sub->getAttribute("element");
			$this->stylesets[$stylename]['subclass'][$elname] = $subclass;
			$subclass = ".".$subclass;
		}
		foreach ($sub->childNodes as $child) {
			switch ($child->tagName) {
				case "style":
					$this->applyStyle($child, $subclass);
					break;
				case "sub":
					$this->loadSubSet($child, $subclass, $stylename);
					break;
			}
		}
	}

	protected function loadWrapper(DOMElement $wrap, $parent) {
		$structure = array();
		$parent->addChild($wrapper = new Div());
		$wrapper->class = $this->generateClassName();
		foreach ($wrap->childNodes as $child) {
			switch ($child->tagName) {
				case "wrapper":
					$i = count($structure);
					$structure[$i]['type'] = "wrapper";
					$structure[$i]['children'] = $this->loadWrapper($child, $wrapper);
					break;
				case "style":
					$i = count($structure);
					$sel = $child->getAttribute("selector");
					$name = $child->getAttribute("name");
					$value = $child->getAttribute("value");
					$structure[$i]['type'] = "style";
					$structure[$i]['name'] = $name;
					$structure[$i]['value'] = $value;
					$structure[$i]['selector'] = $sel;
					if ($sel) {
						$this->applyStyle($child, ".".$wrapper->class." ".$sel);
					} else {
						$this->applyStyle($child, ".".$wrapper->class);
					}
					break;
				case "module":
					$i = count($structure);
					$structure[$i]['type'] = "module";
					$wrapper->addChild($mod = new Div());
					$name = $child->getAttribute("name");
					$structure[$i]['name'] = $name;
					$mod->class = $this->generateClassName($name);
					$this->document->setModule($name, $mod);
					$styles = $child->getElementsByTagName("style");
					foreach ($styles as $style) {
						$sel = $style->getAttribute("selector");
						$c = count($structure[$i]['children']);
						$sname = $style->getAttribute("name");
						$value = $style->getAttribute("value");
						$structure[$i]['children'][$c]['type'] = "style";
						$structure[$i]['children'][$c]['name'] = $sname;
						$structure[$i]['children'][$c]['value'] = $value;
						$structure[$i]['children'][$c]['selector'] = $sel;
						if ($sel) {
							$this->applyStyle($style, ".".$mod->class." ".$sel);
						} else {
							$this->applyStyle($style, ".".$mod->class);
						}
					}
					break;
			}
		}
		return $structure;
	}

	protected function applyStyle($element, $selector) {
		$name = (string)$element->getAttribute("name");
		$value = (string)$element->getAttribute("value");
		$this->css->applyStyle($selector, $name, $value);
	}

	protected function generateClassName($module = "") {
		$name = "sow-";
		$in = StyleManager::$indent;
		$chars = array('q', 'w', 'r', 't', 's');
		$len = count($chars);
		$spin = intval($in/$len);
		$inl = $in-$spin*$len;
		if ($module&&is_string($module)) {
			$name .= $module;
		} else {
			for ($i = 0; $i < $len; $i++) {
				shuffle($chars);
				$dex = $inl+$i;
				if ($dex>=$len) $dex -= $len;
				$name .= $chars[$dex];
			}
		}
		$name .= "-".$spin.$in;
		for ($i = 0; $i < $inl; $i++) {
			$name .= $chars[$i];
		}
		StyleManager::$indent++;
		return $name;
	}

	public function getStyleSet($name) {
		return $this->stylesets[$name];
	}

	public function extractDocument() {
		return $this->document;
	}

	public function getCSS() {
		return $this->css;
	}
	
	public function getStructure() {
		return $this->structure;
	}
}