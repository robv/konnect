<?php

class Index_controller extends Controller {
	
	function __construct($data = '')
	{		
		$this->default_method = 'login';

		// Router uri = app/controller/method
		parent::__construct($data);
	}
	
	public function login()
	{
		// Kick out user if already logged in
		if (Auth::get_auth()->logged_in())
			Core_Helpers::redirect(WEB_ROOT);

		if (isset($_POST['username'])) {
			Auth::get_auth()->login($_POST['username'], $_POST['password']);
			
			if (Auth::get_auth()->logged_in())
				Core_Helpers::redirect(WEB_ROOT);
			else
				Flash::set('<p class="validation">Sorry, you have entered an incorrect username or password. Please try again.</p>');
		}
		
		$this->load_template('login');
	}
	
	public function recover()
	{
		if(isset($_POST['email'])){
					
			$recover = new Users();
			
				if ($recover->select(array('email' => $_POST['email']))) {
					
					// Create a random password and update the table row
					$recover->password = String::random();
					$recover->update();
						
					$msg = 'Your new password is: ' . $recover->password . '<br /><br />';
					$msg .= 'Try logging in at <a href="' . WEB_ROOT . 'login/">' . WEB_ROOT . 'login/</a>';
					
					Core_Helpers::send_html_mail($recover->email, 'Password Recovery', $msg,$data['config']->email_address);
					
					Flash::set('<p class="success">Password has been reset and will be emailed to you shortly.</p>');
					
				} else {	
		
					Flash::set('<p class="validation">Sorry, you have entered an email address that is not associated with any account.</p>');
		
				}
		}
			
		$this->loadView('recover');
	
	}
	
	public function logout()
	{
		
		Auth::get_auth()->logout();
		Core_Helpers::redirect(WEB_ROOT);
	
	}
	
}