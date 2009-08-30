<?php

class Users extends Db_Object
{
	function __construct($id = NULL)
	{
		parent::__construct('users', 'id', array('username', 'password', 'level', 'email', 'api_token'), $id);
	}

	function insert()
	{
		if ($this->select(array('username' => $this->username)))
		{
			return false;
		}
		else
		{
			$this->password = Auth::get_instance()->create_hashed_password($this->password);
			// TODO: This looks strange, "create_hashed_password" method should be renamed to encrypted string or something
			$this->api_token = Auth::get_instance()->create_hashed_password($this->username);
			return parent::insert();
		}
	}

	function update()
	{
		$user = new Users();
		$user->select(array('id' => $this->id));
		
		// Because passwords are stored hashed, we don't want to hash a hash
		if($user->password !== $this->password)
			$this->password = Auth::get_instance()->create_hashed_password($this->password);
			
		parent::update();
	}
	
	function verify_api_token($token)
	{
		$user = new Users();
		return $user->select(array('api_token'=>$token));
	}
	
}