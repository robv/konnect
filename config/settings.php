<?php
	
	// This is where you declare your server enviroment settings which are set dynamically by matching
	// any one of the keys in your servers array to the current host name
	
		// Developement server settings
		$config['development']['servers'] = array('konnect.dev');
		$config['development']['WEB_ROOT'] = 'http://konnect.dev';
		$config['development']['displayErrors'] = 1;
		$config['development']['dbHost'] = 'localhost';
		$config['development']['dbName'] = 'konnect_new';
		$config['development']['dbUsername'] = 'root';
		$config['development']['dbPassword'] = 'rootpassword';
		$config['development']['dbDieOnError'] = true;
		
		// Staging server settings
		$config['staging']['servers'] = array('konnectphp.com','www.konnectphp.com');
		$config['staging']['WEB_ROOT'] = 'http://konnectphp.com';
		$config['staging']['displayErrors'] = 1;
		$config['staging']['dbHost'] = 'localhost';
		$config['staging']['dbName'] = 'konnect_new';
		$config['staging']['dbUsername'] = 'root';
		$config['staging']['dbPassword'] = 'rootpassword';
		$config['staging']['dbDieOnError'] = true;