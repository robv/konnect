<?php

class Users extends Db_Object
{
	function __construct($id = NULL)
	{
		parent::__construct('users', 'id', array('username', 'password', 'level', 'email'), $id);
	}

	function insert()
	{
		$this->password = Auth::get_auth()->create_hashed_password($this->password);
		parent::insert();
	}

	function update()
	{
		$user = new Users();
		$user->select(array('id' => $this->id));
		
		// Because passwords are stored hashed, we don't want to hash a hash
		if($user->password !== $this->password)
			$this->password = Auth::get_auth()->create_hashed_password($this->password);
			
		parent::update();
	}
}