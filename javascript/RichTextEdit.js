(function($) {

	$.valHooks.textarea = {
			get: function(elem) {
				return elem.value.replace( /\r?\n/g, "\r\n" );
			}
	};
	var methods = {
			init : function(options) {
				var settings = {

				};
				return this.each(function() {
					var $this = $(this), con = $this.data('text');
					if (!con) {
						$.extend(settings, options);
						$this.trigger("construct");
						var parent = $this.parent();
						parent.addClass("richtext-holder");
						$this.data("text", $this.text());
						$this.hide();

						var wrap = $(document.createElement('div'));
						wrap.addClass('richtext-wraper');
						parent.append(wrap);

						var toolbar = $(document.createElement('div'));
						toolbar.addClass('richtext-toolbar');
						wrap.append(toolbar);

						var row1 = $(document.createElement('div'));
						row1.addClass('richtext-toolbar-group-row');
						toolbar.append(row1);

						var nameSize = $(document.createElement('span'));
						nameSize.addClass('richtext-toolbar-group');
						row1.append(nameSize);

						var nameSel = $(document.createElement('select'));
						nameSel.addClass('richtext-select');
						nameSel.change(function() {
							$this.trigger("alter-font-name");
						});
						nameSize.append(nameSel);
						var fontList = [ "Arial", "Arial Black", "Comic Sans MS",
						                 "Courier New", "Lucinda Console", "Tahoma",
						                 "Times New Roman", "Trebuchet MS", "Verdana" ];
						for ( var i = 0; i < fontList.length; i++) {
							var nameOp = $(document.createElement('option'));
							nameOp.text(fontList[i]);
							nameOp.val(fontList[i]);
							nameSel.append(nameOp);
						}

						var sizeSel = $(document.createElement('select'));
						sizeSel.addClass('richtext-select');
						sizeSel.change(function() {
							$this.trigger("alter-font-size");
						});
						nameSize.append(sizeSel);
						var sizeList = [ 1, 2, 3, 4, 5, 6, 7 ];
						for ( var i = 0; i < sizeList.length; i++) {
							var sizeOp = $(document.createElement('option'));
							sizeOp.text(sizeList[i]);
							sizeOp.val(sizeList[i]);
							sizeSel.append(sizeOp);
						}
						$("[value='3']", sizeSel).attr("selected", "selected");

						var bstyle = $(document.createElement('span'));
						bstyle.addClass('richtext-toolbar-group');
						row1.append(bstyle);

						var bold = $('<button type="button"></button>');
						bold.addClass('richtext-button richtext-button-bold');
						bold.append(document.createElement('span'));
						bold.attr("title", "F");
						bold.click(function() {
							$this.trigger("alter-bold");
						});
						bstyle.append(bold);

						var ital = $('<button type="button"></button>');
						ital.addClass('richtext-button richtext-button-italic');
						ital.append(document.createElement('span'));
						ital.attr("title", "K");
						ital.click(function() {
							$this.trigger("alter-italic");
						});
						bstyle.append(ital);

						var und = $('<button type="button"></button>');
						und.addClass('richtext-button richtext-button-underline');
						und.append(document.createElement('span'));
						und.attr("title", "U");
						und.click(function() {
							$this.trigger("alter-underline");
						});
						bstyle.append(und);

						var sup = $('<button type="button"></button>');
						sup.addClass('richtext-button richtext-button-super');
						sup.append(document.createElement('span'));
						sup.attr("title", "sup");
						sup.click(function() {
							$this.trigger("alter-superscript");
						});
						bstyle.append(sup);

						var sub = $('<button type="button"></button>');
						sub.addClass('richtext-button richtext-button-sub');
						sub.append(document.createElement('span'));
						sub.attr("title", "sub");
						sub.click(function() {
							$this.trigger("alter-subscript");
						});
						bstyle.append(sub);

						var colo = $(document.createElement('span'));
						colo.addClass('richtext-toolbar-group');
						row1.append(colo);

						var fore = $('<button type="button"></button>');
						fore.addClass('richtext-button  richtext-button-foreground');
						fore.append(document.createElement('span'));
						fore.attr("title", "farge");
						var foredi = $(document.createElement('div'));
						foredi.addClass('richtext-colorpicker');
						fore.click(function() {
							foredi.show("slow");
						});
						colo.append(fore);
						foredi.hide();
						var colist = ["#ffffff", "#cccccc", "#c0c0c0", "#999999", "#666666",
						              "#333333", "#000000", "#ffcccc", "#ff6666", "#ff0000",
						              "#cc0000", "#990000", "#660000", "#330000", "#ffcc99",
						              "#ff9966", "#ff9900", "#ff6600", "#cc6600", "#993300",
						              "#663300", "#ffff99", "#ffff66", "#ffcc66", "#ffcc33",
						              "#cc9933", "#996633", "#663333", "#ffffcc", "#ffff33",
						              "#ffff00", "#ffcc00", "#999900", "#666600", "#333300",
						              "#99ff99", "#66ff99", "#33ff33", "#33cc00", "#009900",
						              "#006600", "#003300", "#99ffff", "#33ffff", "#66cccc",
						              "#00cccc", "#339999", "#336666", "#003333", "#ccffff",
						              "#66ffff", "#33ccff", "#3366ff", "#3333ff", "#000099",
						              "#000066", "#ccccff", "#9999ff", "#6666cc", "#6633ff",
						              "#6600cc", "#333399", "#330099", "#ffccff", "#ff99ff",
						              "#cc66cc", "#cc33cc", "#993399", "#663366", "#330033"];
						for (var i = 0; i < colist.length; i++) {
							var a = $(document.createElement('a'));
							var c = colist[i];
							a.css("background-color", c);
							a.attr("title", c);
							a.click(function() {
								var ce = $(this).attr("title");
								fore.val(ce);
								foredi.hide("slow");
								$this.trigger("alter-foreground");
							});
							foredi.append(a);
						}
						var canc = $('<button type="button"></button>');
						canc.addClass('richtext-button');
						canc.text("avbryt");
						canc.click(function() {
							foredi.hide("slow");
						});
						foredi.append(canc);
						colo.append(foredi);


						var back = $('<button type="button"></button>');
						back.addClass('richtext-button richtext-button-background');
						back.append(document.createElement('span'));
						back.attr("title", "bakgrunnsfarge");
						var backdi = $(document.createElement('div'));
						backdi.addClass('richtext-colorpicker');
						back.click(function() {
							backdi.show("slow");
						});
						colo.append(back);
						backdi.hide();
						for (var i = 0; i < colist.length; i++) {
							var a = $(document.createElement('a'));
							var c = colist[i];
							a.css("background-color", c);
							a.attr("title", c);
							a.click(function() {
								var ce = $(this).attr("title");
								fore.val(ce);
								foredi.hide("slow");
								$this.trigger("alter-background");
							});
							backdi.append(a);
						}
						var canc = $('<button type="button"></button>');
						canc.addClass('richtext-button');
						canc.text("avbryt");
						canc.click(function() {
							backdi.hide("slow");
						});
						backdi.append(canc);
						colo.append(backdi);

						var udo = $(document.createElement('span'));
						udo.addClass('richtext-toolbar-group');
						row1.append(udo);

						var del = $('<button type="button"></button>');
						del.addClass('richtext-button richtext-button-undo');
						del.append(document.createElement('span'));
						del.attr("title", "angre");
						del.click(function() {
							$this.trigger("undo");
						});
						udo.append(del);

						var eld = $('<button type="button"></button>');
						eld.addClass('richtext-button richtext-button-redo');
						eld.append(document.createElement('span'));
						eld.attr("title", "gjør om");
						eld.click(function() {
							$this.trigger("redo");
						});
						udo.append(eld);

						var row2 = $(document.createElement('div'));
						row2.addClass('richtext-toolbar-group-row');
						toolbar.append(row2);

						var align = $(document.createElement('span'));
						align.addClass('richtext-toolbar-group');
						row2.append(align);

						var left = $('<button type="button"></button>');
						left.addClass('richtext-button richtext-button-left');
						left.append(document.createElement('span'));
						left.attr("title", "venstre");
						left.click(function() {
							$this.trigger("alter-left");
						});
						align.append(left);

						var center = $('<button type="button"></button>');
						center.addClass('richtext-button richtext-button-center');
						center.append(document.createElement('span'));
						center.attr("title", "midt");
						center.click(function() {
							$this.trigger("alter-center");
						});
						align.append(center);

						var right = $('<button type="button"></button>');
						right.addClass('richtext-button richtext-button-right');
						right.append(document.createElement('span'));
						right.attr("title", "høyre");
						right.click(function() {
							$this.trigger("alter-right");
						});
						align.append(right);

						var hline = $(document.createElement('span'));
						hline.addClass('richtext-toolbar-group');
						row2.append(hline);

						var headSel = $(document.createElement('select'));
						headSel.addClass('richtext-select');
						headSel.change(function() {
							$this.trigger("alter-head");
						});
						hline.append(headSel);
						var headList = ["p", "h1", "h2", "h3", "h4", "h5", "h6" ];
						var hList = [ "normal", "overskrift 1", "overskrift 2",
						              "overskrift 3", "overskrift 4", "overskrift 5",
						              "overskrift 6" ];
						for ( var i = 0; i < headList.length; i++) {
							var headOp = $(document.createElement('option'));
							headOp.text(hList[i]);
							headOp.val(headList[i]);
							headSel.append(headOp);
						}

						var lists = $(document.createElement('span'));
						lists.addClass('richtext-toolbar-group');
						row2.append(lists);

						var inde = $('<button type="button"></button>');
						inde.addClass('richtext-button richtext-button-indent');
						inde.append(document.createElement('span'));
						inde.attr("title", "rykk inn");
						inde.click(function() {
							$this.trigger("alter-indent");
						});
						lists.append(inde);

						var dede = $('<button type="button"></button>');
						dede.addClass('richtext-button richtext-button-outdent');
						dede.append(document.createElement('span'));
						dede.attr("title", "rykk ut");
						dede.click(function() {
							$this.trigger("alter-dedent");
						});
						lists.append(dede);

						var poil = $('<button type="button"></button>');
						poil.addClass('richtext-button richtext-button-list');
						poil.append(document.createElement('span'));
						poil.attr("title", "liste");
						poil.click(function() {
							$this.trigger("insert-list-unordered");
						});
						lists.append(poil);

						var numl = $('<button type="button"></button>');
						numl.addClass('richtext-button richtext-button-ordlist');
						numl.append(document.createElement('span'));
						numl.attr("title", "num liste");
						numl.click(function() {
							$this.trigger("insert-list-ordered");
						});
						lists.append(numl);

						var insert = $(document.createElement('span'));
						insert.addClass('richtext-toolbar-group');
						row2.append(insert);

						var img = $('<button type="button"></button>');
						img.addClass('richtext-button richtext-button-image');
						img.append(document.createElement('span'));
						img.attr("title", "bilde");
						img.click(function() {
							$this.trigger("insert-image");
						});
						insert.append(img);

						var link = $('<button type="button"></button>');
						link.addClass('richtext-button  richtext-button-link');
						link.append(document.createElement('span'));
						link.attr("title", "lenke");
						link.click(function() {
							$this.trigger("insert-link");
						});
						insert.append(link);

						var plain = $('<button type="button"></button>');
						plain.addClass('richtext-button richtext-button-plain');
						plain.text("ren HTML");
						plain.append(document.createElement('span'));
						plain.attr("title", "ren HTML");
						plain.click(function() {
							$this.trigger("plain-html");
						});
						insert.append(plain);
						//fix position
						$(".richtext-button span", toolbar).text("_");

						var lidi = $(document.createElement('div'));
						lidi.addClass('richtext-dialog richtext-dialog-link');
						var url = $(document.createElement('label'));
						url.text("URL: ");
						var urli = $(document.createElement('input'));
						url.append(urli);
						lidi.append(url);
						urli.keypress(function () {
							$(target).attr("href", urli.val());
						});
						lidi.hide();
						toolbar.append(lidi);

						var imdi = $(document.createElement('div'));
						imdi.addClass('richtext-dialog richtext-dialog-image');
						var src = $(document.createElement('label'));
						src.text("url");
						var srci = $(document.createElement('input'));
						src.append(srci);
						imdi.append(src);
						src.keypress(function () {
							$(target).attr("src", src.val());
						});
						imdi.hide();
						toolbar.append(imdi);

						var editorframe = $(document.createElement('iframe'));
						editorframe.addClass('richtext-editor');
						wrap.append(editorframe);
						$this.addClass('richtext-editor');
						wrap.append($this);

						var editordoc = editorframe.get(0).contentDocument;
						if (!editordoc) {
							editordoc = editorframe.get(0).document;
						}
						var editorbody = $(editordoc.body);
						editorbody.html($this.data("text"));
						editordoc.designMode = "on";

						$this.bind("update", function() {
							var text = "";
							if (onHTML === "off") {
								text = editorbody.html();
							} else {
								text = $this.val();
							}
							$this.data("text", text);
							$this.html(text);
						});
						$this.bind("set-value", function() {
							var text = $this.data("text");
							editorbody.html(text);
							$this.html(text);
						});
						var target = null;
						$(editordoc).bind("click", function(event) {
							$(".richtext-dialog", toolbar).hide();
							target = event.target;
							var tar = $(target);
							var off = tar.offset();
							var height = tar.height();
							var width = tar.width();
							if (target.tagName.toLowerCase() == "img") {
								imdi.offset({top: off.top+height, left: off.left/2});
								srci.val(tar.attr("src"));
								imdi.fadeIn("slow");
							} else if (target.tagName.toLowerCase() == "a") {
								lidi.offset({top: off.top+height, left: off.left+width/2});
								urli.val(tar.attr("href"));
								lidi.fadeIn("slow");
							}
						});

						$this.bind("alter-bold", function() {
							editordoc.execCommand("bold", false, "");
						});
						$this.bind("alter-superscript", function() {
							editordoc.execCommand("superscript", false, "");
						});
						$this.bind("alter-subscript", function() {
							editordoc.execCommand("subscript", false, "");
						});
						$this.bind("undo", function() {
							editordoc.execCommand("undo", false, "");
						});
						$this.bind("redo", function() {
							editordoc.execCommand("redo", false, "");
						});
						$this.bind("alter-italic", function() {
							editordoc.execCommand("italic", false, "");
						});
						$this.bind("alter-left", function() {
							editordoc.execCommand("justifyLeft", false, "");
						});
						$this.bind("alter-center", function() {
							editordoc.execCommand("justifyCenter", false, "");
						});
						$this.bind("alter-right", function() {
							editordoc.execCommand("justifyRight", false, "");
						});
						$this.bind("alter-underline", function() {
							editordoc.execCommand("underline", false, "");
						});
						$this.bind("alter-indent", function() {
							editordoc.execCommand("indent", false, "");
						});
						$this.bind("alter-dedent", function() {
							editordoc.execCommand("outdent", false, "");
						});
						$this.bind("alter-font-name", function() {
							editordoc.execCommand("fontName", false, $(":selected", nameSel).val());
						});
						$this.bind("alter-font-size", function() {
							var size = parseInt($(":selected", sizeSel).val());
							editordoc.execCommand("fontSize", false, size);
						});
						$this.bind("alter-background", function() {
							editordoc.execCommand("backColor", false, back.val());
						});
						$this.bind("alter-foreground", function() {
							editordoc.execCommand("foreColor", false, fore.val());
						});
						$this.bind("alter-head", function() {
							editordoc.execCommand("formatBlock", false, "<"
									+ $(":selected", headSel).val() + ">");
						});
						$this.bind("insert-list-ordered", function() {
							editordoc.execCommand("insertOrderedList", false, "");
						});
						$this.bind("insert-list-unordered", function() {
							editordoc.execCommand("insertUnorderedList", false, "");
						});
						$this.bind("insert-link", function() {
							editordoc.execCommand("createLink", false, " ");
						});
						var imgsel = $(document.createElement('div'));
						imgsel.imageselect({
							choosed : function(chosen) {
								editordoc.execCommand("insertImage", false, chosen);
							}
						});
						$this.bind("insert-image", function() {
							var focus = $(":focus", editorbody);
							if (focus.get(0) && focus.get(0).tagName.toLowerCase() == "img") {

							} else {
								imgsel.dialog("open");
							}
						});
						var onHTML = "off";
						$this.bind("plain-html", function() {
							if (onHTML == "off") {
								editorframe.hide();
								$this.val(editorbody.html());
								$this.show();
								onHTML = "on";
							} else {
								editorframe.show();
								editorbody.html($this.val());
								$this.hide();
								onHTML = "off";
							}
						});
						//fix linking
						//$(".richtext-button", wrap).attr("type", "button"); 
					}
				});
			},

			destroy : function() {
				return this.each(function() {
					var $this = $(this), data = $this.data('text');
					data.calendar.remove();
					$this.removeData('text');
				})
			},

			value : function(value) {
				$this = $(this);
				if (value != null) {
					$this.data("text", value);
					$this.trigger("set-value");
				} else {
					$this.trigger("update");
					return $this.data("text");
				}
			}
	};

	$.fn.richtextedit = function(method) {

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(
					arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method
					+ ' does not exist in jQuery.richtextedit');
		}

	};
})(jQuery);
