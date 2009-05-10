<?php
    // Application flag
    define('KNNCT', true);

    // Determine our absolute document root, includes trailing slash
    define('DOC_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

    // Class Autoloader
    function __autoload($class_name)
    {
     	$folders = array('core','helpers','config','libraries');
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
	
    // Store session info in the database?
    if (Config::getConfig()->useDb_Sessions === true)
        Db_Session::register();

    // Initialize our session
    session_start();
