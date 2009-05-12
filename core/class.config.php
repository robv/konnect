<?php

    // The Config class provides a single object to store your application's settings.
    // Define your settings as public members. (We've already setup the standard options
    // required for the Database and Auth classes.) Then, assign values to those settings
    // inside the "location" functions. This allows you to have different configuration
    // options depending on the server environment you're running on. Ex: local, staging,
    // and production.

    class Config
    {
        // Singleton object. Leave $me alone.
        private static $me;
		
        // The basics
		public static $core; // Array for core information
		public static $db; // Db information
		public static $routes; // Core routes
		public static $servers; // Servers associated with current host
		
		public static $app; // We can store app specific variables here
		
		// App info
        public static $default_app; // Default app to direct to
		public static $installed_apps;

        // Singleton constructor
        private function __construct()
        {
           	self::set_config();
        }

		public static function set_config_vars($config = array()) 
		{
			foreach ($config as $k => $v)
				if(isset(self::$$k) || is_null(self::$$k))
					self::$$k = $v;
		}

        public static function set_config($host = NULL)
        {
			// Allows testing outside of browser by being able to pass host
			if(is_null($host))
				$host = $_SERVER['HTTP_HOST'];
	
			// Returns the array $config and also $core so that we don't have to define all those settings here
			include DOC_ROOT . 'config/settings.php';

			// Load $core settings into object
			self::set_config_vars($core);
			
			foreach ($config as $name => $settings)
			{
				// Search server array to see if where we are matches, if true, then we know what settings to use
	            if (in_array($host, $settings['servers']))
				{
					self::set_config_vars($settings);
		            define('WEB_ROOT', self::$core['web_root']);
				}
				if (!defined('WEB_ROOT')) {
					die('<h1>Where am I?</h1> <p>You need to setup your server names in <code>settings.php</code></p>
	                     <p><code>$_SERVER[\'HTTP_HOST\']</code> reported <code>' . $_SERVER['HTTP_HOST'] . '</code></p>');
				}
			}
		}

	}