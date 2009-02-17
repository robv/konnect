<?PHP

	class Cms_links extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('cms_links', 'id', array('name', 'link', 'authorized_groups'), $id);
		}
	}
	
	class Dashboard_log extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('dashboard_log', 'id', array('table', 'entry', 'action'), $id);
		}
	}

	class Field_information extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('field_information', 'id', array('table_name', 'name', 'type', 'options'), $id);
		}
	}


	class Galleries extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('galleries', 'id', array('parent_gallery', 'name', 'slug'), $id);
		}
	}


	class Gallery_photos extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('gallery_photos', 'id', array('image', 'caption', 'gallery_id'), $id);
		}
	}


	class News extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('news', 'id', array('timestamp', 'title', 'slug', 'author', 'content'), $id);
		}
	}


	class Pages extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('pages', 'id', array('title', 'slug', 'content'), $id);
		}
	}


	class Sessions extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('sessions', 'id', array('data', 'updated_on'), $id);
		}
	}


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


	class View_information extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('view_information', 'id', array('table_name', 'name', 'type', 'options'), $id);
		}
	}

	class Blog_categories extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('blog_categories', 'id', array('blog_category','slug', 'name'), $id);
		}
	}


	class Blog_entries extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('blog_entries', 'id', array('timestamp', 'category', 'author', 'title', 'slug', 'content', 'tags'), $id);
		}
	
		function insert()
		{
		
			$current_id = parent::insert();
		
			$tags_array = explode(',',$this->tags);
		
			$relationships = new Blog_tag_relations;
			$relationships->idName = 'entry_id';
			$relationships->delete($current_id);
			unset($relationships);
		
			foreach($tags_array as $tag):
		
				$tags = new Blog_tags();
				if($tags->select($tag,'name'))
				{
					$tags->count = $tags->count + 1;
					$tags->update();
				
					$relationships = new Blog_tag_relations;
					$relationships->tag_id = $tags->id;
					$relationships->entry_id = $current_id;
					$relationships->insert();
				}
				else
				{
					$tags->count = 1;
					$tags->name = $tag;
					$tags->slug = slugify($tag);
					$tag_id = $tags->insert();
				
					$relationships = new Blog_tag_relations;
					$relationships->tag_id = $tag_id;
					$relationships->entry_id = $current_id;
					$relationships->insert();
				}
		
			endforeach;
		
			return $current_id;
		}
	
		function update()
		{
		
			$old = new Blog_entries($this->id);
			$old_tags_array = explode(',',$old->tags);
		
			$current_id = $this->id;
		
			foreach($old_tags_array as $tag): // CLEANING THE SLATE NEXT FOR EACH REPOPULATES
		
				$tags = new Blog_tags();
				if($tags->select($tag,'name')) // IT SHOULD EXIST BECAUSE THESE WERE ALREADY INSERTED SO NO ELSE
				{
					if($tags->count == 1)
					{
						$tags->delete();
					}
					else
					{
						$tags->count = $tags->count - 1;
						$tags->update();
					}
				}
		
			endforeach;
		
			unset($tag);
		
			$tags_array = explode(',',$this->tags);
		
			$relationships = new Blog_tag_relations;
			$relationships->idName = 'entry_id';
			$relationships->delete($current_id);
			unset($relationships);
		
			foreach($tags_array as $tag): // INSERT ALL NEW TAGS
		
				$tags = new Blog_tags();
				if($tags->select($tag,'name'))
				{
					$tags->count = $tags->count + 1;
					$tags->update();
				
					$relationships = new Blog_tag_relations;
					$relationships->tag_id = $tags->id;
					$relationships->entry_id = $current_id;
					$relationships->insert();
				}
				else
				{
					$tags->count = 1;
					$tags->name = $tag;
					$tags->slug = slugify($tag);
					$tag_id = $tags->insert();
				
					$relationships = new Blog_tag_relations;
					$relationships->tag_id = $tag_id;
					$relationships->entry_id = $current_id;
					$relationships->insert();
				}
		
			endforeach;
		
			return parent::update();;
		}
	}


	class Blog_tag_relations extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('blog_tag_relations', 'id', array('tag_id', 'entry_id'), $id);
		}
	}


	class Blog_tags extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('blog_tags', 'id', array('name', 'slug', 'count'), $id);
		}
	}