<?php

class Main_Controller extends Controller {
	
	function __construct($data = '')
	{		
		$this->default_method = 'dashboard';

		// Router uri = app/controller/method
		parent::__construct($data);
	}
	
	public function dashboard()
	{
		// Kick out user if already logged in
		if (!Auth::get_instance()->logged_in()) 
		{
			Flash::set('<p class="flash warning">You must be logged in to access admin.</p>');
			Core_Helpers::redirect(WEB_ROOT . 'login/');
		}
		
		// First off, how many items per page and what page are we on?
	    $per_page = 4;
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
	
	// TODO: Implement cache system
	public function rss()
	{
		$feed = new Rss;
		$feed->title = 'Konnect';
		
		$api_check = new Users;
		
		if ($api_check->verify_api_token(Router::uri(4)))
		{
		
			if (Router::uri(3) === 'announcements') 
			{
				$feed->title .= ' - Latest Announcements';
				$this->data['announcements'] = new Admin_Announcements;
				$query = 'SELECT admin_announcements.*, users.username FROM admin_announcements LEFT JOIN users ON admin_announcements.author = users.id LIMIT 0,10';
				$this->data['announcements'] = $this->data['announcements']->select_many($query, array('username'));
		
				foreach ($this->data['announcements'] as $announcement) 
				{
					$item = new Rss_Item();
			        $item->title = $announcement->title;
			        $item->link = WEB_ROOT . Router::uri(0) . '/view/admin_announcements/' . $announcement->id . '/';
			        $item->description = $announcement->comments;
			        $item->set_pub_date(String::format_date($announcement->date_posted, 'F j, g:i a'));
			        $feed->add_item($item);
				}
			}
	
			$feed->serve();
		}
		else
		{	
			die('You are not authorized for this feed, please check that your api token is correct');
		}
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
				$uctable = String::uc_slug($table,'_');

				$arr_fields = array();
				$db->query('SHOW FIELDS FROM '.$table);
				while($row = mysql_fetch_array($db->result, MYSQL_ASSOC))
				{
					if(!isset($id_field))
						$id_field = current($row);
					else
						$arr_fields[] = current($row);
				}
				$fields = '\'' . implode('\', \'', $arr_fields) . '\'';
			
				if(!class_exists($uctable,false)){
				
				$out .= 'class ' . $uctable . ' extends Db_Object {' . "\n\n";
				$out .= '	function __construct($id = NULL)' . "\n";
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