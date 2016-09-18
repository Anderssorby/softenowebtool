<?php
/**
 *
 */
class CSS {
	protected $type;
	protected $name;
	protected $body;
	protected $parent;
	protected $spacing = true;

	public function __construct($nodeKey = "doc", $body = array(), $name = "") {
		$this->type = $nodeKey;
		switch ($nodeKey) {
			case "doc":
				$this->name = $name;
				$this->body = array();
				break;
			case "selector":
				$this->body = $body;
				break;
			case "rule":
				$this->name = $name instanceof CSS && $name->type == "selector" ? $name : new CSS("selector");
				$this->body = $body;
				break;
			case "statement":
				$this->name = $name;
				$this->body = $body;
				break;
		}
	}

	public function append($node) {
		$node->parent = $this;
		return $this->body[] = $node;
	}

	public function applyStyle($selector, $name, $value) {
		if ($this->type === "doc") {
			$don = false;
			foreach ($this->body as $rule) {
				$rn = "".$rule->name;
				if ($rn==$selector) {
					$rule->append(new CSS("statement", $value, $name));
					$don = true;
					break;
				}
			}
			if (!$don) {
				$this->append(new CSS("rule", array(new CSS("statement", $value, $name)), new CSS("selector", $selector)));
			}
		}
	}

	public static function parse($string, $type = "doc") {
		$separators = array(" ", ";", "(", ")", "[", "]", "{", "}",
		"=", "?", ":", "/", "*", "+", "<", ">", "%", "'",
		"\"", "!", ".", ",", "&", "|", "\n", "@");
		$doc = false;
		switch ($type) {
			case "doc":
				$doc = new CSS("doc");
				$len = strlen($string);
				$expect = "selector";
				$body = "";
				$name = "";
				$rule = $doc->append(new CSS("rule"));
				$isRight = false;
				$comment = "none";
				for ($i = 0; $i < $len; $i++) {
					$s = substr($string, $i, 1);
					foreach ($separators as $sep) {
						if ($s == $sep) {
							$j = $i+1;
							$token = "";
							while ($j < $len) {
								$t = substr($string, $j, 1);
								$is = false;
								foreach ($separators as $sep) {
									if ($t == $sep) {
										$is = true;
										if ($t == "@") $expect = "special";
										break;
									}
								}
								if ($comment != "none" && ($t == "/" || $t == "*")) {
									if ($comment == "block" && $t == "*") $comment = "notconfend";
									else if ($comment == "notconfend" && $t == "/") $comment = "none";
									else if ($t == "*") $comment = "block";
								} else if ($t == "/") {
									$comment = "notconf";
								} else if ($is) {
									$i = $j-1;
									if ($comment == "none")
									switch ($expect) {
										case "selector":
											switch ($s) {
												case "{":
													$rule = $rule ? $rule : $doc->append(new CSS("rule"));
													$rule->name = new CSS("selector", $body);
													$expect = "statement";
													$body = "";
													break;
												default:
													$body .= $s != "\n" ? $s : "";
												$body .= $token;
												break;
											}
											break;
										case "statement":
											switch ($s) {
												case "}":
													$expect = "selector";
													$rule = false;
													$body = "";
													$name = "";
													$isRight = false;
													break;
												case ";":
													$rule->append(new CSS("statement", $body, $name));
													$expect = "statement";
													$body = "";
													$name = "";
													$isRight = false;
													break;
												case ":":
													$isRight = true;
													$name .= $token;
													break;
												default:
													if ($isRight) {
													$body .= $s != "\n" ? $s : "";
													$body .= $token;
												} else {
													$body .= $s != "\n" ? $s : "";
													$name .= $token;
												}
												break;
											}
											break;
										case "special":
											switch ($s) {
												case "@":
													$name .= $s.$token;
													break;
												case ";":
													$doc->append(new CSS("statement", $body, $name));
													$expect = "selector";
													$body = "";
													$name = "";
													$isRight = false;
													break;
												case ":":
													$isRight = true;
													$body .= $token;
													break;
												default :
													if ($isRight) {
													$body .= $s != "\n" ? $s : "";
													$body .= $token;
												}
												break;
											}
											break;
									}
									break;
								} else {
									$token .= $t;
								}
								$j++;
							}
							break;
						}
					}
				}
				break;
		}
		return $doc;
	}

	public function __toString() {
		$string = "";
		switch ($this->type) {
			case "doc":
				if (is_array($this->body)) {
					foreach ($this->body as $rule) {
						$string .= $rule;
					}
				}
				break;
			case "selector":
				if (is_array($this->body)) {
					foreach ($this->body as $sel) {
						$string .= $sel;
					}
				} else {
					$string .= $this->body;
				}
				break;
			case "statement":
				$string .= $this->name.": ";
				$string .= $this->body.";";
				$string .= $this->spacing ? "\n" : "";
				break;
			case "rule":
				$string .= $this->name;
				$string .= "{";
				$string .= $this->spacing ? "\n" : "";
				foreach ($this->body as $state) {
					$string .= $this->spacing ? "   " : "";
					$string .= $state;
				}
				$string .= "}";
				$string .= $this->spacing ? "\n" : "";
				break;
		}
		return $string;
	}
}
