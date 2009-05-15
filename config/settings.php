<?php

// We don't want anything beside the framework loading this shizzle
if (!defined('KONNECT')) exit();

// This is where you declare your server enviroment settings which are set dynamically by matching
// any one of the keys in your servers array to the current host name

$core['default_app'] = 'home';
$core['installed_apps'] = array('home');

$core['routes'] = array(
						'login' => 'auth/index/login/',
						'recover' => 'auth/index/recover/',
						'logout' => 'auth/index/logout/'
						);

// Developement server settings
$config['development']['servers'] = array('konnect.dev');
$config['development']['core']['web_root'] = 'http://konnect.dev';
$config['development']['core']['display_errors'] = 1;
$config['development']['db']['host'] = 'localhost';
$config['development']['db']['name'] = 'konnect_new';
$config['development']['db']['username'] = 'root';
$config['development']['db']['password'] = 'rootpassword';
$config['development']['db']['die'] = true;

// Staging server settings
$config['staging']['servers'] = array('konnectphp.com', 'www.konnectphp.com');
$config['staging']['core']['web_root'] = 'http://konnectphp.com';
$config['staging']['core']['display_errors'] = 1;
$config['staging']['db']['host'] = 'localhost';
$config['staging']['db']['name'] = 'konnect_new';
$config['staging']['db']['username'] = 'root';
$config['staging']['db']['password'] = 'rootpassword';
$config['staging']['db']['die'] = TRUE;