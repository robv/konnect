<?php
    // Application flag
    define('KONNECT', true);

    // Determine our absolute document root, includes trailing slash
    define('DOC_ROOT', realpath(dirname(__FILE__) . '/../') . '/');
	
	include DOC_ROOT . 'core/class.config.php';
	
    // Class Autoloader
    function __autoload($class_name)
    {
		$folders = array();
	
		foreach(Config::exec()->installed_apps as $app)
		{
			$folders[] = 'apps/' . $app . '/libraries';
		}
		
     	$folders = array_merge($folders, array('core','helpers','libraries'));
		foreach ($folders as $folder)
		{
			if (file_exists(DOC_ROOT . $folder . '/class.' . strtolower($class_name) . '.php'))
	 		{
				require DOC_ROOT .  $folder . '/class.' . strtolower($class_name) . '.php';
				break;
			}
		}
    }

    // Global include files
    require DOC_ROOT . 'core/class.db_object.php'; // TODO: Will this be autoloaded on extends?

    // Fix magic quotes
    if (get_magic_quotes_gpc())
    {
        $_POST    = String::exec()->fix_slashes($_POST);
        $_GET     = String::exec()->fix_slashes($_GET);
        $_REQUEST = String::exec()->fix_slashes($_REQUEST);
        $_COOKIE  = String::exec()->fix_slashes($_COOKIE);
    }

    // Initialize our session
    session_start();
