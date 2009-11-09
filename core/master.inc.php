<?php

// Application flag
define('KONNECT', true);

// Determine our absolute document root, includes trailing slash
define('DOC_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

include DOC_ROOT . 'core/class.config.php';

// Setting core configuration variables.
if (!Config::set_core()) {
	die('<h1>Where am I?</h1> <p>You need to setup your server names in <code>class.config.php</code></p>
		<p><code>$_SERVER[\'HTTP_HOST\']</code> reported <code>' . $_SERVER['HTTP_HOST'] . '</code></p>');
}

// Load all the models and sql dumps
foreach (Config::$config['core']['installed_apps'] as $app)
{
	include DOC_ROOT . 'apps/' . $app . '/models.php';
	App_Init::install($app);
}

// Class Autoloader
function __autoload($class_name)
{
	$folders = array();

	foreach (Config::$config['core']['installed_apps'] as $app) {
		$folders[] = 'apps/' . $app . '/libraries';
	}

	$folders = array_merge($folders, array('core', 'helpers', 'libraries'));
	foreach ($folders as $folder) {
		if (file_exists(DOC_ROOT . $folder . '/class.' . strtolower($class_name) . '.php')) {
			require DOC_ROOT .  $folder . '/class.' . strtolower($class_name) . '.php';
			break;
		}
	}
}

// Fix magic quotes
if (get_magic_quotes_gpc()) {
	$_POST    = String::strip_slashes($_POST);
	$_GET     = String::strip_slashes($_GET);
	$_REQUEST = String::strip_slashes($_REQUEST);
	$_COOKIE  = String::strip_slashes($_COOKIE);
}

// Initialize our session
session_start();