<?php

/**
*
*/

class Developer implements Action, ModuleApp {

	protected $key;

	public function __construct($key) {
		$this->key = $key;
	}

	

	public function performAction($command, $args) {

			switch ($this->key) {
				case "devcommit":

				break;
				case "devrev-remove":

				break;
				case "devscandir":
					$dir = $args["d"];
					
					break;

		}

	}

								

	public function createInstance($args) {
		$content = $args["_element"];
		$file = $args["f"];
		$pth = $args["pth"];
		switch ($this->key) {
			case "developer":
				$content->addChild($h1 = new HtmlElement("h1"));
				$h1->addChild("Developer's cradle");
				$content->addChild($table = table("sow-dev-editor-table"));
				$table->addChild($caption = new HtmlElement("caption"));
				$table->addRow(new Link("javascript: $('#fileview').hide();"),
				$toolbar = new Div("sow-dev-editor-toolbar"));
				$tr = $table->addRow($files = new table(),
				$text = new HtmlElement("textarea"));
				$td = $tr->search("td");
				$td[0]->id = "fileview";
				$f = $this->resolvePath($pth, $file);
				if ($pth) {
					$scan = scandir($pth);
					foreach ($scan as $s) {
						if ($p = $this->resolvePath($pth, $s)) {
							if (!is_string($p)) {
								$files->addRow(new Link("?d=".$pth, $s));
							} else {
								$files->addRow(new Link("?d=".$pth."&f=".$p, $s));
							}
						}
					}
				}
				if ($f) {
					$caption->addChild($f);
					if ($file) {
						
					}
				}
				
				break;
		}	
	}
	
	public function resolvePath(&$path, &$file) {
		if (is_dir($path)) {
			if (is_file($path.DIRECTORY_SEPARATOR.$file)) {
				return $path.DIRECTORY_SEPARATOR.$file;
			} else {
				if (is_dir($path.DIRECTORY_SEPARATOR.$file)) {
					$path = $path.DIRECTORY_SEPARATOR.$file;
					return true;
				} else {
					$path = false;
				}
				$file = false;
				return false;
			}
		} else {
			if (is_file($file)) {
				$path = false;
				return $file;
			} else {
				if (is_dir($file)) {
					$path = $file;
					$file = false;
					return true;
				} else {
					$path = false;
				}
				$file = false;
				return false;
			}
		}
	}
}