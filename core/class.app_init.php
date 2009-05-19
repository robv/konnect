<?php

class App_Init {

	public $config;
	public $app_name;
	public $data;
	
	// $dir is the directory the app sits in
	public function __construct($dir)
	{
		// Pulls $config in
		include DOC_ROOT . 'apps/' . $dir . '/settings.php';
		
		$this->data['app']['name'] = $config['app_name'];
		
		$this->install();
		
		include DOC_ROOT . 'apps/' . $dir . '/settings.php';
		
		Config::set($config, $this->data['app']['name']);
		
		$this->app_name = $this->data['app']['name'];
		
		$this->route();
		$this->load_controller();
		
	}

	private function load_controller()
	{		
		// Uri format: app/controller/method thus making uri[1] the controller
		// If no controller is set through the uri array then set it to default
		if (Router::uri(1) === null)
			Router::uri(1, Config::$config[$this->data['app']['name']]['default_controller']);

		require DOC_ROOT . 'apps/' . $this->app_name . '/controllers/controller.' . Router::uri(1) . '.php';
		
		$controller_name = String::uc_slug(Router::uri(1), '_') . '_Controller';
		$controller_obj = new $controller_name($this->data);
	}

	private function route()
	{
		
		// We can't count on user to set routes, so let's make sure something's there
		if (!isset(Config::$config[$this->data['app']['name']]['routes']))
			Config::$config[$this->data['app']['name']]['routes'] = array();
			
		// We have to build the routes to include the app name before the paths, this includes rewriting keys
		// BECUASE foo/bar/ should really be appname/foo/bar/
		$new_routes = array();
		
		foreach (Config::$config[$this->data['app']['name']]['routes'] as $k => $v)
			$new_routes[$this->data['app']['name'] . '/' . $k] = $this->app_name . '/' . $v;
			
		Config::$config[$this->data['app']['name']]['routes'] = $new_routes;
		
		Router::uri_rewrite(Config::$config[$this->data['app']['name']]['routes']);
	}
	
	private function install()
	{
		if (file_exists(DOC_ROOT . 'apps/' . $this->data['app']['name'] . '/tables.sql')) {
			if(is_writable(DOC_ROOT . 'apps/' . $this->data['app']['name'] . '/tables.sql')) {
				// Create tables
				$sql = file_get_contents(DOC_ROOT . 'apps/' . $this->data['app']['name'] . '/tables.sql');

				// Do this to split up creations to one per query.
				$queries = explode('#',$sql);

				$db = Database::get_db();

					foreach($queries as $query)
						$db->query($query);
				
				rename(DOC_ROOT . 'apps/' . $this->data['app']['name'] . '/tables.sql',DOC_ROOT . 'apps/' . $this->data['app']['name'] . '/tables.sql.bak');
				
			} else {
				die('<h1>You have no installed this app, either run <strong>' . DOC_ROOT . 'apps/' . $this->data['app']['name'] . '/tables.sql' . 
					'</strong> manually and delete the file or change chmod this file so that it is writable by PHP.</h1>');
			}
		}
	}
	
}