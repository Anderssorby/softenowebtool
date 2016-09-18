<?php
//
//  Node.php
//  softenowebtool
//
//  Created by anders on 2011-09-03.
//  Copyright 2011 anders. All rights reserved.
//

/**
 * Node is a component in a Document of the DOM stucture.
 */
class Node {
	private $position = 0;
	protected $attributes = array();
	protected $nodeType = 0;
	protected $type = "";
	protected $children = array();
	protected $parentNode;
	protected $commentData;
	public static $newLine = true;
	protected static $instance = 0;
	protected $ins;
	public static $indent = " ";
	protected $noEndTag;
	function __construct($type = "", $noEndTag = false) {
		$this -> type = $type;
		$this -> noEndTag = !is_array($noEndTag) ? $noEndTag : false;
		if (is_array($noEndTag)) {
			foreach ($noEndTag as $key => $attr) {
				if ($key=="_endtag") {
					$this->noEndTag = $attr;
				} else {
					$this->setAttribute($key, $attr);
				}
			}
		}
		$this->ins = Node::$instance++;
		$this->newLine;
	}

	/**
	 *
	 * sets the named attribute to value
	 * @param unknown_type $name
	 * @param unknown_type $value
	 */
	public function setAttribute($name, $value = "") {
		if (!is_null($value)) {
			if (isset($this->attributes[$name])) {
				$this -> attributes[$name]['value'] = htmlentities($value);
			} else {
				$this -> attributes[$name]['name'] = $name;
				$this -> attributes[$name]['value'] = htmlentities($value);
			}
		}
	}

	public function getAttribute($name) {
		return $this->attributes[$name];
	}

	public function getOwnerDocument() {
		$p = $this;
		while ($p = $p -> parentNode)
		if ($p instanceof Document) {
			return $p;
		}
		return false;
	}

	public function hasAttributes() {
		return isset($this -> attributes);
	}

	public function hasChildren() {
		return isset($this -> children);
	}

	public function convertDOMNode(DOMNode $node) {
		if ($element->nodeType === XML_ELEMENT_NODE) {
			$this->type = $node->nodeName;
			if ($node->hasAttributes()) {
				foreach ($node->attributes as $attr) {
					$this->setAttribute($attr->nodeName, $attr->nodeValue);
				}
			}
			if ($node->hasChildNodes()) {
				foreach ($node->childNodes as $childNode) {
					$child = new Node();
					$child->convertDOMNode($childNode);
					$this->addChild($child);
				}
			}
		} else {
			$this->addChild($element->nodeValue);
		}
	}

	/**
	 *
	 * Checks wether this element is ancestor of $element
	 * @param Node $element
	 */
	public function isAncestor($element) {
		$ancestor = false;
		foreach ($this->children as $child) {
			if ($element===$child) {
				$ancestor = true;
				break;
			} elseif ($child->isAncestor($element)) {
				$ancestor = true;
			}
		}
		return $ancestor;
	}

	/**
	 * Can Either be a string or an object of the HtmlElement class,
	 * as one child, an array of children, several children separated by commas
	 * or several arrays of children separated by commas. If the noEndTag is true
	 * children can still be added.
	 */
	public function addChild($arg = "", $index = -1) {
		if ($index < 0 || $index >= count($this->children)-1) {
			if (is_array($arg)) {
				foreach ($arg as $a) {
					$this -> addChild($a);
				}
			} else {
				if ($arg instanceof Node) {
					if ($arg -> parentNode) {
						$arg -> parentNode -> removeChild($arg);
					}
					$arg -> parentNode = $this;
					return $this -> children[] = $arg;
				} elseif (is_string((string) $arg)) {
					$textNode = new TextNode($arg);
					$textNode -> parentNode = $this;
					return $this -> children[] = $textNode;
				}
			}
		} elseif (is_int($index)) {
			for ($i = count($this->children); $i >= $index; $i--) {
				$child = $this->children[$i];
				$this->children[$i+1] = $child;
			}
			if ($arg instanceof Node) {
				if ($arg -> parentNode) {
					$arg -> parentNode -> removeChild($arg);
				}
				$arg -> parentNode = $this;
				return $this -> children[$index] = $arg;
			} elseif (is_string((string) $arg)) {
				$textNode = new TextNode($arg);
				$textNode -> parentNode = $this;
				return $this -> children[$index] = $textNode;
			}
		}
	}

	public function removeChild(Node $node) {
		foreach ($this->children as $key => $child) {
			if ($child===$node) {
				unset($this->children[$key]);
				break;
			}
		}
		$this->children = array_merge($this->children);
	}

	public function getElementsByTagName($name) {
		$array = array();
		foreach ($this->children as $child) {
			if ($child->type === $name) {
				$array[] = $child;
			}
		}
		return $array;
	}

	public function emptyChildList() {
		foreach ($this->children as $i => $child) {
			$this->removeChild($child);
		}
	}

	public function getChildAt($at) {
		return $this->children[$at];
	}

	public function getChildList() {
		return array_merge($this->children);
	}

	public function cloneNode() {
		$node = new Node($this->type, array_merge($this->attributes, array("_noendtag" => $this->noEndTag)), $this->children);
		return $node;
	}

	public function search($name, $value = "") {
		if ($value) {
			foreach ($this->children as $child) {
				if ($child->attributes[$name] === $value) {
					$array[] = $child;
				}
				$child->search($name, $value, $array);
			}
		} else {
			foreach ($this->children as $child) {
				if ($child->type === $name) {
					$array[] = $child;
				}
				$child->search($name, $value, $array);
			}
		}
		return $array;
	}

	protected function printAttributes() {
		$string = "";
		foreach ($this->attributes as $att) {
			$string .= " " . $att['name'] . "=\"" . $att['value'] . "\"";
		}
		return $string;
	}

	protected function printStartTag() {
		$string = "<" . $this -> type;
		$string .= $this -> printAttributes();
		$string .= $this->noEndTag ? "/" : "";
		$string .= ">";
		return $string;
	}

	protected function printChildren() {
		$string = Node::$newLine && $this->hasChildren() && !($this->children[0] instanceof TextNode) ? "\n" : "";
		$tab = $this -> getTab();
		foreach ($this->children as $child) {
			if ($child instanceof TextNode) {
				$string .= $child;
			} else {
				$string .= Node::$newLine ? $tab : "";
				$string .= $child;
				$string .= Node::$newLine ? "\n" : "";
			}
		}
		return $string;
	}

	protected function getTab($l = false) {
		$tab = "";
		if ($l) {
			$p = $this->parentNode;
		} else {
			$p = $this;
		}
		while ($p = $p->parentNode) {
			$tab .= Node::$indent;
		}
		return $tab;
	}

	protected function printEndTag() {
		$string = "";
		if (Node::$newLine && $this -> hasChildren() && !($this->children[0] instanceof TextNode)) {
			$string .= $this->getTab(true);
		}
		$string .= "</".$this->type.">";
		return $string;
	}

	public function __toString() {
		$string = $this -> printStartTag();
		if (!$this -> noEndTag) {
			$string .= $this -> printChildren();
			$string .= $this -> printEndTag();
		}
		return $string;
	}

	public function __get($name) {
		foreach ($this->attributes as $att) {
			if ($att['name'] === $name) {
				return $att['value'];
			}
		}
		return null;
	}

	public function __set($name, $value) {
		$this -> setAttribute($name, $value);
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return $this->children[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		$this->position++;
	}

	public function valid() {
		return isset($this->children[$this->position]);
	}

}
