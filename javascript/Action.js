(function($) {

	var methods = {
		init : function(options) {

			var settings = {
				args : {},
				action : "",
				callback : function() {},
				before : function() {},
				auto : false,
				message : true,
				confirm : false
			};

			return this.each(function() {

				var $this = $(this), data = $this.data('result');
				if (options) {
					$.extend(settings, options);
				}
				$this.data("action", settings.action);
				if (!data||settings.auto) {
					var result = null;
					if ($(".sow-result").size() <= 0) {
						result = $(document.createElement("div"));
						$(document.body).append(result);
						result.addClass("sow-result");
					} else {
						result = $(".sow-result");
					}
					$this.data('result', "ok");
					$this.unbind("action-before");
					$this.bind("action-before", function() {
						settings.before.apply($this);
					});
					var args = settings.args;
					var listner = function() {
					};
					args["_name"] = $this.data("action");
					var actor = {
						act : function() {
							$this.trigger("action-before");
							var data = {};
							for ( var key in args) {
								var arg = args[key];
								if (arg&&arg.val) {
									data[key] = arg.val();
								} else if ($.isFunction(arg)) {
									data[key] = arg.apply($this);
								} else {
									data[key] = arg;
								}
							}
							$.ajax({
								url : "?",
								type : 'post',
								error: function (jqXHR, textStatus, errorThrown) {
									alert(textStatus);
								},
								success : function(rdata, textStatus, jqXHR) {
									if (settings.message) {
										if (rdata.message) {
											result.text(rdata.message);
											result.fadeIn("slow");
											window.setTimeout(function() {
												result.fadeOut("slow");
											}, 4000);
										}
									}
									listner(rdata);
									settings.callback.apply($this, [rdata]);
								},
								data : $.param({
									action : data
								})
							});
						}
					};
					if (settings.auto) {
						actor.act();
					} else {
						if ($this.is("input[type='button'], button, a")) {
							$this.click(function() {
								if (settings.confirm) {
									if (confirm(settings.confirm)) {
										actor.act();
									}
								} else {
									actor.act();
								}
							});
						} else if ($this.is("form")) {
							$this.submit(function() {
								actor.act();
								return false;
							});
						} else if ($this.is("select")) {
							listner = function(res) {
								$this.empty();
								for ( var key in res.values) {
									var val = res.values[key];
									var opt = $("<option></option>");
									opt.text(key);
									opt.val(val);
									$this.append(opt);
								}
							}
							actor.act();
						}
					}
				}
			});
		},
		destroy : function() {
			return this.each(function() {

				var $this = $(this), data = $this.data('result');
				data.action.remove();
				$this.removeData('result');

			});
		}
	};

	$.fn.action = function(method) {

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(
					arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.action');
		}

	};
})(jQuery);
