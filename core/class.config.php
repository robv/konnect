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
        public $auth; // Array for all auth information
        public $db; // Array for all db information
		public $core; // Array for core information
		public $routes; // Core routes
		
		// App info
        public $default_app; // Default app to direct to
		public $installed_apps;

        // Singleton constructor
        private function __construct()
        {
           	$this->everywhere();
           	$this->set_config();
        }

        // Get Singleton object
        public static function exec()
        {
            if (is_null(self::$me))
                self::$me = new Config();
            return self::$me;
        }

        // Allow access to config settings statically.
        // Ex: Config::get('some_value')
        public static function get($key)
        {
            return self::$me->$key;
        }

		public function set_config_vars($config = array()) 
		{
			foreach ($config as $k => $v)
				if(isset($this->$k) || is_null($this->$k))
					$this->$k = $v;
		}

        // Add code to be run on all servers
        private function everywhere()
        {
            // Store sesions in the database?
            $this->db['session'] = TRUE;

            // Settings for the Auth class
            $this->auth['domain'] = $_SERVER['HTTP_HOST'];
            $this->auth['hash'] = TRUE;
            $this->auth['salt'] = 'wtnMmVyc8vhkrxBrtkm3VTkLwiAFs'; // Pick any random string of characters

        }

        public function set_config()
        {
			// Returns the array $config and also $core so that we don't have to define all those settings here
			include DOC_ROOT . 'config/settings.php';
			
			// Load $core settings into object
			$this->set_config_vars($core);
			
			foreach ($config as $name => $settings)
			{
				// Search server array to see if where we are matches, if true, then we know what settings to use
	            if (in_array($_SERVER['HTTP_HOST'], $settings['servers']))
				{
					$this->set_config_vars($settings);
		            define('WEB_ROOT', $this->core['web_root']);
				}
				if (!defined('WEB_ROOT')) {
					die('<h1>Where am I?</h1> <p>You need to setup your server names in <code>settings.php</code></p>
	                     <p><code>$_SERVER[\'HTTP_HOST\']</code> reported <code>' . $_SERVER['HTTP_HOST'] . '</code></p>');
				}
			}
		}

	}