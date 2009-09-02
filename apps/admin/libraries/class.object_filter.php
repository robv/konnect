<?php

class Object_Filter
{
	public function for_display($table, $field_names, $objects)
	{
		foreach ($field_names as $field)
		{
			$field_info[$field] = new Field_Information(array('name' => $field, 'table' => $table));
		}
		foreach ($objects as &$object)
		{
			foreach($field_info as $name => $info)
			{
				if ($info->options !== NULL)
				{
					$exploded_options = explode("\n", $info->options);
					foreach ($exploded_options as $option)
					{
						if (!empty($option))
						{
							$option_temp = explode(',', $option);
							$options[$option_temp[0]] = $option_temp[1];
						}
					}
				}
				if ($info->type !== NULL)
				{
					$field_type = $info->type;
					$object->$name = $this->$field_type($object->$name, $options);
				}
				else
				{
					$object->$name = String::truncate(htmlspecialchars($object->$name), '400', '[...]');
				}
			}
		}
		return $objects;
	}
	
	private function related($value, $options)
	{
		if(isset($options['table']) && isset($options['display_field']) && isset($options['value_field']))
		{
			$options['table'] = String::uc_slug($options['table'], '_', '_');
			$related_object = new $options['table'](array($options['value_field'] => $value));
			// TODO: Accept template for display
			return $related_object->$options['display_field'];
		}
		return $value;
	}
	
	private function timestamp($value, $options)
	{
		if(!isset($options['format']))
			$options['format'] = NULL;
		return String::format_date($value, $options['format']);
	}
	
}