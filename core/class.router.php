<?php

/*
	ROUTER provides a way to pass in the current uri in "http://konnectphp.com/foo/bar/" 
	form and outputs an array such as $arr[0] = foo and $arr[1] = bar and also contains a method
	for rerwriting that url based on predefined routes in config/settings.php
*/

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
	
	// Simply runs both uri_to_array and uri_rewrite so we don't have to run both methods
	public static function new_uri($routes, $uri = NULL)
	{
		$this->uri = $this->uri_to_array($uri);
		$this->uri = $this->uri_rewrite($routes);
		return $this->uri;
	}
	
	// Maps current uri array to see if matches are found in config/settings.php
	public static function uri_rewrite($routes)
	{
			
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

	public static function uri_to_array($uri = NULL)
    {
		// Not defaulting to server request uri allows some testing to be done
		if(is_null($uri))
			$uri = $_SERVER['REQUEST_URI'];
		
		// Lowercase the entire string then strip http, https and ftp (just for fun) our of uri and then explode by "/"
		$this->uri = explode('/', trim(str_replace(array('http://','https://','ftp://'), '', strtolower($uri)), '/'));
	
		$new_uri = array();
		
		// We want to rebuild the array without the actual domain
		if (@parse_url($uri))
			array_shift($this->uri);
			
		foreach ($this->uri as $key => $singleton)
		{	
			// String the string of all special characters
			$new_uri[] = String::exec()->clean($singleton,'-');
		}
	
		// Replace uri with the new modified version
		$this->uri = $new_uri;
	
		return $this->uri;
    }

}