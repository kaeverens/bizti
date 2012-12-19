DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0',
  `name` text,
  `meta` text,
  `num_invoices` int(11) DEFAULT '0',
  `paid` float DEFAULT NULL,
  `total` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cdate` date DEFAULT NULL,
  `customer_id` int(11) DEFAULT '0',
  `user_id` int(11) DEFAULT '0',
  `total` float DEFAULT '0',
  `paid` float DEFAULT '0',
  `meta` text,
  `products` text,
  `notes` text,
  `num` int(11) DEFAULT '0',
  `tax` float DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) DEFAULT '0',
  `cdate` datetime DEFAULT NULL,
  `amt` float DEFAULT NULL,
  `meta` text,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0',
  `name` text,
  `meta` text,
  `price` float DEFAULT '0',
  `tax` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `taxes`;
CREATE TABLE `taxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT '0',
  `name` text,
  `meta` text,
  `percentage` float DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` text,
  `password` char(32) DEFAULT NULL,
  `active` smallint(6) DEFAULT '0',
  `meta` text,
  `cdate` datetime DEFAULT NULL,
  `currency_symbol` char(10) DEFAULT '€',
  `level` smallint(6) DEFAULT '0',
  `options` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
