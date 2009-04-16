<?php

    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);
	require 'core/master.inc.php'; // loads rewrites and installed arrays into $core
		
	$data['konnect']['config'] = Config::getConfig();
	
	// This is just putting some useful variables together that will be available everywhere
	$ap = new AlterPath($core['rewrites']); 
	$ap->return_paths();
	
	if(!mysql_is_table('users') && $_SERVER['REQUEST_URI'] !== 'install/') {
		include 'controls/install.control.php';
		new Install_controller();
		exit;
	}
	
	if(!isset($data['konnect']['rewritten_path']['0']) || empty($data['konnect']['rewritten_path']['0']))
		$data['konnect']['rewritten_path']['0'] = $data['konnect']['config']->defaultApp;
	
	// TODO: Error management if app isn't in install list
	if(in_array($data['konnect']['rewritten_path']['0'],$core['installed_apps']))
		$core['app'] = $data['konnect']['rewritten_path']['0'];
	else
		die ('<h1>Opps</h1> <p>The app you\'re trying to use doesn\'t exist.</p>');
		
	require DOC_ROOT . '/apps/' . $core['app'] . '/init.php'; // import init class
	$init_class = ucfirst($core['app']) . '_init'; // first letters in classes should always be capital followed by lowercase
	$init_class_obj = new $init_class(); // initiate init class