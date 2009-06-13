<?php

class Main_Controller extends Controller {
	
	function __construct($data = '')
	{		
		$this->default_method = 'dashboard';

		// Router uri = app/controller/method
		parent::__construct($data);
	}
	
	public function dashboard()
	{
		// Kick out user if already logged in
		if (!Auth::get_auth()->logged_in()) {
			Flash::set('<p class="flash warning">You must be logged in to access admin.</p>');
			Core_Helpers::redirect(WEB_ROOT . 'login/');
		}
		
		$this->load_template('dashboard');
	}
	
}