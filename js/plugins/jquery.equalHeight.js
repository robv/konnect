jQuery.fn.equalHeightAll = function() {
	var $ = jQuery, tallest = 0, smallest = 0;
	this.each(function() {
		thisHeight = $(this).height();
		if(thisHeight > tallest) {
			tallest = thisHeight;
		}
		else {
			smallest = $(this).attr("id");
		}
	});
	this.each(function() {
		$("#" + smallest).height(tallest);
	});
};

jQuery.fn.equalHeight = function() {
	var $ = jQuery, tallest = 0;
	this.each(function() {
		thisHeight = $(this).height();
		if(thisHeight > tallest) {
			tallest = thisHeight;
		}
	});
	this.each(function() {
		$(this).css("min-height",tallest);
		$(this).addClass("auto_height");
		$(this).css("height",tallest);
	});
};