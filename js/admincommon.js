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
			
			$("form :input:visible:enabled:first").focus();
			
		});
		
	}
	
};
 
Site.start();

function makeSublist(parent,child,isSubselectOptional,childVal)
{
	$("body").append("<select style='display:none' id='"+parent+child+"'></select>");
	$('#'+parent+child).html($("#"+child+" option"));
	
		var parentValue = $('#'+parent).attr('value');
		$('#'+child).html($("#"+parent+child+" .sub_"+parentValue).clone());
	
	childVal = (typeof childVal == "undefined")? "" : childVal ;
	$("#"+child+' option[@value="'+ childVal +'"]').attr('selected','selected');
	
	$('#'+parent).change( 
		function()
		{
			var parentValue = $('#'+parent).attr('value');
			$('#'+child).html($("#"+parent+child+" .sub_"+parentValue).clone());
			if(isSubselectOptional) $('#'+child).prepend("<option value='none'> -- Select -- </option>");
			$('#'+child).trigger("change");
                        $('#'+child).focus();
		}
	);
}