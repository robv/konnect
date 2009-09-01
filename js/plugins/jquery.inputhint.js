(function($) {

	$.fn.input_hint = function(params) {
		
		// merge default and user parameters
		params = $.extend( {hint_parameter: 'title'}, params);
		
	    return this.each(function() {

	        var $this = $(this);

	        $this.attr('value', $this.attr(params.hint_parameter));

	        $this.focus(function() {
	            if ($this.attr('value') == $this.attr(params.hint_parameter))
	                $this.attr('value', '');
	        }).blur(function() {
	            if ($this.attr('value') == '')
	                $this.attr('value', $this.attr(params.hint_parameter));
	        }).parents('form').submit(function() {
	            if ($this.attr('value') == $this.attr(params.hint_parameter))
	                $this.attr('value', '');
	        });

	    });

		// allow jQuery chaining
		return this;
	};

})(jQuery);