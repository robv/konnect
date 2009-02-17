<?PHP
    // Application flag
    define('SPF', true);

    // Determine our absolute document root
    define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

    // Global include files
    require DOC_ROOT . '/core/functions.inc.php'; // __autoload() is contained in this file
    require DOC_ROOT . '/core/class.dbobject.php';
    require DOC_ROOT . '/core/class.objects.php';
    require DOC_ROOT . '/rewrites.php'; // These are the paths you wish to have rewritten

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
    // Load our config settings
    $Config = Config::getConfig();

    // Store session info in the database if table exists
    if($Config->useDBSessions === true && mysql_is_table('sessions'))
        DBSession::register();

    // Initialize our session
    session_start();

    // Initialize current user if table exists
	if(mysql_is_table('users'))
    	$Auth = Auth::getAuth();

    // Object for tracking and displaying error messages
    $Error = Error::getError();

    // Object for displaying flash messages
    $Flash = new Flash();