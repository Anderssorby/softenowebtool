<?php
/**
 * class for handeling calendar calls etc.
 */
class Calendar implements Action, ModuleApp {
	protected $key;
	private $days = array("mandag", "tirsdag", "onsdag", "torsdag", "fredag", "lørdag", "søndag");
	private $months = array("Januar", "Februar", "Mars", "April", "Mai", "Juni", "Juli",
	"August", "September", "Oktober", "November", "Desember");
	
	public function __construct($key) {
		$this->key = $key;
	}

	public function performAction($command, $args) {
		switch ($this->key) {
			case "create-event":
				$dat = $args['date'];
				$note = $args['note'];
				$name = $args['name'];
				if (is_numeric($dat)) {
					$d = getdate($dat);
					$query = new Query();
					$date = $d['year']."-".$d['mon']."-".$d['mday']." ".$d['hours'].":".$d['minutes'].":".$d['seconds'];
					$result = $query->insert("events", array("date" => $date, "end" => $date, "note" => utf8_decode($note),
					"name" => utf8_decode($name)))->query()->result();
					if ($result) {
						return array("message" => "Event opprettet");
					} else {
						return array("message" => "Event ble ikke opprettet: ".$query->error());
					}
				} else {
					return array("message" => "Event ble ikke opprettet");
				}
				break;
			case "update-event":
				$id = $args['id'];
				$dat = $args['date'];
				$note = $args['note'];
				$name = $args['name'];
				if (is_numeric($dat)) {
					$d = getdate($dat);
					$query = new Query();
					$date = $d['year']."-".$d['mon']."-".$d['mday']." ".$d['hours'].":".$d['minutes'].":".$d['seconds'];
					$result = $query->update("events", array("date" => $date, "end" => $date,
					 "note" => utf8_decode($note), "name" => utf8_decode($name)))->where("id", $id)->limit(1)->query()->result();
					if ($result) {
						return array("message" => "Event oppdatert");
					} else {
						return array("message" => "Event ble ikke oppdatert: ".$query->error());
					}
				} else {
					return array("message" => "Event ble ikke oppdatert");
				}
				break;
			case "del-event":
				$id = $args['id'];
				if (is_numeric($id)) {
					$query = new Query();
					$result = $query->delete("events")->where("id", $id)->limit(1)->query()->result();
					if ($result) {
						return array("message" => "Event ble slettet");
					} else {
						return array("message" => "Event ble ikke slettet");
					}
				} else {
					return array("message" => "Manglende argumenter");
				}
				break;
			case "read-event":
				$date = $args['date'];
				if (is_numeric($date)) {
					$d = getdate($date);
					$e = getdate($date+3600*24);
					$lit = $d['year']."-".$d['mon']."-".$d['mday'];
					$eit = $e['year']."-".$e['mon']."-".$e['mday'];
					$query = new Query();
					$assoc = $query->select("events")->whereString("`date` >= '".$lit."' && `date` < '".$eit."'")->
					orderBy("date", "asc")->query()->assoc();
					if ($assoc) {
						return $assoc;
					} else {
						return array("empty" => true);
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
		$data = $args["_data"];
		switch ($this->key) {
			case 'calendar':
				$content->addChild(new HtmlElement("h1", false, "Kalender"));
				$content->addChild($hol = new Div(array(
				"style" => "position: relative;width: 100%;display: inline-block;")));
				$hol->addChild($cal = new Div());
				$cal->generateId();
				$cal->style = "float: left;";
				$hol->addChild($eves = new Div("", "eves"));
				$set = $style->getStyleSet("contenttext");
				$class = $set['class'];
				$eves->addClass($class);
				$eves->style = "width: 35%;float: right;overflow: auto;";
				$content->addChild(new HtmlElement("h2", false, "Opprett nytt event"));
				$content->addChild(new HtmlElement("label", array("for" => "name"), "Navn på event "));
				$content->addChild($name = new Input("text", "name"));
				$name->class = "title-input";
				$content->addChild(new HtmlElement("label", array("for" => "time"), "Starttid "));
				$content->addChild($hour = new chooser(null, null, "time"));
				$hour->generateRange(23, 0, 1);
				$content->addChild($min = new chooser(null, null, "min"));
				$min->generateRange(59, 0, 1);
				$content->addChild($la = new Button("Lagre", "saving"));
				$la->title = "Oppdater eventet";
				$content->addChild($cr = new Button("Opprett nytt event", "save"));
				$cr->title = "Opprett et nytt event";
				$content->addChild($div = new Div());
				$div->addChild(new HtmlElement("textarea", array("id" => "event")));
				$content->script("$('#".$cal->id."').calendar({large: true,".
				"selected: function(){var sel = $('.calendar-selected', this);$('#eves').action({args: {date: sel.attr('value')},".
				"action: 'read-event', auto: true, before: function(){\$('#eves').empty();".
				"$('<div></div>').appendTo('#eves').text('Laster');},callback: function(d){".
				"$('#eves').empty();$('<h1></h1>').appendTo('#eves').text(sel.attr('title'));if (typeof d == 'string'||d.empty) return;".
				"for(var a in d){var r=d[a], wr=$('<div></div>').appendTo('#eves').attr('id', 'wr'+a);".
				"if (a!=0) $('<hr/>').appendTo(wr);$('<h2></h2>').appendTo(wr).text(r.name);".
				"$('<button></button>').appendTo(wr).text('endre').click(function() {\$('#name').val(r.name);".
				"$('#event').richtextedit('value', r.note);$('#eves').data('eid', r.id);$('#saving').show();".
				"var rx = /(\d+):\d+:\d+/;$('#time').val(rx.exec(r.date)[1]);var rx = /\d+:(\d+):\d+/;$('#min').val(rx.exec(r.date)[1]);});".
				"$('<button></button>').appendTo(wr).text('slett').action({action: 'del-event', args:{id: r.id}, callback:".
				"function() {\$(this).parent().slideUp(1000).remove();$('.calendar').trigger('load-events');}});$('<pre></pre>').appendTo(wr).text(r.date);".
				"$('<p></p>').appendTo(wr).html(r.note);}}});}});".
				"$('#event').richtextedit();$('#saving').hide().action({action: 'update-event', callback: function(){".
				"$('.calendar').trigger('load-events');alert('hallo');}, args: {date: function(){".
				"return $('.calendar .calendar-selected').attr('value')+3600*$('#time').val()+60*$('#min').val();}, ".
				"note: function(){return $('#event').richtextedit('value');}, name: $('#name'), id: $('#eves').data('eid')}});".
				"$('#save').action({action: 'create-event', before: function(){\$('#saving').hide();}, callback: function(){\$('.calendar').trigger('load-events');".
				"$('#name').val('');$('#event').richtextedit('value', '');$('#time').val('0');$('#min').val('0');$('#eves').data('eid', '');}, args: ".
				"{date: function(){return $('.calendar .calendar-selected').attr('value')+3600*$('#time').val()+60*$('#min').val();}, ".
				"note: function(){return $('#event').richtextedit('value');}, name: $('#name')}});");
				break;
			case 'event':
				$date = $args['date'];
				if (is_numeric($date)) {
					$d = getdate($date);
					$e = getdate($date+3600*24);
					$wday = $d['wday']==0?7:$d['wday']-1;
					$lda = $this->days[$wday]." ".$d['mday'].". ".$this->months[$d['mon']-1]." ".$d['year'];
					$lit = $d['year']."-".$d['mon']."-".$d['mday'];
					$eit = $e['year']."-".$e['mon']."-".$e['mday'];
					$content->addChild(new HtmlElement("h1", false, $lda));
					if ($data['contname']) $data['contname']->addChild(new Span($lda));
					$query = new Query();
					$set = $style->getStyleSet("contenttext");
					$class = $set['class'];
					$content->addClass($class);
					$assoc = $query->select("events")->whereString("`date` >= '".$lit."' && `date` < '".$eit."'")->
					orderBy("date", "asc")->query()->assoc();
					foreach ($assoc as $i => $en) {
						if ($i!=0) $content->addChild(new HtmlElement("hr", true));
						$content->addChild(new HtmlElement("h2", false, $en['name']));
						$content->addChild(new HtmlElement("pre", false, $en['date']."-".$en['end']));
						$content->addChild(new HtmlElement("p", false, new TextNode($en['note'], true)));
					}
				}
				break;
			case 'smalcalendar':
				$document->head->addScript("softenowebtool/javascript/Calendar.js");
				$content->addChild($cal = new Div("", ""));
				$cal->generateId();
				$content->addChild(new script("", "$('#".$cal->id."').calendar({large: false, link: true, selected: function() {}});"));
				break;
		}
	}
}
