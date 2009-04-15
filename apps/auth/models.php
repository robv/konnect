<?php


	class Users extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('users', 'id', array('username', 'password', 'level', 'email'), $id);
		}

		function insert()
		{
			global $Auth;
				$this->password = $Auth->createHashedPassword($this->password);
			parent::insert();
		}

		function update()
		{
			global $Auth;
			$thisUser = new Users();
			$thisUser->select($this->id);
			// BECAUSE PASSWORDS ARE STORED HASHED DON'T WANT TO HASH A HASH
			if($thisUser->password !== $this->password)
				$this->password = $Auth->createHashedPassword($this->password);
			parent::update();
		}
	}
