<?php
/**
 *
 */
class TextNode extends Node {
	protected $text;
	protected $html = false;
	public function __construct($value = "", $html = false) {
		$this->html = $html;
		$this->text = $value;
	}

	public function addText($text) {
		$this->text .= $text;
	}

	public function __toString() {
		if ($this->html) {
			return "".$this->text;
		} else {
			return htmlentities("".$this->text);
		}
	}

	public function equals($text) {
		return $this->text == $text;
	}

	public function union(TextNode $node) {
		if ($node->parent) {
			$node->parent->removeChild($node);
		}
		$this->text .= $node->text;
		return $this;
	}
}
