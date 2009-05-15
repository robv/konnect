<?php

	class Users extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('users', array('username', 'password', 'level', 'email'), $id);
		}

		function insert()
		{
			$this->password = Auth::getAuth()->createHashedPassword($this->password);
			parent::insert();
		}

		function update()
		{
			global $Auth;
			$thisUser = new Users();
			$thisUser->select($this->id);
			// BECAUSE PASSWORDS ARE STORED HASHED DON'T WANT TO HASH A HASH
			if($thisUser->password !== $this->password)
				$this->password = Auth::getAuth()->createHashedPassword($this->password);
			parent::update();
		}
	
		function delete()
		{
			$company_user = new Company_users();
			$company_user->select($this->id,'user');
			$company_user->delete();
			parent::delete();
		}
	}