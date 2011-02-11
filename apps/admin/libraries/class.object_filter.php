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
				$options = Scaffolder::parse_options($info->options, $info->type);

				if ($info->type !== NULL && method_exists($this, $info->type))
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
		$value_field = isset($info['options']['value_field']) ? $info['options']['value_field'] : 'id';
		$display_field = isset($info['options']['display_field']) ? $info['options']['display_field'] : 'name';

		if (isset($options['table']))
		{
			$options['table'] = String::uc_slug($options['table'], '_', '_');
			$related_object = new $options['table'](array($value_field => $value));
			// TODO: Accept template for display
			return $related_object->$display_field;
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