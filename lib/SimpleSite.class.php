<?php
class SimpleSite implements ModuleApp, CaseHandler {
	protected $type;
	public function __construct($type) {
		$this->type = $type;
	}

	public function actOnCase($args) {
		switch ($this->type) {
			case 'bilde':
				$id = $args['id'];
				$cr = $args['thumb'];
				if (isset($id)) {
					$img = new Image($id);
					if (isset($cr)) {
						$img -> crop(50, 50);
					}
					return $img;
				}
				break;
			case 'file':
				$id = addslashes($args['id']);
				if ($id) {
					$query = "select * from `archive` WHERE `archive`.`id` LIKE '$id' LIMIT 1";
					$resource = mysql_query($query);
					$rows = mysql_num_rows($resource);
					if (!$resource) {
						echo 'kunne ikke gjøre query:' . mysql_error();
						exit();
					}
					$result = mysql_fetch_array($resource);
					$mime = $result['mime'];
					header('Content-type: ' . $mime);
					return $result[0];
				}
				break;
		}
	}

	public function createInstance($args) {
		$content = $args["_element"];
		$document = $args["_document"];
		$style = $args["_style"];
		switch ($this->type) {
			case "main":
				$set = $style->getStyleSet("newsdisplay");
				$class = $set['class'];
				$wrap = $content;
				$wrap->addClass($class);
				$query = "SELECT * FROM `articles` WHERE `public` LIKE 'yes' ORDER BY `articles`.`dato` DESC LIMIT 4";
				$resource = mysql_query($query);
				$result = mysql_fetch_array($resource);
				$hcl = $set['subclass']['hovednyhet'];
				$hov = new Div($hcl?$hcl:"hovednyhet");
				$a = new Link("?site=artikkel&id={$result['id']}");
				$a -> class = "artikkellink";
				$a -> addChild(new HtmlElement("h1", false, $result[0]));
				$a -> addChild(new HtmlElement("p", false, $result[1]));
				$hov -> addChild($a);
				$wrap -> addChild($hov);
				while ($result = mysql_fetch_array($resource)) {
					$scl = $set['subclass']['subnyhet'];
					$sub = new Div($scl?$scl:"subnyhet");
					$suba = new Link("?site=artikkel&id={$result['id']}");
					$suba -> class = "artikkellink";
					$suba -> addChild(new HtmlElement("h2", false, $result[0]));
					$sub -> addChild($suba);
					$wrap -> addChild($sub);
				}
				break;
			case "articles":
				$set = $style->getStyleSet("contenttext");
				$class = $set['class'];
				$content->addChild($wrap = new Div($class));
				$index = $_GET['index'];
				if (is_numeric($index)&&$index>=0) {
				}
				$query = "SELECT * FROM `articles` WHERE `public` LIKE 'yes' ORDER BY `articles`.`dato` DESC LIMIT 10;";
				$resource = mysql_query($query);
				if (!$resource) {
					$wrap->addChild(mysql_error());
				}
				$num_art = mysql_num_rows($resource);
				for ($i = 0; $i < $num_art; $i++) {
					$result = mysql_fetch_array($resource);
					if ($result['public']==='yes') {
						$wrap->addChild($span = new Span());
						$span->addChild($h1 = new HtmlElement("h1", false, $result['titel']));
						$p = new HtmlElement("p", false, $result[1]);
						$span->addChild($p);
						$link = new Link("?site=artikkel&id=".$result['id'], "les mer");
						$span->addChild($link);
						if ($i < $num_art - 1)
						$wrap->addChild(new HtmlElement("hr", true));
					}
				}
				break;
			case "article":
				$set = $style->getStyleSet("contenttext");
				$class = $set['class'];
				$artikkel = new Div($class);
				$id = $args["id"];
				if (!is_numeric($id)) {
					$id = addslashes($_GET['id']);
				}
				$query = "SELECT * FROM `articles` WHERE `id` LIKE '$id'";
				$resource = mysql_query($query);
				if (!$resource) {
					$artikkel->addChild('kunne ikke gjøre query:' . mysql_error());
				}
				$result = mysql_fetch_array($resource);
				if ($result['public'] === 'yes') {
					$h1 = new HtmlElement("h1");
					$h1->addChild($title = $result['titel']);
					$contname = $args['_data']['contname'];
					if ($contname) $contname->addChild($title);
					$artikkel->addChild($h1);
					$b = new HtmlElement("b");
					$b->addChild(new TextNode($result[1], true));
					$artikkel->addChild($b);
					$p = new HtmlElement("p");
					$p->addChild(new TextNode($result[2], true));
					$artikkel->addChild($p);
				} else {
					$artikkel->addChild(new HtmlElement("p", true, "Beklager, denne artiklen kunne ikke vises."));
				}
				$content->addChild($artikkel);
				break;
			case "info":
				$set = $style->getStyleSet("contenttext");
				$class = $set['class'];
				$content->addClass($class);
				$wrap = $content;
				$id = addslashes($args["id"]);
				$query = "SELECT * FROM `info` WHERE `info`.`id` LIKE '$id' LIMIT 1;";
				$resource = mysql_query($query);
				if (!$resource) {
					$content->addChild(mysql_error());
				}
				$result = mysql_fetch_assoc($resource);
				if ($result) {
					$h1e = new HtmlElement("h1");
					$h1e->addChild($name = $result['name']);
					$contname = $args['_data']['contname'];
					if ($contname) $contname->addChild($name);
					$content->addChild($h1e);
					$content->addChild(new TextNode($result['display'], true));
					return array("footer" => "Sist oppdatert: ".$result['last']);
				} else {
					$content->addChild("Ikke tilgjengelig");
				}
				break;
			case "logo":
				$aimg = new Link("?");
				$src = $args["src"];
				$logim = new Img($src, "head");
				$aimg -> addChild($logim);
				$content -> addChild($aimg);
				break;
			case "menu":
				$set = $style->getStyleSet("menu");
				$class = $set['class'];
				$mwr = $set['subclass']['menu-wrapper'];
				$menu = new Menu($class);
				foreach ($args["_children"] as $child) {
					$menu->addMenuArray($child);
				}
				$content->addClass($mwr?$mwr:"menu-wrapper");
				$content->addChild($menu);
				break;
			case "contenthead":
				$anch = new Link("?");
				$anch->addChild("Hjem");
				$content->addChild($span = new Span($anch));
				$span->addChild(new Span("/"));
				$span->addChild($contname = new Span());
				return array("contname" => $contname);
				break;
			case "newsblock":
				$set = $style->getStyleSet("bluebox");
				$class = $set['class'];
				$nyheter = new Div($class);
				$nyheter -> generateId();
				$h1e = new HtmlElement("h1");
				$h1e -> addChild("Nyheter");
				$nyheter -> addChild($h1e);
				$content -> addChild($nyheter);
				$ul = new HtmlList();
				$nyheter -> addChild($ul);
				$num = $args["num"];
				$query = "SELECT * FROM `articles` WHERE `public` LIKE 'yes' ORDER BY `articles`.`dato` DESC LIMIT $num";
				$resource = mysql_query($query);
				if (!$resource) {
					$content->addChild('kunne ikke gjøre query:'.mysql_error());
				} else {
					while ($row = mysql_fetch_array($resource)) {
						$link = new Link("?site=artikkel&id=" . $row['id']);
						$link -> addChild($row['titel']);
						$ul -> add($link);
					}
				}
				break;
			case "ad":
				$title = $args["title"];
				$link = $args["link"];
				$src = $args["src"];
				$img = new Img($src);
				$img -> style = "width: 100%;";
				$set = $style->getStyleSet("bluebox");
				$class = $set['class'];
				$ads = new Div($class, "");
				$ads -> title = $title;
				$ads -> addChild($pad = new HtmlElement("p"));
				$content -> addChild($ads);
				$pad -> addChild(new Link($link, $img));
				break;
			case "bluebox" :
				$title = $args["title"];
				$set = $style->getStyleSet("bluebox");
				$class = $set['class'];
				$ads = new Div($class, "");
				$ads -> generateId();
				$ads -> addChild(new HtmlElement("h1", false, $title));
				$ads -> addChild($pad = new HtmlList());
				foreach ($args["_children"] as $c) {
					if ($c["name"] === "link") {
						$href = $c["attr"]["href"];
						$value = $c["attr"]["value"];
						$pad->add(new Link($href, $value));
					}
				}
				$content -> addChild($ads);
				break;
			case "footer":
				$footer = $content;
				$footer->addChild(new Link("softenowebtool", $fimg = new Img("bilder/softeno.png")));
				$fimg->height = "15";
				$fimg->width = "70";
				$data = $args['_data']['footer'];
				if ($data) $footer->addChild(new Span($data));
				break;
			case "titleslider":
				$height = $args["height"];
				$document->head->linkStyleSheet("css/slider.css");
				$document->head->addScript("javascript/slider.js");
				$content->addChild($holder = new Div("slider-holder"));
				$holder->generateId();
				$holder->addChild($ul = new HtmlList("slider-list"));
				if (is_array($args["_children"])) {
					foreach ($args["_children"] as $c) {
						if ($c['name'] === "image") {
							$src = $c["attr"]["src"];
							$ul->add(new Img($src));
						}
					}
				}
				$holder->addChild($prev = new Link());
				$prev->class = "slider-prev";
				$holder->addChild($next = new Link());
				$next->class = "slider-next";
				if (is_numeric($height)) {
					$holder->addChild(new script("", "$('#".$holder->id."').slider({height: ".$height."});"));
				} else {
					$holder->addChild(new script("", "$('#".$holder->id."').slider();"));
				}
				break;
		}
	}
}