<?php

class Controller {
	
	public $data;
	
	function __construct($method='',$data = '')
	{
		$defaultMethod = $this->defaultMethod;
		$this->data = $data;
		
		if(empty($method)) // If no controller is set go to default
			$this->$defaultMethod();
		else
			$this->$method();
		
	}
	
	// Loads view file and converts $data array to key => value form.
	// This is the best I could come up with...
	public function loadView($file)
	{
		global $Auth,$Flash,$Error;
			
			// Run through data array for access in view
			if(!empty($this->data)){
				foreach($this->data as $key => $value):
					$$key = $value;
				endforeach;
			}
			
		include DOC_ROOT.'/apps/'.$this->app_name.'/templates/'.$file.'.thtml';
		
	}
	
}