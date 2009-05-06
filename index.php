<?php
	
	require 'core/master.inc.php';
		
		
	// TODO: Rewrite index, and alterpath class to function properly
		
	// This is just putting some useful variables together that will be available everywhere
	$ap = new AlterPath($core['rewrites']); 
	$ap->return_paths();
	
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