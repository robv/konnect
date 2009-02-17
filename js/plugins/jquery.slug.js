//
//	jQuery Slug Generation Plugin by Robert Velasquez (robert@pseudolab.com
//  Kinda Based on jQuery Slug Generation Plugin by Perry Trinier (perrytrinier@gmail.com)
//  Licensed under the GPL: http://www.gnu.org/copyleft/gpl.html


	jQuery.fn.slug = function() {

	  return this.each(function() { 

			var $this = $(this);

			$this.keyup(function(){

				var slugcontent = $this.val();
				var slugcontent_hyphens = slugcontent.replace(/\s+/g,'-');
				var finishedslug = slugcontent_hyphens.replace(/[^a-zA-Z0-9\-]/g,'');

				$('input#' + this.title).val(finishedslug);

			});
	  });
	};
