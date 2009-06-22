<?php

class Forms {
	
	public $fields = array();
	public $iterations;
	
	public $name;
	public $form_wrapper = '<div id="%group%">%body%</div>';
	public $row_wrapper = "<div><label for=\"%id%\">%name%:</label><div class=\"input_field\">%field%</div></div>\n";
	public $row_seperator = '<div class="hr"><hr /></div>';
	
	public $output;
	
	
	// Build all your info first and then send it to constructor for display
	function __construct($name = 'form', $iterations = '1')
	{
		
		$this->name = $name;
		$this->iterations = $iterations;
		
		$this->form_wrapper = str_replace("%group%", $this->name, $this->form_wrapper);
		
	}

/*
	Info Array Example:
	type (field type: text, textarea, etc)
	display_name
	class
	layout (would replace "this->row_wrapper")
	options (this is an array with extra shizzle...)
		size
		col
		rows
		etc....
*/
	function add_field($name,$info='') 
	{
			
		if (!is_array($info))
			$info = array();
			
		foreach ($info as $k => $v) 
		{
			$this->fields['0'][$name][$k] = $v;
		}
		
		// Get rid of white spaces
		$this->fields['0'][$name]['name'] = str_replace(' ', '_', $name);

		// Display name is required so let's make sure it's set
		$this->fields['0'][$name]['display'] = (isset($info['display'])) ? $info['display'] : ucwords(str_replace('_',' ',$name));

		// If id isn't set then set it to "name"
		$this->fields['0'][$name]['id'] = (isset($info['id'])) ? str_replace(' ', '_', $info['id']) : $this->fields['0'][$name]['name'];
		
		// If value is set then use it
		$this->fields['0'][$name]['value'] = (isset($info['value'])) ? $info['value'] : '';
		
	}
	
	function iterate() 
	{
		$original_fields = $this->fields;			
	
		// Loop through iterations and build new field names
		for ($i = 0; $i < $this->iterations; $i++) 
		{
			foreach ($original_fields['0'] as $name => $info) 
			{
				// Grab original information
				$this->fields[$i][$name] = $info;
	
				// These are the ones we have to alter for iterations
				$this->fields[$i][$name]['id'] = $info['id'].'_'.$i;
				$this->fields[$i][$name]['name'] = $info['name'].'_'.$i;
				$this->fields[$i][$name]['value'] = isset($_POST[$name.'_'.$i]) ? $_POST[$name.'_'.$i] : $info['value'];
				$this->fields[$i][$name]['iteration'] = $i;
	
				if (isset($value['options']['title'])) // This is specifically for slug as of now because it needs to replace %n% with iteration
					$this->fields[$i][$name]['options']['title'] = str_replace('%n%',$i,$info['options']['title']);
			}
		}
	}
	
	
	function display()
	{
		$field_output = '';
		
		foreach ($this->fields as $fields) 
		{
			foreach ($fields as $name => $info) 
			{	
				// If the field type is hidden then we want to modify the layout
				// TODO: There should be a better way to send default values for specific field types
				if ($info['type'] === 'hidden') 			
					$info['layout'] = '%field%';
					
					$field_output .= isset($info['layout']) ? $info['layout'] : $this->row_wrapper;
					
					$field_output = str_replace("%id%",$info['id'],$field_output);
					$field_output = str_replace("%name%",$info['display'],$field_output);
					$field_output = str_replace("%field%",$this->display_input($info),$field_output);	
			}
			$field_output .= $this->row_seperator;
		}
		
		$this->output = str_replace('%body%', $field_output, $this->form_wrapper);
		
		return $this->output;
	}


	// Currently does not support validation types with extra options
	function validate()
	{
		
		$field_output = '';
		
		foreach($this->fields as $fields) 
		{
			foreach($fields as $name => $info) 
			{		
				if (isset($info['validation']))
				{	
					$validation = explode(',',$info['validation']);	
					
					foreach($validation as $validator) 
					{	// TODO: Make this more robust, right now, only handles very basic error methods
						if(!empty($validator))
							Error::instance()->$validator($info['value'], $info['name'], $info['display']);
					}
				}
			}
		}
	}
	
	
// ALL THE INPUT TYPES ARE LISTED BELOW...	
	
	
	// THIS HANDLES ALL INPUTS
	function display_input($info)
	{
		// Pretty basic, just about every input type can include these...
		$info['attributes'] = '';
		$info['attributes'] .= isset($info['id']) ? ' id="'.$info['id'].'"' : '';
		$info['attributes'] .= isset($info['name']) ? ' name="'.$info['name'].'"' : '';
		$info['attributes'] .= isset($info['type']) ? ' type="'.$info['type'].'"' : '';
		$info['attributes'] .= isset($info['class']) ? ' class="'.$info['class'].'"' : ' class="input_default"';	

		$info['attributes'] .= isset($info['value']) ? ' value="'.$info['value'].'"' : '';

		// These are more than likely specific to one input type or another
		$info['attributes'] .= isset($info['options']['size']) ? ' size="'.$info['options']['size'].'"' : '';
		$info['attributes'] .= isset($info['options']['src']) ? ' src="'.$info['options']['src'].'"' : '';
		$info['attributes'] .= isset($info['options']['title']) ? ' title="'.$info['options']['title'].'"' : '';
		$info['attributes'] .= isset($info['options']['cols']) ? ' cols="'.$info['options']['cols'].'"' : '';
		$info['attributes'] .= isset($info['options']['rows']) ? ' rows="'.$info['options']['rows'].'"' : '';
		
		
		$out = $this->$info['type']($info);
		
		// You can amend information right after input field by using "extra" in options field
		if (isset($info['options']['extra']))
			$out .= $info['options']['extra'];
		
		return $out;
	}
	
	
	// OTHER FUNCTIONS SUCH AS TEXT,HIDDEN, AND CHECKBOX ARE BASED OFF OF THIS
	function basic_input($info)
	{
		$out = '<input'.$info['attributes'].' />';
		return $out;
	}

	function text($info)
	{
		$info['type'] = 'text';
		return $this->basic_input($info);
	}

	// Requires jquery slug plugin
	function slug($info)
	{	
		if (isset($info['options']['class']))
			$info['options']['class'] .= ' slug input_default';
		else
			$info['options']['class'] = 'slug input_default';
		
		$info['type'] = 'text';
		return $this->basic_input($info);
	}

	function hidden($info)
	{
		return $this->basic_input($info);
	}

	function checkbox($info){
		$info['type'] = 'checkbox';
		return $this->basic_input($info);
	}

	function password($info){
		$info['type'] = 'password';
		return $this->basic_input($info);
	}

	function file($info){

		$info['options']['extra'] = '<p class="form_inner_form"><input type="checkbox" name="'.$name.'_crop" id="'.$name.'_crop" checked="checked" value="yes" /><label for="'.$name.'_crop">Crop After Upload</label></p>';

		$out = $this->basic_input($info);
		
		// Located in libraries folder
		$gd = new Gd();
		
		// Check if there is already and image / file in place and display it to the user
		if ($gd->load_file('./files/uploads/large/' . $info['value']))
			$out .= '<span class="current">Current Image: <a href="' . WEB_ROOT . 'files/uploads/large/' . $info['value'] . '" rel="facebox">' . $info['value'] . '</a></span>';
		else
			$out .= '<span class="current">Current File: <a href="' . WEB_ROOT . 'files/uploads/original/' . $info['value'] . '" target="new">' . $info['value'] . '</a></span>';		
		
		return $out;
	}

	function submit($info){
		$info['type'] = 'submit';
		return $this->basic_input($info);
	}

	function image($info){
		$info['type'] = 'image';
		return $this->basic_input($info);
	}

	function textarea($info){
		
		if(!isset($info['options']['cols']))
			$info['options']['cols'] = 50;
		
		if(!isset($info['options']['rows']))
			$info['options']['rows'] = 5;
		
		$info['value'] = isset($info['value']) ? $info['value'] : '';
		
		$attributes = isset($info['options']['rows']) ? ' rows="'.$info['options']['rows'].'"' : 5;
		$attributes .= isset($info['options']['cols']) ? ' cols="'.$info['options']['cols'].'"' : 50;
		$attributes .= isset($info['options']['class']) ? ' class="'.$info['options']['class'].'"' : ' class="input_full"';
		$attributes .= isset($info['options']['title']) ? ' title="'.$info['options']['title'].'"' : '';
		$attributes .= isset($info['name']) ? ' name="'.$info['name'].'"' : '';
		$attributes .= isset($info['id']) ? ' id="'.$info['id'].'"' : '';
		
		$out = '<textarea'.$attributes.'>'.$info['value'].'</textarea>';
		
		if(isset($info['options']['extra']))
			$out .= $info['options']['extra'];
			
		return $out;
	}	
	
	function dropdown($info){
		
		$attributes = isset($info['options']['class']) ? ' class="'.$info['options']['class'].'"' : '';
		$attributes .= isset($info['options']['title']) ? ' title="'.$info['options']['title'].'"' : '';
		$attributes .= isset($info['name']) ? ' name="'.$info['name'].'"' : '';
		$attributes .= isset($info['id']) ? ' id="'.$info['id'].'"' : '';
		
		$out = '<select'.$attributes.'>';
		
			// Pass selectName as option to change default view
			if(isset($info['options']['default']))
				$out .= '<option value="">'.$info['default'].'</option>';
			
			if(!empty($info['options'])){
				foreach($info['options'] as $key => $value) :
					if($key !== 'default' && $key !== 'class' && $key !== 'title'){
						if($info['value'] === $key){ $selected = ' selected'; }
						else{ $selected = ''; }
						$out .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
					}
				endforeach;
			}
	
		$out .= '</select>';
		
		if(isset($info['options']['extra']))
			$out .= $info['options']['extra'];
			
		return $out;
	}

	// Build a select menu using the get_options function in functions.inc
	function related($info){

		$out = '<select name="'.$info['name'].'" id="'.$info['id'].'">';

		// Pass selectName as option to change default view
		if(isset($info['options']['selectName']))
			$out .= '<option value="">'.$info['options']['selectName'].'</option>';

			if(!isset($info['options']['sql']))
				$info['options']['sql'] = '';

			$out .=  $this->get_options($info['options']['table'],$info['options']['val'],$info['options']['text'],$info['value'],NULL,$info['options']['sql']);

		$out .= '</select>';
		
		if(Auth::getAuth()->user->level === 'admin')
			$out .= '<div class="clearfix modal_add"><a href="'.WEB_ROOT.'franchiser/default/modalForm/'.deslugify($info['options']['table'],'-').'/?table='.$info['options']['table'].'&textField='.$info['options']['text'].'&valueField='.$info['options']['val'].'&idField='.$info['id'].'" rel="facebox[.modal_large]" class="add">Add Entry</a></div>';

		if(isset($info['options']['extra']))
			$out .= $info['options']['extra'];

		return $out;
	}

	// Build a select menu using the get_options function in functions.inc
	function related_dependent($info){

		$out = '<script language="javascript">
			$(document).ready(function()
			{
				makeSublist(\''.$info['options']['parent'].'_'.$info['iteration'].'\',\''.$info['id'].'\', false, \'\');	
			});
		</script>';

		$out .= '<select name="'.$info['name'].'" id="'.$info['id'].'">';

		// Pass selectName as option to change default view
		if(isset($info['options']['selectName']))
			$out .= '<option value="">'.$info['options']['selectName'].'</option>';

			if(!isset($info['sql']))
				$info['sql'] = '';
			$out .=  $this->get_options($info['options']['table'],$info['options']['val'],$info['options']['text'],$info['value'],$info['options']['dependent']);

		$out .= '</select>';

		if(Auth::getAuth()->user->level === 'admin')
			$out .= '<div class="clearfix modal_add"><a href="'.WEB_ROOT.'franchiser/default/modalForm/'.deslugify($info['options']['table'],'-').'/?table='.$info['options']['table'].'&textField='.$info['options']['text'].'&valueField='.$info['options']['val'].'&idField='.$info['id'].'" rel="facebox[.modal_large]" class="add">Add Entry</a></div>';

		if(isset($info['options']['extra']))
			$out .= $info['options']['extra'];

		return $out;
	}

		// This is used in related() function
		function get_options($table, $val, $text, $default = null, $class = null, $sql = '')
	    {
	        $db = Database::getDatabase(true);
	        $out = '';
			
	        $rows = $db->getRows("SELECT * FROM `$table` $sql");
	        foreach($rows as $row)
	        {
				if(!is_null($class))
					$option_class = ' class="sub_' . $row[$class] . '"';
				else
					$option_class = '';
				
	            $the_text = '';
	            if(!is_array($text)) $text = array($text); // Allows you to concat multiple fields for display
	            foreach($text as $t)
	                $the_text .= $row[$t] . ' ';
	            $the_text = htmlspecialchars(trim($the_text));

	            if(!is_null($default) && $row[$val] == $default)
	                $out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '"'.$option_class.' selected="selected">' . $the_text . '</option>';
	            elseif(is_array($default) && in_array($row[$val],$default))
	                $out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '"'.$option_class.' selected="selected">' . $the_text . '</option>';
	            else
	                $out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '"'.$option_class.'>' . $the_text . '</option>';
	        }
	        return $out;
	    }
	
	
///////////////// FUNCTIONS THAT USE OTHER FORM TYPES ////////////////////////////////


	function company_id($info){
		
		$ap = new AlterPath(array()); 
		$data['url_structure'] = $ap->pick_off();
		$company = new Companies();
		$company->select($data['url_structure']['1'],'slug');
		
		$info['type'] = 'hidden';
		$info['value'] = $company->id;
		return $this->basic_input($info);
	}

	function tables($info){
    	$db = Database::getDatabase();
		
		$arrTables = array();
		$result = $db->query('SHOW TABLES');
		while($row = mysql_fetch_array($result)) $info['options'][$row['0']] = $row['0'];
		$out = $this->dropdown($info);
		
		return $out;
	}


	function states($info){
		$info['options'] = array('AL'=>"Alabama",
		                'AK'=>"Alaska", 
		                'AZ'=>"Arizona", 
		                'AR'=>"Arkansas", 
		                'CA'=>"California", 
		                'CO'=>"Colorado", 
		                'CT'=>"Connecticut", 
		                'DE'=>"Delaware", 
		                'DC'=>"District Of Columbia", 
		                'FL'=>"Florida", 
		                'GA'=>"Georgia", 
		                'HI'=>"Hawaii", 
		                'ID'=>"Idaho", 
		                'IL'=>"Illinois", 
		                'IN'=>"Indiana", 
		                'IA'=>"Iowa", 
		                'KS'=>"Kansas", 
		                'KY'=>"Kentucky", 
		                'LA'=>"Louisiana", 
		                'ME'=>"Maine", 
		                'MD'=>"Maryland", 
		                'MA'=>"Massachusetts", 
		                'MI'=>"Michigan", 
		                'MN'=>"Minnesota", 
		                'MS'=>"Mississippi", 
		                'MO'=>"Missouri", 
		                'MT'=>"Montana",
		                'NE'=>"Nebraska",
		                'NV'=>"Nevada",
		                'NH'=>"New Hampshire",
		                'NJ'=>"New Jersey",
		                'NM'=>"New Mexico",
		                'NY'=>"New York",
		                'NC'=>"North Carolina",
		                'ND'=>"North Dakota",
		                'OH'=>"Ohio", 
		                'OK'=>"Oklahoma", 
		                'OR'=>"Oregon", 
		                'PA'=>"Pennsylvania", 
		                'RI'=>"Rhode Island", 
		                'SC'=>"South Carolina", 
		                'SD'=>"South Dakota",
		                'TN'=>"Tennessee", 
		                'TX'=>"Texas", 
		                'UT'=>"Utah", 
		                'VT'=>"Vermont", 
		                'VA'=>"Virginia", 
		                'WA'=>"Washington", 
		                'WV'=>"West Virginia", 
		                'WI'=>"Wisconsin", 
		                'WY'=>"Wyoming");
			
		$out = $this->dropdown($info);

		return $out;
	}
	
	function time_range($info){
		$info['options'] = array(
								'6:00 am' => '6:00 am',
								'6:30 am' => '6:30 am',
								'7:00 am' => '7:00 am',
								'7:30 am' => '7:30 am',
								'8:00 am' => '8:00 am',
								'8:30 am' => '8:30 am',
								'9:00 am' => '9:00 am',
								'9:30 am' => '9:30 am',
								'10:00 am' => '10:00 am',
								'10:30 am' => '10:30 am',
								'11:00 am' => '11:00 am',
								'11:30 am' => '11:30 am',
								'12:00 pm' => '12:00 pm',
								'12:30 pm' => '12:30 pm',
								'1:00 pm' => '1:00 pm',
								'1:30 pm' => '1:30 pm',
								'2:00 pm' => '2:00 pm',
								'2:30 pm' => '2:30 pm',
								'3:00 pm' => '3:00 pm',
								'3:30 pm' => '3:30 pm',
								'4:00 pm' => '4:00 pm',
								'4:30 pm' => '4:30 pm',
								'5:00 pm' => '5:00 pm',
								'5:30 pm' => '5:30 pm',
								'6:00 pm' => '6:00 pm',
								'6:30 pm' => '6:30 pm',
								'7:00 pm' => '7:00 pm',
								'7:30 pm' => '7:30 pm',
								'8:00 pm' => '8:00 pm',
								'8:30 pm' => '8:30 pm',
								'9:00 pm' => '9:00 pm',
								'9:30 pm' => '9:30 pm',
								'10:00 pm' => '10:00 pm',
								'10:30 pm' => '10:30 pm',
								'11:00 pm' => '11:00 pm',
								'11:30 pm' => '11:30 pm',
							);

		$infocop = $info;
		
		if(!empty($info['value']))
			$ex = explode(' to ',$info['value']);
			
		$info['value'] = isset($ex['0']) ? $ex['0'] : '';
		$infocop['value'] = isset($ex['1']) ? $ex['1'] : '';

		$out = $this->dropdown($info);
		
		
		$infocop['name'] = 'second_'.$infocop['name'];
		$infocop['id'] = 'second_'.$infocop['id'];
		$out .= ' - ' . $this->dropdown($infocop);
		
		return $out;
	}

	function timestamp($info){
		
		$info['options']['class'] = 'input_timestamp';
		
		if(isset($info['value']) && !empty($info['value']))
				$info['value'] = (preg_match('#^\d+/\d+/\d+ @ \d+:\d+ \w\w$#',$info['value']) == 0) ? dater($info['value'],'m/d/Y @ h:i a') : $info['value'];
		else
			$info['value'] = date('m/d/Y @ h:i a');
	
		$out = $this->text($info);
	
		$out .=
	    '	<script type="text/javascript">
		    	$(function()
	            	{
						$(\'#'.$info['name'].'\').DatePicker({
							date: $(\'#'.$info['name'].'\').val(),
							current: $(\'#'.$info['name'].'\').val(),
							starts: 1,
							format: \'m/d/Y\',
							position: \'right\',
							onBeforeShow: function(){
								$(\'#'.$info['name'].'\').DatePickerSetDate($(\'#'.$info['name'].'\').val(), true);
							},
							onChange: function(formated, dates){
								$(\'#'.$info['name'].'\').val(formated + " @ '.date('h:i a').'");
							}
						});

		            });
		    </script>';

	return $out;
		
	}


	// requires javascript files
	function dater($info){
		
		$info['options']['class'] = 'input_date';
		
		if(isset($info['value']) && !empty($info['value']))
			$info['value'] = dater($info['value'],'m/d/Y');
		else
			$info['value'] = date('m/d/Y');
	
		$out = $this->text($info);
	
		$out .=
	    '	<script type="text/javascript">
		    	$(function()
	            	{
						$(\'#'.$info['id'].'\').DatePicker({
							date: $(\'#'.$info['id'].'\').val(),
							current: $(\'#'.$info['id'].'\').val(),
							starts: 1,
							format: \'m/d/Y\',
							position: \'right\',
							onBeforeShow: function(){
								$(\'#'.$info['id'].'\').DatePickerSetDate($(\'#'.$info['id'].'\').val(), true);
							},
							onChange: function(formated, dates){
								$(\'#'.$info['id'].'\').val(formated);
							}
						});

		            });
		    </script>';

	return $out;
	
	}
	
	
	// requires javascript files
	function thumbnailer($info){
		
		$out = '<script language="javascript">
				jQuery(function($) {
					$(\'.input_thumbs img\').bind(\'click\', function(e) {
						var $this = $(this);
						$(\'.input_thumbs img\').removeClass(\'on\');
						$this.addClass(\'on\');
						$(\'#'.$info['id'].'\').val($this.attr(\'title\'));
				    });	
				});
		</script>';
		
		$images = explode('/',$info['options']['images']);
		
		$out .= '<div class="input_thumbs">';
	
		foreach($images as $image){
			
			$imgobj = new Media_gallery($image);
			$out .= '<img src="'.WEB_ROOT.'files/uploads/small/'.$imgobj->file.'" title="'.WEB_ROOT.'files/uploads/original/'.$imgobj->file.'" />';
			
		}
		
		$out .= '</div>';
		
		$out .= $this->hidden($info);
	
	return $out;
	
	}
	

	function hour($name)
	{
		if(empty($this->fields['0'][$name]['value']))
			$this->fields['0'][$name]['value'] = date('h');
		
		$hours = array(12, 1, 2, 3, 4, 5, 6, 7, 9, 10, 11);

		foreach($hours as $hour) :
			$this->fields['0'][$name]['options'][$hour] = $hour;
		endforeach;
	
		$output = $this->dropdown($name);
	
		return $output;
		
	}
	

	function minute($name)
	{
		if(empty($this->fields['0'][$name]['value']))
			$this->fields['0'][$name]['value'] = date('i');
		
		$minutes = array('00', 15, 30, 45);

		foreach($minutes as $minute) :
			$this->fields['0'][$name]['options'][$minute] = $minute;
		endforeach;
	
		$output = $this->dropdown($name);
	
		return $output;
	}
	

	function ampm($name)
	{
		if(empty($this->fields['0'][$name]['value']))
			$this->fields['0'][$name]['value'] = date('a');
		
		$ampms = array('am','pm');

		foreach($ampms as $ampm) :
			$this->fields['0'][$name]['options'][$ampm] = $ampm;
		endforeach;
	
		$output = $this->dropdown($name);
	
		return $output;
	}
	
	
	// TO USE THIS YOU WOULD NEED TO MAKE SURE THE
	// TINYMCE JAVASCRIPT LIBRARY IS SET UP
	function htmleditorsimple($info){
		
			$out = '<script language="javascript" type="text/javascript">
				tinyMCE.init({
					mode : "exact",
					elements : "'.$info['name'].'",
					theme : "advanced",
					skin:"thebigreason",
					theme_advanced_buttons1: "formatselect,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,code,fullscreen",
					theme_advanced_buttons2:"", 
					theme_advanced_buttons3:"",
					theme_advanced_buttons4:"", 
					theme_advanced_toolbar_location:"top",
					theme_advanced_toolbar_align:"left",
					theme_advanced_statusbar_location:"bottom",
					theme_advanced_resizing:"1",
					theme_advanced_resize_horizontal:"",
					dialog_type:"clearlooks2",
					relative_urls:"",
					remove_script_host:"", convert_urls:"",
					apply_source_formatting:"",
					remove_linebreaks:"1",
					paste_convert_middot_lists:"1",
					paste_remove_spans:"1",
					paste_remove_styles:"1",
					gecko_spellcheck:"1",
					entities:"38,amp,60,lt,62,gt",
					accessibility_focus:"1",
					tab_focus:":prev,:next",
					wpeditimage_disable_captions:"", 
					plugins:"safari,inlinepopups,spellchecker,paste,media,fullscreen"
				});
			</script>';
		
		
			$info['options']['class'] = 'html_editor_simple';
			
		return $out.$this->textarea($info);
	}
	
	
	// TO USE THIS YOU WOULD NEED TO MAKE SURE THE
	// TINYMCE JAVASCRIPT LIBRARY IS SET UP
	function htmleditor($info){
		
			$out = '<script language="javascript" type="text/javascript">
				tinyMCE.init({
					mode : "exact",
					elements : "'.$info['name'].'",
					theme : "advanced",
					skin:"thebigreason",
					theme_advanced_buttons1: "formatselect,bold,italic,underline,strikethrough,|,bullist,numlist,|,image,media,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,charmap,pastetext,pasteword,|,code,fullscreen",
					theme_advanced_buttons2:"", 
					theme_advanced_buttons3:"",
					theme_advanced_buttons4:"", 
					theme_advanced_toolbar_location:"top",
					theme_advanced_toolbar_align:"left",
					theme_advanced_statusbar_location:"bottom",
					theme_advanced_resizing:"1",
					theme_advanced_resize_horizontal:"",
					dialog_type:"clearlooks2",
					relative_urls:"",
					remove_script_host:"", convert_urls:"",
					apply_source_formatting:"",
					remove_linebreaks:"1",
					paste_convert_middot_lists:"1",
					paste_remove_spans:"1",
					paste_remove_styles:"1",
					gecko_spellcheck:"1",
					entities:"38,amp,60,lt,62,gt",
					accessibility_focus:"1",
					tab_focus:":prev,:next",
					wpeditimage_disable_captions:"", 
					plugins:"safari,inlinepopups,spellchecker,paste,media,fullscreen"
				});
			</script>';
		
		
			$info['options']['class'] = 'html_editor';
			
		return $out.$this->textarea($info);
	}


}