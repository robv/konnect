CREATE TABLE `konnect_field_information` (
  `id` int(11) NOT NULL auto_increment,
  `table_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `options` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

#

INSERT INTO `konnect_field_information` (`id`,`table_name`,`name`,`type`,`options`)
VALUES
	(1,'users','level','dropdown','admin,Administrator|owner,Owner|editor,Editor|user,User'),
	(2,'konnect_field_information','options','textarea',''),
	(3,'konnect_field_information','table_name','tables',''),
	(4,'konnect_view_information','table_name','tables',''),
	(5,'konnect_view_information','options','textarea',''),
	(6,'konnect_links','authorized_groups','textarea',''),
	(7,'konnect_links','parent_link','related','table,konnect_links|text,name|val,id|selectName,None');

#

CREATE TABLE `konnect_links` (
  `id` int(11) NOT NULL auto_increment,
  `parent_link` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `authorized_groups` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

#

INSERT INTO `konnect_view_information` (`id`,`table_name`,`name`,`type`,`options`)
VALUES
	(1,'users','password','hidden',NULL);

#

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL,
  `preference` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;