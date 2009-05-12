<?php

	// This class provides a way to alter paths to controllers

	class Router {
	
		// Singleton object. Leave $me alone.
		private static $me;

		public $uri;
    
    	// Get Singleton object
        public static function exec()
        {
            if (is_null(self::$me))
                self::$me = new Router();
            return self::$me;
        }

		function new_uri($uri = NULL)
		{
			$this->uri = $this->uri_to_array($uri);
			$this->uri = $this->uri_rewrite();
			return $this->uri;
		}
		
		function uri_rewrite()
		{
			$routes = Config::getConfig()->routes;
			
			$uri_string = implode('/',$this->uri) . '/';
			$matches = array();
		
			foreach ($routes as $intial_path => $destination_path)
			{
				if (preg_match('#^' . trim($intial_path, '/') . '/$#', $uri_string, $matches))
				{
					foreach ($matches as $key => $value)
					{ 
						// in destination path use %1%, %2%, etc as you would $1, $2, in mod_rewrite
						$destination_path = str_replace('%'.$key.'%',$value,$destination_path);
					}
					// triming shouldn't be neccessary it's done just in case
					return $this->uri = explode('/',trim(strtolower($destination_path),'/')); 
				}
			}
			
			return $this->uri;
		}
	
		function uri_to_array($uri = NULL)
	    {
			// Not defaulting to server request uri allows some testing to be done
			if(is_null($uri))
			{
				$uri = $_SERVER['REQUEST_URI'];
			}	
			
			// Lowercase the entire string then strip http, https and ftp (just for fun) our of uri and then explode by "/"
			$this->uri = explode('/', trim(str_replace(array('http://','https://','ftp://'), '', strtolower($uri)), '/'));
		
			$new_uri = array();
		
			foreach ($this->uri as $key => $singleton)
			{	
				// We want to rebuild the array without the actual domain
				if ($key != 0)
				{
					// String the string of all special characters
					$new_uri[] = String::exec()->clean($singleton,'-');
				}	
			}
		
			// Replace uri with the new modified version
			$this->uri = $new_uri;
		
			return $this->uri;
	    }
	
	}