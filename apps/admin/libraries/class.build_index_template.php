<?php

class Build_Index_Template
{
	public function build($slug, $search_value)
	{
		// This is to check if the slug being given matches one in the index_information table, if not then we'll check later if it's even a table
		$index_info = new Index_Information;

		if ($index_info->select(array('slug'=>strtolower($slug))))
		{
			$this->data['page_title'] = $index_info->title;
			$this->data['table'] = $index_info->table;

			// If no custom sql is in the table then just use the default select * statement
			if (is_null($index_info->sql) || empty($index_info->sql))
				$index_info->sql = '%select%';

			// Check if we should do a search...
			if (isset($search_value))
			{
				// If there's already a where statement we need an AND
				if (preg_match('/where/i', $index_info->sql))
					$index_info->sql .= 'AND ';

				$index_info->sql .= '';
			}

		    // Next, get the total number of items in the database
			// I know this isn't the most efficient count rows but because this can be a custom query, i don't know how else...
		    $num_entries = Database::get_instance()->num_rows(str_replace('%select%', 'SELECT * FROM ' . $index_info->table, $index_info->sql));
			if ($num_entries == false)
				$num_entries = 0;

		    // Initialize the Pager object
		    $pager = new Pagination($current_page, $per_page, $num_entries);

			// Converting string in url to what should match a db object
			$db_object_name = String::uc_slug($index_info->table, '_', '_');

			// If $db_object doesn't match a current class then something's wrong...
			if (!class_exists($db_object_name))
				die('<h2>Sorry, ' . $db_object_name . ' does not exist.</h2>');

			$db_object = new $db_object_name;

			$this->data['fields'] = $db_object->get_fields();

			// TODO: Parameter for added columns should be included, must add field to table and model
			$this->data['objects'] = $db_object->select_many($index_info->sql . ' LIMIT ' . $pager->first_record . ', ' . $pager->per_page);

			/*
				TEMPLATE FIELD FORMAT:
				<table>header html...
				%startloop%
				<tr>loop info</tr> %fieldname% %fieldname%
				%endloop%
				</table>footer html
			*/

			$this->data['template']['header'] = preg_match('/(.*?)%startloop%/im', $index_info->template, $matches);
			$this->data['template']['header'] = $matches[1];

			$this->data['template']['loop'] = preg_match('/%startloop%(.*?)%endloop%/im', $index_info->template, $matches);
			$this->data['template']['loop'] = $matches[1];

			$this->data['template']['footer'] = preg_match('/%endloop%(.*?)/im', $index_info->template, $matches);
			$this->data['template']['footer'] = $matches[1];

		}
		else
		{
			$this->data['table'] = String::clean($slug, '_');

			// Converting string in url to what should match a db object
			$db_object_name = String::uc_slug($slug, '_', '-');

			$db_object = new $db_object_name;

			$this->data['fields'] = $db_object->get_fields();

			// If $db_object doesn't match a current class then something's wrong...
			if (!class_exists($db_object_name))
				die('<h2>Sorry, ' . $db_object_name . ' does not exist.</h2>');

			// This is where we'll store the WHERE info the sql statement
			$where = '';

			// Check if we should do a search...
			if (isset($search_value))
			{	
				$where .= ' WHERE ';
				foreach ($this->data['fields'] as $field)
				{
					$where .= '(`' . $field .'` LIKE \'%' . Database::get_instance()->escape($search_value) . '%\') OR ';
				}
				$where = ' ' . trim($where, 'OR ');
			}

		    // Next, get the total number of items in the database
		    $this->data['num_entries'] = $num_entries = Database::get_instance()->get_value('SELECT COUNT(*) FROM `' . $this->data['table'] . '`' . $where);

		    // Initialize the Pager object
		    $pager = new Pagination($current_page, $per_page, $num_entries);

			$this->data['objects'] = $db_object->select_many('%select%' . $where . ' ORDER BY ' . $db_object->id_column_name . ' DESC LIMIT ' . $pager->first_record . ', ' . $pager->per_page);

			$this->data['page_title'] = String::uc_slug($slug, ' ', '_');

			$this->data['template']['header'] = '<table class="default_table"><tr><th class="entry_actions_wrapper"></th>';
			$this->data['template']['footer'] = '</table>';
			$this->data['template']['loop'] = '<tr class="entry_row" id="entry_%id%"><td class="entry_actions_wrapper"><div class="entry_actions"><a href="' . WEB_ROOT . Router::uri(0) . '/delete/' . $slug . '/%id%/" class="delete" rel="facebox.default_modal">Delete</a><a href="' . WEB_ROOT . Router::uri(0) . '/edit/' . $slug . '/%id%/" class="edit">Edit</a></div></td>';

				// We only want to return the first 4 fields, more than that and it might be too long
				if (count($this->data['fields']) > 4)
					$this->data['fields'] = array_slice($this->data['fields'], 0, 4);

				foreach ($this->data['fields'] as $field) 
				{
					$this->data['template']['header'] .= '<th>' . String::uc_slug($field, ' ', '_') . '</th>';
					$this->data['template']['loop'] .= '<td>%' . $field . '%</td>';
				}

				$object_filter = new Object_Filter();
				$this->data['objects'] = $object_filter->for_display($this->data['table'], $this->data['fields'], $this->data['objects']);

				// Final column for edit and delete buttons
				$this->data['template']['header'] .= '</tr>';
				$this->data['template']['loop'] .= '</tr>';

			$this->data['template']['header'] .= '</tr>';
		}
	}
	
}