var Site = {
 
	// this vars should be set in <head> server-side
	config : {
		base_url : '',
		site_url : ''
	},
 
	// this method is called on every page
	start: function() {
		
		// On Dom Ready
		jQuery(function($) {
			
		});
 
		// Load Immediately
		(function($) {
 
		})(jQuery);
		
		// On Window Load
		$(window).load(function () {
			$(".match").equalHeight();
		});
		
	},
	
	// this method is called on edit / save pages
	edit_save_start: function() {
		
		// On Dom Ready
		jQuery(function($) {
 			
			$("input.slug").slug();
			$("ul#iterations .dd").click(function () { 
			    $(".show").slideToggle(100);
				return false;
			});
			
		});
		
	}
	
};
 
Site.start();