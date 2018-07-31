--
-- Table `cb_news`
--
CREATE TABLE `cb_news` (
  `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `story_id` int(5) UNSIGNED NOT NULL,
  `cover_id` int(10) UNSIGNED DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='version:1.0';
