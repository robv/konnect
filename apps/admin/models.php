<?php

class Form_Information extends Db_Object {

	function __construct($id = '')
	{
		parent::__construct('form_information', 'id', array('display_name', 'name', 'type', 'value', 'validation', 'class', 'layout', 'options'), $id);
	}

}

class Pages extends Db_Object {

	function __construct($id = '')
	{
		parent::__construct('pages', 'id', array('title', 'content'), $id);
	}

}

