<?php

/*
	SAMPLE USAGE:	
	
	Flash::set('What to say!'); // set
	Flash::show(); // display

	// Advanced
	Flash::set('What to say!','error_one'); // set
	Flash::show('error_one'); // display
*/

class Flash {
	
	private static function set($message, $name='flasher')
	{
		$_SESSION['flasher'][$name] = $message;
	}
	
	private static function show($name='flasher')
	{
		echo isset($_SESSION['flasher'][$name]) ? $_SESSION['flasher'][$name] : '';
	
		if(isset($_SESSION['flasher'][$name]))
			unset($_SESSION['flasher'][$name]);		
	}
	
}