<?php

class Auth_Init extends App_Init {
	public function initiate() {
		if (Router::uri(1) === null)
			Router::$uri[1] = Config::$config[$this->app_name]['default_controller'];

		require DOC_ROOT . 'apps/' . $this->app_name . '/controllers/controller.' . Router::uri(1) . '.php';

		// Because class names should be camel case with underscores
		$controller_name = String::uc_slug(Router::uri(1), '_')

		// What info should we send the controller
		$this->data['app']['name'] = $this->app_name;

		$this->controller_obj = new $controller_name($this->data);
	}
}