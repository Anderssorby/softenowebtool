<?php
class UserHandler implements ModuleApp, Action {
	protected $key;
	public function __construct($key) {
		$this->key = $key;
	}

	public function performAction($command, $args) {
		switch ($this->key) {
			case 'newuser' :
				$bruker = addslashes($args['bruker']);
				$pass = addslashes($args['pass']);
				if ($args['pass']!=$args['pass2']||!$pass||!$bruker) {
					return array("message" => "ikke like passord");
				}
				$query = "INSERT INTO `users` ( `username` , `password`) VALUES ('$bruker', '$pass');";
				$resource = mysql_query($query);
				if (!$resource) {
					return array("message" => 'kunne ikke gjøre query:' . mysql_error());
				} else {
					return array("message" => "Bruker opprettet");
				}
				break;
			case "user-setcategory":
				$id = $args['id'];
				$categories = $args['categories'];
				if (is_numeric($id)&&is_array($categories)) {
					$user = new User($id);
					$cates = $user->getCategories();
					foreach ($cates as $cate) {
						if ($categories[$cate]) {
							$user->addCategory($cate);
						} else {
							$user->removeCategory($cate);
						}
					}
					$user->saveUserData();
					return array("message" => "Tillatelser oppdatert", "categories" => $categories);
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'user-update-pass' :
				$id = addslashes($args["id"]);
				$pass = addslashes($args["pass"]);
				$pass2 = addslashes($args["pass2"]);
				if (is_numeric($id)) {
					if ($pass != $pass2) {
						return array("message" => "Ikke like passord");
					}
					$query = "update `users` set `password` = '$pass' where `id` like '$id' limit 1";
					$resource = mysql_query($query);
					if (!$resource) {
						echo 'kunne ikke gjøre query: ' . mysql_error();
					} else {
						return array("message" => "Passord endret");
					}
				}
				break;
			case 'deluser' :
				$id = addslashes($args['id']);
				if (is_numeric($id)) {
					$query = "DELETE FROM `users` WHERE `users`.`id` = '$id' LIMIT 1;";
					$resource = mysql_query($query);
					if (!$resource) {
						echo 'kunne ikke gjøre query:' . mysql_error();
					} else {
						return array("message" => "Bruker slettet");
					}
				}
				break;
			case 'setconnection':
				$database = $args['database'];
				$host = $args['host'];
				$user = $args['user'];
				$pass = $args['pass'];
				if ($database&&$host&&$user&&$pass) {
					$connection = new Connection($host, $user, $pass, $database);
					if ($error = $connection->getError()) {
						switch ($error) {
							case 'connect':
								return array("message" => "Klarte ikke koble til");
								break;
							case 'database':
								return array("message" => "Ikke gyldig database");
								break;
						}
					} else {
						//softenowebtools config
						$page = new Page(true);
						$config = $page->getConfig();
						$mysql = $config->getElementsByTagName("mysql")->item(0);
						$connect = $mysql->getElementsByTagName("connect")->item(0);
						$connect->setAttribute("host", $host);
						$connect->setAttribute("username", $user);
						$connect->setAttribute("password", $pass);
						$connect->setAttribute("database", $database);
						$page->saveConfig();
						//site config
						chdir("..");
						$page = new Page(true);
						$config = $page->getConfig();
						$mysql = $config->getElementsByTagName("mysql")->item(0);
						$connect = $mysql->getElementsByTagName("connect")->item(0);
						$connect->setAttribute("host", $host);
						$connect->setAttribute("username", $user);
						$connect->setAttribute("password", $pass);
						$connect->setAttribute("database", $database);
						$page->saveConfig();
						chdir("softenowebtool");
						return array("message" => "Database kontrolert og endret suksessfullt.");
					}
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
		}
	}

	public function createInstance($args) {
		$content = $args["_element"];
		$document = $args["_document"];
		$style = $args["_style"];
		switch ($this->key) {
			case 'users':
				$user = User::getUser();
				$content->addChild($left = new Div(array("style" => "width: 50%;float:left;")));
				$left->addChild(new HtmlElement("h1", false, "Brukere"));
				$content->style = "display: inline-block; width: 100%;";
				$dataReader = new DataReader("users");
				$left->addChild($ald = new Div("sow-styleform", "aldia"));
				foreach ($user->getCategories() as $category) {
					$ald->addChild($cat = new Input("checkbox", "cat-".$category, $category));
					$cat->addClass("userald");
					$ald->addChild(new HtmlElement("label", array("for" => "cat-".$category), $category));
				}
				$left->script("$('#aldia').dialog({autoOpen: false, width: 400, title: 'Endre tillatelser', buttons:{".
				"'Ok':function() {\$(this).action({action: 'user-setcategory',auto:true, args:{".
				"categories:function(){var re={};$('.userald').each(function(){if($(this).attr('checked'))".
				"re[$(this).val()]=$(this).val()});return re;}, id: $('#aldia').data('uid')},callback:function(d){".
				"}});$('#aldia').dialog('close');},".
				"'Avbryt':function(){\$('#aldia').dialog('close');}}});");
				$left->addChild($dia = new Div("sow-styleform", "dia"));
				$dia->addChild(new HtmlElement("label", array("for" => "upass"), "Nytt passord:"));
				$dia->addChild(new Input("password", "upass"));
				$dia->addChild(new HtmlElement("label", array("for" => "upass2"), "Gjenta passord:"));
				$dia->addChild(new Input("password", "upass2"));
				$left->script("$('#dia').dialog({autoOpen: false, width: 400, title: 'Endre passord', buttons:{".
				"'Ok':function() {\$(this).action({action: 'user-update-pass',auto:true, args:{".
				"pass: $('#upass'), pass2: $('#upass2'), id: $('#dia').data('uid')}});$('#dia').dialog('close');},".
				"'Avbryt':function(){\$('#dia').dialog('close');}},beforeClose:function(){\$('#upass').val('');$('#upass2').val('');}});");
				$left->addChild($table = new table("table-display"));
				foreach ($dataReader->assoc as $key => $value) {
					$use = new User($value['id']);
					$alo = new Link("#", "Endre tillatelser", array("id" => "alo".$key));
					$pas = new Link("#", "Endre passord", array("id" => "pas".$key));
					$sle = new Link("#", "Slett", array("id" => "del".$key));
					$row = $table->addRow($value["username"], $alo, $pas, $sle);
					$row->id = "sow-user-".$key;
					$str = "";
					foreach ($user->getCategories() as $cate) {
						if ($use->isPermitted($cate)) {
							$str .= "$('#cat-".$cate."').attr('checked', 'checked');";
						} else {
							$str .= "$('#cat-".$cate."').removeAttr('checked');";
						}
					}
					$left->script("$('#".$alo->id."').click(function(){\$('#aldia').data('uid', ".$value['id'].").dialog('open');".$str."});");
					$left->script("$('#".$pas->id."').click(function(){\$('#dia').data('uid', ".$value['id'].").dialog('open');});");
					$left->script("$('#".$sle->id."').action({action: 'deluser', args: {id: ".$value['id']."}, confirm: 'Vil du slette bruker?',".
					"callback: function(){\$('".$row->id."').remove();}});");
				}
				$left->addChild(new HtmlElement("h2", false, "Ny bruker"));
				$nybr = new Form("?site=users&action=newuser", false);
				$nybr->id = "newusr";
				$nybr->class = "sow-styleform";
				$nybr->addChild(new HtmlElement("label", array("for" => "bruker"), "Brukernavn: "));
				$nybr->addChild(new Input("text", "bruker"));
				$nybr->addChild(new HtmlElement("label", array("for" => "pass"), "Passord: "));
				$nybr->addChild(new Input("password", "pass"));
				$nybr->addChild(new HtmlElement("label", array("for" => "pass2"), "Gjenta passord: "));
				$nybr->addChild(new Input("password", "pass2"));
				$nybr->addChild($sub = new Input("submit", "", "Opprett"));
				$sub->class = "sow-button";
				$left->addChild($nybr);
				$left->script("$('#newusr').action({action: 'newuser', args: {bruker: $('#bruker'), pass: $('#pass'), pass2: $('#pass2')}});");
				$left->addChild($on = new Div());
				$on->addChild(new HtmlElement("h2", false, "Pålogget"));
				$on->addChild($list = new HtmlList());
				$resource = mysql_query("select `username`, `last` from `users` where `last` >= NOW()-600");
				if (!$resource) {
					$content->addChild(mysql_error());
				} else {
					while ($ass = mysql_fetch_assoc($resource)) {
						$list->add($ass['username']." sist aktiv ".$ass['last']);
					}
				}
				$content->addChild($usr = new Div());
				$usr->style = "position: relative; float: right; width: 50%;";
				$usr->addChild(new HtmlElement("h2", false, "Brukerlogg"));
				$usr->addChild($list = new table("table-display"));
				foreach ($user->getActionLog() as $action) {
					$list->addRow($action->getAttribute("name"),
					date("H:i:s d.m.Y", $action->getAttribute("time")));
				}
				break;
			case 'config':
				$content->addChild(new HtmlElement("h1", false, "Administrer"));
				$content->addChild($left = new Div());
				$left->style = "float:left;width:50%;";
				$left->addChild(new HtmlElement("h2", false, "Database"));
				$page = new Page();
				$properties = $page->getProperties();
				$config = $page->getConfig();
				$mysql = $config->getElementsByTagName("mysql")->item(0);
				$connect = $mysql->getElementsByTagName("connect")->item(0);
				$left->addChild(new HtmlElement("p", array("class" => "sow-warning"),
				"ADVARSEL! Ikke gjør endringer her hvis du ikke vet hva du gjør. Det kan få siden til ikke å virke."));
				$left->addChild($wrap = new Div("sow-styleform"));
				$wrap->addChild(new HtmlElement("label", array("for" => "datb"), "Database: "));
				$wrap->addChild(new Input("text", "datb", $connect->getAttribute("database")));
				$wrap->addChild(new HtmlElement("label", array("for" => "host"), "Vert: "));
				$wrap->addChild(new Input("text", "host", $connect->getAttribute("host")));
				$wrap->addChild(new HtmlElement("label", array("for" => "user"), "Bruker: "));
				$wrap->addChild(new Input("text", "user", $connect->getAttribute("username")));
				$wrap->addChild(new HtmlElement("label", array("for" => "pass"), "Passord: "));
				$wrap->addChild(new Input("password", "pass", $connect->getAttribute("password")));
				$wrap->addChild(new Input("button", "upd", "Oppdater"));
				$wrap->script("$('#upd').action({action:'setconnection', args:".
				"{database:$('#datb'), host:$('#host'), user:$('#user'), pass:$('#pass')}});");

				$libraries = $properties["libraries"];
				if ($libraries) {
					$left->addChild(new HtmlElement("h2", false, "Plugins"));
					$left->addChild($table = new table("table-display"));
					foreach ($libraries as $key => $lib) {
						$name = $lib["name"];
						$conf = $lib["config"];
						$table->addRow($name, $conf);
					}
				}

				$left->addChild(new HtmlElement("h2", false, "Modulapplikasjoner"));
				$left->addChild($table = new table("table-display"));
				$apps = $properties["apps"];
				foreach ($apps as $key => $app) {
					$name = $app["name"];
					$cate = $app["category"];
					$class = $app["class"];
					$edit = $app["edit"];
					$table->addRow($name, $cate, $class, $edit);
				}

				$content->addChild($right = new Div());
				$right->style = "float:right;width:50%;";

				$right->addChild(new HtmlElement("h2", false, "Actions"));
				$right->addChild($table = new table("table-display"));
				$actions = $properties["actions"];
				foreach ($actions as $key => $action) {
					$name = $key;
					$access = $action["access"];
					$category = $action["category"];
					$class = $action["class"];
					$table->addRow($name, $access, $category, $class);
				}
				break;
		}
	}
}