SET NAMES utf8;
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `participant_has_sport`;

DROP TABLE IF EXISTS `participant`;
CREATE TABLE `participant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `name_key` char(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`),
  KEY `name_key` (`name_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `sport`;
CREATE TABLE `sport` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `participant_has_sport` (
  `participant_id` int(10) unsigned NOT NULL,
  `sport_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `unique_participant_id_sport_id` (`participant_id`,`sport_id`),
  KEY `sport_id` (`sport_id`),
  KEY `participant_id` (`participant_id`),
  CONSTRAINT `participant_has_sport_ibfk_3` FOREIGN KEY (`sport_id`) REFERENCES `sport` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `participant_has_sport_ibfk_4` FOREIGN KEY (`participant_id`) REFERENCES `participant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;