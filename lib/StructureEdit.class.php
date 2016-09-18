<?php
class StructureEdit implements Action, ModuleApp {
	protected $key;
	public function __construct($key) {
		$this->key = $key;
	}

	public function performAction($command, $args) {
		switch ($this->key) {
			case "logoalter":
				$src = $args['src'];
				$mod = $args['mod'];
				$num = $args['num'];
				if ($src&&$mod&&is_numeric($num)) {
					chdir("..");
					$mngr = PageManager::getCurrent();
					$page = $mngr->loadPage();
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					$element->setAttribute("src", $src);
					$page->saveTemplate();
					chdir("softenowebtool");
					return array("message" => "Logo er endret", "src" => $src);
				}
				break;
			case "adalter":
				$title = $args['title'];
				$link = $args['link'];
				$src = $args['src'];
				$mod = $args['mod'];
				$num = $args['num'];
				if ($src&&$mod&&is_numeric($num)
				&& $title && $link && $src) {
					chdir("..");
					$mngr = PageManager::getCurrent();
					$page = $mngr->loadPage();
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					$element->setAttribute("title", $title);
					$element->setAttribute("link", $link);
					$element->setAttribute("src", $src);
					$page->saveTemplate();
					chdir("softenowebtool");
				}
				break;
			case "delapp":
				$mod = $args['mod'];
				$num = $args['num'];
				if ($mod&&is_numeric($num)) {
					chdir("..");
					$mngr = PageManager::getCurrent();
					$page = $mngr->loadPage();
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					$element->parentNode->removeChild($element);
					$page->saveTemplate();
					chdir("softenowebtool");
					return array("message" => "Applikasjon ".$list['name']." slettet.");
				}
				break;
			case 'bluelink':
				$mod = $args['mod'];
				$num = $args['num'];
				$link = $args['href'];
				$value = $args['value'];
				$linum = $args['linum'];
				if ($mod&&is_numeric($num)&&$link&&$value) {
					chdir("..");
					$mngr = PageManager::getCurrent();
					$page = $mngr->loadPage();
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					$links = $element->getElementsByTagName("link");
					if ($linum&&$linum<$links->length) {
						$el = $links->item($linum);
						$el->setAttribute("href", $link);
						$el->setAttribute("value", $value);
					} else {
						$element->appendChild($el = $page->getXMLDoc()->createElement("link"));
						$el->setAttribute("href", $link);
						$el->setAttribute("value", $value);
						$linum = $element->getElementsByTagName("link")->length-1;
					}
					$page->saveTemplate();
					chdir("softenowebtool");
					return array("message" => "Lenke endret/opprettet",
					"link" => $link, "value" => $value, "num" => $linum);
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'sliderimage':
				$mod = $args['mod'];
				$num = $args['num'];
				$src = $args['src'];
				$linum = $args['linum'];
				if ($mod&&is_numeric($num)&&$src) {
					chdir("..");
					$page = new Page(true);
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					$images = $element->getElementsByTagName("image");
					if (is_numeric($linum)&&$linum<$images->length) {
						$el = $images->item($linum);
						$el->setAttribute("src", $src);
					} else {
						$element->appendChild($el = $page->getXMLDoc()->createElement("image"));
						$el->setAttribute("src", $src);
					}
					$page->saveTemplate();
					chdir("softenowebtool");
				}
				break;
			case 'altertitle':
				$title = $args['title'];
				if ($title) {
					chdir("..");
					$page = new Page(true);
					$doc = $page->getXMLDoc();
					$tel = $doc->getElementsByTagName("load")->item(0)->getElementsByTagName("title")->item(0);
					$tel->setAttribute("value", $title);
					$page->saveTemplate();
					chdir("softenowebtool");
					return array("message" => "Tittel endret til ".utf8_decode($title));
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'dellink':
				$mod = $args['mod'];
				$num = $args['num'];
				$linum = $args['linum'];
				if ($mod&&is_numeric($num)&&is_numeric($linum)) {
					chdir("..");
					$page = new Page(true);
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					$links = $element->getElementsByTagName("link");
					if ($linum<$links->length) {
						$el = $links->item($linum);
						$element->removeChild($el);
					}
					$page->saveTemplate();
					chdir("softenowebtool");
					return array("message" => "Lenke slettet");
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'sliderimagedel':
				$mod = $args['mod'];
				$num = $args['num'];
				$linum = $args['linum'];
				if ($mod&&is_numeric($num)&&$linum) {
					chdir("..");
					$page = new Page(true);
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					$images = $element->getElementsByTagName("image");
					if ($linum&&$linum<$images->length) {
						$el = $images->item($linum);
						$element->removeChild($el);
					}
					$page->saveTemplate();
					chdir("softenowebtool");
				}
				break;
			case 'modapp':
				$mod = $args['mod'];
				$num = $args['num'];
				if ($mod&&is_numeric($num)) {
					chdir("..");
					$page = new Page(true);
					$list = $page->listModuleApplications($mod);
					$element = $list[$num]["element"];
					foreach ($args as $key => $p) {
						if ($key!=="name"&&$key!=="mod"&&$key!=="num") {
							$element->setAttribute($key, $p);
						}
					}
					$page->saveTemplate();
					chdir("softenowebtool");
					return array("message" => "Applikasjon endret");
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'modreorder':
				$mod = $args['mod'];
				$apps = $args['apps'];
				if ($mod&&is_array($apps)) {
					chdir("..");
					$page = new Page(true);
					$doc = $page->getXMLDoc();
					$model = $doc->getElementsByTagName($mod)->item(0);
					$list = array();
					for ($i = $model->childNodes->length-1; $i >= 0; $i--) {
						$child = $model->childNodes->item($i);
						if ($child->nodeType == XML_ELEMENT_NODE) {
							$list[] = $child;
							$model->removeChild($child);
						}
					}
					$recr = array();
					for ($i = count($list)-1; $i >= 0; $i--) {
						$recr[count($list)-($i+1)] = $list[$i];
					}
					$list = $recr;
					foreach ($apps as $key => $app) {
						$model->appendChild($list[$app]);
					}
					$page->saveTemplate();
					chdir("softenowebtool");
					return array("message" => "Applikasjoner ble omrokkert.");
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'appmodule':
				$mod = $args['mod'];
				$app = $args['app'];
				$case = $args['case'];
				if ($mod&&$app) {
					chdir("..");
					$mngr = PageManager::getCurrent();
					$page = $mngr->loadPage();
					$properties = $page->getProperties();
					$isin = false;
					$hasp = false;
					$edit = "";
					foreach ($properties['apps'] as $papp) {
						if ($papp['name']===$app) {
							$isin = true;
							$edit = $papp['edit'];
							$hasp = is_array($papp['props'])||is_array($papp['props']);
							break;
						}
					}
					$doc = $page->getXMLDoc();
					$model = $doc->getElementsByTagName($mod)->item(0);
					$appel = null;
					if ($isin) {
						if ($case&&is_string($case)) {
							$appel = $doc->createElement("app");
							$appel->setAttribute("name", $app);
							$switch = $model->getElementsByTagName("switch")->item(0);
							$cases = $switch->getElementsByTagName("case");
							$found = false;
							foreach ($cases as $cas) {
								$value = $cas->getAttribute("value");
								if ($case === $value) {
									$cas->appendChild($appel);
									$found = true;
									break;
								}
							}
							if (!$found) {
								$cas = $doc->createElement("case");
								$cas->setAttribute("value", $case);
								$cas->appendChild($appel);
								$switch->appendChild($cas);
							}
						} else {
							$appel = $doc->createElement("app");
							$appel->setAttribute("name", $app);
							$model->appendChild($appel);
						}
						$list = $page->listModuleApplications($mod);
						$num = 0;
						foreach ($list as $i => $l) {
							if ($l['element']===$appel) {
								$num = $i;
								break;
							}
						}
						$page->saveTemplate();
						chdir("softenowebtool");
						return array("message" => "Applikasjon $app ble lagt til i $mod #".$case,
						"app" => $app, "case" => $case,	"edit" => $edit, "num" => $num);
					} else {
						return array("message" => "Applikasjon ".$app." er ikke registrert.");
					}
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'structure':
				chdir("..");
				$page = new Page(false, "..");
				foreach ($page->document->getModules() as $key => $app) {
					$app->addChild(new HtmlElement("h2", array("align" => "center"),
					$a = new Link("?site=editmodule&mod=".urlencode($key), $key)));
					$a->style = "color: #000";
					$a->target = "_parent";
					$app->title = $key;
					$app->align = "center";
					$app->addClass("recognise-module");
					$app->addChild($list = new Div());
					foreach ($page->listModuleApplications($key) as $i => $l) {
						switch ($l['name']) {
							case 'menu':
								$list->addChild($a = new Link("?site=menu&mod=".urlencode($key)."&num=".$i, $l['name']));
								$a->style = "color: #000";
								$a->target = "_parent";
								break;
							case 'logo':
								$list->addChild($a = new Link("?site=logoedit&mod=".urlencode($key)."&num=".$i, $l['name']));
								$a->style = "color: #000";
								$a->target = "_parent";
								break;
							case 'ad':
								$list->addChild($a = new Link("?site=adedit&mod=".urlencode($key)."&num=".$i, $l['name']));
								$a->style = "color: #000";
								$a->target = "_parent";
								break;
							case 'bluebox':
								$list->addChild($a = new Link("?site=blueboxedit&mod=".urlencode($key)."&num=".$i, $l['name']));
								$a->style = "color: #000";
								$a->target = "_parent";
								break;
							case 'titleslider':
								$list->addChild($a = new Link("?site=slideredit&mod=".urlencode($key)."&num=".$i, $l['name']));
								$a->style = "color: #000";
								$a->target = "_parent";
								break;
							default:
								$list->addChild($l['name']);
							break;
						}
						$list->addChild(new HtmlElement('br', true));
					}
				}
				foreach ($page->document->body->getChildList() as $child) {
					$child->style = "max-width: 90%;";
				}
				echo $page->document;
				exit();
				break;
		}
	}

	public function createInstance($args) {
		$content = $args["_element"];
		$document = $args["_document"];
		switch ($this->key) {
			case 'structure':
				chdir("..");
				$mngr = PageManager::getCurrent();
				$page = $mngr->loadPage();
				chdir("softenowebtool");
				$content->addChild($h1 = new HtmlElement("h1", array("align" => "center"), "Struktur"));
				$content->addChild($left = new Div(array("style" => "width: 35%;float:left;")));
				$left->addChild($set = new HtmlElement("div"));
				$set -> addChild(new HtmlElement("h2", false, "Oppsett"));
				$properties = $page->getProperties();
				$set->addChild(new Span($properties['desc']));
				$set -> addChild(new HtmlElement("h3", false, "Stil"));
				$set->addChild(new Span($properties['style']));
				$set -> addChild(new HtmlElement("h3", false, "Tittel"));
				$set->addChild($fit = new Form("?site=struktur&action=altertitle"));
				$fit->addChild($titl = new Input("text", "title", $properties['title']));
				$titl->style = "width:250px;";
				$fit->addChild($lagre = new Input("button", "", "lagre"));
				$lagre->id = "tit";
				$fit->addChild(new script("", "$('#tit').action({action: 'altertitle', args: {title: $('[name=\'title\']')}, callback: function() {}});"));
				$set -> addChild(new HtmlElement("h3", false, "css"));
				$set->addChild($sty = new HtmlList());
				foreach ($properties['css'] as $prop) {
					$sty->add($prop);
				}
				$set -> addChild(new HtmlElement("h3", false, "Skript"));
				$set->addChild($sty = new HtmlList());
				foreach ($properties['script'] as $prop) {
					$sty->add($prop);
				}
				$left->addChild(new HtmlElement("h2", false, "Modulapplikasjoner"));
				$left->addChild($sty = new table("table-display"));
				foreach ($properties['apps'] as $prop) {
					$row = $sty->addRow($prop['name']);
					$ti = "";
					if (is_array($prop['props'])) {
						foreach ($prop['props'] as $i => $pr) {
							if ($i!=0) $ti .= ', ';
							$ti .= $pr['name']."(".$pr['type'].")";
						}
					}
					$row->title = $ti;
				}
				$content -> addChild($right = new Div());
				$right->style = "float:right;width:65%;";
				$right -> addChild($struct = new HtmlElement("iframe", array("_noendtag" => false,
		"src" => "?action=structure", "id" => "frame")));
				$struct -> style = "width: 100%; height: 700px;";

				// 				$content->addChild($struct = new Div());
				// 				$styles = $document->head->getChildAt(0);
				// 				$document->head->addChild($styles);
				// 				$struct->style = "right: 30px; top: 180px; position: absolute; width: 600px; overflow: hide;";
				// 				$struct->addChild($body = new Div($page->document->body->class));
				// 				foreach ($page->document->body->getChildList() as $child) {
				// 					$body->addChild($child);
				// 				}
				// 				foreach ($page->document->getModules() as $key => $app) {
				// 					$app->addChild(new HtmlElement("h2", array("align" => "center"), $a = new Link("?site=editmodule&mod=".urlencode($key), $key)));
				// 					$a->style = "color: #000";
				// 					$a->target = "_parent";
				// 					$app->title = $key;
				// 					$app->align = "center";
				// 					$app->class = $app->class." recognise-module";
				// 					$app->addChild($list = new Div());
				// 					foreach ($page->listModuleApplications($key) as $i => $l) {
				// 						switch ($l['name']) {
				// 							case 'menu':
				// 								$list->addChild($a = new Link("?site=menu&el=".urlencode($l['att']['src']), $l['name']));
				// 								$a->style = "color: #000";
				// 								$a->target = "_parent";
				// 								break;
				// 							case 'logo':
				// 								$list->addChild($a = new Link("?site=logoedit&mod=".urlencode($key)."&num=".$i, $l['name']));
				// 								$a->style = "color: #000";
				// 								$a->target = "_parent";
				// 								break;
				// 							case 'ad':
				// 								$list->addChild($a = new Link("?site=adedit&mod=".urlencode($key)."&num=".$i, $l['name']));
				// 								$a->style = "color: #000";
				// 								$a->target = "_parent";
				// 								break;
				// 							case 'bluebox':
				// 								$list->addChild($a = new Link("?site=blueboxedit&mod=".urlencode($key)."&num=".$i, $l['name']));
				// 								$a->style = "color: #000";
				// 								$a->target = "_parent";
				// 								break;
				// 							case 'titleslider':
				// 								$list->addChild($a = new Link("?site=slideredit&mod=".urlencode($key)."&num=".$i, $l['name']));
				// 								$a->style = "color: #000";
				// 								$a->target = "_parent";
				// 								break;
				// 							default:
				// 								$list->addChild($l['name']);
				// 							break;
				// 						}
				// 						$list->addChild('<br/>');
				// 					}
				// 				}

				break;
			case 'logoedit':
				$mod = $args['mod'];
				$num = $args['num'];
				$document -> head -> addScript("javascript/ImageSelect.js");
				$content->addChild(new HtmlElement("h2", false, "Tittelbilde"));
				chdir("..");
				$mngr = PageManager::getCurrent();
				$page = $mngr->loadPage();
				chdir("softenowebtool");
				if (is_string($mod)&&is_numeric($num)) {
					$lis = $page->listModuleApplications($mod);
					$src = $lis[$num]["att"]["src"];
					$content->addChild($titl = new Img($src));
					$titl->style = "max-width: 300px; max-height: 200px;";
					$titl->id = "logo";
					$content->addChild($tform = new Form("?site=logoedit&action=logoalter&mod=".urlencode($mod)."&num=".$num, false));
					$tform->id = "tform";
					$tform->addChild($l = new Link("#", "Velg et bilde"));
					$l->id = "vlgbil";
					$tform->addChild(new Input("text", "src", $src));
					$tform->addChild(new Input("submit", "subm", "Lagre"));
					$content->addChild(new Div("", "imgsel1"));
					$content->addChild(new script("", "$(function() { $('#imgsel1').imageselect({choosed: function(choosen) {
							$('#src').val(choosen);
						}});
						$('#vlgbil').click(function() {
						$('#imgsel1').dialog('open');
					});$('#tform').action({action : 'logoalter', callback: function(d) { $('#logo').attr('src', d.src);},
					 args: {src: $('[name=\'src\']'), mod: '".$mod."', num: '".$num."'}});
					});"));
				}
				break;
			case 'adedit':
				$mod = $args['mod'];
				$num = $args['num'];
				if ($mod&&is_numeric($num)) {
					chdir("..");
					$page = new Page(true);
					$list = $page->listModuleApplications($mod);
					$node = $list[$num]["element"];
					$content->addChild($h1 = new HtmlElement("h1", false, "Endre ad-element"));
					$content->addChild($p = new HtmlElement("p", false, "Oppgi ny titel, bildeadresse og/eller lenke."));
					$content->addChild($form = new Form("?site=adedit&action=adalter&mod=".urlencode($mod)."&num=".$num));
					$form->addChild(new HtmlElement("h3", false, "Endre ad-element"));
					$form->addInput("Lenke: ", new Input("text", "link", $node->getAttribute("link")));
					$form->addInput("Titel: ", new Input("text", "title", $node->getAttribute("title")));
					$form->addInput($op = new Link("#", "Bildeadresse: "), new Input("text", "src", $node->getAttribute("src")));
					$form->addChild(new Img($node->getAttribute("src"), "bilde ikke tilgjengelig"));
					$form->addChild(new Input("submit", "", "Lagre"));
					$form->addChild(new Div("", "sel"));
					$op->generateId();
					$form->addChild(new script("", "$('#sel').imageselect({ choosed: function(chosen) {".
					"$('#src').val(chosen);}});$('#{$op->id}').click(function(){ $('#sel').dialog('open');})"));
					chdir("softenowebtool");
				}
				break;
			case 'blueboxedit':
				$mod = $args['mod'];
				$num = $args['num'];
				if ($mod&&is_numeric($num)) {
					chdir("..");
					$mngr = PageManager::getCurrent();
					$page = $mngr->loadPage();
					$list = $page->listModuleApplications($mod);
					$node = $list[$num]["element"];
					$content->addChild($h1 = new HtmlElement("h1", false, "Endre bluebox"));
					$content->addChild($p = new HtmlElement("p", false, "Oppgi ny titel, bildeadresse og/eller lenke."));
					$content->addChild($form = new Form("?site=blueboxedit&action=modapp&mod=".urlencode($mod)."&num=".$num));
					$form->addClass("sow-styleform title-form");
					$form->addChild(new HtmlElement("label", array("for" => "title", "style" => "min-width:60px;"), "Titel: "));
					$form->addChild(new Input("text", "title", $node->getAttribute("title")));
					$form->addChild(new Input("submit", "", "Lagre"));
					$content->script("$('.title-form').action({action:'modapp', args:{mod: '".$mod."', num: ".$num.", title: $('#title')}});");
					$content->addChild($links = new table("table-display link-table"));
					$links->addHead(new Span("Tekst"), new Span("Lenke"), "", "");
					foreach ($list[$num]["childs"] as $key => $lin) {
						$tr = $links->addRow(
						$value = new Input("text", "value".$key, $lin["attr"]["value"]),
						$href = new Input("text", "href".$key, $lin["attr"]["href"]),
						$but = new Button("Endre", "edit".$key),
						$ln = new Button("Slett", "del".$key));
						$tr->addClass("link-row");
						$tr->id = "row-".$key;
						$href->style = "width: 200px;";
						$value->style = "width: 160px;";
						$content->script("$('#".$ln->id."').action({action:'dellink', args:{mod: '".$mod."', num: ".$num.", linum: ".$key."},".
						"callback: function(d) {\$('#row-".$key."').remove();}});");
						$content->script("$('#".$but->id."').action({action:'bluelink', args:{mod: '".$mod."', num: ".$num.", linum: ".$key.", ".
						"value: $('#value".$key."'), href: $('#href".$key."')}});");
					}
					$tr = $links->addRow(
					new Input("text", "nvalue"),
					new Input("text", "nhref"),
					new Button("Legg til ny", "nedit"),
					"");
					$tr->class = "link-add";
					$content->script("$('#nedit').action({action:'bluelink', args:{mod: '".$mod."',".
					"num: ".$num.", value: $('#nvalue'), href: $('#nhref')}, callback: function(d) {".
					"var el = $('.link-row').first().clone().insertBefore('.link-add').hide();".
					"el.find(':text:eq(0)').val(d.value).attr('id', 'value'+d.num);".
					"el.find(':text:eq(1)').val(d.link).attr('id', 'href'+d.num);el.slideDown(800);".
					"el.find(':button:eq(0)').attr('id', 'edit'+d.num);el.find(':button:eq(1)').attr('id', 'del'+d.num);".
					"$('#del'+d.num).action({action:'dellink', args:{mod: '".$mod."', num: ".$num.", linum: d.num},".
					"callback: function() {el.remove();}});$('#edit'+d.num).action({action:'bluelink', ".
					"args:{mod: '".$mod."', num: ".$num.", linum: d.num, value: $('#value'+d.num), href: $('#href'+d.num)}});".
					"$('.link-add').find(':text:eq(1)').val('');$('.link-add').find(':text:eq(0)').val('');}});");
					chdir("softenowebtool");
				}
				break;
			case 'slideredit':
				$mod = $args['mod'];
				$num = $args['num'];
				if ($mod&&is_numeric($num)) {
					$content->addChild(new Div("", "imgsel"));
					$content->addChild(new script("", "$('#imgsel').imageselect({choosed: function(chosen) {if (fil) fil.val(chosen);}}); var fil = null;"));
					chdir("..");
					$mngr = PageManager::getCurrent();
					$page = $mngr->loadPage();
					$list = $page->listModuleApplications($mod);
					$node = $list[$num]["element"];
					$content->addChild($h1 = new HtmlElement("h1", false, "Endre slider"));
					$content->addChild($links = new table("table-display"));
					foreach ($node->getElementsByTagName("image") as $key => $lin) {
						$row = $links->addRow($fo = new Form("?site=slideredit&action=sliderimage&mod=".urlencode($mod)."&num=".$num));
						$fo->addChild($l = new Link("", "Bildeadresse: "));
						$fo->addChild($t = new Input("text", "src", $src = utf8_decode($lin->getAttribute("src"))));
						$row->addChild(new HtmlElement("td", false, $img = new Img($src)));
						$img->style = "max-height: 100px; max-width: 100px;";
						$fo->addChild(new Input("hidden", "linum", $key));
						$fo->addChild(new Input("submit", "", "Endre"));
						$fo->addChild(new Link("?site=slideredit&action=sliderimagedel&mod=".urlencode($mod)."&num=".$num."&linum=".$key, "slett", true));
						$l->generateId();
						$t->generateId();
						$content->script("$('#".$l->id."').click(function() {fil = $('#".$t->id."');$('#imgsel').dialog('open');});");
					}
					$row = $links->addRow($fo = new Form("?site=slideredit&action=sliderimage&mod=".urlencode($mod)."&num=".$num));
					$fo->addChild($l = new Link("", "Bildeadresse: "));
					$l->generateId();
					$fo->addChild($t = new Input("text", "src"));
					$t->generateId();
					$fo->addChild(new Input("submit", "", "Legg til"));
					$row->addChild(new HtmlElement("td"));
					$content->script("$('#".$l->id."').click(function() {fil = $('#".$t->id."');$('#imgsel').dialog('open');});");
					chdir("softenowebtool");
				}
				break;
			case 'editmodule':
				$mod = $args['mod'];
				if ($mod) {
					chdir("..");
					$mngr = PageManager::getCurrent();
					$page = $mngr->loadPage();
					$list = $page->listModuleApplications($mod);
					$properties = $page->getProperties();
					$hol = $content;
					$hol->addChild(new HtmlElement("h2", false, "Installerte applikasjoner"));
					$hol->addChild($ta = new table("table-display sort"));
					$script = new script();
					$cases = new chooser("case-chooser");
					$cases->addOption("none", "");
					foreach ($list as $i => $l) {
						switch ($l['name']) {
							case 'menu':
								$n = new Link("?site=menu&mod=".urlencode($mod)."&num=".$i, $l['name']);
								break;
							case 'logo':
								$n = new Link("?site=logoedit&mod=".urlencode($mod)."&num=".$i, $l['name']);
								break;
							case 'ad':
								$n = new Link("?site=adedit&mod=".urlencode($mod)."&num=".$i, $l['name']);
								break;
							case 'bluebox':
								$n = new Link("?site=blueboxedit&mod=".urlencode($mod)."&num=".$i, $l['name']);
								break;
							case 'titleslider':
								$n = new Link("?site=slideredit&mod=".urlencode($mod)."&num=".$i, $l['name']);
								break;
							default:
								$n = new Link("#", $l['name']);
								$n->generateId();
								$script->addChild("$('#".$n->id."').click(function(){\$('.appmodule-dia').dialog('open');$('.appmodule-dia').data('num', ".$i.");});");
							break;
						}
						$n->style = "color: #000";
						$case = $l['case'];
						if ($case) {
							if (!$cases->hasValue($case)) $cases->addOption($case, $case);
							$row = $ta->addRow($n, new Input("text", "case-".$i, $case), $slett = new Link("#", "slett"));
						} else {
							$row = $ta->addRow($n, $slett = new Link("#", "slett"));
						}
						$row->num = $i;
						$slett->class = "sort-del-".$i;
						$row->class = "sort-row-".$i;
						$script->addChild("$('.sort-del-".$i."').action({action: 'delapp', args: {mod: '".$mod."', num: ".$i."},".
						"confirm:'Vil du slette app?', callback:function(data) {\$('.sort-row-".$i."').fadeOut(1000).delay(1000).remove();}});");
					}
					$hol->addChild($script);
					$hol->addChild($but = new Button("Lagre", "lagre"));
					$but->style = "margin-top: 10px;";
					$hol->addChild($h1 = new HtmlElement("h2", false, "Legg til ny app i ".$mod));
					$hol->addChild($cases);
					$hol->addChild(new HtmlElement("h3", false, "Applikasjon"));
					$hol->addChild($sel = new table("table-display"));
					foreach ($properties['apps'] as $i => $p) {
						$n = new Link("#", $p['name']);
						$n->class = "appmodule-link";
						$n->mod = $mod;
						$n->app = $p['name'];
						$row = $sel->addRow($n);
						if (is_array($p['props'])) {
							foreach ($p['props'] as $prop) {
								$td = new HtmlElement("td", false, $prop['name']." (".$prop['type'].")");
								$row->addChild($td);
							}
						}
						if ($p['childs']) {
							foreach ($p['childs'] as $chi) {
								$td = new HtmlElement("th", false, $chi['name']);
								$row->addChild($td);
								foreach ($chi['props'] as $prop) {
									$td = new HtmlElement("td", false, $prop['name']." (".$prop['type'].")");
									$row->addChild($td);
								}
							}
						}
					}
					$hol->addChild($dia = new Div("appmodule-dia sow-styleform"));
					$dia->addChild(new HtmlElement("label", array("for" => "aname"), "Navn: "));
					$dia->addChild(new Input("text", "aname"));
					$dia->addChild(new HtmlElement("label", array("for" => "avalue"), "Verdi: "));
					$dia->addChild(new Input("text", "avalue"));
					$dia->addChild(new Link("#", "Til editeringsverktøy", array("class" => "edit-link")));
					$hol->script("$('.appmodule-dia').dialog({autoOpen: false, width: 400, title:'Sett opp verdier for applikasjonen', ".
					"buttons: {'Sett verdi':function(){var args = {num: $('.appmodule-dia').data('num'), mod:'".$mod."'};".
					"args[$('#aname').val()] = $('#avalue').val();$(this).action({auto: true, action:'modapp', args: args});}}});".
					"$('.appmodule-link').action({action:'appmodule', confirm:'Vil du legge til app?', args:{mod:function(){".
					"return this.attr('mod');},app:function(){return this.attr('app');},case:$('.case-chooser')},".
					"callback: function(d){\$('.edit-link').attr('href', d.edit?'?site='+encodeURIComponent(d.edit)+'&mod=".$mod."&num='+d.num:'#');".
					"$('.appmodule-dia').dialog('open');$('.appmodule-dia').data('num', d.num);}});$('#lagre').button().action({action:'modreorder',args:".
					"{mod: '".$mod."',apps: function() {var res = {};$('.sort tr').each(function(index)".
					"{res[$(this).attr('num')] = index;});return res;}}});$('.sort').sortable({items: 'tr'});");
					chdir("softenowebtool");
				}
				break;
		}
	}
}