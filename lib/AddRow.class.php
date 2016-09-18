<?php 
/** 
 * 
 * addrow is a class for inserting new entries through ajax
 */
class AddRow extends Form {
	protected $action;
	protected $button;
	protected $header;
	protected $table;
	protected $head;

	public function __construct($table) {
		parent::__construct("?action=insert");
		$this -> action = $action;
		$this -> table = $table;
		$this -> button = "subm";
		$this -> header = "header";
		$dataReader = new DataReader($table);
		$this -> head = $dataReader -> head;
		$this -> makeHTML();
	}

	function makeHTML() {
		$this -> addAttribute("style", "margin: 5px; padding: 3px; border: solid 1px rgb(20, 20, 220);");
		$nhead = new HtmlElement("h1");
		$nhead -> addChild($this -> table);
		$nhead -> setId($this -> header);
		$this -> addChild($nhead);
		$hid = new Input("hidden", "table");
		$hid -> setValue($this -> table);
		$this -> addChild($hid);
		for ($i = 0; $head = $this -> head[$i]; $i++) {
			$field = $head[0];
			$type = $head[1];
			if ($field != "id") {
				$label = new HtmlElement("label");
				$label -> addChild($field . ": ");
				switch ($type) {
					case 'longblob' :
					case 'mediumblob' :
					case 'tinyblob' :
					case 'blob' :
						$inbr = new Input("file", $field);
						$inbr -> id = $i;
						$label -> addChild($inbr);
						break;
					case 'longtext' :
					case 'mediumtext' :
					case 'tinytext' :
					case 'text' :
						$inbr = new HtmlElement("textarea");
						$inbr -> name = $field;
						$inbr -> id = $i;
						$label -> addChild($inbr);
						break;
					case 'date' :
						$inbr = new Input("date", $field);
						$inbr -> id = $i;
						$label -> addChild($inbr);
						break;
					default :
						$inbr;
						if ($field == "password")
							$inbr = new Input("password", $field);
						else
							$inbr = new Input("text", $field);
						$inbr -> id = $i;
						$label -> addChild($inbr);
						break;
				}
				$this -> addChild($label);
			}
		}
		$inbu = new Input("submit", "", "Send");
		$this -> addChild($inbu);
	}
}