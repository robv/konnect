<?php

class Auth_init extends App_Init {
		
	function initiateApp()
	{
		
		if (!isset(Router::exec()->uri['1']))
		{
			Router::exec()->uri['1'] = $this->default_controller;
		}
		
		require DOC_ROOT . 'apps/' . $this->app_name . '/controllers/controller.' . Router::exec()->uri['1'] . '.php';
		
		// Because class names should be camel case with underscores
		$controller_name = String::exec()->uc_slug(Router::exec()->uri['1'], '_')
		
		// What info should we send the controller
		$this->data['app']['name'] = $this->app_name;
		
		$this->controller_obj = new $controller_name($this->data);

	}
	
}