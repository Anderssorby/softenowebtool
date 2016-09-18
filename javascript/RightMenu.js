(function($) {

	var methods = {
			init : function(options) {

				var settings = {
						buttons : {}
				};

				return this.each(function() {

					var $this = $(this), data = $this.data('result');
					if (options) {
						$.extend(settings, options);
					}
					$this.data("action", settings.action);
					if (!data) {
						var result = $(document.createElement("div"));
						$(document.body).append(result);
						result.addClass("");
						$this.find("ul>li").each(function(index, element) {
							var el = $(element);
						});
						$this.data('result', "ok");
						
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
