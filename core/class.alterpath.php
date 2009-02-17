<?PHP

// This class provides a way to alter paths to controllers


class AlterPath {
	
	public $seperator = '/';
	public $return_array;
	
	function __construct($paths)
	{
		// deslugify is a custom functions located in exfunctions.inc.php
		$act_paths = deslugify($this->pick_off());
		
		foreach($paths as $original => $path):
		
			$current_path = '';
			
			foreach($act_paths as $act_path):
			
				$current_path .= $act_path.'/';
			
			endforeach;
			
			// If the current path matches one that needs to be rewritten
			if($original === $current_path)
				$this->return_array = explode($this->seperator,trim($path,$this->seperator));
		
		endforeach;
		
		// If the current path does not match anything that should be rewritten simply return the original
		if(empty($this->return_array))
			$this->return_array = $act_paths;
		
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