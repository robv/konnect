<?php

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
		
		function grab($start=0,$stop=5,$where='ORDER BY timestamp DESC')
		{
				$db = Database::getDatabase();
	            return DBObject::glob(get_class($this),'SELECT * FROM `blog_entries` '.$where.' LIMIT '.$start.','.$stop);
		}
	
		function insert()
		{
		
			$current_id = parent::insert();
			
			$this->tags = trim($this->tags);
			$this->tags = str_replace(array(', ',' ,'),',',$this->tags);
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
			$this->tags = str_replace(array(', ',' ,'),',',$this->tags);
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
		
		function grab($where='ORDER BY name ASC')
		{
				$db = Database::getDatabase();
	            return DBObject::glob(get_class($this),'SELECT * FROM `blog_categories` '.$where);
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
		
		function grab($where='ORDER BY count DESC LIMIT 0,10')
		{
				$db = Database::getDatabase();
	            return DBObject::glob(get_class($this),'SELECT * FROM `blog_tags` '.$where);
		}
	}