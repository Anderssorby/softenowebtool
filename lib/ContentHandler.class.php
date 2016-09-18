<?php
class ContentHandler implements ModuleApp, Action {
	protected $key;
	public function __construct($key) {
		$this->key = $key;
	}

	public function performAction($command, $args) {
		switch ($this->key) {
			case 'lagreinfo' :
				$titel = utf8_decode(addslashes($args['titel']));
				$display = utf8_decode(addslashes($args['display']));
				$id = utf8_decode(addslashes($args['id']));
				if ($titel || $display) {
					if (!is_numeric($id)) {
						$art_query = "INSERT INTO `info` ( `name` , `display` , `last` ) VALUES ('$titel', '$display', NOW( ) ); ";
						$resource = mysql_query($art_query);
						if (!$resource) {
							echo 'kunne ikke gjøre query: ' . mysql_error();
						}
						$art_query = "SELECT LAST_INSERT_ID();";
						$resource = mysql_query($art_query);
						if (!$resource) {
							echo 'kunne ikke gjøre query: ' . mysql_error();
						}
						$result = mysql_fetch_array($resource);
						$id = $GLOBALS['infoid'] = $result[0];
						return array("id" => $id);
					} else {
						$query = "update `info` set `name` = '$titel', `display` = '$display', `last` = NOW( ) where `id` like '$id'";
						$resource = mysql_query($query);
						if (!$resource) {
							echo 'kunne ikke gjøre query: ' . mysql_error();
						}
						return array("id" => $id);
					}
				}
				break;
			case 'apneinfo' :
				$id = addslashes($args['id']);
				if (is_numeric($id)) {
					$art_query = "select * from `info` where `id` like '$id' limit 1;";
					$resource = mysql_query($art_query);
					if (!$resource) {
						echo 'kunne ikke gjøre query:' . mysql_error();
					}
					$row = mysql_fetch_array($resource);
					$data = array();
					$data['titel'] = $GLOBALS['titel'] = $row['name'];
					$data['display'] = $GLOBALS['display'] = $row['display'];
					$data['infoid'] = $GLOBALS['infoid'] = $row['id'];
					return array("_data" => $data);
				}
				break;
			case 'slettinfo' :
				$id = addslashes($args['id']);
				if (is_numeric($id)) {
					$art_query = "DELETE FROM `info` WHERE `info`.`id` = '$id' LIMIT 1 ;";
					$resource = mysql_query($art_query);
					if (!$resource) {
						echo mysql_error();
					} else {
						return array("message" => "Infoside slettet");
					}
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'slettart' :
				$id = addslashes($args["id"]);
				if (is_numeric($id)) {
					$art_query = "DELETE FROM `articles` WHERE `articles`.`id` = '$id' LIMIT 1 ;";
					$resource = mysql_query($art_query);
					if (!$resource) {
						return array("message" => 'Kunne ikke gjøre query:'.mysql_error());
					} else  {
						return array("message" => "Artikkel slettet");
					}
				} else {
					return array("message" => "Artikkel ble ikke slettet");
				}
				break;
			case 'apneart' :
				$id = addslashes($args['id']);
				if (is_numeric($id)) {
					$art_query = "select * from `articles` where `id` like '$id' limit 1;";
					$resource = mysql_query($art_query);
					if (!$resource) {
						echo mysql_error();
					}
					$row = mysql_fetch_array($resource);
					$data = array();
					$data['arttitel'] = $GLOBALS['arttitel'] = $row['titel'];
					$data['artbrod'] = $GLOBALS['artbrod'] = $row['brodtekst'];
					$data['artcontent'] = $GLOBALS['artcontent'] = $row['innhold'];
					$data['artid'] = $GLOBALS['artid'] = $row['id'];
					$data['public'] = $GLOBALS['public'] = $row['public'];
					return array("_data" => $data);
				}
				break;
			case 'lagreart' :
				$head = utf8_decode(addslashes($args['head']));
				$brod = utf8_decode(addslashes($args['brod']));
				$content = utf8_decode(addslashes($args['content']));
				$id = addslashes($args['id']);
				$public = $args['public']==='yes' ? 'yes' : 'no';
				if ($head || $brod || $content) {
					if (!is_numeric($id)) {
						$art_query = "INSERT INTO `articles` (`titel`, `brodtekst`, `innhold`, `dato`, `public`) VALUES ('$head', '$brod', '$content', NOW(), '$public'); ";
						$resource = mysql_query($art_query);
						if (!$resource) {
							echo 'kunne ikke gjøre query: ' . mysql_error();
						}
						$art_query = "SELECT LAST_INSERT_ID();";
						$resource = mysql_query($art_query);
						if (!$resource) {
							echo 'kunne ikke gjøre query: ' . mysql_error();
						}
						$result = mysql_fetch_array($resource);
						$id = $result['id'];
						return array("id" => $id);
					} else {
						$query = "update `articles` set `titel` = '$head', `brodtekst` = '$brod', `innhold` = '$content', `public` = '$public' where `id` like '$id'";
						$resource = mysql_query($query);
						if (!is_resource($resource)) {
							echo mysql_error();
						}
						return array("id" => $id);
					}
				}
				return array("message" => "Manglende argumenter");
				break;
		}
	}

	public function createInstance($args) {
		$content = $args["_element"];
		$document = $args["_document"];
		$style = $args["_style"];
		switch ($this->key) {
			case 'infosites':
				$content->addChild(new HtmlElement("h1", false, "Infosider"));
				$dataReader = new DataReader("info");
				$content->addChild($table = new table("table-display"));
				foreach ($dataReader->assoc as $key => $row) {
					$r = array();
					$r[] = new Span($row['name']);
					$r[] = new Span($row['last']);
					$r[] = new Link("?site=editinfosite&action=apneinfo&id=".$row['id'], "Rediger");
					$r[] = $slett = new Link("", "Slett");
					$slett->class = "info-del";
					$slett->del = $row['id'];
					$table->addRow($r);
				}
				$content->addChild(new Link("?site=editinfosite", "Ny infoside", array("id" => "newinfo")));
				$content->addChild(new script("", "$('#newinfo').button();$('.info-del').action({action: 'slettinfo', args: {id: function() {return $(this).attr('del');}},".
				" confirm: 'Vil du slette denne infosiden?', callback: function(data) {\$(this).parent().parent().remove();}});"));
				break;
			case 'editinfosite':
				$infoid = $args['id'];
				if (!$infoid) {
					$query = "INSERT INTO `info` (`name` , `last`) VALUES ('ny infoside', NOW()); ";
					$resource = mysql_query($query);
					if (!$resource) {
						echo 'kunne ikke gjøre query: ' . mysql_error();
						exit();
					}
					$query = "SELECT LAST_INSERT_ID();";
					$resource = mysql_query($query);
					if (!$resource) {
						echo 'kunne ikke gjøre query: ' . mysql_error();
						exit();
					}
					$result = mysql_fetch_row($resource);
					$infoid = $result[0];
					//fix reload issue
					header("location: ?site=editinfosite&action=apneinfo&id=$infoid");
				}
				$p = new HtmlElement("p");
				$content -> addChild($p);
				$p -> addChild("Titel: ");
				$tit = new Input("text", "titel");
				global $titel;
				$tit -> setValue($titel);
				$tit->class = "title-input";
				$p -> addChild($tit);
				$editor = new Editor("display", false);
				$editor -> addContent($GLOBALS['display']);
				$content -> addChild($editor);
				$hid = new Input("hidden", "id");
				$hid -> setValue($infoid);
				$content -> addChild($hid);
				$subm = new Input("button", "save", "lagre");
				$subm -> class = "button-big";
				$content -> addChild($subm);
				$content -> addChild($span = new HtmlElement("span", false));
				$span -> id = "result";
				$content -> addChild($script = new script());
				$script->addChild('$(function() {'.
						'$("#save").action({action: "lagreinfo",'.
						'args: {
						display: function() {return $("#'.$editor->editid.'").richtextedit("value");},'.
						'id: '.$infoid.',
						titel: $("#titel")},
						callback: function(data) {'.
				 	'$("#result").text("Lagring fullført.").show("fast");'.
					'window.setTimeout(function () {$("#result").hide("fast");}, 3000);}});});');
				break;
			case 'editarticle':
				$id = $args['id'];
				if (!$id) {
					$art_query = "INSERT INTO `articles` (`titel`, `dato`, `public`) VALUES ('ny artikkel', NOW(), 'no'); ";
					$resource = mysql_query($art_query);
					if (!$resource) {
						echo 'kunne ikke gjøre query: ' . mysql_error();
					}
					$art_query = "SELECT LAST_INSERT_ID();";
					$resource = mysql_query($art_query);
					if (!$resource) {
						echo 'kunne ikke gjøre query: ' . mysql_error();
					}
					$result = mysql_fetch_row($resource);
					$id = $result[0];
					//fix reload issue
					header("location: ?site=nyartikkel&action=apneart&id=$id");
				}
				$submit = new Input("button", "lagre", "lagre");
				$submit->class = "button-big";
				global $public;
				$val = $public=='yes';
				$content->addChild($label = new HtmlElement("label", false, "Publisér "));
				$label->addChild($pub = new Input("checkbox", "public", "yes"));
				if ($val) $pub->checked = $val;
				$content->addChild($submit);
				$content->addChild($span = new HtmlElement("span", false));
				$span->id = "result";
				$p = new HtmlElement("p");
				$content -> addChild($p);
				$p->addChild("Overskrift: ");
				$tit = new Input("text", "head");
				$tit->class = "title-input";
				global $arttitel;
				$tit->setValue($arttitel);
				$p->addChild($tit);
				$p->addChild(new HtmlElement("h3", false, "Ingress"));
				$editor = new Editor("brod", false);
				$editor -> addContent($GLOBALS['artbrod']);
				$content -> addChild($editor);
				$content -> addChild(new HtmlElement("h3", false, "Brødtekst"));
				$editor2 = new Editor("content", false);
				$editor2 -> addContent($GLOBALS['artcontent']);
				$content -> addChild($editor2);
				$content -> addChild($script = new script());
				$script->addChild('$(function() {
						$("#lagre").action({action: "lagreart",
						args: {
							brod: function() {return $("#'.$editor->editid.'").richtextedit("value");},
							content: function() {return  $("#'.$editor2->editid.'").richtextedit("value");},
							id: '.$id.',
							head: $("#head"),
							public: $("#public")
						},
						message: false,
						callback: function(data) {
						var res = "Lagring fullført. ("+data.id+")";
					$("#result").show("fast").text(res);
					window.setTimeout(function () {$("#result").hide("fast");}, 3000);
				}
		});});');
				break;
			case 'articlemanager':
				$content->addChild(new HtmlElement("h1", false, "Artikler"));
				$dataReader = new DataReader("articles", 0, 100);
				$content->addChild($table = new table("table-display"));
				foreach ($dataReader->assoc as $key => $row) {
					$r = array();
					$r[] = new Span($row['titel']);
					$r[] = new Span($row['dato']);
					$r[] = new Span("Publisért: ".($row['public']==='yes'?'ja':'nei'));
					$r[] = new Link("?site=nyartikkel&action=apneart&id=".$row['id'], "Rediger");
					$r[] = $slett = new Link("", "Slett");
					$slett->class = "art-del";
					$slett->del = $row['id'];
					$table->addRow($r);
				}
				$content->addChild(new Link("?site=nyartikkel", "Ny artikkel", array("id" => "newart")));
				$content->addChild(new script("", "$('#newart').button();$('.art-del').action({action: 'slettart', args: {id: function() {return $(this).attr('del');}},".
				" confirm: 'Vil du slette denne artikkelen?', callback: function(data) {\$(this).parent().parent().remove();}});"));
				break;
		}
	}
}