DROP TABLE IF EXISTS `admin_links`;

#

DROP TABLE IF EXISTS `admin_announcements`;

#

DROP TABLE IF EXISTS `field_information`;

#

DROP TABLE IF EXISTS `index_information`;

#

DROP TABLE IF EXISTS `pages`;

#

CREATE TABLE `admin_links` (
  `id` int(11) NOT NULL auto_increment,
  `order` int(11) NOT NULL default '1',
  `display` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `authorized_groups` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

#

INSERT INTO `admin_links` (`id`,`order`,`display`,`link`,`authorized_groups`) VALUES (2,2,'Announcements','admin/index/admin-announcements/',NULL), (1,1,'Dashboard','admin/',NULL);
	
#

CREATE TABLE `admin_announcements` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `date_posted` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `author` int(11) unsigned default NULL,
  `comments` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#

CREATE TABLE `field_information` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `table` varchar(255) default NULL,
  `display_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `value` varchar(255) default NULL,
  `validation` varchar(255) default NULL,
  `class` varchar(255) default NULL,
  `layout` varchar(255) default NULL,
  `options` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#

CREATE TABLE `index_information` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `table` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sql` varchar(255) default NULL,
  `template` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#

CREATE TABLE `pages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `content` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;