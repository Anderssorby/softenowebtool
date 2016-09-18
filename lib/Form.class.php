<?php
/** * * */
class Form extends HtmlElement {
	protected $iframe;
	protected $script;

	public function __construct($action, $ajax = false) {
		parent::__construct("form");
		$this->setAttribute("action", $action);
		$this->method = "post";
		$this->enctype = "multipart/form-data";
		if ($ajax) {
			$this->iframe = new HtmlElement("iframe");
			$this->iframe->style = "display: none; border: 0;";
			$this->iframe->name = "upload" . HtmlElement::$instance;
			$this->iframe->id = "upload" . HtmlElement::$instance;
			$this->target = "upload" . HtmlElement::$instance;
			$this->addChild($this->iframe);
			$this->script = new script();
		}
	}

	public function setOnSubmit(JavaScript $js) {
		$gbi = new JavaScript("getbyid", "", $this -> id);
		$this -> script -> addChild($gbi);
		$gbi -> setAssignment("set", "onSubmit", $js);
	}

	public function addInput($label, Input $input) {
		$lab = new HtmlElement("label");
		$lab -> style = "display: block;";
		$lab -> addChild($label);
		$lab -> addChild($input);
		$this -> addChild($lab);
	}
}