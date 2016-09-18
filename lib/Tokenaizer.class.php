<?php
class Tokenaizer {
	protected $string;
	protected $separators;
	protected $special;
	protected $position;
	protected $lenght;

	public function __construct($string, $separators, $special = array()) {
		$this->string = $string;
		$this->separators = $separators;
		$this->special = $special;
		$this->position = 0;
		$this->lenght = strlen($string);
	}

	public function nextToken() {
		$token = "";
		for ($i = $this->position; $i < $this->lenght; $i++) {
			$s = substr($this->string, $i, 1);
			if ($this->isSeparator($s)) {
				$special = $this->isSpecial($s);
				if ($this->isSpecial($token.$s)) {
					$token .= $s;
				} else if ($special) {
					$token .= $s;
				} else if ($token && $this->isSpecial($token)) {
					break;
				}
			} else {
				$token .= $s;
			}
		}
		$this->position = $i;
		return $token;
	}

	protected function isSeparator($s) {
		foreach ($this->separators as $key => $sep) {
			if ($s == $sep) {
				return true;
			}
		}
		return false;
	}
	
	protected function isSpecial($s) {
		foreach ($this->special as $key => $sep) {
			if ($s == $sep) {
				return true;
			}
		}
		return false;
	}
}