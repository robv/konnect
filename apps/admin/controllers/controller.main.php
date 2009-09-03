<?php

class Main_Controller extends Controller {
	
	function __construct($data = '')
	{		
		// Kick out user if already logged in
		if (!Auth::get_instance()->logged_in()) 
		{
			Flash::set('<p class="flash warning">You must be logged in to access admin.</p>');
			Core_Helpers::redirect(WEB_ROOT . 'login/');
		}
		
		$this->default_method = 'dashboard';
		
		$links_object = new Admin_Links;
		$data['header_links'] = $links_object->get_links();

		// Router uri = app/controller/method
		parent::__construct($data);
	}
	
	public function dashboard()
	{
		
		// First off, how many items per page and what page are we on?
	    $per_page = 5;
		$current_page = (isset($_GET['p'])) ? intval($_GET['p']) : '1';

	    // Next, get the total number of items in the database
	    $num_entries = Database::get_instance()->get_value('SELECT COUNT(*) FROM admin_announcements');

	    // Initialize the Pager object
	    $pager = new Pagination($current_page, $per_page, $num_entries);		
		
		$this->data['announcements'] = new Admin_Announcements;
		$query = 'SELECT admin_announcements.*, users.username FROM admin_announcements LEFT JOIN users ON admin_announcements.author = users.id LIMIT ' . $pager->first_record . ', ' . $pager->per_page;
		$this->data['announcements'] = $this->data['announcements']->select_many($query, array('username'));
		
		$this->data['pager'] = $pager;
		
		$this->load_template('dashboard');
	}
	
	public function index()
	{
		// Universal variables we're going to need...
		
			// Lets start with pagination
			// First off, how many items per page and what page are we on?
		    $per_page = 10;
			$current_page = (isset($_GET['p'])) ? intval($_GET['p']) : '1';
			
			// If there a search being done?
			if (isset($_GET['search']))
			{
				$this->data['search_value'] = htmlspecialchars($_GET['search']);
			}
			
		// This is to check if the slug being given matches one in the index_information table, if not then we'll check later if it's even a table
		$index_info = new Index_Information;
		
		if ($index_info->select(array('slug'=>strtolower(Router::uri(3)))))
		{
			$this->data['page_title'] = $index_info->title;
			$this->data['table'] = $index_info->table;
			
			// If no custom sql is in the table then just use the default select * statement
			if (is_null($index_info->sql) || empty($index_info->sql))
				$index_info->sql = '%select%';
			
			// Check if we should do a search...
			if (isset($this->data['search_value']))
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
			$this->data['table'] = String::clean(Router::uri(3), '_');
			
			// Converting string in url to what should match a db object
			$db_object_name = String::uc_slug(Router::uri(3), '_', '-');
			
			$db_object = new $db_object_name;
		
			$this->data['fields'] = $db_object->get_fields();
		
			// If $db_object doesn't match a current class then something's wrong...
			if (!class_exists($db_object_name))
				die('<h2>Sorry, ' . $db_object_name . ' does not exist.</h2>');
			
			// This is where we'll store the WHERE info the sql statement
			$where = '';
			
			// Check if we should do a search...
			if (isset($this->data['search_value']))
			{	
				$where .= ' WHERE ';
				foreach ($this->data['fields'] as $field)
				{
					$where .= '(`' . $field .'` LIKE \'%' . Database::get_instance()->escape($this->data['search_value']) . '%\') OR ';
				}
				$where = ' ' . trim($where, 'OR ');
			}

		    // Next, get the total number of items in the database
		    $this->data['num_entries'] = $num_entries = Database::get_instance()->get_value('SELECT COUNT(*) FROM `' . $this->data['table'] . '`' . $where);

		    // Initialize the Pager object
		    $pager = new Pagination($current_page, $per_page, $num_entries);
			
			$this->data['objects'] = $db_object->select_many('%select%' . $where . ' ORDER BY ' . $db_object->id_column_name . ' DESC LIMIT ' . $pager->first_record . ', ' . $pager->per_page);

			$this->data['page_title'] = String::uc_slug(Router::uri(3), ' ', '_');
			
			$this->data['template']['header'] = '<table class="default_table"><tr><th class="entry_actions_wrapper"></th>';
			$this->data['template']['footer'] = '</table>';
			$this->data['template']['loop'] = '<tr class="entry_row" id="entry_%id%"><td class="entry_actions_wrapper"><div class="entry_actions"><a href="' . WEB_ROOT . Router::uri(0) . '/delete/' . Router::uri(3) . '/%id%/" class="delete" rel="facebox.default_modal">Delete</a><a href="' . WEB_ROOT . Router::uri(0) . '/edit/' . Router::uri(3) . '/%id%/" class="edit">Edit</a></div></td>';
		
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
		
		$this->data['pager'] = $pager;
		$this->load_template('index');
	}
	
	public function delete()
	{	
		$this->data['table'] = String::clean(Router::uri(3), '_');
		
		if (isset($_GET['confirm']))
		{
			$db_object_name = String::uc_slug($this->data['table'], '_', '-');
			$object = new $db_object_name(array('id' => Router::uri(4)));
			$object->delete();
		}
		else
		{
			$this->load_template('delete');
		}
	}
	
	public function add()
	{
		$this->data['table'] = String::clean(Router::uri(3), '_');
		$scaffolder = new Scaffolder($this->data['table']);
		$scaffolder->iterate();
		$this->data['form'] = $scaffolder->display();
		
		if (isset($_POST['submit']))
		{
			if ($scaffolder->save_object())
			{
				//redirect
			}
		}
		
		$this->load_template('add');
	}
	
	public function models()
	{
		$db = Database::get_instance();
		
		$out = '';
		$arr_tables = array();
		$db->query('SHOW TABLES');
		
		while($row = mysql_fetch_array($db->result))
		{
			if (!class_exists(ucfirst($row[0]),false))
				$arr_tables[] = $row[0];
		}
		
		if (!empty($arr_tables)){
			foreach ($arr_tables as $table)
			{
				$table = trim($table);
				$uctable = String::uc_slug($table, '_', '_');

				$arr_fields = array();
				$db->query('SHOW FIELDS FROM '.$table);
				while($row = mysql_fetch_array($db->result, MYSQL_ASSOC))
				{
					if (!isset($id_field))
						$id_field = current($row);
					else
						$arr_fields[] = current($row);
				}
				$fields = '\'' . implode('\', \'', $arr_fields) . '\'';
			
				if (!class_exists($uctable,false)){
				
				$out .= 'class ' . $uctable . ' extends Db_Object {' . "\n\n";
				$out .= '	public function __construct($id = NULL)' . "\n";
				$out .= '	{' . "\n";
				$out .= '		parent::__construct(\'' . $table . '\', \'' . $id_field . '\', array(' . $fields . '), $id);' . "\n";
				$out .= '	}' . "\n\n";
				$out .= '}' . "\n";
				$out .= "\n";
			
				}
	
				unset($id_field);
			}
		} else {
			$out = 'Your database objects seem to be up to date.';
		}
		
		$this->data['object_out'] = $out;
		
		$this->load_template('models');
	}
	
}