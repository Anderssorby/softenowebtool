<?php 
class Head extends HtmlElement {
	public function __construct() {
		parent::__construct("head");
	}
	public function linkStyleSheet($src) {
		$link = new HtmlElement("link", true);
		$link -> setAttribute("rel", "stylesheet");
		$link -> setAttribute("type", "text/css");
		$link -> setAttribute("media", "screen");
		$link -> setAttribute("href", $src);
		return $this -> addChild($link);
	}
	
	public function setTitle($title) {
		foreach ($this->getElementsByTagName("title") as $el) {
			$this->removeChild($el);
		}
		$ti = new HtmlElement("title");
		$ti -> addChild($title);
		$this -> addChild($ti);
	}
	
	public function addScript($script) {
		if ($script instanceof script)
			$this->addChild($script);
		else if (is_string($script)) {
			$this->addChild(new script($script));
		}
	}
	
	public function setIcon($src) {
		$meta = new HtmlElement("meta", array("itemprop" => "image",
		"content" => $src, "_endtag" => true));
		$this->addChild($meta);
		$meta = new HtmlElement("link", array("rel" => "shortcut icon",
		"href" => $src, "_endtag" => true));
		$this->addChild($meta);
	}
	
	public function addDocumentReady($value) {
		$this->addChild($script = new script());
		$script->addChild(new JavaScript("call", "$(document)", "ready", $widget = new JavaScript("function", "")));
		$widget->addChild($value);
		return $widget;	
	}
}