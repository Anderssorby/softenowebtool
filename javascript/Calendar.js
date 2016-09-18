(function($) {

	var days = [ "man", "tir", "ons", "tor", "fre", "lør", "søn" ];
	var daysFull = [ "mandag", "tirsdag", "onsdag", "torsdag", "fredag", "lørdag", "søndag" ];
	var months = [ "Januar", "Februar", "Mars", "April", "Mai", "Juni", "Juli",
	               "August", "September", "Oktober", "November", "Desember" ];

	var methods = {
			init : function(options) {
				var settings = {
						selected: function() {},
						large: false,
						link: false
				};
				return this.each(function() {
					var $this = $(this), con = $this.data('mon');
					if (!con) {
						$.extend(settings, options);
						$this.data('link', settings.link);
						$this.empty();
						$this.addClass("calendar");
						$this.data("large", settings.large);
						if (settings.large) {
							$this.addClass("calendar-large");
						}

						var title = $(document.createElement('div'));
						title.addClass('calendar-head');
						$this.append(title);

						var prev = $(document.createElement('a'));
						prev.text("<");
						prev.addClass('calendar-prev');
						title.append(prev);

						var month = $(document.createElement('span'));
						month.addClass('calendar-month');
						title.append(month);

						var next = $(document.createElement('a'));
						next.text(">");
						next.addClass('calendar-next');
						title.append(next);

						var table = $(document.createElement('table'));
						table.addClass('calendar-table');
						$this.append(table);
						var curr = new Date();
						$this.calendar('update', curr.getMonth());
						next.click(function() {
							$this.calendar('update', $this.data('mon') + 1);
						});
						prev.click(function() {
							$this.calendar('update', $this.data('mon') - 1);
						});
						$this.bind("selected", settings.selected);
					}

				});
			},

			destroy : function() {
				return this.each(function() {
					var $this = $(this), data = $this.data('mon');
					data.calendar.remove();
					$this.empty();
					$this.removeData('mon');
					$this.unbind("selected");
				});
			},

			update : function(mon) {
				return this.each(function() {
					var $this = $(this);
					var large = $this.data("large");
					if (mon != null) {
						$this.data('mon', mon);
					} else {
						mon = $this.data('mon');
					}
					var table = $(".calendar-table", this);
					var month = $(".calendar-month", this);
					table.hide();
					table.empty();
					var curr = new Date();
					var first = new Date(curr.getFullYear(), mon, 1);
					month.text(months[first.getMonth()] + " " + first.getFullYear());
					var head = $(document.createElement('tr'));
					table.append(head);
					for ( var i = 0; i < 6; i++) {
						var row = $(document.createElement('tr'));
						table.append(row);
						for ( var j = first.getDay() == 0 ? -5 : 2 - first.getDay(); first
						.getDay() == 0 ? j < 2 : j < 9 - first.getDay(); j++) {
							var d = new Date(curr.getFullYear(), mon, j + 7 * i);
							if (i == 0) {
								var day = $(document.createElement('th'));
								var da = d.getDay() == 0 ? 6 : d.getDay() - 1;
								day.text(days[da]);
								head.append(day);
								day.addClass('calendar-day');
							}
							var cell = $(document.createElement('td'));
							row.append(cell);
							cell.addClass('calendar-cell');
							var a = $(document.createElement('a'));
							a.click(function() {
								$(".calendar-selected", $this).removeClass("calendar-selected");
								$(this).addClass("calendar-selected");
								$this.trigger("selected");
							});
							a.text(d.getDate());
							a.attr("title", daysFull[d.getDay()==0?6:d.getDay()-1]+" "+d.getDate()+". "+months[d.getMonth()]+" "+first.getFullYear());
							var time = (d.getTime())/1000+60*60*2;
							if ($this.data('link')) {
								a.attr('href', '?site=event&date='+time);
							}
							a.attr('value', time);
							var id = 'cal-'+d.getDate()+'-'+d.getMonth()+'-'+d.getFullYear();
							a.attr('id', id);
							cell.append(a);
							if (d.getMonth() === curr.getMonth()
									&& d.getFullYear() === curr.getFullYear()
									&& d.getDate() === curr.getDate()) {
								a.addClass('calendar-today');
							} else if (d.getMonth() !== first.getMonth()) {
								a.addClass('calendar-not-of-month');
							} else {
								a.addClass('calendar-of-month');
								if (d.getDay() === 0 || d.getDay() === 6) {
									a.addClass('calendar-week-end');
								}
							}
							if (large&&(a.hasClass('calendar-of-month')||a.hasClass('calendar-today'))) {
								var hol = $('<div></div>').appendTo(a).addClass("calendar-event-holder");
								hol.empty().action({
										action: 'read-event',
										args: {
											date: time
										},
										before: function () {

										},
										callback: function (data) {
											if (typeof data === "string"||data.empty) {
												return;
											}
											for (var key in data) {
												var event = data[key];
												$('<div></div>').appendTo(this).text(event.name)
												.addClass("calendar-event");
											}
										},
										auto: true
									});
								$this.bind("load-events", function() {
									
								});
							}
						}
						table.show("slow");
						$this.trigger("load-events");
					}
				});
			}
	}

	$.fn.calendar = function(method) {

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(
					arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist in jQuery.calendar');
		}

	};
})(jQuery);
