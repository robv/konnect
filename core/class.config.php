<?php

    // The Config class provides a single object to store your application's settings.
    // Define your settings as public members. (We've already setup the standard options
    // required for the Database and Auth classes.) Then, assign values to those settings
    // inside the "location" functions. This allows you to have different configuration
    // options depending on the server environment you're running on. Ex: local, staging,
    // and production.

   class Config
    {
		public static $config;

		public static function set($config = array(), $namespace = 'core') 
		{
			foreach ($config as $k => $v)
			{
				self::$config[$namespace][$k] = $v;
			}
		}

        public static function set_core($host = NULL)
        {
			// Allows testing outside of browser by being able to pass host
			if (is_null($host))
				$host = $_SERVER['HTTP_HOST'];
	
			// Returns the array $config and also $core so that we don't have to define all those settings here
			include DOC_ROOT . 'config/settings.php';

			// Load $core settings into object
			self::set($core);
			foreach ($config as $name => $settings)
			{
				// Search server array to see if where we are matches, if true, then we know what settings to use
	            if (in_array($host, $settings['servers']))
				{
					self::set($settings);
		            error_reporting(self::$config['core']['error_reporting']);
		            define('WEB_ROOT', self::$config['core']['web_root']);
					return true;
				}
			}
			return false;
		}

	}