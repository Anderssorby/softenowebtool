<?php 
/** 
 * 
 * Enter description here ...
 * @author anders
 ** */
class Album extends Div {
	protected $album;
	protected $img;	protected $num;

	function __construct($album, $num = 0, $exp = true) {
		parent::__construct("album", "album" . HtmlElement::$instance);
		$this -> album = $album;
		$this -> num = $num;
		if ($exp ) {
			$this -> makeExperimentalVersion();
		} else {
			$this -> construct();
		}
	}

	function construct() {
		$num = addslashes($this->num);
		$album = addslashes($this->album);
		$query = "select `id`, `album` from `bilder` where `album` = '$album' limit $num, $num";		$resource = mysql_query($query);		if (!$resource) {			return false;		}		$ass = mysql_fetch_assoc($resource);		$n = $this->num+1<$len ? $this->num+1 : 0;		$f = $this->num-1>=0 ? $this->num-1 : $len-1;		$this -> addChild(new Div("", "", "Viser ".($this->num+1)." av ".$len));		$this -> img = new Img("?site=bilde&id=" . $data[$this->num]);		$this -> img -> style = "max-width: 300px";		$this -> addChild($this -> img);		$neste = new Link("?site=album&album=".$this->album."&num=".$n, "Neste");		$forrige = new Link("?site=album&album=".$this->album."&num=".$f, "Forrige");		$this->addChild($foot = new Div());		$foot -> addChild($forrige);		$foot -> addChild($neste);	}	function makeExperimentalVersion() {		$dataReader = new DataReader("album");
		$head = new Div("album-head");
		$this -> addChild($head);
		$select = new chooser("", "", "", true);
		$data = array();
		$albums = array();
		foreach ($dataReader->assoc as $h) {
			$name = $h['album'];
			$bilde = $h['bilde'];
			if ($name == $this->album) {
				$data[] = $bilde;
			}
			$isin = false;
			foreach ($data as $a) {
				if ($a['album'] == $name) {
					$isin = true;
					$a['images'][] = $bilde;
				}
			}
			if (!$isin) {
				$albums[]['album'] = $name;
			}
		}
		$len = count($data);
		$n = $this->num+1<$len ? $this->num+1 : 0;
		$f = $this->num-1>=0 ? $this->num-1 : $len-1;
		$this -> addChild(new Div("", "", "Viser ".($this->num+1)." av ".$len));
		$this -> img = new Img("?site=bilde&id=" . $data[$this->num]);
		$this -> img -> style = "max-width: 300px";
		$this -> img -> id = "img" . HtmlElement::$instance;
		$this -> addChild($this -> img);
		$neste = new Link("?site=album&album=".$this->album."&num=".$n, "Neste");
		$forrige = new Link("?site=album&album=".$this->album."&num=".$f, "Forrige");
		$this->addChild($foot = new Div());
		$foot -> addChild($forrige);
		$foot -> addChild($neste);
		$script = new script();
		$this -> addChild($script);
	}
}