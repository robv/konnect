<?php

	class Konnect_links extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('konnect_links', 'id', array('parent_link', 'name', 'link', 'authorized_groups'), $id);
		}
	
		function getLinks()
		{
			
            $links = DBObject::glob(get_class($this),'SELECT * FROM `konnect_links` WHERE (authorized_groups LIKE "%'.Auth::getAuth()->level.'%" OR authorized_groups is NULL OR authorized_groups="") AND (parent_link = "" OR parent_link IS NULL)');
			$sub_links = array();
		
			foreach($links as $link){
				$sub_links[$link->id] = DBObject::glob(get_class($this),'SELECT * FROM `konnect_links` WHERE (authorized_groups LIKE "%'.Auth::getAuth()->level.'%" OR authorized_groups is NULL OR authorized_groups="") AND (parent_link = "'.$link->id.'")');
			}
		
			$return['object'] = $links;
			$return['sub_links'] = $sub_links;
			return $return;
		
		}
	}

	class Konnect_field_information extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('konnect_field_information', 'id', array('table_name', 'name', 'type', 'options'), $id);
		}
	}


	class Konnect_sessions extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('konnect_sessions', 'id', array('data', 'updated_on'), $id);
		}
	}


	class Konnect_view_information extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('konnect_view_information', 'id', array('table_name', 'name', 'type', 'options'), $id);
		}
	}

	class User_preferences extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('user_preferences', 'id', array('user', 'preference', 'value'), $id);
		}
	
		function setPreference($user_id,$value = 'add')
		{
		
			$this->preference = 'next';
			$this->value = $value;
			$this->user = $user_id;
		
			if(strlen($this->value) > 1)
				return parent::update();
			else
				return parent::insert();
			
		}
	}