CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(65) character set utf8 collate utf8_unicode_ci NOT NULL,
  `password` varchar(65) character set utf8 collate utf8_unicode_ci NOT NULL,
  `level` varchar(255) NOT NULL default 'user',
  `email` varchar(65) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;