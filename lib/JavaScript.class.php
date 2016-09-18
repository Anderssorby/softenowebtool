<?php
/**
 *
 */
class JavaScript extends TextNode {
	protected $children = array();
	protected $arguments = array();
	protected $template;
	protected $name;
	protected $asiType;
	protected $asiValue;
	protected $set;
	
	public function __construct($template, $name = "") {
		if (false) {
			$args = func_get_args();
			for ($i = 2, $len = count($args); $i < $len; $i++) {
				$this->arguments[] = $args[$i];
			}
			$this->template = $template;
			$this->name = $name;
		} else {
			$this->text = $template;
		}
	}

	public function addChild($child) {
		$this->children[] = $child;
	}

	public function addArgument($arg) {
		$this->arguments[] = $arg;
	}
	
	public function setAssignment($type, $name = "", $value = null) {
		$this->asiType = $type;
		$this->asiName = $name;
		if (3 < func_num_args() && $type === "call") {
			$args = func_get_args();
			for ($i = 2, $arg; $arg = $args[$i]; $i++) {
				$this->asiValue[] = $arg;
			}
		} else {
			$this->asiValue = $value;
		}

	}
	
	public function getName() {
		return $this->name;
	}
	
	public function doAssignment() {
		$num = func_num_args();
		$string = "";
		if ($num === 0) {
			switch ($this->asiType) {
				case 'set':
					$string .= ".".$this->asiName." = ".$this->asiValue;
					$string .= ";";
					break;
				case 'get':
					$string .= ".".$this->asiName;
					break;
				case 'call':
					$string .= ".".$this->asiName."(";
					for ($i = 0, $value; $value = $this->asiValue[$i]; $i++) {
						$string .= $i == 0 ? "" : ", ";
						$string .= $value;
					}
					$string .= ");";
					break;
			}
		} else {
			$args = func_get_args();
			list($type, $name, $value) = $args;
			$string .= $this->name;
			switch ($type) {
				case 'set':
					$string .= ".".$name." = ".$value;
					$string .= ";";
					break;
				case 'get':
					$string .= ".".$name;
					break;
				case 'call':
					$string .= ".".$name;
					$string .= "(";
					for ($i = 2, $arg; $arg = $args[$i]; $i++) {
						$string .= $i==2?"":", ";
						$string .= $arg;
					}
					$string .= ");";
					break;
			}
		}
		return $string;
	}

	public function __toString() {
		$string = "";
		switch ($this->template) {
			case 'function':
				$string .= "function ".$this->name;
				$string .= "(";
				for ($i = 0, $len = count($this->arguments); $i < $len; $i++) {
					$arg = $this->arguments[$i];
					$string .= $i==0?"":", ";
					$string .= $arg;
					$i++;
				}
				$string .= ") {";
				for ($i = 0, $len = count($this->children); $i < $len; $i++) {
					$child = $this->children[$i];
					$string .= $child;
				}
				$string .= "}";
				break;
			case 'exfunction':
				$string .= "(function ()";
				$string .= " {";
				for ($i = 0, $len = count($this->children); $i < $len; $i++) {
					$child = $this->children[$i];
					$string .= $child;
				}
				$string .= "})();";
				break;			case 'getbyid':
					$string .= $this->name ? "var ".$this->name." = " : "";
					$string .= "document.getElementById('".$this->arguments[0]."')";
					$string .= $this->doAssignment();
					break;
				case 'newobj':
					$string .= $this->name ? "var ".$this->name." = " : "";
					$string .= "new ".$this->arguments[0];
					$string .= "(";
					for ($i = 1, $len = count($this->arguments); $i < $len; $i++) {
						$arg = $this->arguments[$i];
						$string .= $i==1?"":", ";
						$string .= $arg;
					}
					$string .= ")";
					$string .= $this->set ? ".".$this->set : "";
					$string .= ";";
					break;
				case 'call':
					$string .= $this->name ? $this->name."." : "";
					$string .= $this->arguments[0];
					$string .= "(";
					for ($i = 1, $len = count($this->arguments); $i < $len; $i++) {
							
						$arg = $this->arguments[$i];
						$string .= $i==1?"":", ";
						$string .= $arg;
					}				$string .= ")";
					$string .= $this->set ? ".".$this->set : "";
					$string .= ";";
					break;
				case 'array':
					$string .= $this->name ? "var ".$this->name." = " : "";
					$string .= "[";
					for ($i = 0, $arg; $arg = $this->arguments[0][$i]; $i++) {
						$string .= $i==0?"":", ";
						$string .= $arg;
					}				$string .= "]";
					$string .= $this->name ? ";" : "";
					break;
				default:
					$string .= $this->text;
		}
		return $string;
	}

	public static function prase($document) {
		$separators = array(" ", ";", "(", ")", "[", "]", "{", "}",
		"=", "?", ":", "/", "*", "+", "-","<", ">", "%", "'",	
		"\"", "!", ".", ",", "&", "|", "\n");	
		$special = array(";", "(", ")", "[", "]", "{", "}",
		"=", "?", ":", "/", "*", "+", "-", "<", ">", "%", "'",	
		"\"", "!", ".", ",", "&", "&&", "|", "||", "//", "/*",	
		"*/", "+=", "*=", "/=", "-=", "<=", ">=", "++", "--");
		$reserved = array("function", "this", "for", "in", "var",
		"new", "while", "true", "false", "if", "else", "null", "return",
		"try", "catch", "instanceof", "switch", "case", "default", "break",	
		"do");		
		$parser = new Parser($document, $separators, $special, $reserved);
		$expect = "reserved";
		while ($parsed = $parser->nextStep()) {
			switch ($parser->getParsedPattern(0)) {
				case "":
					break;
			}
		}
	}
}