<?php

/**
 *
 */
class Oppsett extends Div {
	protected $type;
	protected $doc;
	protected $file;

	public function __construct($path, $title = "") {
		parent::__construct("set-wrap", "oppsett");
		$this->doc = new DOMDocument($path);
		$this->doc->load($this->file = $path);
		if (!$title) {
			$this->addChild(new HtmlElement("h1", false, "Forandre på sideoppsett"));
			$this->addChild(new HtmlElement("p", false, "Legg til nye lenker ved å trykke på + (du må laste inn siden på nytt for å se endringer). "));
			$this->readLeft();
			$this->readRight();
			$this->addChild($form = new Form("?site=side&action=oppsett-new"));
			$form->addChild(new HtmlElement("h3", false, "Nytt ad-element"));
			$form->addInput("Lenke: ", new Input("text", "link"));
			$form->addInput("Tittel: ", new Input("text", "title"));
			$form->addInput($bild = new Link ("#", "Bildeadresse: (trykk her)"), new Input("text", "src"));
			$bild->id = "bild";
			$form->addChild(new Input("submit", "", "Opprett"));
			$this->addChild(new Div("", "side"));

			//$this->addChild($imgsel = new ImageSelect());
			$this->addChild(new Div("", "imageselect"));
			//$imgsel->asDialog("src");
			$this->addChild($script = new script("", '$(function() {
			$("#imageselect").imageselect({
		choosed: function(choosen) {
		$("#src").val(choosen);
		}
		});
			$("#bild").click(function() {$("#imageselect").dialog("open");});
		});'));

			$this->addChild($dial = new Div("", "dial"));
			$dial->title = "Ny lenke";
			$dial->addChild($form = new Form("?site=side&action=oppsett-box-alter&type=add"));
			$form->id = "diform";
			$form->addInput("Tittel: ", $val = new Input("text", "val"));
			$form->addInput("Lenke: ", $lin = new Input("text", "lin"));
			$val->title = "Teksten som skal vises";
			$lin->title = "Nett-adressen som lenken skal føre til";
			$this->addChild($script = new script("", '$(function() {
			$("#dial").dialog({
			autoOpen: false,
			buttons: {"Opprett": function() {
				var $this = $("#right");
		var data = {
		data: "link*href="+$("#lin").val()+"*value="+$("#val").val(),
		element: "Lenker"
		};
			$.ajax({
				url : $("#diform").attr("action"),
				type : \'post\',
				success : function(rdata, textStatus, jqXHR) {
					alert("fullført");
					$("#dial").dialog("close");
				},
				data : $.param(data)
			});
			return false;
		 }, "Avbryt": function() { $(this).dialog("close"); }
				 },
			show: "slide"
		});
		$("#diform").submit(function() {
		var $this = $("#right");
		var data = {
		data: "link*href="+$("#lin").val()+"*value="+$("#val").val(),
		element: "Lenker"
		};
			$.ajax({
				url : $(this).attr("action"),
				type : \'post\',
				success : function(rdata, textStatus, jqXHR) {
					alert("fullført");
					$("#dial").dialog("close");
				},
				data : $.param(data)
			});
			return false;
		});
		});'));
		} else if ($node = $this->searchRight($title)) {
			$this->addChild($h1 = new HtmlElement("h1", false, "Endre ad-element"));
			$this->addChild($p = new HtmlElement("p", false, "Oppgi ny titel, bildeadresse og/eller lenke."));
			$this->addChild($form = new Form("?site=side&action=oppsett-alter"));
			$form->addChild(new HtmlElement("h3", false, "Endre ad-element"));
			$form->addInput("Lenke: ", new Input("text", "link", $node->getAttribute("link")));
			$form->addInput("Titel: ", new Input("text", "title", $node->getAttribute("title")));
			$form->addInput($op = new Link("#", "Bildeadresse: "), new Input("text", "src", $node->getAttribute("src")));
			$form->addChild(new Input("hidden", "oldtitle", $title));
			$form->addChild(new Input("submit", "", "Lagre"));
			$form->addChild(new Div("", "sel"));
			$op->generateId();
			$form->addChild(new script("", "$('#sel').imageselect({ choosed: function(chosen) { $('#src').val(chosen);}});".
			"$('#{$op->id}').click(function(){ $('#sel').dialog('open');})"));
		}
	}

	public function searchRight($title) {
		$node = $this -> doc -> getElementsByTagName("right") -> item(0);
		if ($node->hasChildNodes()) {
			foreach ($node->childNodes as $child) {
				if ($child->nodeType == XML_ELEMENT_NODE) {
					$_title = $child -> getAttribute("title");
					if ($title==$_title) {
						return $child;
					}
				}
			}
		}
		return false;
	}

	public function search($tag, $title = "", $name = "title") {
		$node = $this -> doc -> documentElement;
		$list = $node->getElementsByTagName($tag);
		foreach ($list as $child) {
			$_title = $child -> getAttribute($name);
			if ($_title==$title) {
				return $child;
			}
		}
		return false;
	}

	public function reorderOppsett() {
		global $error;
		$parent = $this->doc->getElementsByTagName("right")->item(0);
		$keys = array_keys($_POST);

		$opp = array();
		foreach ($keys as $key) {
			$value = Data::post($key, "i", 5);
			$key = str_replace("_", " ", $key);
			$result = $this->searchRight($key);
			if ($result) {
				$opp[$value] = $result->cloneNode(true);
			}
		}
		$len = 0;
		$list = array();
		foreach ($parent->childNodes as $c) {
			if ($c->nodeName == "ad") {
				$list[] = $c;
				$len++;
			}
		}
		if (count($opp) == $len) {
			foreach ($opp as $index => $men) {
				$item = $list[$index];
				$parent->removeChild($item);
				$parent->appendChild($men);
			}
		} else {
			$error->app("lenght $len not fully represented ".implode(", ", $keys));
			return false;
		}
		return $this->doc->save($this->file);
	}

	public function newRight($title = "", $link = "", $src = "") {
		if ($title&&$link&&$src) {
			$right = $this->doc->getElementsByTagName("right")->item(0);
			$node = $this->doc->createElement("ad");
			$node->setAttribute("link", $link);
			$node->setAttribute("title", $title);
			$node->setAttribute("src", $src);
			$right->appendChild($node);
			return $this->doc->save($this->file);
		}
		return false;
	}

	public function alterRight($old, $title, $link, $src) {
		$result = $this->searchRight($old);
		$altered = false;
		if ($result) {
			$result->setAttribute("title", $title);
			$result->setAttribute("link", $link);
			$result->setAttribute("src", $src);
			$altered = $this->doc->save($this->file);
		}
		return $altered;
	}

	public function alterBox($element, $type, $data = "") {
		$altered = false;
		switch ($type) {
			case 'add':
				$result = $this->search("bluebox", $element);
				if ($result) {
					$d = explode("*", $data);
					$el = $this->doc->createElement($d[0]);
					for ($i = 1, $l = count($d); $i<$l; $i++) {
						$att = explode("=", $d[$i]);
						$el->setAttribute($att[0], $att[1]);
					}
					$result->appendChild($el);
					$altered = $this->doc->save($this->file);
				}
				break;
			case 'remove':
				$result = $this->search("link", $element, "value");
				if ($result) {
					$parent = $result->parentNode;
					$parent->removeChild($result);
					$altered = $this->doc->save($this->file);
				}
				break;
			case 'alter':

				break;
		}
		return $altered;
	}

	public function removeRight($title) {
		$node = $this -> doc -> getElementsByTagName("right") -> item(0);
		$removed = false;
		if ($node->hasChildNodes()) {
			foreach ($node->childNodes as $child) {
				if ($child->nodeType == XML_ELEMENT_NODE) {
					$_title = $child -> getAttribute("title");
					if ($title==$_title) {
						$node->removeChild($child);
						$removed = true;
					}
				}
			}
		}
		$result = $this->doc->save($this->file);
		return $removed&&$result;
	}

	protected function readLeft() {
		$this->addChild($left = new Div("left inline-block", "left"));
		$left->addChild($script = new script());
		$left->addChild($lagre = new Button("Lagre rekkefølge"));
		$script->addChild("$(document).ready(function () { $('#left').sortable({filter: 'div'});});");
		$script->addChild("$('#$lagre->id').click(function () {\n  $('#left').menulister('save');\n});");
		$node = $this -> doc -> getElementsByTagName("left") -> item(0);
		if ($node->hasChildNodes()) {
			foreach ($node->childNodes as $child) {
				if ($child->nodeType == XML_ELEMENT_NODE) {
					switch ($child->nodeName) {
						case "nyheter" :
							$nyheter = new Div("bluebox");
							$nyheter -> generateId();
							$h1e = new HtmlElement("h1");
							$h1e -> addChild("Nyheter");
							$nyheter -> addChild($h1e);
							$left -> addChild($nyheter);
							$ul = new HtmlList();
							$nyheter -> addChild($ul);
							$num = $child -> getAttribute("num");
							$query = "SELECT * FROM `artikkler` WHERE `public` LIKE 'yes' ORDER BY `artikkler`.`dato` DESC LIMIT $num";
							$resource = mysql_query($query);
							if (!$resource) {
								echo 'kunne ikke gjøre query:' . mysql_error();
								exit();
							}
							while ($row = mysql_fetch_array($resource)) {
								$link = new Link("#");
								$link -> addChild($row['titel']);
								$ul -> add($link);
							}
							break;
						case "bluebox" :
							$title = $child -> getAttribute("title");
							$ads = new Div("bluebox", "");
							$ads -> generateId();
							$ads -> title = $title;
							$ads -> addChild($h1 = new HtmlElement("h1", false, $title));
							$h1 -> addChild($add = new Link("#", "+"));
							$add -> generateId();
							$add -> title = "Legg til ny lenke";
							$script -> addChild("$(function() { $('#$add->id').click(function() { $('#dial').dialog('open');});});");
							$ads -> addChild($pad = new HtmlList());
							foreach ($child->childNodes as $c) {
								if ($c->nodeName == "link") {
									$href = $c->getAttribute("href");
									$value = utf8_decode($c->getAttribute("value"));
									$pad->add(new Link("#", $value), new Link("?site=side&action=oppsett-box-alter&type=remove&element=".urlencode($value), "slett"));
								}
							}
							$left -> addChild($ads);
							break;
					}
				}
			}
		}
	}

	protected function readRight() {
		$this->addChild($right = new Div("right inline-block", "right"));
		$right->addChild($script = new script());
		$right->addChild($lagre = new Button("Lagre rekkefølge"));
		$script->addChild('$(document).ready(function () { $(\'#right\').sortable({filter: \'div\'});
		$("#'.$lagre->id.'").click(function () {
		var link = "?action=oppsett-reorder",
			$this = $("#right");
			var data = {};
			$this.children("div").each(function(index) {
				data[$(this).attr("title")] = index;
			});
			$.ajax({
				url : link,
				type : \'post\',
				success : function(rdata, textStatus, jqXHR) {
					$(\'#side\').html(rdata);
				},
				data : $.param(data)
			});
		});});');
		$node = $this -> doc -> getElementsByTagName("right") -> item(0);
		if ($node->hasChildNodes()) {
			foreach ($node->childNodes as $child) {
				if ($child->nodeType == XML_ELEMENT_NODE) {
					$title = $child -> getAttribute("title");
					$link = $child->getAttribute("link");
					$src = $child->getAttribute("src");
					$_im = preg_match("#http://#", $src) ? $src : "../".$src;
					$img = new Img($_im);
					$img -> style = "width: 140px;";
					$ads = new Div("bluebox", "");
					$ads -> title = $title;
					$ads -> addChild($pad = new HtmlElement("p"));
					$right -> addChild($ads);
					$pad -> addChild(new Link("#", $img));
					$pad->addChild($slett = new Link("?site=side&action=oppsett-remove&title=".urlencode($title), "Slett"));
					$pad->addChild($rediger = new Link("?site=rightedit&title=".urlencode($title), "Rediger"));
					$slett -> onclick = "return confirm('Er du sikker på at du vil slette $title?')";
				}
			}
		}
	}
}
