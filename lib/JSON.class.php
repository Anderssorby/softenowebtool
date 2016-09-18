<?php
class JSON extends JavaScript {
	protected $data = array();

	function __construct($name = "") {
		parent::__construct("JSON", $name);
	}

	function add($name, $value) {
		$len = count($this->data);
		$this->data[$len]['name'] = $name;
		if (is_array($value)) {
			$json = new JSON();
			foreach ($value as $key => $v) {
				$json->add($key, $v);
			}
			$this->data[$len]['value'] = $json;
		} else {
			$this->data[$len]['value'] = $value;
		}
	}

	public function isEmpty() {
		return count($this->data)<=0?true:false;
	}

	function __toString() {
		$string = $this->name ? "var ".$this->name." = " : "";
		$string = "{";
		for ($i = 0, $len = count($this->data); $i < $len; $i++) {
			if ($i !== 0) {
				$string.=",";
			}
			$v = $this->data[$i];
			$string .= '"'.($v['name']).'":';
			if ($v['value'] instanceof JavaScript) {
				$string .= $v['value'];
			} elseif (is_null($v['value'])) {
				$string .= '""';
			} else {
				//$string .= '"';
				$string .= json_encode(utf8_encode($v['value']));
				//$string .= '"';
			}
		}
		$string .= "}";
		return $string;
	}
}