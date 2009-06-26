/*
	When parent option is selected, makes visible all child options with the class name sub_"select option value"
	
	Parameter List:
	parent = parent select box
	child = child select box
	isSubselectOptional = if true will prepend option with the class name of "default" to ever menu
	defaultValue = pass in default value for child select box
*/
function sublist(parent,child,isSubselectOptional,defaultValue)
{
	$("body").append("<select style='display:none' id='" + parent + child + "'></select>");
	$('#' + parent + child).html($("#" + child + " option"));
	
	var parentValue = $('#' + parent).attr('value');
	$('#' + child).html($("#" + parent + child + " .sub_" + parentValue).clone());

	defaultValue = (typeof defaultValue == "undefined")? "" : defaultValue ;
	$("#" + child + ' option[@value="' + defaultValue +'"]').attr('selected','selected');
	
	$('#' + parent).change( 
		function()
		{
			var parentValue = $('#' + parent).attr('value');
			$('#' + child).html($("#" + parent + child + " .sub_" + parentValue).clone());
			if(isSubselectOptional) $('#' + child).prepend($("#" + parent + child + " .default").clone());
			$('#' + child).trigger("change");
                        $('#' + child).focus();
		}
	);
}