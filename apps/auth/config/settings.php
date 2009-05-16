<?php
	
	// We don't want anything beside the framework loading this shizzle
	if (!defined('KONNECT')) exit();
	
	/*
		Available variables;
		public $app_name; // Should match name of directory this app sits in
		public $default_controller;
		public $routes;
		public $data; // You can pass some data in as array, this array will also get passed to the controller
	*/
	
	$config['app_name'] = 'auth';
	$config['default_method'] = 'index';