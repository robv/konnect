<?php

class Controller {
	
	public $data;
	
	// TODO: Can private functions be extended?
	public function __construct($method='', $data = '')
	{
		$default_method = Config::$config[$this->data['app_name']]['default_method'];
		$this->data = $data;
		
		 // If no method is set go to default
		if (empty($method))
			$this->$default_method();
		else
			$this->$method();
	}
	
	// Loads view file and converts $data array to key => value form.
	// This is the best I could come up with...
	public function load_template($file)
	{	
			// Run through data array for access in view
		if (!empty($this->data)) {
			foreach ($this->data as $key => $value) {
				$$key = $value;
			}
		}
		
		include DOC_ROOT . '/apps/' . $this->data['app_name'] . '/templates/' . $file . '.thtml';
		
	}
	
	public function install()
	{
		// Create tables
		$sql = file_get_contents(DOC_ROOT . '/apps/' . $this->app_name . '/db.sql');

		// Do this to split up creations to one per query.
		$queries = explode('#',$sql);
		
		$db = Database::getDatabase();
		
			foreach($queries as $query)
				$db->query($query);
				
		redirect(WEB_ROOT . $this->app_name . '/');
	}
	
}