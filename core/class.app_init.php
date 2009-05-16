<?php

class App_Init {

	public $config;
	public $app_name
	public $data;
	
	// $dir is the directory the app sits in
	public function __construct($dir)
	{
		$this->data['app_name'] = $config['app_name'];
		
		include DOC_ROOT . $dir . '/config/settings.php';
		
		Config::set($config, $this->data['app_name']);
		
		// We can't count on user to set routes, so let's make sure something's there
		if(!isset(Config::$config[$this->data['app_name']]['routes']))
			Config::$config[$this->data['app_name']]['routes'] = array();
			
		// We have to build the routes to include the app name before the paths, this includes rewriting keys
		// BECUASE foo/bar/ should really be appname/foo/bar/
		$new_routes = array();
		
		foreach(Config::$config[$this->data['app_name']]['routes'] as $k => $v)
			$new_routes[$this->data['app_name'] . '/' . $k] = $this->app_name . '/' . $v
			
		Config::$config[$this->data['app_name']]['routes'] = $new_routes;
		unset($new_routes);
		
		Router::exec()->uri_rewrite(Config::$config[$this->data['app_name']]['routes']);
		
		$this->initiate();
	}
	
	public function initiate()
	{
		die('<h1>Please set up your "initiate" method for this app</h1>');
	}
	
	public function load_controller()
	{
		// Uri format: app/controller/method thus making uri[1] the controller
		$controller_name = String::exec()->uc_slug(Router::exec()->uri[1], '_')
		$controller_obj = new $controller_name($this->data);
	}
}