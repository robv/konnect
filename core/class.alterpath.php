<?php

// This class provides a way to alter paths to controllers


class AlterPath {
	
	public $seperator = '/';
	public $rewritten_path;
	public $original_path;
	
	function __construct($rewrites)
	{
		if($this->pick_off()){
			$cleaned_current_arr = explode('/',strtolower(implode('/',deslugify($this->pick_off(),'_')))); // Lowercase and deslugify(core/extfunctions.inc.php)
		} else {
			$cleaned_current_arr = array();
		}
		
		// imploding that deslugified array so we get the path we should match
		$current_path = implode('/',$cleaned_current_arr).'/';
		$matches = array();
		
		foreach($rewrites as $intial_path => $destination_path){
			if(empty($this->rewritten_path)){ // if we already matched something stop trying
			
				if(preg_match('#^'.trim($intial_path,'/').'/$#',$current_path,$matches)){
				
					foreach($matches as $key => $value) // in destination path use %1%, %2%, etc as you would $1, $2, in mod_rewrite
						$destination_path = str_replace('%'.$key.'%',$value,$destination_path);
				
					$this->rewritten_path = explode($this->seperator,trim(strtolower($destination_path),$this->seperator)); // trim seperator then explode by seperator
			
				}
			}
		}
		
		// If the current path does not match anything that should be rewritten simply return the original array
		if(empty($this->rewritten_path))
			$this->rewritten_path = $cleaned_current_arr;
			
		$this->original_path = $cleaned_current_arr;
		
	}
	
	function return_paths()
	{
		global $data;
		
		// all array values are lowercase and use _ (underscore) as seperators
		$data['konnect']['original_path'] = $this->original_path; // real url used to access page
		$data['konnect']['rewritten_path'] = $this->rewritten_path; // rewritten if called for in rewrites
	}
	
	function pick_off($pairing='0',$grabFirst = false)
    {
		$ret = array();
		$arr = explode($this->seperator, trim($_SERVER['REQUEST_URI'], $this->seperator));
		if($grabFirst && $pairing !== 'key') $ret[$pairing++] = array_shift($arr);
		while(count($arr) > 0)
		        $pairing === 'key' ? $ret[array_shift($arr)] = array_shift($arr) : $ret[$pairing++] = array_shift($arr);
		
		foreach($ret as $key => $value){
			$check_count = explode('?',$value);
			if(count($check_count) > 1)
				unset($ret[$key]);
		}
		
		return (count($ret) > 0) ? $ret : false;
    }
	
}