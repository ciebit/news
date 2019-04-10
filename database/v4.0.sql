--
-- Table `cb_news`
--
CREATE TABLE `cb_news` (
  `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cover_id` int(10) UNSIGNED DEFAULT NULL,
  `author_id` int(5) UNSIGNED DEFAULT NULL,
  `title` varchar(300) DEFAULT NULL,
  `summary` varchar(500) DEFAULT NULL,
  `body` text,
  `datetime` datetime DEFAULT NULL,
  `slug` varchar(300) DEFAULT NULL,
  `views` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `language` varchar(6) DEFAULT NULL,
  `languages_references` varchar(300) DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='version:4.0';

CREATE TABLE `cb_news_labels` (
  `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id` int(5) UNSIGNED NOT NULL,
  `label_id` int(5) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='version:4.0';
