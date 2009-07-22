<?php

class Form_Information extends Db_Object {

	function __construct($id = NULL)
	{
		parent::__construct('form_information', 'id', array('display_name', 'name', 'type', 'value', 'validation', 'class', 'layout', 'options'), $id);
	}

}

class Index_information extends Db_Object {

	function __construct($id = NULL)
	{
		parent::__construct('index_information', 'id', array('table', 'slug', 'template'), $id);
	}

}

class Pages extends Db_Object {

	function __construct($id = NULL)
	{
		parent::__construct('pages', 'id', array('title', 'content'), $id);
	}

}

class Admin_Announcements extends Db_Object {

	function __construct($id = NULL)
	{
		parent::__construct('admin_announcements', 'id', array('date_posted', 'title', 'author', 'comments'), $id);
	}

}

