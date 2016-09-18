(function($) {

	var methods = {
			init : function(options) {
				var settings = {
						choosed : function(chosen) {
						}
				};
				return this.each(function() {
					var $this = $(this), con = $this.data('img-data');
					$.extend(settings, options);
					if (!con) {
						$this.data('start', 0);
						var choo = $(document.createElement('div'));
						$this.append(choo);
						choo.addClass("img-chooser");
						var head = $(document.createElement('div'));
						head.addClass("img-chooser-head");
						head.text("viser 1 til 12");
						choo.append(head);
						var list = $(document.createElement('ul'));
						choo.append(list);
						var imgdata = {};
						$this.imageselect("update", 0);
						var forrige = $(document.createElement('div'));
						choo.append(forrige);
						forrige.addClass("sow-button");
						forrige.text("Forrige");
						forrige.click(function() {
							var start = $this.data("start");
							if (start >= 12)
								$this.imageselect("update", start - 12);
						});
						var neste = $(document.createElement('div'));
						choo.append(neste);
						neste.addClass("sow-button");
						neste.text("Neste");
						neste.click(function() {
							var start = $this.data("start");
							$this.imageselect("update", start + 12);
						});
						$this.dialog({
							title : 'Velg et bilde',
							autoOpen : false,
							buttons : {
								"Ok" : function() {
									$(this).dialog("close");
									var result = $(".ui-selected", list).first()
									.find("img").attr("src");
									settings.choosed(result);
								},
								"Avbryt" : function() {
									$(this).dialog("close");
								}
							},
							show : "slide",
							modal : true,
							width : 900
						});
						$this.data('img-data', imgdata);
					}
				});
			},
			destroy : function() {
				return this.each(function() {
					var $this = $(this), data = $this.data('img-data');
					data.calendar.remove();
					$this.removeData('img-data');

				})
			},
			update : function(start) {
				$(this).data('start', start);
				var $this = $(this),
				list = $("ul", this);
				list.selectable("destroy");
				list.empty();
				$(".img-chooser-head", this).text("Viser "+(start+1)+" til "+(start+12));
				var imgdata = {};
				$this.action({
					action: 'fetch-img',
					auto : true,
					args : {
						start: start
					},
					callback : function(data) {
						list.empty();
						for ( var da in data) {
							var el = data[da];
							var frame = $(document.createElement('li'));
							list.append(frame);
							frame.addClass("img-chooser-frame");
							var ifr = $(document.createElement('div'));
							frame.append(ifr);
							ifr.addClass("img-chooser-wrap");
							var img = $(document.createElement('img'));
							ifr.append(img);
							img.attr("src", el["src"]);
							img.attr("alt", el["id"]);
							img.attr("style", "max-height: 70px; max-width: 80px;");
							var span = $(document.createElement('span'));
							frame.append(span);
							span.text(el["title"]);

						}
						imgdata = data;
					}
				});
				list.selectable({
					filter : ".img-chooser-frame"
				});
			}
	};

	$.fn.imageselect = function(method) {

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(
					arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method
					+ ' does not exist on jQuery.imageselect');
		}

	};
})(jQuery);
