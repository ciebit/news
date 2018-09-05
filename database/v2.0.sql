--
-- Table `cb_news`
--
CREATE TABLE `cb_news` (
  `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `story_id` int(5) UNSIGNED NOT NULL,
  `cover_id` int(10) UNSIGNED DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='version:2.0';

CREATE TABLE `cb_news_labels` (
  `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id` int(5) UNSIGNED NOT NULL,
  `label_id` int(5) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='version:2.0';

CREATE VIEW cb_news_complete AS
SELECT
    `news`.`id`,
    `news`.`status`,
    `stories`.`id` as `story_id`,
    `stories`.`title` as `story_title`,
    `stories`.`summary` as `story_summary`,
    `stories`.`body` as `story_body`,
    `stories`.`datetime` as `story_datetime`,
    `stories`.`uri` as `story_uri`,
    `stories`.`views` as `story_views`,
    `stories`.`status` as `story_status`,
    `files`.`id` as `cover_id`,
    `files`.`name` as `cover_name`,
    `files`.`description` as `cover_description`,
    `files`.`uri` as `cover_uri`,
    `files`.`extension` as `cover_extension`,
    `files`.`size` as `cover_size`,
    `files`.`views` as `cover_views`,
    `files`.`mimetype` as `cover_mimetype`,
    `files`.`date_hour` as `cover_date_hour`,
    `files`.`metadata` as `cover_metadata`,
    `files`.`status` as `cover_status`,
    (SELECT GROUP_CONCAT(`label_id`) FROM `cb_news_labels` WHERE `news_id` = `news`.`id`) as `labels_id`
FROM `cb_news` AS `news`
INNER JOIN `cb_stories` AS `stories`
	ON `stories`.`id` = `news`.`story_id`
LEFT JOIN `cb_files` AS `files`
	ON `files`.`id` = `news`.`cover_id`
