<?php

class Forms {
	
	public $fields = array();
	public $iterations;
	
	public $name;
	public $form_wrapper = '<div id="%group%">%body%</div>';
	public $row_wrapper = "<div class=\"input_row clearfix\"><label for=\"%id%\">%name%:</label><div class=\"input_field\">%field%</div></div>\n";
	public $row_seperator = '<div class="hr"><hr /></div>';
	
	public $output;
	
	
	// Build all your info first and then send it to constructor for display
	public function __construct($name = 'form', $iterations = 1)
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
	public function add_field($name, $info='') 
	{
			
		if (!is_array($info))
		{
			$info = array();
		}
			
		foreach ($info as $k => $v) 
		{
			$this->fields['0'][$name][$k] = $v;
		}
		
		// Get rid of white spaces
		$this->fields['0'][$name]['name'] = str_replace(' ', '_', $name);

		// Display name is required so let's make sure it's set
		$this->fields['0'][$name]['display'] = isset($info['display']) ? $info['display'] : ucwords(str_replace('_',' ',$name));

		// If id isn't set then set it to "name"
		$this->fields['0'][$name]['id'] = isset($info['id']) ? str_replace(' ', '_', $info['id']) : $this->fields['0'][$name]['name'];
		
		// If value is set then use it
		$this->fields['0'][$name]['value'] = empty($info['value']) ? '' : $info['value'];
		
		$this->fields['0'][$name]['type'] = isset($info['type']) ? $info['type'] : 'text';
		
	}
	
	public function iterate() 
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
				$this->fields[$i][$name]['id'] = $info['id'] . '_' . $i;
				$this->fields[$i][$name]['name'] = $info['name'] . '_' . $i;
				$this->fields[$i][$name]['value'] = isset($_POST[$name.'_' . $i]) ? $_POST[$name.'_' . $i] : $info['value'];
				$this->fields[$i][$name]['iteration'] = $i;
		
				if (isset($value['options']['title'])) // This is specifically for slug as of now because it needs to replace %n% with iteration
				{
					$this->fields[$i][$name]['options']['title'] = str_replace('%n%', $i, $info['options']['title']);					
				}
			}
		}
	}
	
	
	public function display()
	{
		$field_output = '';
		
		foreach ($this->fields as $fields) 
		{
			foreach ($fields as $name => $info) 
			{	
				// If the field type is hidden then we want to modify the layout
				// TODO: There should be a better way to send default values for specific field types
				if ($info['type'] == 'hidden') 	
				{
					$info['layout'] = '%field%';					
				}		
				$field_output .= isset($info['layout']) ? $info['layout'] : $this->row_wrapper;
				
				$field_output = str_replace("%id%", $info['id'], $field_output);
				$field_output = str_replace("%name%", $info['display'], $field_output);
				$field_output = str_replace("%field%", $this->display_input($info), $field_output);	
			}
			$field_output .= $this->row_seperator;
		}
		
		$this->output = str_replace('%body%', $field_output, $this->form_wrapper);
		
		return $this->output;
	}


	// Currently does not support validation types with extra options
	public function validate()
	{
		
		$field_output = '';
		
		foreach ($this->fields as $fields) 
		{
			foreach ($fields as $name => $info) 
			{		
				if (isset($info['validation']))
				{	
					$validation = explode(',', $info['validation']);	
					
					foreach ($validation as $validator) 
					{	// TODO: Make this more robust, right now, only handles very basic error methods
						if (!empty($validator))
							Error::instance()->$validator($info['value'], $info['name'], $info['display']);
					}
				}
			}
		}
	}
	
	
	public function display_input($info)
	{
		// Pretty basic, just about every input type can include these...
		$info['attributes'] = '';
		$info['attributes'] .= isset($info['id']) ? ' id="' . htmlspecialchars($info['id']) . '"' : '';
		$info['attributes'] .= isset($info['name']) ? ' name="' . htmlspecialchars($info['name']) . '"' : '';
		$info['attributes'] .= isset($info['type']) ? ' type="' . htmlspecialchars($info['type']) . '"' : '';
		$info['attributes'] .= isset($info['class']) ? ' class="' . htmlspecialchars($info['class']) . '"' : ' class="input_default"';	

		$info['attributes'] .= isset($info['value']) ? ' value="' . htmlspecialchars($info['value']) . '"' : '';

		// These are more than likely specific to one input type or another
		$info['attributes'] .= isset($info['options']['size']) ? ' size="' . htmlspecialchars($info['options']['size']) . '"' : '';
		$info['attributes'] .= isset($info['options']['src']) ? ' src="' . htmlspecialchars($info['options']['src']) . '"' : '';
		$info['attributes'] .= isset($info['options']['title']) ? ' title="' . htmlspecialchars($info['options']['title']) . '"' : '';
		$info['attributes'] .= isset($info['options']['cols']) ? ' cols="' . htmlspecialchars($info['options']['cols']) . '"' : '';
		$info['attributes'] .= isset($info['options']['rows']) ? ' rows="' . htmlspecialchars($info['options']['rows']) . '"' : '';
		
		
		$out = $this->$info['type']($info);
		
		// You can amend information right after input field by using "extra" in options field
		if (isset($info['options']['extra']))
		{
			$out .= $info['options']['extra'];
		}
		
		return $out;
	}
	
	
	public function basic_input($info)
	{
		// Pretty basic, just about every input type can include these...
		$info['attributes'] = '';
		$info['attributes'] .= isset($info['id']) ? ' id="' . htmlspecialchars($info['id']) . '"' : '';
		$info['attributes'] .= isset($info['name']) ? ' name="' . htmlspecialchars($info['name']) . '"' : '';
		$info['attributes'] .= isset($info['type']) ? ' type="' . htmlspecialchars($info['type']) . '"' : '';
		$info['attributes'] .= isset($info['class']) ? ' class="' . htmlspecialchars($info['class']) . '"' : ' class="input_default"';	

		$info['attributes'] .= isset($info['value']) ? ' value="' . htmlspecialchars($info['value']) . '"' : '';

		// These are more than likely specific to one input type or another
		$info['attributes'] .= isset($info['options']['size']) ? ' size="' . htmlspecialchars($info['options']['size']) . '"' : '';
		$info['attributes'] .= isset($info['options']['src']) ? ' src="' . htmlspecialchars($info['options']['src']) . '"' : '';
		$info['attributes'] .= isset($info['options']['title']) ? ' title="' . htmlspecialchars($info['options']['title']) . '"' : '';
		$info['attributes'] .= isset($info['options']['cols']) ? ' cols="' . htmlspecialchars($info['options']['cols']) . '"' : '';
		$info['attributes'] .= isset($info['options']['rows']) ? ' rows="' . htmlspecialchars($info['options']['rows']) . '"' : '';
		
		$out = '<input' . $info['attributes'] . ' />';
		return $out;
	}

	public function text($info)
	{
		$info['type'] = 'text';
		return $this->basic_input($info);
	}

	// Requires jquery slug plugin
	public function slug($info)
	{	
		if (isset($info['options']['class']))
		{
			$info['options']['class'] .= ' slug input_default';			
		}
		else
		{
			$info['options']['class'] = 'slug input_default';			
		}
		
		$info['type'] = 'text';
		return $this->basic_input($info);
	}

	public function hidden($info)
	{
		return $this->basic_input($info);
	}

	public function checkbox($info)
	{
		return $this->basic_input($info);
	}

	public function password($info)
	{
		return $this->basic_input($info);
	}

	public function file($info)
	{
		$info['options']['extra'] = '<p class="form_inner_form"><input type="checkbox" name="' . htmlspecialchars($name) . '_crop" id="' . htmlspecialchars($name) .'_crop" checked="checked" value="yes" /><label for="' . htmlspecialchars($name) . '_crop">Crop After Upload</label></p>';

		$out = $this->basic_input($info);
		
		// Located in libraries folder
		$gd = new Gd();
		
		// Check if there is already and image / file in place and display it to the user
		if ($gd->load_file('./files/uploads/large/' . $info['value']))
		{
			$out .= '<span class="current">Current Image: <a href="' . WEB_ROOT . 'files/uploads/large/' . htmlspecialchars($info['value']) . '" rel="facebox">' . htmlspecialchars($info['value']) . '</a></span>';
		}
		else
		{
			$out .= '<span class="current">Current File: <a href="' . WEB_ROOT . 'files/uploads/original/' . htmlspecialchars($info['value']) . '" rel="external">' . htmlspecialchars($info['value']) . '</a></span>';
		}
		
		return $out;
	}

	public function submit($info)
	{
		return $this->basic_input($info);
	}

	public function image($info)
	{
		return $this->basic_input($info);
	}

	public function textarea($info)
	{
		if (!isset($info['options']['cols']))
		{
			$info['options']['cols'] = 50;			
		}
		
		if (!isset($info['options']['rows']))
		{
			$info['options']['rows'] = 5;
		}
		
		$info['value'] = isset($info['value']) ? $info['value'] : '';
		
		$attributes = isset($info['options']['rows']) ? ' rows="' . htmlspecialchars($info['options']['rows']) . '"' : 5;
		$attributes .= isset($info['options']['cols']) ? ' cols="' . htmlspecialchars($info['options']['cols']) . '"' : 50;
		$attributes .= isset($info['options']['class']) ? ' class="' . htmlspecialchars($info['options']['class']) . '"' : ' class="input_full"';
		$attributes .= isset($info['options']['title']) ? ' title="' . htmlspecialchars($info['options']['title']) . '"' : '';
		$attributes .= isset($info['name']) ? ' name="' . htmlspecialchars($info['name']) . '"' : '';
		$attributes .= isset($info['id']) ? ' id="' . htmlspecialchars($info['id']) . '"' : '';
		
		$out = '<textarea' . $attributes.'>' . htmlspecialchars($info['value']) . '</textarea>';
		
		if (isset($info['options']['extra']))
		{
			$out .= $info['options']['extra'];			
		}

			
		return $out;
	}	
	
	public function dropdown($info)
	{	
		$attributes = isset($info['options']['class']) ? ' class="' . htmlspecialchars($info['options']['class']) . '"' : '';
		$attributes .= isset($info['options']['title']) ? ' title="' . htmlspecialchars($info['options']['title']) . '"' : '';
		$attributes .= isset($info['name']) ? ' name="' . htmlspecialchars($info['name']) . '"' : '';
		$attributes .= isset($info['id']) ? ' id="' . htmlspecialchars($info['id']) . '"' : '';
		
		$out = '<select' . $attributes . '>';
		
		// Pass default as option to change default view
		if (isset($info['options']['default']))
		{
			$out .= '<option value="">' . htmlspecialchars($info['options']['default']) . '</option>';				
		}

		
		if (!empty($info['options']))
		{
			foreach ($info['options'] as $key => $value)
			{
				if ($key != 'default' && $key != 'class' && $key != 'title')
				{
					if($info['value'] == $key)
					{
						$selected = ' selected="selected"';
					}
					else
					{
						$selected = '';
					}
					$out .= '<option value="' . htmlspecialchars($key) . '"' . $selected . '>' . htmlspecialchars($value) . '</option>';
				}
			}
		}
	
		$out .= '</select>';
		
		if (isset($info['options']['extra']))
		{
			$out .= $info['options']['extra'];			
		}
			
		return $out;
	}

	/*
	Options for related type:
	default = default value to be shown, value is empty
	sql = this will be appended to the sql statement which by default is SELECT * FROM table
	seperator = if display is comma seperated then use this to seperate the values (ie FirstName LastName or FirstName and LastName)
	display_field = field to display to end user can be multiple fields comma seperated
	value_field = field to pass through the form
	*/
	public function related($info)
	{
		$out = '<select name="' . htmlspecialchars($info['name']) . '" id="' . htmlspecialchars($info['id']) . '">';

		// Pass default as option to change default view
		if (isset($info['options']['default']))
		{
			$out .= '<option value="">' . htmlspecialchars($info['options']['default']) . '</option>';			
		}

		if (!isset($info['options']['sql']) || empty($info['options']['sql']))
		{
			$info['options']['sql'] = NULL;			
		}

		if (!isset($info['options']['seperator']))
		{
			$info['options']['seperator'] = ' ';			
		}
			
		$display_fields = explode(',', $info['options']['display_field']);
		
		$table_obj_name = String::uc_slug($info['options']['table'], '_', '_');
		$objects = new $table_obj_name;
		$objects = $objects->select_many($info['options']['sql']);
		
		foreach ($objects as $object) 
		{
			$value_field = $info['options']['value_field'];
			$display = array();
			
			foreach ($display_fields as $display_field)
			{
				$display[] = $object->$display_field;
			}
			
			$out .= '<option value="' . htmlspecialchars($object->$value_field) . '">' . htmlspecialchars(implode($info['options']['seperator'], $display)) . '</option>';
		}

		$out .= '</select>';

		if (isset($info['options']['extra']))
		{
			$out .= $info['options']['extra'];			
		}

		return $out;
	}


	/*
		Options for related type:
		default = default value to be shown, value is empty
		sql = this will be appended to the sql statement which by default is SELECT * FROM table
		seperator = if display is comma seperated then use this to seperate the values (ie FirstName LastName or FirstName and LastName)
		display_field = field to display to end user can be multiple fields comma seperated
		value_field = field to pass through the form
		related_field = field in child table that relates to parent table
		parent = parent field id
	*/
	
	public function related_dependent($info)
	{
		// $default variable used in sublist javascript function
		$default = 'false';
		// Pass selectName as option to change default view
		if (isset($info['options']['default']))
		{
			$out .= '<option value="" class="default">' . htmlspecialchars($info['options']['default']) . '</option>';
			$default = 'true';
		}
		
		$out = "<script language=\"javascript\">
					jQuery(function($) {
						sublist('{$info['options']['parent']}_{$info['iteration']}', '{$info['id']}', '$default');
					});
				</script>";

		$out .= '<select name="' . htmlspecialchars($info['name']) . '" id="' . htmlspecialchars($info['id']) . '">';

			if (!isset($info['sql']))
			{
				$info['sql'] = '';				
			}

			$objects = new $info['options']['object'];
			$objects = $objects->select_many($info['options']['sql']);

			foreach ($objects as $object) 
			{
				$value_field = $info['options']['value_field'];
				$related_field = $info['options']['related_field'];
				$display = array();

				foreach ($display_fields as $display_field)
				{
					$display[] = $object->$display_field;
				}

				$out .= '<select value="' . htmlspecialchars($object->$value_field) . '" class="sub_' . htmlspecialchars($object->$related_field) . '">' . htmlspecialchars(implode($info['options']['seperator'], $display)) . '</select>';
			}

		$out .= '</select>';

		if (isset($info['options']['extra']))
		{
			$out .= $info['options']['extra'];			
		}

		return $out;
	}
	
	
///////////////// FUNCTIONS THAT USE OTHER FORM TYPES ////////////////////////////////

	public function tables($info)
	{
    	$db = Database::get_instance();
		
		$result = $db->query('SHOW TABLES');
		while ($row = mysql_fetch_array($result))
		{
			$obj_name = uc_slug($row[0], '_');
			if (class_exists($obj_name))
			{
				$info['options'][$row[0]] = $row[0];				
			}
		}
	
		$out = $this->dropdown($info);	
		return $out;
	}


	public function states($info, $required = true)
	{
		$info['options'] = array(
			'AL' => 'Alabama',
			'AK' => 'Alaska', 
			'AZ' => 'Arizona', 
			'AR' => 'Arkansas', 
			'CA' => 'California', 
			'CO' => 'Colorado', 
			'CT' => 'Connecticut', 
			'DE' => 'Delaware', 
			'DC' => 'District Of Columbia', 
			'FL' => 'Florida', 
			'GA' => 'Georgia', 
			'HI' => 'Hawaii', 
			'ID' => 'Idaho', 
			'IL' => 'Illinois', 
			'IN' => 'Indiana', 
			'IA' => 'Iowa', 
			'KS' => 'Kansas', 
			'KY' => 'Kentucky', 
			'LA' => 'Louisiana', 
			'ME' => 'Maine', 
			'MD' => 'Maryland', 
			'MA' => 'Massachusetts', 
			'MI' => 'Michigan', 
			'MN' => 'Minnesota', 
			'MS' => 'Mississippi', 
			'MO' => 'Missouri', 
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio', 
			'OK' => 'Oklahoma', 
			'OR' => 'Oregon', 
			'PA' => 'Pennsylvania', 
			'RI' => 'Rhode Island', 
			'SC' => 'South Carolina', 
			'SD' => 'South Dakota',
			'TN' => 'Tennessee', 
			'TX' => 'Texas', 
			'UT' => 'Utah', 
			'VT' => 'Vermont', 
			'VA' => 'Virginia', 
			'WA' => 'Washington', 
			'WV' => 'West Virginia', 
			'WI' => 'Wisconsin', 
			'WY' => 'Wyoming'
		);

		if ($required == false)
		{
			$info['options'] = array_merge(array('' => '- Select State -'), $info['options']);
		}

		$out = $this->dropdown($info);

		return $out;
	}

	public function timestamp($info)
	{
		$info['options']['class'] = 'input_timestamp';
		
		if (isset($info['value']) && !empty($info['value']))
		{
				$info['value'] = preg_match('#^\d+/\d+/\d+ @ \d+:\d+ \w\w$#', $info['value']) == 0 ? String::format_date($info['value'], 'm/d/Y @ h:i a') : $info['value'];			
		}
		else
		{
			$info['value'] = date('m/d/Y @ h:i a');			
		}
	
		$out = $this->text($info);
		
		$timestamp = date('h:i a');
		$out .= "
			<script type=\"text/javascript\">
				jQuery(function($) {
					$('#{$info['id']}').DatePicker({
						date: $('#{$info['id']}').val(),
						current: $('#{$info['id']}').val(),
						starts: 1,
						format: 'm/d/Y',
						position: 'bottom',
						onBeforeShow: function() {
							$('#{$info['name']}').DatePickerSetDate($('#{$info['name']}').val(), true);
						},
						onChange: function(formated, dates) {
							$('#{$info['id']}').val(formated + ' @ $timestamp);
							$('#{$info['id']}').DatePickerHide();
						}
					});

				});
		    </script>
		";

		return $out;
	}


	// requires javascript files
	public function dater($info)
	{

		$info['options']['class'] = 'input_date';
		
		if (isset($info['value']) && !empty($info['value']))
		{
			$info['value'] = String::format_date($info['value'], 'm/d/Y');			
		}
		else
		{
			$info['value'] = date('m/d/Y');			
		}
	
		$out = $this->text($info);
	
		$out .= "
			<script type=\"text/javascript\">
				jQuery(function($) {
					$('#{$info['id']}').DatePicker({
						date: $('#{$info['id']}').val(),
						current: $('#{$info['id']}').val(),
						starts: 1,
						format: 'm/d/Y',
						position: 'right',
						onBeforeShow: function() {
							$('#{$info['id']}').DatePickerSetDate($('#{$info['id']}').val(), true);
						},
						onChange: function(formated, dates){
							$('#{$info['id']}').val(formated);
							$('#{$info['id']}').DatePickerHide();
						}
					});
				});
		    </script>
		";

	return $out;
	
	}
	
	// requires javascript files
	public function thumbnailer($info)
	{
		$out = "
			<script language=\"javascript\">
				jQuery(function($) {
					$('.input_thumbs img').bind('click', function(e) {
						var \$this = $(this);
						$('.input_thumbs img').removeClass('on');
						\$this.addClass('on');
						$('#{$info['id']}').val(\$this.attr('title'));
				    });
				});
			</script>
		";

		$images = explode('/', $info['options']['images']);
		
		$out .= '<div class="input_thumbs">';
	
		foreach($images as $image)
		{
			$imgobj = new Media_gallery($image);
			$out .= '<img src="' . WEB_ROOT . 'files/uploads/small/' . htmlspecialchars($imgobj->file) . '" title="' . WEB_ROOT . 'files/uploads/original/' . htmlspecialchars($imgobj->file) . '" />';
		}
		
		$out .= '</div>';
		
		$out .= $this->hidden($info);
	
		return $out;
	
	}
	
	// TO USE THIS YOU WOULD NEED TO MAKE SURE THE
	// TINYMCE JAVASCRIPT LIBRARY IS SET UP
	public function htmleditorsimple($info)
	{
		$out = '
			<script language="javascript" type="text/javascript">
				tinyMCE.init({
					mode : "exact",
					elements : "' . $info['name'] . '",
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
			</script>
		';


		$info['options']['class'] = 'html_editor_simple';

		return $out . $this->textarea($info);
	}
	
	
	// TO USE THIS YOU WOULD NEED TO MAKE SURE THE
	// TINYMCE JAVASCRIPT LIBRARY IS SET UP
	public function htmleditor($info)
	{
		$out = '
			<script language="javascript" type="text/javascript">
				tinyMCE.init({
					mode : "exact",
					elements : "' . $info['name'] . '",
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
			</script>
		';

		$info['options']['class'] = 'html_editor';
			
		return $out . $this->textarea($info);
	}


}