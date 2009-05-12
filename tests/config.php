<?php

// Determine our absolute document root, includes trailing slash
define('DOC_ROOT', realpath(dirname(__FILE__) . '/../') . '/');
define('KONNECT','poop');

include DOC_ROOT . 'tests/autoload.php';

// START TEST ///////

include DOC_ROOT . 'core/class.config.php';


	
	Config::set_config('konnect.dev');
	
	echo Config::$core['web_root'] . "\n\n";
	var_dump(Config::$core);