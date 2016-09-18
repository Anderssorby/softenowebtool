<?php
/**
 *
 * Basic edit applications
 * @author anders
 *
 */
class EditSite extends SimpleSite implements Action {
	protected $key;
	public function __construct($key) {
		$this->key = $key;
	}

	public function performAction($command, $args) {
		switch ($this->key) {
			case 'loggut' :
				User::getUser()->loggout();
				break;
			case 'readtable' :
				$table = $args['id'];
				$start = $args['from'];
				$ftext = $args['ftext']==="true"?true:false;
				$query = new Query();
				if ($table&&is_numeric($start)&&$query->tableExists($table)) {
					$fields = $query->showColumns($table)->query()->row();
					$assoc = $query->select($table)->limit(30, $start)->query()->assoc();
					$array = array();
					if ($assoc) {
						foreach ($assoc as $l => $ass) {
							foreach ($fields as $i => $field) {
								$name = $field[0];
								$type = $field[1];
								switch ($type) {
									case 'longblob' :
									case 'mediumblob' :
									case 'tinyblob' :
									case 'blob':
										$array[$l][$name] = "file";
										break;
									case 'longtext' :
									case 'mediumtext' :
									case 'tinytext' :
									case 'text' :
										if ($ftext) {
											$array[$l][$name] = $ass[$name];
										} else {
											$array[$l][$name] = substr($ass[$name], 0, 10)."...";
										}
										break;
									default:
										$array[$l][$name] = $ass[$name];
									break;
								}
							}
						}
					}
					//$array[0]["tabell"] = "Tabell er tom";
					return $array;
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'mail-get-message':
				if ($args['userid']&&$args['pass']) {
					$ref = "{".($args['server'] ? $args['server'] : "imap.softeno.com").":143/novalidate-cert}INBOX";
					$resource = imap_open($ref, $args['userid'], $args['pass']);
				}
				break;
			case 'login':
				if ($args['bruker']) {
					$log = User::getUser();
					$log->update($args['bruker'], $args['passord']);
					$loggedin = $log->isLoggedIn();
					if (!$loggedin) {
						return array("_data" => array("login-status" => "Brukernavn eller passord er ikke riktig"));
					}
				} else {
					return array("_data" => array("login-status" => "Brukernavn ikke oppgitt"));
				}
				break;
		}
	}

	function createInstance($args) {
		$content = $args["_element"];
		$document = $args["_document"];
		$data = $args["_data"];
		switch ($this->key) {
			case "logbox":
				$args["_element"] -> addChild($logbox = new Div("logbox", "logbox"));
				$logbox -> addChild($list = new HtmlElement("h3", false, "Pålogget"));
				$logbox -> addChild($list = new HtmlList());
				$resource = mysql_query("select `username`, `last` from `users` where `last` >= NOW()-600");
				if (!$resource) {
					$content->addChild(mysql_error());
				} else {
					while ($ass = mysql_fetch_assoc($resource)) {
						$li = $list->add($ass['username']);
						$li->title = "Sist aktiv {$ass['last']}";
					}
				}
				break;
			case 'siteindicator':
				$content->addChild(new Span("Til hjemmesiden: "));
				$content->addChild($l = new Link("..", $_SERVER['SERVER_NAME']));
				$content->style = "position: absolute;right: 5px;top: 5px;";
				break;
			case 'main':
				$document -> head -> linkStyleSheet("css/HovedNav.css");

				$intro = new Div();
				$h1 = new HtmlElement("h1", array("align" => "center"), "Velkommen til SoftenoWebTools!");
				$intro -> addChild($h1);
				$intro -> addChild($p = new HtmlElement("p", false, "SoftenoWebTools er redigeringsverktøyet til hjemmesiden din.".
		' Her kan du blandt annet skrive artikkler, laste opp bilder og filer, endre oppsettet av siden eller administrere brukere. Les')); 
				$p -> addChild(new Link("brukerveiledning.pdf", "kom i gang!"));
				$p -> addChild('for å få en introduksjon.');
				$content -> addChild($intro);
				$content->addChild($hol = new HtmlElement("div", array("class" => "sow-nav-hol")));

				$artbox = new Div("sow-nav-box sow-nav-oppslag");
				$h3 = new HtmlElement("h3", false, "Oppslag");
				$artbox -> addChild($h3);
				$ul = new HtmlList();
				$ul -> add(new Link("?site=nyartikkel", "", array("class" => "sow-nav-article", "title" => "Opprett en ny artikkel")));
				$ul -> add(new Link("?site=articles", "", array("class" => "sow-nav-articles", "title" => "Artikkler")));
				$ul -> add(new Link("?site=editinfo", "", array("class" => "sow-nav-newinfo", "title" => "Ny infoside")));
				$ul -> add(new Link("?site=infosites", "", array("class" => "sow-nav-infosites", "title" => "Infosider")));
				$artbox -> addChild($ul);
				$hol -> addChild($artbox);

				$artbox = new Div("sow-nav-box sow-nav-innhold");
				$h3 = new HtmlElement("h3", false, "Innhold");
				$artbox -> addChild($h3);
				$ul = new HtmlList();
				$ul -> add(new Link("?site=filer", "", array("class" => "sow-nav-files", "title" => "Last opp og behandle filer")));
				$ul -> add(new Link("?site=bilder", "", array("class" => "sow-nav-images", "title" => "Last opp og behandle bilder")));
				$ul -> add(new Link("?site=calendar", "", array("class" => "sow-nav-calendar", "title" => "Kalender")));
				$artbox -> addChild($ul);
				$hol -> addChild($artbox);

				$artbox = new Div("sow-nav-box sow-nav-admin");
				$h3 = new HtmlElement("h3", false, "Administrer");
				$artbox -> addChild($h3);
				$ul = new HtmlList();
				$ul -> add(new Link("?site=structure", "", array("class" => "sow-nav-structure", "title" => "Struktur")));
				$ul -> add(new Link("?site=users", "", array("class" => "sow-nav-users", "title" => "Brukere")));
				$ul -> add(new Link("?site=config", "", array("class" => "sow-nav-config", "title" => "Instillinger")));
				$artbox -> addChild($ul);
				$hol -> addChild($artbox);
				break;
			case "tabell":
				$content->addChild(new HtmlElement("h1", false, "Tabeller"));
				$content->addChild($tools = new Div(""));
				$chooser = new chooser("", "", "tb");
				$query = new Query();
				$result = $query->showTables()->query()->row();
				foreach ($result as $tab) {
					$chooser->addOption($tab[0], $tab[0]);
				}
				$tools->addChild($chooser);
				$tools->addChild(new Input("checkbox", "ftext", "true"));
				$tools->addChild(new HtmlElement("label", array("for" => "ftext"), "Full tekst"));
				$tools->addChild(new Button("Les tabell", "subm"));
				$tools->addChild(new Span("", "status"));
				$con = new Div("", "con");
				$con->addChild(new table("database-table table-display"));
				$content->addChild($con);
				$content->script("$('#subm').action({action: 'readtable', before: function(){\$('.status').text('laster');}, ".
				"args: {from: 0, id: $('#tb'), ftext: function() {return $('#ftext').is(':checked')?'true':'false';}},".
				"callback: function (d) {if(typeof d == 'string'){alert(d.substring(0, 30));return;}var th = $('<tr></tr>');".
				"$('.database-table').empty().append(th);for (var a in d) {var tr = $('<tr></tr>');$('.database-table').append(tr);".
				"for (var k in d[a]) {if (a==0) $('<th></th>').text(k).appendTo(th);$('<td></td>').appendTo(tr).text(d[a][k]);}}$('.status').text('ferdig');}});");
				break;
			case 'mail':
				$form = new Form("?site=mail");
				$form->addInput("Bruker: ", new Input("text", "userid", $_POST['userid']));
				$form->addInput("Passord: ", new Input("password", "pass", $_POST['pass']));
				$form->addInput("Server: ", new Input("text", "server", $_POST['server']));
				$form->addChild(new Input("submit", "", "hent"));
				$content->addChild($form);
				$content->addChild($holder = new Div("qmail"));
				$holder->addChild($left = new Div("qmail-left"));
				$holder->addChild($right = new Div("qmail-right"));
				if ($_POST['userid']&&$_POST['pass']) {
					$ref = "{".($_POST['server'] ? $_POST['server'] : "imap.softeno.com").":143/novalidate-cert}INBOX";
					$resource = imap_open($ref, $_POST['userid'], $_POST['pass']);
					$folders = imap_list($resource, $ref, "*");
					if ($folders == false) {
						$left->addChild("Call failed");
					} else {
						$left->addChild($maplist = new HtmlList("qmail-list"));
						foreach ($folders as $val) {
							$maplist->add($val);
						}
					}
					$num = imap_num_msg($resource);
					$messages = imap_fetch_overview($resource, "1:$num");
					$right->addChild($mails = new HtmlList("qmail-list qmail-maillist"));
					foreach ($messages as $key => $msg) {
						$li = $mails->add(new Span($msg->from));
						$li->addChild(new Link("?site=mail", $msg->subject));
						$li->addChild(new Span($msg->date));
					}
				}
				break;
			case 'noaccess':
				$content->addClass("sow-warning");
				$content->addChild("Du har ikke tilgang til denne siden. #".$args['_name']);
				break;
			case 'login':
				$document->head->setTitle("Logg inn på Softenowebtools");
				$center = new Div("logginbox", "logbox");
				$titel = new HtmlElement("h1");
				$titel->setAttribute("align", "center");
				$titel->addChild("Logg inn");
				$center->addChild($titel);
				if ($status = $data["login-status"]) {
					$center->addChild($err = new Div("sow-warning"));
					$err->addChild($status);
				}
				$form = new Form("?action=login", false);
				$form->addChild($b = new Div());
				$b->addChild(new HtmlElement("label", array("for" => "bruker"), "Brukernavn"));
				$b->addChild(new Input("text", "bruker", $_POST['bruker']));
				$form->addChild($f = new Div());
				$f->addChild(new HtmlElement("label", array("for" => "passord"), "Passord"));
				$f->addChild(new Input("password", "passord"));
				$subm = new Input("submit", "subm", "Logg inn");
				$form->addChild($subm);
				$center->addChild($form);
				$center->addChild($script = new script());
				$script->addChild("$('#logbox').draggable({handle: 'h1', cursor: 'move'});");
				$content->addChild($center);
				break;
		}
	}
}