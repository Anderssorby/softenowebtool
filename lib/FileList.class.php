<?php
/**
 *
 */
class FileList extends Div {
	public function __construct() {
		parent::__construct();
		$this->makeFileList();
	}

	public function makeContent() {
		$this->makeFileList();
		return $this;
	}

	protected function makeFileList() {
		$direct = "arkiv";
		$dirs = array();
		$ul = new HtmlList("file-three-list");
		$dir = scandir($direct);
		for ($i = 2; $d = $dir[$i]; $i++) {
			if (is_dir("$direct/$d")) {
				$dirs[] = $d;
			}
		}
		for ($i = 2; $d = $dir[$i]; $i++) {
			if (is_dir("$direct/$d")) {
				$li = $ul -> add(new HtmlElement("h3", TRUE, $d));
				$li -> addChild(new InteractElement("?site=filer&action=file-deldir&dir=" . urlencode($d), "Slett", true));
				$ul2 = new HtmlList("file-three-list");
				$ul -> addChild($ul2);
				$dir2 = scandir("$direct/$d");
				for ($j = 2; $d2 = $dir2[$j]; $j++) {
					$li2 = $ul2 -> add(new Link("$direct/$d/" . rawurlencode($d2), $d2));
					$flytt = new InteractElement("?site=filer&action=file-move&file=" . urlencode("$d/$d2"), "Flytt");
					$li2 -> addChild($flytt);
					$sel = new chooser("", "dir", "", TRUE);
					foreach ($dirs as $di) {
						$sel -> addOption($di, $di);
					}
					$flytt -> addChild($sel);
					$li2 -> addChild(new InteractElement("?site=filer&action=file-del&file=" . urlencode("$d/$d2"), "Slett", true));
					$path = "http://".$_SERVER['SERVER_NAME'].reset(explode("/index.php", $_SERVER['PHP_SELF']))."/$direct/$d/" . rawurlencode($d2);
					$li2 -> addChild(new Input("text", "", $path));
				}
			} else {
				$li = $ul -> add(new Link("$direct/" . rawurlencode($d), $d));
				$flytt = new InteractElement("?site=filer&action=file-move&file=" . urlencode($d), "Flytt");
				$li -> addChild($flytt);
				$sel = new chooser("", "dir", "", TRUE);
				foreach ($dirs as $di)
				$sel -> addOption($di, $di);
				$flytt -> addChild($sel);
				$li -> addChild(new InteractElement("?site=filer&action=file-del&file=" . urlencode($d), "Slett", true));
				$path = "http://".$_SERVER['SERVER_NAME'].reset(explode("/index.php", $_SERVER['PHP_SELF']))."/$direct/" . rawurlencode($d);
				$li -> addChild(new Input("text", "", $path));
			}
		}

		$this->addChild($ul);
		$ndir = new InteractElement("?action=file-newdir&site=filer", "Ny mappe");
		$navn = new Input("text", "dir");
		$ndir -> addInput("navn: ", $navn);
		$this -> addChild($ndir);
		$form = new Form("?action=lastfil&site=filer", FALSE);
		$filinput = new Input("file", "fil");
		$filinput -> multiple = "true";
		$form -> addInput("Filer :", $filinput);
		$submit = new Button("Last opp", true);
		$form -> addChild($submit);
		$this -> addChild($form);
	}
}