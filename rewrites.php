<?PHP

	// This file will quickly rewrite paths to controllers.
	// Path to login is rewritten below as an example.
	// Also supports regex
	
	
	$core['rewrites'] = array(
		
		'login/' => 'auth/login/',
		'recover/' => 'auth/recover/',
		'logout/' => 'auth/logout/'
	
	);