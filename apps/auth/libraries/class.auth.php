<?php

// TODO: Convert to only use db_object
class Auth
{
    // Singleton object. Leave $me alone.
    private static $me;

    public $id;
    public $username;
    public $level;
    public $user; // Db_Object User object (if available)

	private $auth_salt;

    private $logged_in;

    // Call with no arguments to attempt to restore a previous logged in session
    // which then falls back to a guest user (which can then be logged in using
    // $this->login($un, $pw). Or pass a user_id to simply login that user. The
    // $seriously is just a safeguard to be certain you really do want to blindly
    // login a user. Set it to TRUE.
    private function __construct($user_to_impersonate = NULL)
    {
		$this->id             = NULL;
		$this->username       = NULL;
		$this->level          = 'guest';
		$this->user           = NULL;
		$this->logged_in      = FALSE;

		$this->auth_salt 	  = 'G9yyiNxdyWnp***twitter.com/thisisrobv***PenaMp8DaNDGjvmo';

        $this->user = new Users();

        if (!is_null($user_to_impersonate))
            return $this->impersonate($user_to_impersonate);

        if ($this->attempt_session_login())
            return TRUE;

        if ($this->attempt_cookie_login())
            return TRUE;

        return FALSE;
    }

    // Get Singleton object
    public static function get_instance($user_to_impersonate = NULL)
    {
        if (is_null(self::$me))
            self::$me = new Auth($user_to_impersonate);
        return self::$me;
    }

    // You'll typically call this function when a user logs in using
    // a form. Pass in their username and password.
    // Takes a username and a *plain text* password
    public function login($un, $pw)
    {
        $pw = $this->create_hashed_password($pw);
        return $this->attempt_login($un, $pw);
    }

    public function logout()
    {
        $this->id             = NULL;
        $this->username       = NULL;
        $this->level          = 'guest';
        $this->user           = NULL;
        $this->logged_in       = FALSE;

        if (class_exists('User') && (is_subclass_of('User', 'Db_Object')))
            $this->user = new User();

        $_SESSION['un'] = '';
        $_SESSION['pw'] = '';
        setcookie('s', '', time() - 3600, '/', Config::$config['core']['basics']['cookie_domain']);
    }

    // Is a user logged in? This was broken out into its own function
    // in case extra logic is ever required beyond a simple bool value.
    public function logged_in()
    {
        return $this->logged_in;
    }

    // Helper function that redirects away from 'admin only' pages
    public function require_level($level)
    {
		// We can accept an array to check for multiple levels or a string equal to the level's name
		if (is_array($level))
			if(!$this->logged_in() || !in_array($this->level, $level))
				redirect($url);
		elseif (!$this->logged_in() || $this->level != $level)
			redirect($url);

    }

    // Helper function that redirects away if you're a guest
    public function require_user($url)
    {
        if (!$this->logged_in())
            redirect($url);
    }

    // Check if the submitted password matches what we have on file, takes a *plain text* password
    public function password_is_correct($pw)
    {
        $db = Database::get_instance();
        $pw = $this->create_hashed_password($pw);

        $db->query('SELECT COUNT(*) FROM users WHERE username = :username AND password = BINARY :password', array('username' => $this->username, 'password' => $pw));
        return $db->get_value() == 1;
    }

    // Login a user simply by passing in their username or id. Does
    // not check against a password. Useful for allowing an admin user
    // to temporarily login as a standard user for troubleshooting.
    // Takes a username
    public function impersonate($user_to_impersonate)
    {
		$db = Database::get_instance();
		$row = $db->getRow('SELECT * FROM users WHERE username = ' . $db->quote($user_to_impersonate));

        if (is_array($row)) {
            $this->id       = $row['id'];
            $this->username = $row['username'];
            $this->level    = $row['level'];

            // Load any additional user info into user object
            $this->user = new User();
            $this->user->id = $row['id'];
            $this->user->load($row);
            
			$row['password'] = $this->create_hashed_password($row['password']);
			
			$this->store_session_data($this->username, $row['password']);
            $this->logged_in = TRUE;

            return TRUE;
        }
        return FALSE;
    }

    // Attempt to login using data stored in the current session
    private function attempt_session_login()
    {
        if (isset($_SESSION['un']) && isset($_SESSION['pw']))
            return $this->attempt_login($_SESSION['un'], $_SESSION['pw']);
        else
            return FALSE;
    }

    // Attempt to login using data stored in a cookie
    private function attempt_cookie_login()
    {
        if (isset($_COOKIE['s']) && is_string($_COOKIE['s'])) {
			// Cookie is a json string containing un and pw, so lets check that shit
            $s = json_decode($_COOKIE['s'], TRUE);
            if (isset($s['un']) && isset($s['pw']))
                return $this->attempt_login($s['un'], $s['pw']);
        }
        return FALSE;
    }

    // The function that actually verifies an attempted login and
    // processes it if successful.
    // Takes a username and a hashed password
    private function attempt_login($un, $pw)
    {
        $db = Database::get_instance();

        // We SELECT * so we can load the full user record into the user Db_Object later
        $row = $db->get_row('SELECT * FROM users WHERE username = ' . $db->quote($un));
        if ($row === FALSE) 
			return FALSE;

        if ($pw != $row['password'])
			return FALSE;

        $this->id       = $row['id'];
        $this->username = $row['username'];
        $this->level    = $row['level'];

        // Load any additional user info into db_object
		$this->user = new Users();
		$this->user->id = $row['id'];
		$this->user->load($row);

        $this->store_session_data($un, $pw);
        $this->logged_in = TRUE;

        return TRUE;
    }

    // Takes a username and a *hashed* password
    private function store_session_data($un, $pw)
    {
        if (headers_sent())
			return FALSE;
        
		$_SESSION['un'] = $un;
		$_SESSION['pw'] = $pw;
		$s = json_encode(array('un' => $un, 'pw' => $pw));
		return setcookie('s', $s, time()+60*60*24*30, '/', Config::$config['core']['basics']['cookie_domain']);
    }

    private function create_hashed_password($pw)
    {
        return sha1($pw . $this->auth_salt);
    }

	public function api_token()
	{
		return sha1($this->id . $this->auth_salt);
	}
}