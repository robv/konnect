
CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL auto_increment,
  `blog_category` int(11) default NULL,
  `slug` varchar(255) default NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#

CREATE TABLE `cms_links` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `authorized_groups` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

#

INSERT INTO `cms_links` (`id`,`name`,`link`,`authorized_groups`) VALUES (1,'News','manage/news/','');

#

CREATE TABLE `dashboard_log` (
  `id` int(11) NOT NULL auto_increment,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `table` varchar(255) default NULL,
  `entry` int(11) default NULL,
  `action` enum('add','edit','delete') default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#

CREATE TABLE `field_information` (
  `id` int(11) NOT NULL auto_increment,
  `table_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `options` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

#

INSERT INTO `field_information` (`id`,`table_name`,`name`,`type`,`options`) VALUES (1,'news','title','slug','title,slug_%n%'), (2,'field_information','options','textarea',''), (3,'users','level','dropdown','admin,Administrator|owner,Owner|editor,Editor|user,User'), (4,'field_information','table_name','tables',''), (5,'view_information','table_name','tables',''), (6,'view_information','options','textarea',''), (7,'cms_links','authorized_groups','textarea','');

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

CREATE TABLE `sessions` (
  `id` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `data` text NOT NULL,
  `updated_on` int(10) NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#

CREATE TABLE `view_information` (
  `id` int(11) NOT NULL auto_increment,
  `table_name` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `options` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

#

INSERT INTO `view_information` (`id`,`table_name`,`name`,`type`,`options`) VALUES (1,'users','password','hidden',NULL), (2,'news','slug','hidden','');



