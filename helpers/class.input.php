<?php 
class input
{
	static public function get($key = '', $default = null)
	{
		return isset($_GET[$key]) ? $_GET[$key] : $default;
	}

	static public function post($key = '', $default = null)
	{
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}

	static public function cookie($key = '', $default = null)
	{
		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
	}

	static public function server($key = '', $default = null)
	{
		return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
	}

	static public function session($key = '', $default = null)
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}
	
	static public function segment($index)
	{
		$uri = Input::server('REQUEST_URI');
		$segments = explode('/', trim($uri, '/'));
		
		return isset($segments[$index - 1]) ? $segments[$index - 1] : '';
	}
}