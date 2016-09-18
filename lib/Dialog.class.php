<?php
/**
*
*/
class Dialog extends Div {
	protected $content;
	function __construct($titel, $id = "") {
		parent::__construct("Hover ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable ui-resizable", $id?$id:"dialog" . HtmlElement::$instance);
		$titlebar = new Div("ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix");
		$titlebar -> addChild($span = new HtmlElement("span", false, $h3 = new HtmlElement("h1", false, $titel)));
		$span -> class = "ui-dialog-title";
		$titlebar -> addChild($close = new Link("#", $span2 = new HtmlElement("span", false, "lukk")));
		$close -> generateId();
		$close -> class = "ui-dialog-titlebar-close ui-corner-all";
		$this -> addChild($titlebar);
		$this -> content = new Div();
		$this -> addChild($this->content);
		global $document;
		$ready = $document -> head -> addDocumentReady("$('#$this->id').draggable();");
		$ready -> addChild("$('#$close->id').click(function() { close('$this->id');});");
	}

	public function addContent() {
		$this -> content -> addChild(func_get_args());
	}
}