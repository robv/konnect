<?PHP

class Auth_controller extends Controller {
	
	function __construct($controller='',$data = '')
	{
		// This is how you set your default controller I should probably think of a better method
		if(empty($controller))
			$controller = 'login';
		
		parent::__construct($controller,$data);
		
	}
	
	public function login()
	{
		global $Auth,$Flash;
		
		// Kick out user if already logged in
		if($Auth->loggedIn()) redirect(WEB_ROOT);

		if(isset($_POST['username']))
		{
			$Auth->login($_POST['username'], $_POST['password']);
			if($Auth->loggedIn())
				redirect(WEB_ROOT.'admin/');
			else
				$Flash->set('<p class="validation">We\'re sorry, you have entered an incorrect username and password. Please try again.</p>');
		}

		$this->setGlobal('username',isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '');
		
		$this->loadView('auth/login');
	}
	
	public function recover()
	{
		global $Flash;
		
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
					
					$Flash->set('<p class="success">Password has been reset and will be emailed to you shortly.</p>');
					
					redirect(WEB_ROOT.'auth/');
					
				}else{
					
					$Flash->set('<p class="validation">We\'re sorry, you have entered an email address that is not associated with any account.</p>');
					
				}
		}
			
		$this->loadView('auth/recover');
	
	}
	
	public function logout()
	{
		global $Auth;
		
		$Auth->logout();
		
		redirect(WEB_ROOT);
	
	}
	
}