<?PHP

class Controller {
	
	public $data;
	private $defaultController = 'index';
	
	function __construct($controller='',$data = '')
	{
		$default_controller = $this->defaultController;
		$this->data = $data;
		
		if(empty($controller) || $controller === $data['config']->defaultController) // If no controller is set go to default
			$this->$default_controller();
		else
			$this->$controller();
		
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
			
		include DOC_ROOT.'/templates/'.$file.'.thtml';
		
	}
	
	public function setGlobal($name,$value) {
		$this->data[$name] = $value;
	}
	
}