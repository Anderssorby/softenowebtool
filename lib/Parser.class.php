<?php 
class Parser extends Tokenaizer {
	protected $reserved;
	protected $tokenList;
	protected $pattern;
	protected $patternKey;
	protected $step = 0;
	public function __construct($string, $separators, $special = array(), $reserved = array()) {
		parent::__construct($string, $separators, $special);
		$this->reserved = $reserved;
		$this->tokenList = array();
	}
	
	public function nextStep() {
		$token = $this->nextToken();
		$this->tokenList[$this->step] = $token;
		$this->pattern[] = $token;
		if ($this->isReserved($token) && !$this->patternKey) {
			$this->patternKey = $token;
		}
		$this->step++;
		return $token;
	}
	
	public function getParsePattern($l = 0) {
		return $this->pattern[$l];
	}
	
	public function matchPattern($pattern = array()) {
		;
	}
	
	public function resetPattern() {
		$this->pattern = array();
		$this->patternKey = "";
	}
	
	public function previousToken($l = 1) {
		return $this->tokenList[$this->step-$l];
	}
	
	protected function isReserved($token) {
		foreach ($this->reserved as $re) {
			if ($re === $token) {
				return true;
			}
		}
		return false;
	}
}