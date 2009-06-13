<?php

class Forms {
	
	public $fields = array();
	
	public $name;
	public $wrapper;
	public $format;
	public $iterations;
	public $output;
	public $validation;
	public $seperator;
	
	
	// Build all your info first and then send it to constructor for display
	function __construct($name='form',$iterations='1',$wrapper='',$seperator='')
	{
		
		// I understand this is a stupid way to do this but seems like the most readable / simple
		if(empty($wrapper))
			//$wrapper = '<form action="" method="post" enctype="multipart/form-data">'."\n".'%body%'."\n".'</form>';
			$wrapper = '%body%';
		if(empty($format))
			$format='<div class="%row_class%"><label for="%id%">%name%:</label><div class="input_field">%field%</div></div>'."\n";
		if(empty($seperator))
			$seperator = '<div class="hr"><hr /></div>';
		
		$this->name = $name;
		$this->wrapper = str_replace("%group%",$this->name,$wrapper);
		$this->format = $format;
		$this->iterations = $iterations;
		$this->seperator = $seperator;
	
		if(isset($_SESSION['forms'][$this->name]) && submit()){
			if(!empty($_SESSION['forms'][$this->name])){
				$this->fields = safe_unserialize($_SESSION['forms'][$this->name]['fields']);
				$this->iterations = $_SESSION['forms'][$this->name]['iterations'];
			}
		}
		
	}

	function addfield($name,$type,$info=''){
			
		if(!is_array($info))
			$info = array();
			
		foreach($info as $k => $v):
		
			$this->fields['0'][$name][$k] = $v;
		
		endforeach;
		
		// These weren't set in the loop
		$this->fields['0'][$name]['type'] = $type;
		$this->fields['0'][$name]['name'] = str_replace(' ','_',$name);

		// This is just to ensure these required fields are set
		$this->fields['0'][$name]['display'] = (isset($info['display'])) ? $info['display'] : ucwords(str_replace('_',' ',$name));
		//$this->fields['0'][$name]['id'] = (isset($info['id'])) ? $info['id'] : str_replace('_',' ',$name);
		$this->fields['0'][$name]['id'] = (isset($info['id'])) ? $info['id'] : $this->fields['0'][$name]['name'];
		$this->fields['0'][$name]['value'] = (isset($info['value'])) ? $info['value'] : '';
		
	}
	
	function iterate(){

		$foreach_fields = $this->fields;			
	
			// Loop through iterations and build new field names
			for($i=0;$i<$this->iterations;$i++){
			
				foreach($foreach_fields['0'] as $name => $value) :
				
					// Grab original information
					$this->fields[$i][$name] = $value;
					
					// These are the ones we have to alter for iterations
					$this->fields[$i][$name]['id'] = $value['id'].'_'.$i;
					$this->fields[$i][$name]['name'] = $value['name'].'_'.$i;
					$this->fields[$i][$name]['value'] = isset($_POST[$name.'_'.$i]) ? $_POST[$name.'_'.$i] : $value['value'];
					$this->fields[$i][$name]['iteration'] = $i;
					
					if(isset($value['options']['title'])) // This is specifically for slug as of now because it needs to replace %n% with iteration
						$this->fields[$i][$name]['options']['title'] = str_replace('%n%',$i,$value['options']['title']);
					
				endforeach;
			}
	
	}
	
	
	function display()
	{
		
		$field_output = '';
		
		foreach($this->fields as $fields):
		
			foreach($fields as $fieldName => $field):
			
				$type = $field['type'];
				
				if($type === 'hidden' || $type === 'company_id'){ // should not show anything for hidden fields
				
					$field_output .= $this->$type($field);
				
				} else {
					
					if($field['id'] === 'monday_status_0'){
						$field_output .= '<h3>Store Hours</h3><div class="hr"><hr /></div>';
					}
					if($field['id'] === 'owner_name_0'){
						$field_output .= '<h3>Owner Information</h3><div class="hr"><hr /></div>';
					}
					
					$field_output .= $this->format
					$field_output = str_replace("%id%",$field['id'],$field_output);
					$field_output = str_replace("%name%",$field['display'],$field_output);
					$field_output = str_replace("%field%",$this->$type($field),$field_output);
					if(isset($field['options']['row_class']))
						$field_output = str_replace("%row_class%",$field['options']['row_class'],$field_output);
				}
			
			endforeach;
		
			$field_output .= $this->seperator;
		
		endforeach;
		
		$this->output = str_replace('%body%',$field_output,$this->wrapper);
			
		$_SESSION['forms'][$this->name]['fields'] = safe_serialize($this->fields);
		$_SESSION['forms'][$this->name]['iterations'] = $this->iterations;
		
		return $this->output;
	
	}


	// Currently does not support validation types with extra options
	function validate()
	{
		global $Error;
		
		$field_output = '';
		
		foreach($this->fields as $fields):
		
			foreach($fields as $fieldName => $field):
					
				if(isset($field['validate'])){
					
					$vtypes = explode('&',$field['validate']);	
					
					foreach($vtypes as $vfunction):
						
						if(!empty($vfunction))
							$Error->$vfunction($field['value'],$field['name'],$field['display'].$field['value']);
					
					endforeach;
				
				}
					
			endforeach;

		endforeach;

	}
	
	
// ALL THE INPUT TYPES ARE LISTED BELOW...	
	
	
	// OTHER FUNCTIONS SUCH AS TEXT,HIDDEN, AND CHECKBOX ARE BASED OFF OF THIS
	function basicinput($info)
	{
		$attributes = isset($info['options']['size']) ? ' size="'.$info['options']['size'].'"' : '';
		$attributes .= isset($info['options']['src']) ? ' src="'.$info['options']['src'].'"' : '';
		$attributes .= isset($info['options']['class']) ? ' class="'.$info['options']['class'].'"' : ' class="input_default"';
		$attributes .= isset($info['options']['title']) ? ' title="'.$info['options']['title'].'"' : '';
		$attributes .= isset($info['name']) ? ' name="'.$info['name'].'"' : '';
		$attributes .= isset($info['id']) ? ' id="'.$info['id'].'"' : '';
		$attributes .= isset($info['value']) ? ' value="'.$info['value'].'"' : '';
		$attributes .= isset($info['type']) ? ' type="'.$info['type'].'"' : '';
		
		$out = '<input'.$attributes.' />';
		
		if($info['type'] === 'file' && !empty($info['value'])){
			
			$gd = new GD();
			if($gd->loadFile('./files/uploads/large/'.$info['value']))
				$out .= '<span class="current">Current File: <a href="'.WEB_ROOT.'files/uploads/large/'.$info['value'].'" rel="facebox">'.$info['value'].'</a></span>';
			else
				$out .= '<span class="current">Current File: <a href="'.WEB_ROOT.'files/uploads/original/'.$info['value'].'" target="new">'.$info['value'].'</a></span>';
		
		}
		
		if(isset($info['options']['extra']))
			$out .= $info['options']['extra'];

		return $out;
	}

	function text($info){
		$info['type'] = 'text';
		return $this->basicinput($info);
	}

	// Requires jquery slug plugin
	function slug($info){
		$info['options']['class'] = 'slug input_default';
		$info['type'] = 'text';
		return $this->basicinput($info);
	}

	function hidden($info){
		$info['type'] = 'hidden';
		
		if(empty($info['value']) && isset($info['options']['value']))
			$info['value'] = $info['options']['value'];
		
		return $this->basicinput($info);
	}

	function checkbox($info){
		$info['type'] = 'checkbox';
		return $this->basicinput($info);
	}

	function password($info){
		$info['type'] = 'password';
		return $this->basicinput($info);
	}

	function file($info){
		$info['type'] = 'file';
		$name = isset($info['name']) ? $info['name'] : '';
		$info['options']['extra'] = '<p class="form_inner_form"><input type="checkbox" name="'.$name.'_crop" id="'.$name.'_crop" checked="checked" value="yes" /><label for="'.$name.'_crop">This is an image and I want to crop it after upload</label></p>';
		return $this->basicinput($info);
	}

	function submit($info){
		$info['type'] = 'submit';
		return $this->basicinput($info);
	}

	function image($info){
		$info['type'] = 'image';
		return $this->basicinput($info);
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
		return $this->basicinput($info);
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