-- Adminer 3.6.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `event_status`;
CREATE TABLE `event_status` (
  `status` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci,
  PRIMARY KEY (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='list of allowed statuses (for data integrity)';

INSERT INTO `event_status` (`status`, `description`) VALUES
('applied',	'obecně přihlášen'),
('approved',	'schváleno'),
('cancelled',	'místo smazání'),
('participated',	'opravdu se zúčastnil'),
('pending',	'čeká na schválení'),
('spare',	'náhradník');

-- 2014-01-20 22:14:38
