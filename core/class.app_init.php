<?php

class App_init {
	
	public $app_name;
	public $default_controller;
	public $rewrites;
	public $data;
	public $seperator = '/';
	
	function __construct()
	{
		global $data; // pulls in data array
		$this->data = $data;
		
		$this->initiateApp();
	}
	
	function rewrite()
	{
		
		if(isset($this->data['konnect']['rewritten_path']))
			$current_path = trim(str_replace($this->app_name,'',implode('/',$this->data['konnect']['rewritten_path'])),'/');
		else
			$current_path = '';
			
		$matches = array();
		
		foreach($this->rewrites as $intial_path => $destination_path){
			if(empty($this->rewritten_path)){ // if we already matched something stop trying
				if(preg_match('#^'.$intial_path.'$#i',$current_path,$matches)){
								
					foreach($matches as $key => $value) // in destination path use %1%, %2%, etc as you would $1, $2, in mod_rewrite
						$destination_path = str_replace('%'.$key.'%',$value,$destination_path);
				
					$this->data['konnect']['rewritten_path'] = explode($this->seperator,trim(strtolower($this->app_name.'/'.$destination_path),$this->seperator)); // trim seperator then explode by seperator
				}
			}
		}
		
		$this->data['konnect']['app_original_path'] = $current_path;
		
	}
	
	public function initiateApp()
	{
		die('Please set up your "initiateApp" method for this app');
	}
	
}