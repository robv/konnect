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

        // Standard Config Options...

        // ...For Auth Class
        public $auth_domain;         // Domain to set for the cookie
        public $auth_salt;           // Can be any random string of characters
        public $hash_passwords; // Store hashed passwords in database? (versus plain-text)

        // ...For Database Class
        public $db_host;       // Database server
        public $db_name;       // Database name
        public $db_username;   // Database username
        public $db_password;   // Database password
        public $db_die; // What do do on a database error (see class.database.php for details)

        // Add your config options here...
        public $use_db_session; // Set to true to store sessions in the database
		public $web_root; // Self explanitory
		public $display_errors; // Display php errors or not
		
		// App info
        public $default_app; // Default app to direct to
		public $installed_apps;

        // Singleton constructor
        private function __construct($config = NULL)
        {
			if (!is_null($config)) {
            	$this->everywhere();
            	$this->set_config($config);
            }
        }

        // Get Singleton object
        public static function exec()
        {
			// Returns the array $config and also $settings
			include DOC_ROOT . 'config/settings.php';
			
            if (is_null(self::$me))
                self::$me = new Config($config,$core);
            return self::$me;
        }

        // Allow access to config settings statically.
        // Ex: Config::get('some_value')
        public static function get($key)
        {
            return self::$me->$key;
        }

		public function set_config_vars($config = array()) {
		  foreach ($config as $k => $v) {
		    if (isset($this->$k)) $this->$k = $v;
		  }
		}

        // Add code to be run on all servers
        private function everywhere()
        {
            // Store sesions in the database?
            $this->use_db_session = true;

            // Settings for the Auth class
            $this->auth_domain         = $_SERVER['HTTP_HOST'];
            $this->hash_passwords = true;
            $this->auth_salt           = 'wtnMmVyc8vhkrxBrtkm3VTkLwiAFs'; // Pick any random string of characters

        }

        public function set_config($config,$core)
        {
			// Load $core settings into object
			$this->set_config_vars($core);
			
			foreach ($config as $name => $settings)
			{
				// Search server array to see if where we are matches, if true, then we know what settings to use
	            if (in_array($_SERVER['HTTP_HOST'], $settings['servers']))
				{
					$this->set_config_vars($settings);
		            define('WEB_ROOT', $this->web_root);
				}
				if (!defined('WEB_ROOT')) {
					die('<h1>Where am I?</h1> <p>You need to setup your server names in <code>settings.php</code></p>
	                     <p><code>$_SERVER[\'HTTP_HOST\']</code> reported <code>' . $_SERVER['HTTP_HOST'] . '</code></p>');
				}
			}
		}

	}