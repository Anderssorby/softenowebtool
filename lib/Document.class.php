<?php
class Document extends Node {
	public $head;
	public $body;
	public $documentType;
	protected $applications;
	public function __construct() {
		parent::__construct("html");
		$this->documentType = new DocumentType();
		$this -> addChild($this -> head = new Head());
		$this -> addChild($this -> body = new Body());
	}

	public function setDocumentType(DocumentType $type) {
		$this->documentType = $type;
	}

	public function getApplication($name) {
		$app = $this->applications[$name];
		return isset($app) ? $app : false;
	}

	public function setModule($name, $module) {
		if ($this->isAncestor($module)) {
			$this->applications[$name] = $module;
		}
	}

	public function getModules() {
		return $this->applications;
	}

	public function __toString() {
		$string = $this->documentType;
		$string .= $this -> printStartTag();
		$string .= $this -> printChildren();
		$string .= $this -> printEndTag();
		return $string;
	}
	/**
	 * fetches a document structure defined in a xml file
	 *  @param unknown_type $file	 */
	public function loadStructure($file) {
		$doc = new DOMDocument();
		$doc->load($file);
		$node = $doc->documentElement;
		foreach ($node->childNodes as $child) {
			$this->body->addChild($this->registerApplications($child));;
		}
	}
	/**
	 * Registers the ElementsOfIntrets as defined in the xml document
	 * @param Node $element	
	 */
	protected function registerApplications(DOMNode $element, Node $parent = null) {
		if ($element->nodeType == XML_ELEMENT_NODE) {
			$node = new Node();
			$node->type = $element->nodeName;
			if ($element->hasAttributes()) {
				foreach ($element->attributes as $attr) {
					$node->setAttribute($attr->nodeName, $attr->nodeValue);
				}
			}			if ($element->hasChildNodes()) {
				foreach ($element->childNodes as $childNode) {
					$child = $this->registerApplications($childNode, $node);
					$node->addChild($child);
				}
			}			return $node;
		} else if ($element->nodeType == XML_COMMENT_NODE) {
			$split = explode(" ", $element->data);
			$navn = strtolower($split[0]);
			$this->applications[$navn] = $parent;
			return null;
		} else {
			return null;
		}
	}
	/**
	 *
	 */
	public static function loadDOM($string) {
		$parseList = array();
		$pattern = '/(<[^>]*>)/i';
		$split = preg_split($pattern, $string);
		print_r($match);
		for ($i = 1; $s = $split[$i]; $i++) {
			$nd = '/([^<>\s])*/i';
			preg_match($nd, $s, $inn);
			$tagname = $inn[1];
			for ($j = 2; $a = $inn[$j]; $j++) {
				$th = '/([[:alpha:]]+)="([[:alnum:]]*)"/';
				preg_match($th, $a, $sa);
				$attributes[$j - 1]['name'] = $sa[0];
				$attributes[$j - 1]['value'] = $sa[1];
			}			$parseList[] = $inn;
		}		for ($i = 0; $p = $parseList[$i]; $i++) {
			$hasEndTag = $s[2] ? TRUE : FALSE;
			$node = new Node($tagname, $hasEndTag);
			$pchilds = $children ? Node::loadDOM($children) : array();
			foreach ($pchilds as $c) {
				$node -> addChild($c);
			}
		}
		return $nodeList;
	}
}