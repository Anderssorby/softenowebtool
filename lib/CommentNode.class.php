<?php

class CommentNode extends Node {
	protected $contents;

	public function __construct($contents = "") {
		parent::__construct();
		$this->contents = $contents;
	}

	public function addContents($contents) {
		$this->contents .= $contents;
	}

	public function __toString() {
		$string = "<!--";
		$string .= $this->contents;
		$string .= "-->";
		return $string;
	}

}