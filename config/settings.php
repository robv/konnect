<?php
	
	// We don't want anything beside the framework loading this shizzle
	if (!defined('KONNECT')) exit();
	
	// This is where you declare your server enviroment settings which are set dynamically by matching
	// any one of the keys in your servers array to the current host name
	
		$core['default_app'] = 'home';
		$core['installed_apps'] = array('home');
		
		$rewrites = array(
							'login' => 'auth/index/login/',
							'recover' => 'auth/index/recover/',
							'logout' => 'auth/index/logout/'
					);
	
		// Developement server settings
		$config['development']['servers'] = array('konnect.dev');
		$config['development']['web_root'] = 'http://konnect.dev';
		$config['development']['display_errors'] = 1;
		$config['development']['db_host'] = 'localhost';
		$config['development']['db_name'] = 'konnect_new';
		$config['development']['db_username'] = 'root';
		$config['development']['db_password'] = 'rootpassword';
		$config['development']['db_die'] = true;
		
		// Staging server settings
		$config['staging']['servers'] = array('konnectphp.com','www.konnectphp.com');
		$config['staging']['web_root'] = 'http://konnectphp.com';
		$config['staging']['display_errors'] = 1;
		$config['staging']['db_host'] = 'localhost';
		$config['staging']['db_name'] = 'konnect_new';
		$config['staging']['db_username'] = 'root';
		$config['staging']['db_password'] = 'rootpassword';
		$config['staging']['db_die'] = true;