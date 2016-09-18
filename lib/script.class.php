<?php class script extends HtmlElement {
	function __construct($src = "", $content = "") {
		parent::__construct("script");
		$this->setAttribute("type", "text/javascript");
		if ($src) {
			$this->setAttribute("src", $src);
		}
		$this -> addChild($content);
	}
	
	public function addChild($child) {
		$javascript = new JavaScript($child);
		parent::addChild($javascript);
	}
}