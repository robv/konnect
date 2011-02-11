var Site = {

	// this vars should be set in <head> server-side
	config : {
		base_url : '',
		site_url : ''
	},
	
	// this method is called on every page
	start : function() {

		jQuery(function($) {
			
			setTimeout(function(){
				$('.flash').fadeTo('slow', 0).slideUp();
			}, 2000);
			
			$('input.hint').input_hint();
			$('a[rel*=facebox]').facebox();			
		});

	},
	
	indexer : function() {

		jQuery(function($) {
	
			$('.entry_row').bind('mouseover', function() {
				$('.entry_actions', $(this)).show();
			}).bind('mouseout', function() {
				$('.entry_actions', $(this)).hide();
			});
		
		
		});
		
	},
	
	deleter : function() {

		jQuery(function($) {
	
			$('.no').bind('click', function() {
				$(document).trigger('close.facebox');
				return false;
			});
			
			$('.yes').bind('click', function() {
				var entry_id = $(this).attr('title');
				$(document).trigger('close.facebox');
				$("#output").load($(this).attr('href') + '?confirm=yes');
				setTimeout(function(){
					$('#entry_' + entry_id).fadeOut('slow');
				}, 800);
				return false;
			});
		
		});
		
	}
	
};

Site.start();