<?php

class Field_Information extends Db_Object {

	public function __construct($id = NULL)
	{
		parent::__construct('field_information', 'id', array('table', 'display_name', 'name', 'type', 'value', 'validation', 'class', 'layout', 'options'), $id);
	}

}

class Index_Information extends Db_Object {

	public function __construct($id = NULL)
	{
		parent::__construct('index_information', 'id', array('table', 'slug', 'template'), $id);
	}

}

class Pages extends Db_Object {

	public function __construct($id = NULL)
	{
		parent::__construct('pages', 'id', array('title', 'content'), $id);
	}

}

class Admin_Announcements extends Db_Object {

	public function __construct($id = NULL)
	{
		parent::__construct('admin_announcements', 'id', array('date_posted', 'title', 'author', 'comments'), $id);
	}

}

class Admin_Links extends Db_Object {

	public function __construct($id = NULL)
	{
		parent::__construct('admin_links', 'id', array('order', 'display', 'link', 'authorized_groups'), $id);
	}
	
	public function get_links()
	{
		// TODO: Only return links the user is authorized for
		return $this->select_many('%select% ORDER BY `order` ASC');
	}

}

