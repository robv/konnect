<?php
    // Application flag
    define('kt', true);

    // Determine our absolute document root
    define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

    // Global include files
    require DOC_ROOT . '/core/functions.inc.php'; // __autoload() is contained in this file
    require DOC_ROOT . '/core/class.dbobject.php';
    require DOC_ROOT . '/config/rewrites.php'; // These are the paths you wish to have rewritten
    require DOC_ROOT . '/config/config.php'; // These are the paths you wish to have rewritten
	
	// Load all objects for installed apps
	foreach($core['installed_apps'] as $app){
		require DOC_ROOT . '/apps/' . $app . '/models.php';
	}	
	
    // Fix magic quotes
    if(get_magic_quotes_gpc())
    {
        $_POST    = fix_slashes($_POST);
        $_GET     = fix_slashes($_GET);
        $_REQUEST = fix_slashes($_REQUEST);
        $_COOKIE  = fix_slashes($_COOKIE);
    }


/*
	TODO: Refactor so namespace is not polluted, should call these methods every time you need to use them
*/

    // Store session info in the database if table exists
    if(Config::getConfig()->useDBSessions === true && mysql_is_table('sessions'))
        DBSession::register();

    // Initialize our session
    session_start();

    // Object for tracking and displaying error messages
    $Error = Error::getError();