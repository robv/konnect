<?PHP

    ini_set('display_errors', '1');
    ini_set('error_reporting', E_ALL);
	require 'core/master.inc.php';
	
	// This is just putting some useful variables together that will be available everywhere
	$ap = new AlterPath($core['rewrites']); 
	$data['pick_off'] = deslugify($ap->return_array);
	$data['url_structure'] = $ap->pick_off();
	$data['config'] = Config::getConfig();
	
	if(!mysql_is_table('users') && $_SERVER['REQUEST_URI'] !== 'install/'){
		include 'controls/install.control.php';
		new Install_controller();
		exit;
	}
	
	// If there is no controller set, set it to your default controller
	if(empty($data['pick_off']['0']))
		$data['pick_off']['0'] = $data['config']->defaultController;
		

		if(file_exists('controls/'.$data['pick_off']['0'].'.control.php')){
	
			include 'controls/'.$data['pick_off']['0'].'.control.php';
	
			$controller_name = ucfirst($data['pick_off']['0']).'_controller';

			if(!isset($data['pick_off']['1']))
				$data['pick_off']['1'] = '';
				
			new $controller_name($data['pick_off']['1'],$data);
	
		}