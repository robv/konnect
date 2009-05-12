<?php

class App_init {
	
	public $app_name;
	public $default_controller;
	public $routes = array();
	public $controller_obj;
	public $data;
	
	// $dir is the directory the app sits in
	private function __construct($dir)
	{
		include DOC_ROOT . $dir . '/config/settings.php';
		$this->set_config($config);
		
		foreach($this->routes as $k => $v)
		{
			
		}
		
		Router::exec()->uri_rewrite($this->routes);
		
		$this->initiateApp();
	}

	public function set_config($config = array()) 
	{
    	foreach ($config as $k => $v) {
      		if (isset($this->$k) || is_null($this->$k)) $this->$k = $v;
	}
	
	public function initiateApp()
	{
		die('Please set up your "initiateApp" method for this app');
	}
	
}