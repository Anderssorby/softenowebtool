<?php
// 
//  XMLDoc.php
//  softenowebtool
//  
//  Created by anders on 2011-09-03.
//  Copyright 2011 anders. All rights reserved.
// 

/**
 * 
 */
class XMLDoc extends Node {
	
	protected $version;
	
	protected $encoding;
	
	function __construct($file) {
		parent::__construct("root");
		$document = new DOMDocument();
		$document->load($file);
		$this->convertDOMNode($document->documentElement);
		$this->version = '1.0';
		$this->encoding = 'ISO-8859-1';
	}
	
	public function __toString() {
		$string = '<?xml version="'.$this->version.'" encoding="'.$this->encoding.'"?>';
		$string .= $this->printStartTag();
		$string .= $this->printChildren();
		$string .= $this->printEndTag();
		return $string;
	}
	
	public static function parseXML($path) {
		if (!is_link($path))
			return false;
		
		$size = filesize($path);
		$handle = fopen($path, "rb");
		$data = fread($handle, $size);
		fclose($handle);
		
		return DOMDocument::loadXML($data);
	}
	
	public function save($file) {
		$size = filesize($path);
		$handle = fopen($path, "rb");
		$bytes = fwrite($handle, $this, $size);
		fclose($handle);
		return $bytes;
	}
}
