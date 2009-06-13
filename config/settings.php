<?php

// We don't want anything beside the framework loading this shizzle
if (!defined('KONNECT'))
	exit();

// This is where you declare your server enviroment settings which are set dynamically by matching
// any one of the keys in your servers array to the current host name

$core['default_app'] = 'admin';
$core['installed_apps'] = array('auth','admin');

$core['routes'] = array(
	'login' => 'auth/main/login/',
	'recover' => 'auth/main/recover/',
	'logout' => 'auth/main/logout/'
);

// Developement server settings ///////////

$config['development']['servers'] = array('konnectphp.dev');
$config['development']['basics']['web_root'] = 'http://konnectphp.dev/';
$config['development']['basics']['cookie_domain'] = '.konnectphp.dev'; // for cookies
$config['development']['basics']['error_reporting'] = E_ALL; // See http://us3.php.net/manual/en/function.error-reporting.php
$config['development']['db']['host'] = 'localhost';
$config['development']['db']['name'] = 'konnectphp';
$config['development']['db']['username'] = 'root';
$config['development']['db']['password'] = 'rootpassword';
$config['development']['db']['die'] = true;


// Staging server settings ////////////////

$config['staging']['servers'] = array('konnectphp.com','www.konnectphp.com');
$config['staging']['basics']['web_root'] = 'http://konnectphp.com/';
$config['staging']['basics']['cookie_domain'] = '.konnectphp.com';
$config['staging']['basics']['error_reporting'] = 0; // See http://us3.php.net/manual/en/function.error-reporting.php
$config['staging']['db']['host'] = 'localhost';
$config['staging']['db']['name'] = 'konnect_new';
$config['staging']['db']['username'] = 'root';
$config['staging']['db']['password'] = 'rootpassword';
$config['staging']['db']['die'] = TRUE;

