<?php

class Index_controller extends Controller {

	public $defaultMethod = 'login';
	public $app_name;
	
	function __construct($app_name='',$data = '')
	{		
		$this->data = $data;
		$this->app_name = $app_name;
		
		// Building the method name
		if(!isset($this->data['konnect']['rewritten_path']['2']) || empty($this->data['konnect']['rewritten_path']['2']))
			$method = $this->defaultMethod;
		else
			$method = $this->data['konnect']['rewritten_path']['2'];
			
		parent::__construct($method,$data);
	}
	
	public function login()
	{
		

		// Kick out user if already logged in
		if(Auth::getAuth()->loggedIn()) redirect(WEB_ROOT);

		if(isset($_POST['username']))
		{
			Auth::getAuth()->login($_POST['username'], $_POST['password']);
			if(Auth::getAuth()->loggedIn())
				redirect(WEB_ROOT);
			else
				Flash::set('<p class="validation">We\'re sorry, you have entered an incorrect username and password. Please try again.</p>');
		}
		
		$this->data['username'] = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
		
		$this->loadView('login');
	}
	
	public function recover()
	{
		
		
		$this->setGlobal('email',isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '');
				
		if(isset($_POST['email'])){
			
			$recover = new Users();
			
				if($recover->select($_POST['email'],'email')){
					
					// Create a random password and update the table row
					$recover->password = rand_string();
					$recover->update();
						
					$msg = 'Your new password is: '.$recover->password.'<br /><br />';
					$msg .= 'Try logging in at <a href="'.WEB_ROOT.'login/">'.WEB_ROOT.'login/</a>';
					
					send_html_mail($recover->email, 'Password Recovery', $msg,$data['config']->email_address);
					
					Flash::set('<p class="success">Password has been reset and will be emailed to you shortly.</p>');
					
					redirect(WEB_ROOT.'auth/');
					
				}else{
					
					Flash::set('<p class="validation">We\'re sorry, you have entered an email address that is not associated with any account.</p>');
					
				}
		}
			
		$this->loadView('recover');
	
	}
	
	public function logout()
	{
		
		Auth::getAuth()->logout();
		
		redirect(WEB_ROOT);
	
	}
	
}