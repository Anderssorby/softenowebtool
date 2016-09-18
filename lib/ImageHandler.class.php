<?php
/** * */
class ImageHandler implements Action, ModuleApp {
	protected $key;

	public function __construct($key) {
		$this->key = $key;
	}

	public function performAction($command, $args) {
		switch ($this->key) {
			case 'fetch-img':
				$start = addslashes($args['start']);
				$album = addslashes($args['album']);
				if (is_numeric($start)) {
					$arr = array();
					if ($album) {
						$query = "select `id`, `title` from `images` where `album` like '$album' limit $start, 12;";
						$resource = mysql_query($query);
						if (!$resource) {
							return array("message" => mysql_error());
						} else {
							while ($assoc[] = mysql_fetch_assoc($resource));
						}
					} else {
						$query = "select `id`, `title` from `images` limit $start, 12;";
						$resource = mysql_query($query);
						if (!$resource) {
							return array("message" => mysql_error());
						} else {
							while ($assoc[] = mysql_fetch_assoc($resource));
						}
					}
					foreach ($assoc as $key => $val) {
						$id = $val['id'];
						$src = "?site=bilde&id=$id";
						$title = addslashes($val['title']);
						$arr[$title]["id"] = $id;
						$arr[$title]["src"] = $src;
						$arr[$title]["title"] = $title;
					}
					return $arr;
				} else {
					return array("message" => "Argumenter stemmte ikke");
				}
				break;
			case 'lastbilde' :
				$fil = new FileManager("fil");
				$i = 0;
				while ($i < $fil -> getNumFiles()) {
					$f = $fil -> fetchUploadedFile($i, "image/*");
					if ($f) {
						$data = addslashes($f['data']);
						$name = addslashes($f['name']);
						$mime = addslashes($f['mime']);
						$query = "INSERT INTO `images` (`bilde`, `title`, `mime`) VALUES ('$data', '$name', '$mime')";
						$resource = mysql_query($query);
						if (!$resource) {
							return array("message" => 'kunne ikke gjøre query:' . mysql_error());
						} else {
							header("location: ?site=bilder");
						}
					} else {
						return array("message" => "fil passer ikke krav");
					}
					$i++;
				}
				break;
			case 'update-img':
				$name = $args['name'];
				$album = $args['album'];
				$id = $args['id'];
				if (is_numeric($id)) {
					$query = new Query();
					$result = $query->update("images", array("title" => $name,
					"album" => $album))->where("id", $id)->result();
					return array("message" => "Bilde oppdatert");
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'slettbilde' :
				$id = $args['id'];
				if (is_numeric($id)) {
					$query = "DELETE FROM `images` WHERE `images`.`id` = '$id' LIMIT 1 ;";
					$resource = mysql_query($query);
					if (!$resource) {
						return array("message" => mysql_error());
					} else {
						return array("message" => "Bilde slettet");
					}
				} elseif (is_array($id)) {
					$res = true;
					foreach ($id as $i) {
						if (is_numeric($i)) {
							$query = "DELETE FROM `images` WHERE `images`.`id` = $i LIMIT 1 ;";
							$resource = mysql_query($query);
							if (!$resource) {
								return array("message" => mysql_error());
							} else {
								$res = $res && true;
							}
						} else {
							$res = false;
						}
					}
					if ($res) {
						return array("message" => "Bilder slettet");
					} else {
						return array("message" => "Mangler argumenter");
					}
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
		}
	}

	public function createInstance($args) {
		$content = $args["_element"];
		switch ($this->key) {
			case 'images':
				$start = Data::get('start', 'i', 10) ? $_GET['start'] : 0;
				$show = Data::post('show', 's', 100) ? $_POST['show'] : "";
				$content->addChild(new HtmlElement("h1", false, "Bilder"));
				$content->addChild($imgsel = new Div("img-chooser"));
				$imgsel->generateId();
				$imgsel->addChild($script = new script(""));
				$header = new Div("img-chooser-head");
				$header->addChild(new Span("Viser ".($start+1)." til " . ($start + 30)));
				$imgsel->addChild($header);
				$form = new Form("?site=bilder&start=".$start, false);
				$form->class = "inline";
				$albsel = new chooser("", "show", "", true);
				$form->addChild($albsel);
				$form->addChild(new Button("Oppdater", "update", true));
				$header->addChild($form);
				$header->addChild($del = new Button("Slett", "del"));
				$del->addClass("img-chooser-action");
				$header->addChild($edt = new Button("Endre", "edit"));
				$edt->addClass("img-chooser-action");
				$albsel->addOption("Alle bilder", "");
				$query = new Query();
				$assoc = $query->select("album")->query()->assoc();
				$albums = array();
				foreach ($assoc as $a) {
					$albums[$a['name']] = $a['name'];
					$op = $albsel->addOption($a['name'], $a['name']);
					if ($a['name'] === $show) {
						$op->selected = "selected";
					}
				}
				$list = new HtmlList("img-chooser-list");
				$list->generateId();
				$result = $query->select("images")->limit(30, $start)->query()->assoc();
				$i = 0;
				foreach ($result as $i => $assoc) {
					$id = $assoc['id'];
					if (isset($albums[$show])) {
						$alb = $albums[$show];
						$isin = $alb===$assoc['album'];
					} else {
						$isin = true;
					}
					if ($isin) {
						$frame = $list->add($ifr = new Div("img-chooser-wrap"));
						$frame->class = "img-chooser-frame";
						$frame->id = "img-choos-".$i;
						$frame->value = $assoc['id'];
						$ifr->addChild($img = new Img($id, $id));
						$frame->addChild(new Span($result[$i]['title'], "img-chooser-name"));
					}
				}
				$imgsel->addChild($list);
				$script->addChild("$(function() {\$('#".$list->id."').selectable({filter: 'li',".
				"selected: function(){\$('.img-chooser-action').show();}, unselected: function(){\$('.img-chooser-action').hide();}});".
				"$('#del').action({action:'slettbilde', args:{id:function(){var res = {};".
				"$('.img-chooser-list .ui-selected').each(function(i) {res['id'+i]=$(this).val();});".
				"return res;}}, confirm: 'Vil du slette valgte bilder?', callback: function(){".
				"$('.img-chooser-list .ui-selected').remove();}});});");
				$imgsel->addChild($foot = new Div("img-chooser-foot"));
				$for = $start - 30;
				if ($for >= 0)
				$foot->addChild($pre = new Link("?site=bilder&start=".$for, "Forrige", array("class" => "sow-button")));
				if ($i+1>=30)
				$foot->addChild($nes = new Link("?site=bilder&start=".($start + 30), "Neste", array("class" => "sow-button")));
				$imgsel->addChild($form = new Form("?site=bilder&action=lastbilde"));
				$form->id = "upform";
				$last = new Input("file", "fil");
				$last->accept = "image/*";
				$last->multiple = "true";
				$lbut = new Button("Last opp", "", true);
				$form->addChild($last);
				$form->addChild($lbut);
				$imgsel->addChild(new Div("sow-progress"));
				$form->script("$('#upform').submit(function() {\$('.sow-progress').fadeIn(400);});");
				break;
		}
	}

}