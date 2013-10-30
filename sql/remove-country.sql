ALTER TABLE `region`
DROP FOREIGN KEY `region_ibfk_1`;

DROP TABLE `country`;

ALTER TABLE `region`
CHANGE `nuts` `nuts` varchar(5) COLLATE 'utf8_general_ci' NOT NULL AFTER `country_iso`,
COMMENT='';


