var Site = {

	// this vars should be set in <head> server-side
	config : {
		base_url : '',
		site_url : ''
	},
	
	// this method is called on every page
	start : function() {

		// On Dom Ready
		jQuery(function($) {
			setTimeout(function(){
				$('.flash').slideUp(function() {
					$('.flash').remove();
			  	});
				$('.flash').fadeOut();
			}, 2000);
		});
		
		// Load Immediately
		(function($) {
		
		})(jQuery);


		// On Window Load
		jQuery(window).load(function($) {

		});

	},
	
	login : function() {
	}
	
};

Site.start();