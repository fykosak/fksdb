-- Adminer 3.6.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';



INSERT INTO `address` (`address_id`, `street`, `house_nr`, `city`, `postal_code`, `region_id`) VALUES
(1,	'Macháňova',	'12',	'Tábor',	'12345',	1);


INSERT INTO `contest` (`contest_id`, `name`) VALUES
(1,	'FYKOS'),
(2,	'Výfuk');

INSERT INTO `contestant` (`ct_id`, `contest_id`, `year`, `person_id`, `school_id`, `class`, `study_year`) VALUES
(1,	1,	1,	3,	1,	NULL,	4),
(2,	1,	1,	2,	1,	NULL,	2);

INSERT INTO `country` (`country_iso`, `name_cs`, `name_en`) VALUES
('CZ',	'Česká republika',	'Czech Republic'),
('SK',	'Slovensko',	'Slovakia');

INSERT INTO `login` (`person_id`, `login`, `email`, `hash`, `fb_id`, `created`, `last_login`, `active`) VALUES
(1,	'michal',	'michal@fykos.cz',	'202cb962ac59075b964b07152d234b70',	NULL,	'2012-10-07 12:19:57',	'2012-10-07 15:18:51',	1);

INSERT INTO `org` (`person_id`, `contest_id`, `since`, `until`, `role`, `note`, `order`, `tex_signature`) VALUES
(1,	1,	1,	NULL,	NULL,	NULL,	1,	'koutny'),
(1,	2,	1,	NULL,	NULL,	NULL,	1,	'koutny');


INSERT INTO `person` (`person_id`, `first_name`, `last_name`, `gender`) VALUES
(1,	'Michal',	'Koutný',	'M'),
(2,	'Marie',	'Nováková',	'F'),
(3,	'Lukáš',	'Timko',	'M');



INSERT INTO `region` (`region_id`, `country_iso`, `name`) VALUES
(1,	'CZ',	'Jihočeský kraj');


INSERT INTO `school` (`school_id`, `name_full`, `name`, `name_abbrev`, `address_id`, `email`, `ic`, `izo`, `active`, `note`) VALUES
(1,	NULL,	'Gymnázium Pierra de Coubertina',	'G PdC, Tábor',	1,	NULL,	NULL,	NULL,	1,	NULL),
(2,	NULL,	'Gymnázium Pustevna',	'G Pustevna, Tábot',	1,	NULL,	NULL,	NULL,	1,	NULL);





INSERT INTO `submit` (`ct_id`, `task_id`, `submitted_on`, `source`, `note`, `raw_points`, `calc_points`) VALUES
(1,	1,	'2012-10-07 12:29:35',	'upload',	NULL,	NULL,	NULL);

INSERT INTO `task` (`task_id`, `label`, `name`, `contest_id`, `year`, `series`, `tasknr`, `points`, `submit_mode`, `submit_start`, `submit_deadline`, `correction_mode`) VALUES
(1,	'1',	'proudová',	1,	1,	1,	1,	2,	'S',	NULL,	NULL,	NULL),
(2,	'2',	'vrtulová',	1,	1,	1,	2,	4,	'S',	NULL,	NULL,	NULL),
(3,	'E',	'náporová',	1,	1,	1,	3,	10,	'S',	NULL,	NULL,	NULL);

-- 2012-10-07 17:11:31
