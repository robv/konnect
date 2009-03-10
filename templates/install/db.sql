CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `slug` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

#

CREATE TABLE `blog_entries` (
  `id` int(11) NOT NULL auto_increment,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `category` int(11) default NULL,
  `author` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) default NULL,
  `content` text NOT NULL,
  `tags` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

#

CREATE TABLE `blog_tag_relations` (
  `id` int(11) NOT NULL auto_increment,
  `tag_id` int(11) default NULL,
  `entry_id` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

#

CREATE TABLE `blog_tags` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `slug` varchar(255) default NULL,
  `count` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

#

CREATE TABLE `galleries` (
  `id` int(11) NOT NULL auto_increment,
  `parent_gallery` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


#

CREATE TABLE `gallery_photos` (
  `id` int(11) NOT NULL auto_increment,
  `image` varchar(255) NOT NULL,
  `gallery` int(11) default NULL,
  `caption` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#

CREATE TABLE `konnect_field_information` (
  `id` int(11) NOT NULL auto_increment,
  `table_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `options` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

#

INSERT INTO `konnect_field_information` (`id`,`table_name`,`name`,`type`,`options`)
VALUES
	(1,'users','level','dropdown','admin,Administrator|owner,Owner|editor,Editor|user,User'),
	(2,'konnect_field_information','options','textarea',''),
	(3,'konnect_field_information','table_name','tables',''),
	(4,'konnect_view_information','table_name','tables',''),
	(5,'konnect_view_information','options','textarea',''),
	(6,'konnect_links','authorized_groups','textarea',''),
	(7,'blog_categories','name','slug','title,slug_%n%'),
	(8,'blog_entries','title','slug','title,slug_%n%'),
	(9,'blog_entries','category','related','table,blog_categories|text,name|val,id'),
	(10,'news','title','slug','title,slug_%n%');

#

CREATE TABLE `konnect_links` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `authorized_groups` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

#

INSERT INTO `konnect_links` (`id`,`name`,`link`,`authorized_groups`)
VALUES
	(1,'News','manage/news/',''),
	(2,'Blog Categories','manage/blog-categories/',''),
	(3,'Blog Entries','manage/blog-entries/','');

#

CREATE TABLE `konnect_sessions` (
  `id` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `data` text NOT NULL,
  `updated_on` int(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#

CREATE TABLE `konnect_view_information` (
  `id` int(11) NOT NULL auto_increment,
  `table_name` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `options` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

#

INSERT INTO `konnect_view_information` (`id`,`table_name`,`name`,`type`,`options`)
VALUES
	(1,'users','password','hidden',NULL),
	(2,'news','slug','hidden',''),
	(3,'blog_entries','slug','hidden','');

#

CREATE TABLE `news` (
  `id` int(11) NOT NULL auto_increment,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#

CREATE TABLE `pages` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#

CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(65) character set utf8 collate utf8_unicode_ci NOT NULL,
  `password` varchar(65) character set utf8 collate utf8_unicode_ci NOT NULL,
  `level` varchar(255) NOT NULL default 'user',
  `email` varchar(65) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;