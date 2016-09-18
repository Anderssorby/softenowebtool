<?php
/** * */
class DataReader {
	protected $table = "";
	protected $from;
	protected $size;
	protected $log;
	protected $rowid = array();
	protected $head = array();
	protected $data = array();
	protected $assoc = array();
	protected $actions = array();
	protected $tableId;

	function __construct($table, $from = 0, $size = 30, $auto = true) {
		$this->table = addslashes($table);
		$this->from = $from;
		$this->size = $size;
		$this->log = new Logger();
		if ($this->tableExists($this -> table) && $auto) {
			$this->readHead();
			$this->read();
			$this->readId();
		}
	}

	public static function tableExists($table) {
		$query = "SHOW TABLES LIKE '" . $table . "'";
		$resource = mysql_query($query);
		if (!is_resource($resource)) {
			trigger_error(mysql_error());
		}
		return mysql_result($resource, 0) ? true : false;
	}

	function readHead() {
		$query = "SHOW COLUMNS FROM `" . $this -> table . "`";
		$resource = mysql_query($query);
		if (!is_resource($resource)) {
			trigger_error('kunne ikke gjøre query:' . mysql_error());
		}
		$i = 0;
		while ($row = mysql_fetch_array($resource)) {
			$this -> log -> app($row[0], "readHead", "datareader");
			for ($j = 0, $l = count($row); $j < $l; $j++) {
				$this -> head[$i][$j] = $row[$j];
				$this -> log -> app($row[$j], "readHead", "datareader");
			}
			$i++;
		}
	}

	public function read($where = "", $as = "", $like = "like") {
		$query = 'select * from `' . $this -> table . '`';
		$where = addslashes($where);
		$as = addslashes($as);
		$like = addslashes($like);
		if ($where&&$as) {
			$query .= " where `$where` $like '$as'";
		}
		$query .= ' limit ' . $this -> from . ',' . $this -> size;
		$resource = mysql_query($query);
		if (!is_resource($resource)) {
			trigger_error('kunne ikke gjøre "' . $query . '":' . mysql_error());
		}
		$i = 0;
		while ($row = mysql_fetch_assoc($resource)) {
			$j=0;
			foreach ($row as $key => $r) {
				$this -> data[$i][$j] = $r;
				$this -> assoc[$i][$key] = $r;
				if ($key=="id") {
					$this->rowid[$i] = $r;
				}
				$j++;
			}
			$i++;
		}
	}

	function readId() {
		// 		$query = 'select `id` from `' . $this -> table . '` limit ' . $this -> from . ',' . $this -> size;
		// 		$resource = mysql_query($query);
		// 		if (!is_resource($resource)) {
		// 			trigger_error('kunne ikke gjøre query:' . mysql_error());
		// 		}
		// 		$i = 0;
		// 		while ($row = mysql_fetch_assoc($resource)) {
		// 			$this -> rowid[] = $row['id'];
		// 			$i++;
		// 		}
	}

	function addAction($dis, $act) {
		$i = count($this -> actions);
		$this -> actions[$i]['discription'] = $dis;
		$this -> actions[$i]['action'] = $act;
	}

	function makeAddRow() {
		return new AddRow($this -> table);
	}

	public function makeReloadButton() {
		$button = new Button("Reload");
		$onf = new JavaScript("function", "");
		$res = new JavaScript("function", "");
		$gbi = new JavaScript("getbyid", "", $this -> tableId);
		$gbi -> setAssignment("set", "outerHTML", "this.result");
		$res -> addChild($gbi);
		$req = new JavaScript("newobj", "nreq", "Request", "'?action=readtable&from=0&id=" . $this -> table . "'", $res);
		$onf -> addChild($req);
		$onf -> addChild($req -> doAssignment("call", "makeRequest"));
		$onf -> addChild($req -> doAssignment("call", "go"));
		return $button;
	}

	public function makeNextButton() {
		$button = new Button("Neste");
		$onf = new JavaScript("function", "");
		$res = new JavaScript("function", "");
		$gbi = new JavaScript("getbyid", "", $this -> tableId);
		$gbi -> setAssignment("set", "outerHTML", "this.result");
		$res -> addChild($gbi);
		$req = new JavaScript("newobj", "nreq", "Request", "'?action=readtable&from=30&id=" . $this -> table . "'", $res);
		$onf -> addChild($req);
		$onf -> addChild($req -> doAssignment("call", "makeRequest"));
		$onf -> addChild($req -> doAssignment("call", "go"));
		$button -> setOnclick($onf);
		return $button;
	}

	function makeTable() {
		$table = new table();
		$this -> tableId = $table -> id = "table";
		$head_list = array();
		$col_rules = array();
		foreach ($this->head as $head) {
			$field = $head[0];
			$head_list[count($head_list)] = $field;
			$type = $head[1];
			$this -> log -> app($type);
			switch ($type) {
				case 'longblob' :
				case 'mediumblob' :
				case 'tinyblob' :
				case 'blob' :
					$col_rules[] = 'blob';
					break;
				case 'longtext' :
				case 'mediumtext' :
				case 'tinytext' :
				case 'text' :
					$col_rules[] = 'text';
					break;
				default :
					$col_rules[] = 'default';
				break;
			}
		}
		$table -> addHead($head_list);
		$n = 0;
		foreach ($this->data as $row) {
			$trow = array();
			for ($i = 0, $l = count($row); $i < $l; $i++) {
				$cell = $row[$i];
				switch ($col_rules[$i]) {
					case 'blob' :
						if ($head_list[0] == 'bilde') {
							$img = new HtmlElement("img", true);
							$img->setAttribute("src", "?site=bilde&thumb=true&id=".$this -> rowid[$n]);
							$trow[$i] = $img;
						} else {
							$trow[$i] = "fil";
						}
						break;
					case 'text' :
						$trow[$i] = "...";
						break;
					case 'default' :
					default :
						$trow[$i] = $cell;
						break;
				}
			}
			foreach ($this->actions as $a) {
				global $site;
				$link = new HtmlElement("a");
				$link->setAttribute("href", "?site=".$site."&action=".$a['action']."&id=".$this -> rowid[$n]);
				$link->addChild($a['discription']);
				$trow[] = $link;
			}
			$table -> addRow($trow);
			$n++;
		}
		return $table;
	}

	function __get($name) {
		switch ($name) {
			case 'rowid' :
			case 'id' :
				return $this -> rowid;
			case 'head' :
				return $this -> head;
			case 'data' :
			case 'rows' :
				return $this -> data;
			case 'assoc' :
				return $this -> assoc;
			default :
				return null;
		}
	}
}
