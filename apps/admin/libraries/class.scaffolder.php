<?php

class Scaffolder extends Forms {

	public $table;
	public $current_id;
	public $current_object;
	
	// Build all your info first and then send it to constructor for display
	public function __construct($table, $current_id = '', $iterations = '1')
	{
		$this->table = $table;
		$this->table_object = $table_object = String::uc_slug($this->table, '_', '_');
		$this->current_id = $current_id;
		
		if (class_exists($this->table_object) && (get_parent_class($this->table_object) == 'Db_Object'))
		{
			$this->current_object = new $table_object();
			
			if (!empty($this->current_id))
				$this->current_object->select(array('id' => $current_id));
		}
		else
		{
			echo '<p class="error"><strong>Framework Error:</strong><br/>Database object does not exist for table: ' . $this->table . '</p>';
			die;
		}
		
		parent::__construct($this->table, $iterations);
		
		$this->build();
		
	}
	
	public function build()
	{
		$db = Database::get_instance();
		
		$values = $this->current_object->get_fields();
		$options = array();
		
        foreach ($values as $value)
		{
			$res = $db->query('SELECT `' . $value . '` FROM ' . $this->table)->result;
			$type  = mysql_field_type($res, 0);
			$extra = mysql_field_flags($res, 0);
			$notnullsearch = strpos($extra, 'not_null');
			
			// If value can not be NULL lets add blank as a error check
			if ($notnullsearch === false)
			{ 
				$options['validate'] = ''; 
			}
			else 
			{
				$options['validate'] = 'blank';
			}
			
			$options['type'] = ($type === 'blob') ? 'htmleditor' : 'text';
			$options['type'] = ($type === 'timestamp') ? 'timestamp' : $options['type'];
			$options['type'] = ($value === 'password') ? 'password' : $options['type'];
			
			// Grab rows where field name matches
			$value_info = new Field_Information(array('table' => $this->table, 'name' => $value));
			
			if ($value_info->type !== NULL)
			{
				$options['type'] = $value_info->type;
				if ($options['type'] === 'file') // shouldn't validate for blank if it's a file.... TODO: Come up with a way to validate files
					$options['validate'] = '';
			}
			
			if ($value_info->options !== NULL)
			{
				$exploded_options = explode("\n", $value_info->options);
				foreach ($exploded_options as $option)
				{
					if (!empty($option))
					{
						$option_temp = explode(',', $option);
						$options['options'][$option_temp[0]] = $option_temp[1];
					}
				}
			}
				
			$options['value'] = $this->current_object->$value;
			$this->add_field($value, $options);	
		}
		
	}
	
	public function save_object() // This method adds / updates field entries into database
	{
		$this->validate();
		
		$validator = Error::instance();
		
		if ($validator->ok()){		

				// Loop through iterations and build new field names
				for ($i = 0; $i < $this->iterations; $i++)
				{
					$object_copy = $this->current_object;
						foreach($this->fields[$i] as $object_name => $field)
						{
							// Make directories, TODO: Find more efficient way to do this
							@mkdir('./files/uploads/');
							@mkdir('./files/uploads/original/');
							@mkdir('./files/uploads/large/');
							@mkdir('./files/uploads/medium/');
							@mkdir('./files/uploads/small/');
							@mkdir('./files/uploads/cropped/');
						
							if ($field['type'] === 'file')
							{
								// Check if a session of images to crop already exists, if so we need to overwrite it
								if (isset($_SESSION['crop_images']))
									unset($_SESSION['crop_images']);

								if (!empty($_FILES[$field['name']]['name']))
								{
								
									$gd = new Gd_Image;
								
									// Check if file is already existing and start deleting
									if (strlen($object_copy->$object_name) > 0){
										@unlink('./files/uploads/original/'.$object_copy->$field['name']);
											@unlink('./files/uploads/large/'.$object_copy->$field['name']);
											@unlink('./files/uploads/medium/'.$object_copy->$field['name']);
											@unlink('./files/uploads/small/'.$object_copy->$field['name']);
									}
								
									// If a file of that name already exists add random string to begining else keep same name
									if (file_exists('./files/uploads/original/'.$_FILES[$field['name']]['name']))
									{
										// rand_string() is located in core/extfunctions.inc.php
										$newname = $object_copy->$object_name = str_replace(' ', '', rand_string().'_'.$_FILES[$field['name']]['name']);
									}
									else
									{	
										$newname = $object_copy->$object_name = str_replace(' ', '', $_FILES[$field['name']]['name']);
									}
								
									$newname = str_replace(' ', '', $newname);
							
									// TODO: There should be a way in options to pass multiple image locations and sizes
									if ($gd->loadFile($_FILES[$field['name']]['tmp_name']))
									{
										$gd->scale_safe('1800', '1800');
											$gd->save_as('./files/uploads/original/' . $newname);
										$gd->scale_safe('700', '700');
											$gd->save_as('./files/uploads/large/' . $newname);
										$gd->scale_safe('300', '300');
											$gd->save_as('./files/uploads/medium/' . $newname);
										$gd->scale_crop('200', '200');
											$gd->save_as('./files/uploads/cropped/' . $newname);
										$gd->load_file($_FILES[$field['name']]['tmp_name']);
											$gd->scale_safe('150', '150');
											$gd->save_as('./files/uploads/small/' . $newname);
				
										$_SESSION['crop_images'][str_replace(array('.',' '), '', $newname)] = $newname;
									}
									else
									{
										move_uploaded_file($_FILES[$field['name']]['tmp_name'], './files/uploads/original/' . $newname);
									}
									
								}
						
							}
							elseif ($field['type'] === 'timestamp')
							{
								$object_copy->$object_name = date('Y-m-d H:i:s', strtotime(str_replace('@', '', $field['value'])));
							}
							elseif ($field['type'] === 'time_range')
							{
								$object_copy->$object_name = $field['value'] . ' to ' . $_POST['second_' . $field['name']];
							}
							else  // Any other type
							{
								$object_copy->$object_name = $field['value'];
							}
						}
					$this->current_id = $object_copy->save();
					$this->current_object = $object_copy;
					unset($object_copy);
				}
				
			return true; // passed validation
		}
		else 
		{
			return false; // didn't pass validation
		}
	}


}