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
			Flash::set('<div class="notice_errrors"><p>We could not find this recordset.</p></div>');
			Core_Helpers::redirect(WEB_ROOT . 'admin/');
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
				$options['validation'] = ''; 
			}
			else 
			{
				$options['validation'] = 'blank';
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
					$options['validation'] = '';
			}
			
			$options['options'] = $this->parse_options($value_info->options, $value_info->type);
			$options['value'] = $this->current_id ? $this->current_object->$value : (isset($options['options']['value']) ? $options['options']['value'] : '');

			$this->add_field($value, $options);	
		}
		
	}

	/**
	 * Get scaffolder field type
	 * 
	 * @param string $table 
	 * @param string $field 
	 * @return array $field
	 * @author Dave Salazar
	 */
	public function get_field($table = '', $field = '')
	{
		
		$db = Database::instance();
		$field_info = new field_information;
		$field = @array_shift($field_info->get(array('`table`' => $table, '`name`' => $field), array(), NULL, NULL, FALSE));
		
		$field['options'] = $field ? $field['options'] : '';
		$field['options'] = self::parse_options($field['options'], $field['type']);

		return $field;
	}

	/**
	 * Parse options string into a key value array
	 *
	 * @param string $options_str 
	 * @return array $options
	 * @author Dave Salazar
	 */
	private function parse_options($options_str = '', $type = '')
	{
		$options = array();
		$pairs = array_map('trim', explode("\n", $options_str));
		foreach ($pairs as $pair_str)
		{
			$pair = array_map('trim', explode(',', $pair_str, 2));

			// If this is a valid name value pair
			if (count($pair) == 2)
			{
				list($name, $value) = $pair;
				$options[$name] = $value;
			}
		}
		
		if ($type == 'file')
		{
			if (!isset($options['dir']) || !is_dir($options['dir']))
				$options['dir'] = 'files/uploads/';
		}

		return $options;
	}
	
	public function save_object() // This method adds / updates field entries into database
	{
		$this->validate();
		
		$validator = Error::instance();
		
		if ($validator->ok())
		{		
			// Loop through iterations and build new field names
			for ($i = 0; $i < $this->iterations; $i++)
			{
				$object_copy = $this->current_object;
				foreach($this->fields[$i] as $object_name => $field)
				{
					if ($field['type'] === 'file')
					{
						if (!empty($_FILES[$field['name']]['name']))
						{
							$dir = $field['options']['dir'];
							$width = isset($field['options']['width']) ? $field['options']['width'] : 150;
							$height = isset($field['options']['height']) ? $field['options']['height'] : 150;

							$filename = $_FILES[$field['name']]['name'];

							if (preg_match('#.*\.(php.?|cgi|pl)#i', $filename))
							{
								$filename = preg_replace('#\.(php.?|cgi|pl)#', '.bak', $filename);
							}
							

							// If a file of that name already exists and it's not the same field, we rename it otherwise we replace.
							if (file_exists($dir . 'original/' . $filename) && ($object_copy->$object_name != $filename))
							{
								$filename = format::rename_if_exists($dir . 'original/', $filename);
							}

							$object_copy->$object_name = $filename;

							copy($_FILES[$field['name']]['tmp_name'], $dir . 'original/' . $filename);

							$resize_methods = array('scale_crop', 'scale');
							$resize_method = isset($value['options']['resize_method']) && in_array($value['options']['resize_method'], $resize_methods) ? $value['options']['resize_method'] : current($resize_methods);

							if (preg_match('#.+\.(png|jp(e)?g|gif)#i', $filename))
							{
								if (@getimagesize($_FILES[$field['name']]['tmp_name']))
								{
									$gd = new Gd_Image($_FILES[$field['name']]['tmp_name']);

									$gd->$resize_method($width, $height);
									$gd->save_as($dir . 'resized/' . $filename);									
								}
								else
								{
									Flash::set('<div class="notice_warnings"><p>An error was encountered when trying to resize this file.</p></div>');
								}

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
				
			return TRUE; // passed validation
		}
		else 
		{
			Flash::set('<div class="notice_warnings"><p>' . implode('<br />', $validator->errors()) . '</p></div>');
			return FALSE; // didn't pass validation
		}
	}


}