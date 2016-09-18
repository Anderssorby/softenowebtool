(function($) {

	var methods = {
			init : function(options) {

				var settings = {
						parent : false,
						action : "menu-reorder"
				};

				return this.each(function() {

					var $this = $(this), data = $this.data('lidata');
					if (options) {
						$.extend(settings, options);
					}
					if (!data) {
						var result = $(document.createElement("div"));
						$(document.body).append(result);
						result.addClass("sow-result");
						$this.add("ul", $this).sortable({
							items : "li",
							connectWith : "ul, .menu-lister-bin",
							handle : "span",
							placeholder : "menu-lister-holder"
						});
						$(".menu-lister-bin").sortable({
							receive: function(event, ui) {
								result.text("Meny fjernet fra liste. ");
								var ul = $(this);
								var a = $('<a href="#">Angre</a>');
								result.append(a);
								result.fadeIn("slow");
								a.click(function() {
									$this.append(ul.find(">li"));
								});
								window.setTimeout(function() {
									result.fadeOut("slow");
								}, 4000);
							}
						});
						var link = $(""),
							value = "";
							dialog = $("<div></div>");
						$(document.body).append(dialog);
						var form = $('<form></form>'),
							label = $('<label for="linp">Link</label>'),
							linp = $('<input type="text" class="text ui-widget-content ui-corner-all" name="linp">'),
							label2 = $('<label for="info">Infosider</label>'),
							sel = $('<select name="info"></select>');
						sel.action({
							action: "infosites"
						});
						sel.change(function() {
							linp.val($(":selected", this).val());
						});
						form.append(label);
						form.append(linp);
						form.append(label2);
						form.append(sel);
						dialog.append(form);
						dialog.dialog({ 
							title: "Fest lenke",
							autoOpen: false,
							buttons: { 
								"Ok": function() {
									link.val(linp.val());
									$(this).dialog("close");
								},
								"Avbryt": function() {
									$(this).dialog("close");
								}
							},
							modal: true
						});
						var update = function() {
							$(this).empty();
							var li = $("<li></li>");
							li.append('<span>_</span> <input type="text" name="name"> <input type="text" name="link">');
							var ul = $("<ul></ul>");
							ul.sortable({
								items: "li", 
								connectWith : "ul, .menu-lister-bin",
								handle : "span",
								placeholder : "menu-lister-holder"
							});
							li.append(ul);
							$(this).append(li);
							$("li>input[name='link']").focus(function() {
								link = $(this);
								linp.val($(this).val());
								dialog.dialog("open");
							});
						};
						update.apply($(".menu-lister-new").get(0));
						$(".menu-lister-new").sortable({
							items : "li",
							connectWith : "ul",
							placeholder : "menu-lister-holder",
							remove : update
						});
						$("input[name='link']", $this).focus(function() {
							link = $(this);
							linp.val($(this).val());
							dialog.dialog("open");
						});
					}
				});
			},
			destroy : function() {

				return this.each(function() {

					var $this = $(this), data = $this.data('lidata');
					data.menulister.remove();
					$this.removeData('lidata');

				});
			},
			save : function() {
				var $this = $(this);
				var lister = function(par) {
					var list = {};
					$(">li", par).each(function(index) {
						list[index] = {
								name : $(this).find("input[name='name']").val(),
								link : $(this).find("input[name='link']").val(),
								children : lister($(">ul", this))
						};
					});
					return list;
				};
				return lister($this);
			}
	};

	$.fn.menulister = function(method) {

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(
					arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$
			.error('Method ' + method
					+ ' does not exist on jQuery.menulister');
		}

	};
})(jQuery);
