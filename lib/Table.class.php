<?php
/** * Reperesents a HTML table element */
class table extends HtmlElement {
	function __construct($class = "", $id = "") {
		parent::__construct("table");
		$this->setAttribute("class", $class);
		$this->setAttribute("id", $id);
	}

	/**
	 * Builds a table from array keys and the array values. convinient for displaing database tables
	 *
	 * @param associative_array $assoc	 */
	public function fromAssoc($assoc) {
		$keys = array_keys($assoc[0]);
		$this->addHead($keys);
		foreach ($assoc as $a) {
			$this->addRow($a);
		}
	}

	function addRow() {
		$num = func_num_args();
		$row = new HtmlElement("tr");
		if ($num == 1) {
			$arg = func_get_arg(0);
			if (is_array($arg)) {
				foreach ($arg as $a) {
					$cell = new HtmlElement("td");
					$cell -> addChild($a);
					$row -> addChild($cell);
				}
			} else {
				$cell = new HtmlElement("td");
				$cell -> addChild($arg);
				$row -> addChild($cell);
			}
		} else {
			$args = func_get_args();
			foreach ($args as $arg) {
				if (is_array($arg)) {
					foreach ($arg as $a) {
						$cell = new HtmlElement("td");
						$cell -> addChild($a);
						$row -> addChild($cell);
					}
				} else {
					$cell = new HtmlElement("td");
					$cell -> addChild($arg);
					$row -> addChild($cell);
				}
			}
		}
		return $this -> addChild($row);
	}

	function addHead() {
		$num = func_num_args();
		$row = new HtmlElement("tr");
		if ($num == 1) {
			$arg = func_get_arg(0);
			if (is_array($arg)) {
				foreach ($arg as $a) {
					$cell = new HtmlElement("th");
					$cell -> addChild($a);
					$row -> addChild($cell);
				}
			} else {
				$cell = new HtmlElement("th");
				$cell -> addChild($arg);
				$row -> addChild($cell);
			}
		} else {
			$args = func_get_args();
			foreach ($args as $arg) {
				if (is_array($arg)) {
					foreach ($arg as $a) {
						$cell = new HtmlElement("th");
						$cell -> addChild($a);
						$row -> addChild($cell);
					}
				} else {
					$cell = new HtmlElement("th");
					$cell -> addChild($arg);
					$row -> addChild($cell);
				}
			}
		}
		return $this -> addChild($row);
	}
}