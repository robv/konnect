jQuery.fn.input_hint = function() {
	
    return this.each(function() {
	
        var $this = $(this);

        $this.attr('value', $this.attr('title'));

        $this.focus(function() {
            if ($this.attr('value') == $this.attr('title'))
                $this.attr('value', '');
        }).blur(function() {
            if ($this.attr('value') == '')
                $this.attr('value', $this.attr('title'));
        }).parents('form').submit(function() {
            if ($this.attr('value') == $this.attr('title'))
                $this.attr('value', '');
        });

    });

};