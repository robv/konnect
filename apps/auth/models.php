<?php

class Users extends DBObject
{
	function __construct($id = "")
	{
		parent::__construct('users', array('username', 'password', 'level', 'email'), $id);
	}

	function insert()
	{
		$this->password = Auth::get_auth()->create_hashed_password($this->password);
		parent::insert();
	}

	function update()
	{
		$user = new Users();
		$user->select($this->id);
		
		// Because passwords are stored hashed, we don't want to hash a hash
		if($user->password !== $this->password)
			$this->password = Auth::get_auth()->create_hashed_password($this->password);
			
		parent::update();
	}
}