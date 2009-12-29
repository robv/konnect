<?php

	// This file will quickly rewrite paths to controllers.
	// Path to login is rewritten below as an example.
	// Also supports regex
	
	$core['default_app'] = 'admin';
	
	$core['installed_apps'] = array(
										'admin',
										'auth'
									);

	$core['rewrites'] = array(
								'login' => 'auth/index/login/',
								'recover' => 'auth/index/recover/',
								'logout' => 'auth/index/logout/'
							);