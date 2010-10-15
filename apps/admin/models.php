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
		parent::__construct('admin_links', 'id', array('sort_order', 'display', 'name', 'link', 'sub_links', 'authorized_groups'), $id);
	}
	
	public function nav()
	{
		$links = $this->get(array(), array('sort_order' => 'ASC'));
		
		foreach ($links as $k => $v)
		{
			$v['sub_links'] = $this->parse_sub_links($v['sub_links']);
			$links[$k] = $v;
		}
		
		$links = arr::reindex($links, 'name');

		return $links;
	}
	
	private function parse_sub_links($links)
	{
		$sub_links = array();
		
		foreach (explode("\n", trim($links)) as $link)
		{
			$pieces = explode(',', $link);
			if ($pieces && count($pieces) == 3)
			{
				$arr = array(
					'display' => $pieces[0],
					'name' => $pieces[1],
					'link' => $pieces[2]
				);

				$sub_links[$pieces[1]] = $arr;
			}
		}
		
		return $sub_links;
	}

}

