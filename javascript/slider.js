(function($) {

	var methods = {
			init : function(options) {
				var settings = {
						delay: 3000,
						height: 300
				};
				return this.each(function() {

					var $this = $(this), data = $this.data('index');
					if (options) {
						$.extend(settings, options);
					}
					if (!data) {
						var da = $(".slider-list li", $this);
						$this.data("index", 0);
						$(".slider-list", $this).css("height", settings.height);
						var next = function synchronized() {
							var idx = $this.data("index");
							var i = idx+1;
							if (da.size()<=i) i = 0;
							var pel = $(da.get(idx));
							var el = $(da.get(i));
							$this.data("index", i);
							el.css("left", "100%");
							da.not(pel).css("top", (-100)*i+"%");
							pel.animate({left: "-100%"}, 1300);
							el.animate({left: "0%"}, 1300);
						};
						var slid = function() {
							next();
							out = window.setTimeout(slid, settings.delay);
						};
						var out = window.setTimeout(slid, settings.delay);
						$(".slider-next", $this).click(function () {
							next();
							window.clearTimeout(out);
						});
						var prev = function synchronized() {
							var idx = $this.data("index");
							var i = idx-1;
							if (i<0) i = da.size()-1;
							var nel = $(da.get(idx));
							var el = $(da.get(i));
							$this.data("index", i);
							el.css("left", "-100%");
							da.not(nel).css("top", (-100)*i+"%");
							el.animate({left: "0%"}, 1300);
							nel.animate({left: "100%"}, 1300);
						};
						$(".slider-prev", $this).click(function () {
							prev();
							window.clearTimeout(out);
						});
						$this.bind("mouseleave", function () {
							$(".slider-next, .slider-prev", $this).fadeOut("fast");
						});
						$this.bind("mouseenter", function () {
							$(".slider-next, .slider-prev", $this).fadeIn("fast");
						});
					}
				});
			},
			destroy : function() {
				return this.each(function() {
					var $this = $(this);
					$this.unbind("mouseleave");
					$this.unbind("mouseenter");
					$this.removeData("index");
				});
			}
	};

	$.fn.slider = function(method) {

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(
					arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.slider');
		}

	};
})(jQuery);
