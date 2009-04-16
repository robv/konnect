<?php

class Admin_init extends App_init {
	
	public $app_name = 'admin';
	public $default_controller = 'index';
		
	function initiateApp()
	{
		$this->rewrites = array(
									'([^/]+)/?(.*)' => 'index/%1%/%2%' // smaller url by making everything go through index controller
								);

		// Creates $this->data['konnect']['app_rewritten_path'] and $this->data['konnect']['app_rewritten_path']
		// and $this->data['konnect']['app_original_path']						
		$this->rewrite();
		
		// Building the controller name, should be ucfirst and end with _controller
		if(!isset($this->data['konnect']['rewritten_path']['1']) || empty($this->data['konnect']['rewritten_path']['1']))
			$controller_uc = ucfirst($controller = $this->default_controller).'_controller';
		else
			$controller_uc = ucfirst($controller = $this->data['konnect']['rewritten_path']['1']).'_controller';
		
		require DOC_ROOT . '/apps/' . $this->app_name . '/controller.' . $controller .'.php'; // require controller document
		$this->controller = new $controller_uc($this->app_name,$this->data);

	}
	
}