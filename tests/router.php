<?php

// Determine our absolute document root, includes trailing slash
define('DOC_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

include DOC_ROOT . 'tests/autoload.php';

// START TEST ///////

include DOC_ROOT . 'core/class.router.php';

	Router::new_uri(array(
								'login' => 'auth/index/login/',
								'recover' => 'auth/index/recover/',
								'logout' => 'auth/index/logout/'
						) , 'http://konnectapp.com/login/');
						
	var_dump(Router::$uri);