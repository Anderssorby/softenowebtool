(function($) {

	var methods = {
		init : function(options) {

			var settings = {

			};

			return this.each(function(index, element) {

				var $this = $(this), data = $this.data('album');

				// If the plugin hasn't been initialized yet
				if(!data) {
					$(element).each(function() {
						var img = $(this);
						
					});
					$(this).data('album', {
						target : $this,
						tooltip : tooltip
					});

				}
			});
		},
		destroy : function() {

			return this.each(function() {

				var $this = $(this), data = $this.data('album');

				// Namespacing FTW
				$(window).unbind('.album');
				data.album.remove();
				$this.removeData('album');

			})
		},
		next : function() {

		},
		prev : function() {
		},
		update : function(content) {
		}
	};

	$.fn.album = function(method) {

		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if( typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.album');
		}

	};
})(jQuery);
