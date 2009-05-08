<?php
	
	// This is where you declare your server enviroment settings which are set dynamically by matching
	// any one of the keys in your servers array to the current host name
	
		// Developement server settings
		$Config['development']['servers'] = array('konnect.dev');
		$Config['development']['WEB_ROOT'] = 'http://konnect.dev';
		$Config['development']['displayErrors'] = 1;
		$Config['development']['dbHost'] = 'localhost';
		$Config['development']['dbName'] = 'konnect_new';
		$Config['development']['dbUsername'] = 'root';
		$Config['development']['dbPassword'] = 'rootpassword';
		$Config['development']['dbDieOnError'] = true;
		
		// Staging server settings
		$Config['staging']['servers'] = array('konnectphp.com','www.konnectphp.com');
		$Config['staging']['WEB_ROOT'] = 'http://konnectphp.com';
		$Config['staging']['displayErrors'] = 1;
		$Config['staging']['dbHost'] = 'localhost';
		$Config['staging']['dbName'] = 'konnect_new';
		$Config['staging']['dbUsername'] = 'root';
		$Config['staging']['dbPassword'] = 'rootpassword';
		$Config['staging']['dbDieOnError'] = true;