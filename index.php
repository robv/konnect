<?php
	
	require 'core/master.inc.php';
	
	// Remap URI with routes if needed
	Router::new_uri(Config::$config['core']['routes']);
	
	// If no app is set through the uri array then set it to default
	if (is_null(Router::uri(0)))
		Router::uri(0, Config::$config['core']['default_app']);

	// If app doesn't exist then show error
	if (!in_array(Router::uri(0), Config::$config['core']['installed_apps']))
		die ('<h1>Opps</h1> <p>The app <strong>"' . Router::uri(0) . '"</strong> does not exist.</p>');

	$init = new App_Init(Router::uri(0)); // initiate init class