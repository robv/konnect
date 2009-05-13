<?php
	
	require 'core/master.inc.php';
	
	Router::exec()->new_uri(Config::exec()->routes);
	
	// If no app is set through the uri array then set it to default
	if (!isset(Router::exec()->uri['0']) || empty(Router::exec()->uri['0']))
		Router::exec()->uri['0'] = Config::exec()->default_app;
	
	// If app doesn't exist then show error
	if (!in_array(Router::exec()->uri['0'], Config::exec()->installed_apps))
		die ('<h1>Opps</h1> <p>The app <strong>' . Router::exec()->uri['0'] . '</strong> does not exist.</p>');
		
	// Import  init class for app, should always be apps/appname/init.php
	require DOC_ROOT . 'apps/' . Router::exec()->uri['0'] . '/init.php';
	
	// Build init class name, should always be ucwords of app name followed by _init, example this_admin = This_Admin_init
	$init_class = String::exec()->uc_slug(Router::exec()->uri['0'], '_') . '_init'; 
	
	$init_class_obj = new $init_class(Router::exec()->uri['0']); // initiate init class