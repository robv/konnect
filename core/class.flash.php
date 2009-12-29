<?php

/*

	Sample Usage: 
		
		// SET SOMETHING TO FLASH
		$flash->set('What to say!');
		
		// DISPLAY FLASH MESSAGE
		$flash->show();
		
		// ADVANCED :
		$flash->set('What to say!','error_one');
		$flash->show('error_one');
		
*/

class Flash {
	
	function __construct(){
		
		if(!isset($_SESSION['flasher'])){
			$_SESSION['flasher'] = array();
		}
		
	}
	
	function set($message,$name='flasher')
	{
	
		$_SESSION['flasher'][$name] = $message;
	}
	
	function show($name='flasher')
	{
		
		echo isset($_SESSION['flasher'][$name]) ? $_SESSION['flasher'][$name] : '';
		
		if(isset($_SESSION['flasher'][$name]))
			unset($_SESSION['flasher'][$name]);
		
	}
	
}