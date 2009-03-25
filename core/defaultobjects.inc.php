<?PHP

	class Konnect_links extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('konnect_links', 'id', array('name', 'link', 'authorized_groups'), $id);
		}
		
		function getLinks()
		{
			global $Auth;
				$db = Database::getDatabase();
	            return DBObject::glob(get_class($this),'SELECT * FROM `Konnect_links` WHERE authorized_groups LIKE "%'.$Auth->level.'%" OR authorized_groups is NULL OR authorized_groups=""');
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
			
			$this->tags = trim($this->tags);
			$this->tags = str_replace(array(', ',' ,',',',$this->tags));
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


	class Blog_categories extends DBObject
	{
		function __construct($id = "")
		{
			parent::__construct('blog_categories', 'id', array('name' ,'slug'), $id);
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