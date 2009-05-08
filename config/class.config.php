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

        // Add your server hostnames to the appropriate arrays. ($_SERVER['HTTP_HOST'])
        private $servers = array();

        // Standard Config Options...

        // ...For Auth Class
        public $authDomain;         // Domain to set for the cookie
        public $authSalt;           // Can be any random string of characters
        public $useHashedPasswords; // Store hashed passwords in database? (versus plain-text)

        // ...For Database Class
        public $dbHost;       // Database server
        public $dbName;       // Database name
        public $dbUsername;   // Database username
        public $dbPassword;   // Database password
        public $dbDieOnError; // What do do on a database error (see class.database.php for details)

        // Add your config options here...
        public $useDBSessions; // Set to true to store sessions in the database

        // Singleton constructor
        private function __construct($config)
        {

            $this->everywhere();
            $this->setConfig($config);
                
        }

        // Get Singleton object
        public static function getConfig()
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

        // Add code to be run on all servers
        private function everywhere()
        {
            // Store sesions in the database?
            $this->useDBSessions = true;

            // Settings for the Auth class
            $this->authDomain         = $_SERVER['HTTP_HOST'];
            $this->useHashedPasswords = true;
            $this->authSalt           = 'wtnMmVyc8vhkrxBrtkm3VTkLwiAFs'; // Pick any random string of characters

        }

        public function setConfig($config)
        {
			foreach($config as $name => $settings)
			{
	            if (in_array($_SERVER['HTTP_HOST'], $settings['servers']))
				{
		            ini_set('display_errors', $settings['displayErrors']);
		            define('WEB_ROOT', $settings['WEB_ROOT']);

		            $this->dbHost       = $settings['dbHost'];
		            $this->dbName       = $settings['dbName'];
		            $this->dbUsername   = $settings['dbUsername'];
		            $this->dbPassword   = $settings['dbPassword'];
		            $this->dbDieOnError = $settings['dbDieOnError'];
				}
				if(!defined('WEB_ROOT')){
					die('<h1>Where am I?</h1> <p>You need to setup your server names in <code>settings.php</code></p>
	                     <p><code>$_SERVER[\'HTTP_HOST\']</code> reported <code>' . $_SERVER['HTTP_HOST'] . '</code></p>');
				}
			}
		}

	}