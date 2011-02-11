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

DROP TABLE IF EXISTS `admin_links`;

#

CREATE TABLE `admin_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(11) NOT NULL DEFAULT '1',
  `display` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `link` varchar(255) NOT NULL,
  `authorized_groups` text,
  `sub_links` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#

INSERT INTO `admin_links` (`id`,`sort_order`,`display`,`name`,`link`,`authorized_groups`,`sub_links`) VALUES (2,1,'Dashboard','dashboard','dashboard',NULL,NULL);
	
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