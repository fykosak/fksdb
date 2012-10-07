-- Adminer 3.6.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `submit`;
CREATE TABLE `submit` (
  `ct_id` int(11) NOT NULL COMMENT 'Contestant',
  `task_id` int(11) NOT NULL COMMENT 'Task',
  `submitted_on` datetime NOT NULL,
  `source` enum('post','upload') NOT NULL COMMENT 'odkud přišlo řešení',
  `note` varchar(255) DEFAULT NULL COMMENT 'Pocet stranek a jine poznamky',
  `raw_points` decimal(4,2) DEFAULT NULL COMMENT 'Pred prepoctem',
  `calc_points` decimal(4,2) DEFAULT NULL COMMENT 'Po prepoctu (NULL pokud se v tomto rocniku neprepocitava)',
  UNIQUE KEY `ct_id` (`ct_id`,`task_id`),
  KEY `task_id` (`task_id`),
  CONSTRAINT `submit_ibfk_1` FOREIGN KEY (`ct_id`) REFERENCES `contestant` (`ct_id`),
  CONSTRAINT `submit_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `submit` (`ct_id`, `task_id`, `submitted_on`, `source`, `note`, `raw_points`, `calc_points`) VALUES
(1,	1,	'2012-10-07 12:29:35',	'upload',	NULL,	NULL,	NULL);

-- 2012-10-07 12:29:51
