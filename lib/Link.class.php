<?php
class Link extends HtmlElement {

	public function __construct($link = "", $text = "", $confirm = false, $title = "") {
		parent::__construct("a", is_array($confirm)?$confirm:false);
		if ($link) $this->setAttribute("href", $link);
		if ($text) $this->addChild($text);
		if ($title) $this->title = $title;
		if ($confirm&&!is_array($confirm)) $this->onclick = "return confirm('Er du sikker på at du vil utføre denne handlingen?');";
	}
}