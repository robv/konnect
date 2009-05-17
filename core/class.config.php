<?php

class Config {
	public static $config;

	private function __construct() {}

	public static function set($config = array(), $namespace = 'core') {
		foreach ($config as $k => $v) {
			self::$config[$namespace][$k] = $v;
		}
	}

	public static function set_core($host = NULL) {
		// Allows testing outside of browser by being able to pass host
		if (is_null($host))
			$host = $_SERVER['HTTP_HOST'];

		// Returns the array $config and also $core so that we don't have to define all those settings here
		include DOC_ROOT . 'config/settings.php';

		// Load $core settings into object
		self::set($core);
		foreach ($config as $name => $settings) {
			// Search server array to see if where we are matches, if true, then we know what settings to use
			if (in_array($host, $settings['servers'])) {
				self::set($settings);
	            error_reporting(self::$config['core']['error_reporting']);
	            define('WEB_ROOT', self::$config['core']['web_root']);
				return true;
			}
		}
		return false;
	}
}