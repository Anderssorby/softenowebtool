<?php
class FileHandler implements ModuleApp, Action {
	protected $key;
	public function __construct($key) {
		$this->key = $key;
	}

	public function performAction($command, $args) {
		switch ($this->key) {
			case 'file-rename':
				$id = $args['id'];
				$name = $args['name'];
				if (is_numeric($id)&&is_string($name)) {
					$name = addslashes($name);
					$query = "update `archive` set `name` = '$name' where `id` = '$id'";
					$resource = mysql_query($query);
					if (!$resource) {
						return array("message" => "Kunne ikke gjøre query: ".mysql_error());
					} else {
						return array("message" => "Fil heter nå $name", "name" => $name);
					}
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'slettfil' :
				$id = $args['id'];
				if (is_numeric($id)) {
					$query = "DELETE FROM `archive` WHERE `archive`.`id` = '$id' LIMIT 1 ;";
					$resource = mysql_query($query);
					if (!$resource) {
						return array("message" => 'kunne ikke gjøre query: ' . mysql_error());
					} else {
						return array("message" => "Fil slettet");
					}
				} elseif (is_array($id)) {
					$res = true;
					foreach ($id as $i) {
						if (is_numeric($i)) {
							$query = "DELETE FROM `archive` WHERE `archive`.`id` = '$i' LIMIT 1 ;";
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
						return array("message" => "Filer slettet");
					} else {
						return array("message" => "Mangler argumenter");
					}
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case 'lastfil' :
				$fil = new FileManager("fil");
				if (true) {
					$i = 0;
					while ($i < $fil -> getNumFiles()) {
						$f = $fil -> fetchUploadedFile($i);
						if ($f) {
							$fil_query = "INSERT INTO `archive` (`data`, `dato`, `name`, `mime`, `size`) VALUES ('".
							addslashes($f['data'])."', CURDATE(),'".addslashes($f['name'])."', '".addslashes($f['mime'])."', '".addslashes($f['size'])."')";
							$resource = mysql_query($fil_query);
							if (!$resource) {
								return array("message" => 'kunne ikke gjøre query: '.mysql_error());
							} else {
								header("location: ?site=filer");
							}
						} else {
							return array("message" => 'fil passer ikke krav');
						}
						$i++;
					}
				} else {
					$fil -> moveAllToArkiv("*/*", 8388608);
				}
				break;
		}
	}

	public function createInstance($args) {
		$content = $args["_element"];
		$document = $args["_document"];
		$style = $args["_style"];
		switch ($this->key) {
			case 'files':
				$content->addChild(new HtmlElement("h1", false, "Database"));
				$start = $args['start'];
				$start = is_numeric($start)?intval($start):0;
				$resource = mysql_query("SELECT `name`, `category`, `dato`, `mime`, `size`, `id` FROM `archive` LIMIT $start, 24;");
				$next = mysql_num_rows($resource)==24;
				$prev = $start-24>=0;
				$content->addChild($wrap = new Div("sow-filelist-wrapper"));
				$wrap->addChild($head = new Div("sow-filelist-header"));
				$head->addChild($label = new HtmlElement("label", array("class" => ""), "kategori:"));
				$label->addChild($category = new chooser("sow-filelist-select"));
				$category->addOption("ingen", "");
				$head->addChild(new HtmlElement("a", array("class" => "sow-filelist-button sow-filelist-action",
				"id" => "down"), "Last ned"));
				$head->addChild(new HtmlElement("button", array("class" => "sow-filelist-button sow-filelist-action",
				"id" => "nam"), "Gi nytt navn"));
				$head->addChild(new HtmlElement("button", array("class" => "sow-filelist-button sow-filelist-action",
				"id" => "del"), "Slett"));
				$head->addChild(new Span("lenke: ", "sow-filelist-link sow-filelist-action"));
				$head->addChild(new Span("mime: ", "sow-filelist-mime sow-filelist-action"));
				$head->addChild(new Span("størrelse: ", "sow-filelist-size sow-filelist-action"));
				$head->addChild(new Span("kategori: ", "sow-filelist-category sow-filelist-action"));
				$cate = array();
				$wrap->addChild($list = new HtmlList("sow-filelist-list"));
				while ($assoc = mysql_fetch_assoc($resource)) {
					$li = $list->add($icon = new Div("sow-filelist-icon"));
					$li->addChild(new Span($assoc['name'], "sow-filelist-name"));
					$li->class = "sow-filelist-file";
					$li->title = $assoc['name'];
					$li->mime = $assoc['mime'];
					$li->value = $assoc['id'];
					$li->fsize = $assoc['size'];
					$li->category = $assoc['category'];
					$mim = explode("/", $assoc['mime']);
					switch ($mim[0]) {
						case 'application':
							switch ($mim[1]) {
								case 'pdf':
									$icon->addClass("sow-filelist-icon-pdf");
									break;
								case 'msword':case 'vnd.openxmlformats-officedoc':
									$icon->addClass("sow-filelist-icon-word");
									break;
								case 'vnd.oasis.opendocument.text':
									$icon->addClass("sow-filelist-icon-text");
									break;
								case 'vnd.ms-powerpoint':
									$icon->addClass("sow-filelist-icon-powerpoint");
									break;
							}
							break;
					}
					if ($assoc['category']&&!$cate[$assoc['category']]) {
						$category->addOption($assoc['category'], $assoc['category']);
						$cate[$assoc['category']] = true;
					}
				}
				$wrap->addChild($foot = new Div("sow-filelist-footer"));
				if ($prev) $foot->addChild(new Link("?site=filer&start=".($start-24), "<< forrige", array("class" => "sow-filelist-button")));
				if ($next) $foot->addChild(new Link("?site=filer&start=".($start+24), "Neste >>", array("class" => "sow-filelist-button")));
				$wrap->addChild($form = new Form("?site=filer&action=lastfil"));
				$form->class = "sow-filelist-upload";
				$form->addChild(new Input("file", "fil"));
				$form->addChild(new Input("submit", "", "Last opp"));
				$content->addChild($nam = new Div("", "namdial"));
				$nam->addChild(new HtmlElement("label", array("for" => "fname"), "Nytt filnavn: "));
				$nam->addChild(new Input("text", "fname"));
				$content->addChild(new Div("sow-progress"));
				$content->addChild(new script("", "$('.sow-filelist-list').selectable({filter: 'li',".
				"selected: function() {\$('.sow-filelist-action').show();var sel=$('.sow-filelist-list .ui-selected'),l=sel.size()-1,s='';".
				"sel.each(function(i){s+='?site=file&id='+$(this).attr('value')+(i<l?', ':'');});$('.sow-filelist-link').text('lenke: '+s);".
				"s='';sel.each(function(i){s+=$(this).attr('mime')+(i<l?', ':'');});$('.sow-filelist-mime').text('mime: '+s);".
				"s='';sel.each(function(i){s+=$(this).attr('fsize')+(i<l?', ':'');});$('.sow-filelist-size').text('størrelse: '+s);".
				"s='';sel.each(function(i){s+=$(this).attr('catesgory')+(i<l?', ':'');});$('.sow-filelist-category').text('kategori: '+s);".
				"$('#down').attr('href', '?site=file&id='+sel.val());},".
				"unselected: function() {\$('.sow-filelist-action').hide();}});".
				"$('#namdial').dialog({autoOpen: false,buttons:{'Ok':function(){\$(this).action({".
				"action:'file-rename',auto:true,args:{id:$('#fname').data('id'),name:$('#fname')},".
				"callback:function(d){\$('#namdial').dialog('close');$('.sow-filelist-list .ui-selected').attr('title',d.name);".
				"$('.sow-filelist-list .ui-selected .sow-filelist-name').text(d.name)}});},'Avbryt':".
				"function(){\$('#namdial').dialog('close');}}, title:'Gi nytt navn'});".
				"$('#nam').click(function(){\$('#fname').val($('.sow-filelist-list .ui-selected').attr('title'));$('#fname').data('id', $('.sow-filelist-list .ui-selected').attr('value'));".
				"$('#namdial').dialog('open');});$('.upload').submit(function() {\$('.sow-progress').fadeIn(300);});".
				"$('#del').action({action: 'slettfil', confirm: 'Er du sikker du vil slette disse filene?', args:{id: function() {var res = {};".
				"$('.sow-filelist-list .ui-selected').each(function(i) {res['id'+i]=$(this).val();});".
				"return res;}}, callback: function() {\$('.sow-filelist-list .ui-selected').remove();}});".
				"$('.sow-filelist-wrapper').click(function(e){if (e.which==3);});"));
				break;
		}
	}
}