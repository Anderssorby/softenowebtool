<?php
/** * */
class Editor extends Div {
	protected $width;
	protected $height;
	protected $rich;
	protected $name;
	public $editid;
	protected $dial;
	function __construct($name = "", $old = true, $width = 700, $height = 600) {
		parent::__construct("", "");
		$this -> width = $width;
		$this -> height = $height;
		$this -> name = $name;
		$this -> makeEdit();
	}

	public function makeEdit() {
		$this->editid = $editid = "editor" . HtmlElement::$instance;
		$this -> addChild('Innhold:');
		$this -> rich = new HtmlElement("textarea");
		$this -> rich -> id = $editid;
		$this -> rich -> name = $this -> name;
		$this -> addChild($this -> rich);
		$this -> addChild(new script("", "$('#$editid').richtextedit();"));
	}
	
	function addContent() {
		$this -> rich -> addChild(func_get_args());
	}
}