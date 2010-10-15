<?php

class Main_Controller extends Controller {
	
	function __construct($data = '')
	{		
		$this->default_method = 'login';

		// Router uri = app/controller/method
		parent::__construct($data);
	}
	
	public function login()
	{
		// Kick out user if already logged in
		if (Auth::get_instance()->logged_in())
			Core_Helpers::redirect(WEB_ROOT);

		if (isset($_POST['username'])) 
		{
			Auth::get_instance()->login($_POST['username'], $_POST['password']);
			
			if (Auth::get_instance()->logged_in())
				Core_Helpers::redirect(WEB_ROOT);
			else
				Flash::set('<p class="flash validation">Sorry, you have entered an incorrect username or password. Please try again.</p>');
		}
		
		// If there are no users then let's create one.
		$db = Database::get_instance();
		$db->query('SELECT * FROM `users` LIMIT 1');
		if (!$db->has_rows())
		{
			Core_Helpers::redirect(WEB_ROOT . 'auth/create-user/');
		}
		
		$this->load_template('login');
	}
	
	public function recover()
	{
		return FALSE;
		
		if (isset($_POST['email']))
		{
					
			$recover = new Users();
			
				if ($recover->select(array('email' => $_POST['email']))) {
					
					// Create a random password and update the table row
					$recover->password = String::random();
					$recover->update();
						
					$msg = 'Your new password is: ' . $recover->password . '<br /><br />';
					$msg .= 'Try logging in at <a href="' . WEB_ROOT . 'login/">' . WEB_ROOT . 'login/</a>';
					
					Core_Helpers::send_html_mail($recover->email, 'Password Recovery', $msg, $data['config']->email_address);
					
					Flash::set('<p class="flash success">Password has been reset and will be emailed to you shortly.</p>');
					
				} else {	
		
					Flash::set('<p class="flash validation">Sorry, you have entered an email address that is not associated with any account.</p>');
		
				}
		}
		$this->load_template('recover');
	}
	
	public function logout()
	{
		
		Auth::get_instance()->logout();
		Core_Helpers::redirect(WEB_ROOT);
	
	}
	
	public function create_user()
	{	
		
		// If there are no users then let's create one.
		$db = Database::get_instance();
		$db->query('SELECT * FROM `users` LIMIT 1');
		if ($db->has_rows() && !Auth::get_instance()->logged_in())
		{
			Flash::set('<p class="flash validation">Sorry but to create new users, you must be logged in.</p>');
			Core_Helpers::redirect(WEB_ROOT . 'login/');
		}
		
		$validator = Error::instance();
		
		if (isset($_POST['email']))
		{
			$validator->email($_POST['email'], 'email');
			$validator->blank($_POST['username'], 'username');
			$validator->blank($_POST['password'], 'password');
			$validator->passwords($_POST['password'], $_POST['confirm_password'], 'confirm_password');
			
			$user = new Users;
			if ($user->select(array('username' => $_POST['username'])))
			{
				$validator->add('username', 'The username <strong>' . htmlspecialchars($_POST['username']) . '</strong> is already taken.');
			}
			
			if ($validator->ok())
			{
				$user = new Users;
				$user->load($_POST);
				$user->level = 'superadmin';
				$user->insert();
				Flash::set('<p class="flash success">User created successfully.</p>');
				Core_Helpers::redirect(WEB_ROOT . 'login/');
			}
		}
		
		$this->data['error'] = $validator;
		$this->load_template('create_user');
	
	}
	
}