SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `fksdb` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `fksdb` ;

-- -----------------------------------------------------
-- Table `fksdb`.`contest`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`contest` (
  `contest_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`contest_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '(sub)semináře';


-- -----------------------------------------------------
-- Table `fksdb`.`event_type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`event_type` (
  `event_type_id` INT NOT NULL AUTO_INCREMENT,
  `contest_id` INT(11) NOT NULL,
  `name` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`event_type_id`),
  INDEX `fk_event_type_contest1_idx` (`contest_id` ASC),
  CONSTRAINT `fk_event_type_contest1`
    FOREIGN KEY (`contest_id`)
    REFERENCES `fksdb`.`contest` (`contest_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`event`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`event` (
  `event_id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_type_id` INT NOT NULL,
  `year` TINYINT(4) NOT NULL COMMENT 'ročník semináře',
  `event_year` TINYINT(4) NOT NULL COMMENT 'ročník akce',
  `begin` DATE NOT NULL COMMENT 'první den akce',
  `end` DATE NOT NULL COMMENT 'poslední den akce, u jednodenní akce shodný s begin',
  `registration_begin` DATETIME NULL DEFAULT NULL COMMENT 'případný počátek webové registrace',
  `registration_end` DATETIME NULL DEFAULT NULL COMMENT 'případný konec webové registrace',
  `name` VARCHAR(255) NOT NULL COMMENT 'název akce',
  `fb_album_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'id galerie na Facebooku',
  `report` TEXT NULL DEFAULT NULL COMMENT '(HTML) zápis z proběhlé akce',
  `parameters` TEXT NULL DEFAULT NULL COMMENT 'optional parameters',
  PRIMARY KEY (`event_id`),
  INDEX `fk_event_event_type1_idx` (`event_type_id` ASC),
  UNIQUE INDEX `UQ_EVENT_YEAR` (`event_year` ASC, `event_type_id` ASC),
  CONSTRAINT `fk_event_event_type1`
    FOREIGN KEY (`event_type_id`)
    REFERENCES `fksdb`.`event_type` (`event_type_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`person`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`person` (
  `person_id` INT(11) NOT NULL AUTO_INCREMENT,
  `family_name` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_czech_ci' NOT NULL COMMENT 'Příjmení (nebo více příjmení oddělených jednou mezerou)',
  `other_name` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_czech_ci' NOT NULL COMMENT 'Křestní jména, von, de atd., oddělená jednou mezerou',
  `display_name` VARCHAR(511) CHARACTER SET 'utf8' COLLATE 'utf8_czech_ci' NULL DEFAULT NULL COMMENT 'zobrazované jméno, liší-li se od <other_name> <family_name>',
  `gender` ENUM('M','F') CHARACTER SET 'utf8' NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`person_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_czech_ci
COMMENT = 'řazení: <family_name><other_name>, zobrazení <other_name>';


-- -----------------------------------------------------
-- Table `fksdb`.`event_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`event_status` (
  `status` VARCHAR(20) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`status`))
ENGINE = InnoDB
COMMENT = 'list of allowed statuses (for data integrity)';


-- -----------------------------------------------------
-- Table `fksdb`.`event_participant`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`event_participant` (
  `event_participant_id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `person_id` INT(11) NOT NULL,
  `note` TEXT NULL DEFAULT NULL COMMENT 'poznámka',
  `status` VARCHAR(20) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'čas vytvoření přihlášky',
  `accomodation` TINYINT(1) NULL DEFAULT NULL,
  `diet` TEXT NULL DEFAULT NULL COMMENT 'speciální stravování',
  `health_restrictions` TEXT NULL DEFAULT NULL COMMENT 'alergie, léky, úrazy,...',
  `tshirt_size` TEXT NULL DEFAULT NULL,
  `price` DECIMAL(6,2) NULL DEFAULT NULL COMMENT 'spočtena cena',
  PRIMARY KEY (`event_participant_id`),
  INDEX `action_id` (`event_id` ASC),
  INDEX `person_id` (`person_id` ASC),
  INDEX `fk_event_participant_e_status1_idx` (`status` ASC),
  CONSTRAINT `action_application_ibfk_1`
    FOREIGN KEY (`event_id`)
    REFERENCES `fksdb`.`event` (`event_id`),
  CONSTRAINT `action_application_ibfk_2`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`),
  CONSTRAINT `fk_event_participant_e_status1`
    FOREIGN KEY (`status`)
    REFERENCES `fksdb`.`event_status` (`status`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`region`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`region` (
  `region_id` INT(11) NOT NULL AUTO_INCREMENT,
  `country_iso` CHAR(2) NOT NULL COMMENT 'ISO 3166-1',
  `nuts` VARCHAR(5) NOT NULL COMMENT 'NUTS of the EU region',
  `name` VARCHAR(255) NOT NULL COMMENT 'name of the region in the language intelligible in that region',
  PRIMARY KEY (`region_id`),
  UNIQUE INDEX `nuts` (`nuts` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Ciselnik regionu pro vyber skoly v registraci';


-- -----------------------------------------------------
-- Table `fksdb`.`address`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`address` (
  `address_id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_row` VARCHAR(255) NULL DEFAULT NULL COMMENT 'doplňkový řádek adresy (např. bytem u X Y)',
  `second_row` VARCHAR(255) NULL DEFAULT NULL COMMENT 'ještě doplňkovější řádek adresy (nikdo neví)',
  `target` VARCHAR(255) NOT NULL COMMENT 'ulice č.p./or., vesnice č.p./or., poštovní přihrádka atd.',
  `city` VARCHAR(255) NOT NULL COMMENT 'město doručovací pošty',
  `postal_code` CHAR(5) NULL DEFAULT NULL COMMENT 'PSČ (pro ČR a SR)',
  `region_id` INT(11) NOT NULL COMMENT 'detekce státu && formátovacích zvyklostí',
  PRIMARY KEY (`address_id`),
  INDEX `region_id` (`region_id` ASC),
  CONSTRAINT `address_ibfk_1`
    FOREIGN KEY (`region_id`)
    REFERENCES `fksdb`.`region` (`region_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'adresa jako poštovní nikoli územní identifikátor, immut';


-- -----------------------------------------------------
-- Table `fksdb`.`login`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`login` (
  `login_id` INT(11) NOT NULL AUTO_INCREMENT,
  `person_id` INT(11) NULL DEFAULT NULL,
  `login` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Login name',
  `hash` CHAR(40) NULL DEFAULT NULL COMMENT 'sha1(login_id . md5(password)) as hexadecimal',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME NULL DEFAULT NULL,
  `active` TINYINT(1) NOT NULL,
  PRIMARY KEY (`login_id`),
  UNIQUE INDEX `login` (`login` ASC),
  UNIQUE INDEX `person_id_UNIQUE` (`person_id` ASC),
  CONSTRAINT `login_ibfk_1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fksdb`.`auth_token`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`auth_token` (
  `token_id` INT(11) NOT NULL AUTO_INCREMENT,
  `login_id` INT(11) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `type` VARCHAR(31) NOT NULL COMMENT 'type of token (from programmers POV)',
  `data` VARCHAR(255) NULL DEFAULT NULL COMMENT 'various purpose data',
  `since` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `until` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE INDEX `token_UNIQUE` (`token` ASC),
  PRIMARY KEY (`token_id`),
  INDEX `fk_auth_token_login1_idx` (`login_id` ASC),
  CONSTRAINT `fk_auth_token_login1`
    FOREIGN KEY (`login_id`)
    REFERENCES `fksdb`.`login` (`login_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fksdb`.`contestant_base`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`contestant_base` (
  `ct_id` INT(11) NOT NULL AUTO_INCREMENT,
  `contest_id` INT(11) NOT NULL COMMENT 'seminář',
  `year` TINYINT(4) NOT NULL COMMENT 'Rocnik semináře',
  `person_id` INT(11) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ct_id`),
  UNIQUE INDEX `contest_id` (`contest_id` ASC, `year` ASC, `person_id` ASC),
  INDEX `person_id` (`person_id` ASC),
  CONSTRAINT `contestant_base_ibfk_1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `contestant_base_ibfk_3`
    FOREIGN KEY (`contest_id`)
    REFERENCES `fksdb`.`contest` (`contest_id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
COMMENT = 'Instance ucastnika (v konkretnim rocniku a semináři)';


-- -----------------------------------------------------
-- Table `fksdb`.`dakos_person`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`dakos_person` (
  `dakos_id` INT(11) NOT NULL COMMENT 'Id účastníka z dakosího exportu',
  `person_id` INT(11) NOT NULL,
  PRIMARY KEY (`dakos_id`),
  INDEX `person_id` (`person_id` ASC),
  CONSTRAINT `dakos_person_ibfk_1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Identifikace osoby z DaKoSu';


-- -----------------------------------------------------
-- Table `fksdb`.`school`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`school` (
  `school_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name_full` VARCHAR(255) NULL DEFAULT NULL COMMENT 'plný název školy',
  `name` VARCHAR(255) NOT NULL COMMENT 'zkrácený název školy (na obálku)',
  `name_abbrev` VARCHAR(32) NOT NULL COMMENT 'Zkratka pouzivana napr. ve vysledkove listine',
  `address_id` INT(11) NOT NULL,
  `email` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Kontaktní e-mail',
  `ic` CHAR(8) NULL DEFAULT NULL COMMENT 'IČ (osm číslic)',
  `izo` VARCHAR(32) NULL DEFAULT NULL COMMENT 'IZO kód (norma?)',
  `active` TINYINT(1) NULL DEFAULT NULL COMMENT 'Platný záznam školy',
  `note` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`school_id`),
  UNIQUE INDEX `ic` (`ic` ASC),
  UNIQUE INDEX `izo` (`izo` ASC),
  INDEX `address_id` (`address_id` ASC),
  CONSTRAINT `school_ibfk_1`
    FOREIGN KEY (`address_id`)
    REFERENCES `fksdb`.`address` (`address_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fksdb`.`dakos_school`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`dakos_school` (
  `dakos_SKOLA_Id` INT(11) NOT NULL,
  `school_id` INT(11) NOT NULL,
  PRIMARY KEY (`dakos_SKOLA_Id`),
  INDEX `school_id` (`school_id` ASC),
  CONSTRAINT `dakos_school_ibfk_1`
    FOREIGN KEY (`school_id`)
    REFERENCES `fksdb`.`school` (`school_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fksdb`.`olddb_person`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`olddb_person` (
  `olddb_uid` INT(11) NOT NULL COMMENT 'users.id ze staré DB',
  `person_id` INT(11) NOT NULL,
  `olddb_redundant` TINYINT(1) NOT NULL COMMENT 'Tato data se nezkopírovala',
  PRIMARY KEY (`olddb_uid`),
  INDEX `person_id` (`person_id` ASC),
  CONSTRAINT `olddb_person_ibfk_1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fksdb`.`org`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`org` (
  `org_id` INT(11) NOT NULL AUTO_INCREMENT,
  `person_id` INT(11) NOT NULL,
  `contest_id` INT(11) NOT NULL,
  `since` TINYINT(4) NOT NULL COMMENT 'od kterého ročníku orguje',
  `until` TINYINT(4) NULL DEFAULT NULL COMMENT 'v kterém rončíku skončil',
  `role` VARCHAR(255) NULL DEFAULT NULL COMMENT 'hlavní org, úlohář, etc.',
  `order` TINYINT(4) NOT NULL COMMENT 'pořadí pro řazení ve výpisech',
  `contribution` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`org_id`),
  UNIQUE INDEX `contest_id` (`contest_id` ASC, `person_id` ASC),
  INDEX `person_id` (`person_id` ASC),
  CONSTRAINT `org_ibfk_1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `org_ibfk_2`
    FOREIGN KEY (`contest_id`)
    REFERENCES `fksdb`.`contest` (`contest_id`)
    ON DELETE CASCADE
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fksdb`.`role`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`role` (
  `role_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(16) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fksdb`.`grant`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`grant` (
  `grant_id` INT(11) NOT NULL AUTO_INCREMENT,
  `login_id` INT(11) NOT NULL,
  `role_id` INT(11) NOT NULL,
  `contest_id` INT NOT NULL,
  INDEX `right_id` (`role_id` ASC),
  PRIMARY KEY (`grant_id`),
  UNIQUE INDEX `grant_UNIQUE` (`role_id` ASC, `login_id` ASC, `contest_id` ASC),
  INDEX `fk_grant_contest1_idx` (`contest_id` ASC),
  INDEX `permission_ibfk_1_idx` (`login_id` ASC),
  CONSTRAINT `permission_ibfk_1`
    FOREIGN KEY (`login_id`)
    REFERENCES `fksdb`.`login` (`login_id`)
    ON DELETE CASCADE
    ON UPDATE RESTRICT,
  CONSTRAINT `permission_ibfk_2`
    FOREIGN KEY (`role_id`)
    REFERENCES `fksdb`.`role` (`role_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_grant_contest1`
    FOREIGN KEY (`contest_id`)
    REFERENCES `fksdb`.`contest` (`contest_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fksdb`.`person_info`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`person_info` (
  `person_id` INT(11) NOT NULL,
  `born` DATE NULL DEFAULT NULL COMMENT 'datum narození',
  `id_number` VARCHAR(32) NULL DEFAULT NULL COMMENT 'číslo OP či ekvivalent',
  `born_id` VARCHAR(32) NULL DEFAULT NULL COMMENT 'rodné číslo (pouze u CZ, SK)',
  `phone` VARCHAR(32) NULL DEFAULT NULL COMMENT 'tel. číslo',
  `im` VARCHAR(32) NULL DEFAULT NULL COMMENT 'ICQ, XMPP, etc.',
  `note` TEXT NULL DEFAULT NULL COMMENT 'ostatní/poznámka',
  `uk_login` VARCHAR(8) NULL DEFAULT NULL COMMENT 'CAS login, pro orgy',
  `account` VARCHAR(32) NULL DEFAULT NULL COMMENT 'bankovní účet jako text',
  `agreed` DATETIME NULL DEFAULT NULL COMMENT 'čas posledního souhlasu ze zprac. os. ú. nebo null',
  `birthplace` VARCHAR(255) NULL DEFAULT NULL COMMENT 'název města narození osoby',
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `origin` TEXT NULL DEFAULT NULL COMMENT 'Odkud se o nás dozvěděl.',
  `tex_signature` VARCHAR(32) NULL DEFAULT NULL COMMENT 'zkratka používaná v TeXových vzorácích',
  `domain_alias` VARCHAR(32) NULL DEFAULT NULL COMMENT 'alias v doméně fykos.cz',
  `career` TEXT NULL DEFAULT NULL COMMENT 'co studuje/kde pracuje',
  `homepage` VARCHAR(255) NULL DEFAULT NULL COMMENT 'URL osobní homepage',
  `fb_id` VARCHAR(255) NULL DEFAULT NULL,
  `linkedin_id` VARCHAR(255) NULL DEFAULT NULL,
  `phone_parent_d` VARCHAR(32) NULL DEFAULT NULL COMMENT 'tel. číslo rodič otec',
  `phone_parent_m` VARCHAR(32) NULL DEFAULT NULL COMMENT 'tel. číslo rodič mama',
  PRIMARY KEY (`person_id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
  UNIQUE INDEX `uk_login_UNIQUE` (`uk_login` ASC),
  UNIQUE INDEX `born_id_UNIQUE` (`born_id` ASC),
  UNIQUE INDEX `fb_id_UNIQUE` (`fb_id` ASC),
  UNIQUE INDEX `domain_alias_UNIQUE` (`domain_alias` ASC),
  UNIQUE INDEX `tex_signature_UNIQUE` (`tex_signature` ASC),
  UNIQUE INDEX `linkedin_id_UNIQUE` (`linkedin_id` ASC),
  CONSTRAINT `person_info_ibfk_1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE CASCADE
    ON UPDATE RESTRICT)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Podrobné informace o osobě, zde jsou všechny osobní úda';


-- -----------------------------------------------------
-- Table `fksdb`.`post_contact`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`post_contact` (
  `post_contact_id` INT(11) NOT NULL AUTO_INCREMENT,
  `person_id` INT(11) NOT NULL,
  `address_id` INT(11) NOT NULL,
  `type` ENUM('P','D') NOT NULL COMMENT 'doručovací (Delivery), trvalá (Permanent)',
  PRIMARY KEY (`post_contact_id`),
  INDEX `person_id` (`person_id` ASC),
  INDEX `address_id` (`address_id` ASC),
  UNIQUE INDEX `person_id_type` (`person_id` ASC, `type` ASC),
  CONSTRAINT `post_contact_ibfk_1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE CASCADE,
  CONSTRAINT `post_contact_ibfk_2`
    FOREIGN KEY (`address_id`)
    REFERENCES `fksdb`.`address` (`address_id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Přiřazení adres lidem vztahem M:N';


-- -----------------------------------------------------
-- Table `fksdb`.`psc_region`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`psc_region` (
  `psc` CHAR(5) NOT NULL,
  `region_id` INT(11) NOT NULL,
  PRIMARY KEY (`psc`),
  INDEX `region_id` (`region_id` ASC),
  CONSTRAINT `psc_region_ibfk_1`
    FOREIGN KEY (`region_id`)
    REFERENCES `fksdb`.`region` (`region_id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'mapování českých a slovenských PSČ na evidovaný regio';


-- -----------------------------------------------------
-- Table `fksdb`.`mail_batch`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`mail_batch` (
  `mail_batch_id` INT NOT NULL AUTO_INCREMENT,
  `flag_id` INT(11) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL COMMENT 'druh rozesílané pošty (brožurka, pozvánka, etc.)',
  `ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mail_batch_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`mail_log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`mail_log` (
  `person_id` INT(11) NOT NULL,
  `ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `address_id` INT NULL DEFAULT NULL,
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `mail_batch_id` INT(11) NOT NULL,
  INDEX `person_id` (`person_id` ASC),
  PRIMARY KEY (`person_id`),
  INDEX `fk_mail_log_mail_batch1_idx` (`mail_batch_id` ASC),
  INDEX `fk_mail_log_address1_idx` (`address_id` ASC),
  CONSTRAINT `si_log_ibfk_1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_mail_log_mail_batch1`
    FOREIGN KEY (`mail_batch_id`)
    REFERENCES `fksdb`.`mail_batch` (`mail_batch_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_mail_log_address1`
    FOREIGN KEY (`address_id`)
    REFERENCES `fksdb`.`address` (`address_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'v tabulce se loguje historická hodnota adresy nebo emailu, ';


-- -----------------------------------------------------
-- Table `fksdb`.`task`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`task` (
  `task_id` INT(11) NOT NULL AUTO_INCREMENT,
  `label` VARCHAR(16) NOT NULL COMMENT 'Oznaceni ulohy, treba \"23-4-5\"',
  `name_cs` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Jmeno ulohy',
  `name_en` VARCHAR(255) NULL DEFAULT NULL,
  `contest_id` INT(11) NOT NULL COMMENT 'seminář',
  `year` TINYINT(4) NOT NULL COMMENT 'Rocnik seminare',
  `series` TINYINT(4) NOT NULL COMMENT 'Serie',
  `tasknr` TINYINT(4) NULL DEFAULT NULL COMMENT 'Uloha',
  `points` TINYINT(4) NULL DEFAULT NULL COMMENT 'Maximalni pocet bodu',
  `submit_start` DATETIME NULL DEFAULT NULL COMMENT 'Od kdy se smi submitovat',
  `submit_deadline` DATETIME NULL DEFAULT NULL COMMENT 'Do kdy',
  PRIMARY KEY (`task_id`),
  INDEX `contest_id` (`contest_id` ASC),
  UNIQUE INDEX `contest_id_year_series_tasknr` (`contest_id` ASC, `year` ASC, `series` ASC, `tasknr` ASC),
  CONSTRAINT `task_ibfk_1`
    FOREIGN KEY (`contest_id`)
    REFERENCES `fksdb`.`contest` (`contest_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fksdb`.`submit`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`submit` (
  `submit_id` INT(11) NOT NULL AUTO_INCREMENT,
  `ct_id` INT(11) NOT NULL COMMENT 'Contestant',
  `task_id` INT(11) NOT NULL COMMENT 'Task',
  `submitted_on` DATETIME NULL DEFAULT NULL,
  `source` ENUM('post','upload') NOT NULL COMMENT 'odkud přišlo řešení',
  `note` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Pocet stranek a jine poznamky',
  `raw_points` DECIMAL(4,2) NULL DEFAULT NULL COMMENT 'Pred prepoctem',
  `calc_points` DECIMAL(4,2) NULL DEFAULT NULL COMMENT 'Cache spoctenych bodu.',
  PRIMARY KEY (`submit_id`),
  UNIQUE INDEX `cons_uniq` (`ct_id` ASC, `task_id` ASC),
  INDEX `task_id` (`task_id` ASC),
  CONSTRAINT `submit_ibfk_1`
    FOREIGN KEY (`ct_id`)
    REFERENCES `fksdb`.`contestant_base` (`ct_id`),
  CONSTRAINT `submit_ibfk_2`
    FOREIGN KEY (`task_id`)
    REFERENCES `fksdb`.`task` (`task_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fksdb`.`event_has_org`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`event_has_org` (
  `event_id` INT(11) NOT NULL,
  `person_id` INT(11) NOT NULL,
  PRIMARY KEY (`event_id`, `person_id`),
  INDEX `fk_event_has_org_person1_idx` (`person_id` ASC),
  CONSTRAINT `fk_action_has_org_event1`
    FOREIGN KEY (`event_id`)
    REFERENCES `fksdb`.`event` (`event_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_event_has_org_person1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`task_contribution`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`task_contribution` (
  `contribution_id` INT NOT NULL AUTO_INCREMENT,
  `task_id` INT(11) NOT NULL,
  `person_id` INT(11) NOT NULL,
  `type` ENUM('author', 'solution', 'grade') NOT NULL,
  PRIMARY KEY (`contribution_id`),
  UNIQUE INDEX `person_id_task_id_type` (`person_id` ASC, `task_id` ASC, `type` ASC),
  INDEX `fk_org_task_contribution_task1_idx` (`task_id` ASC),
  INDEX `fk_task_contribution_person1_idx` (`person_id` ASC),
  CONSTRAINT `fk_org_task_contribution_task1`
    FOREIGN KEY (`task_id`)
    REFERENCES `fksdb`.`task` (`task_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_task_contribution_person1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`e_fyziklani_team`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`e_fyziklani_team` (
  `e_fyziklani_team_id` INT NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `name` VARCHAR(30) NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `teacher_id` INT(11) NULL DEFAULT NULL COMMENT 'kontaktní osoba',
  `teacher_accomodation` TINYINT(1) NOT NULL DEFAULT 0,
  `teacher_present` TINYINT(1) NOT NULL DEFAULT 0,
  `category` CHAR(1) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `phone` VARCHAR(30) NULL DEFAULT NULL,
  `note` TEXT NULL DEFAULT NULL,
  `password` CHAR(40) NULL DEFAULT NULL,
  `points` INT(11) NULL DEFAULT NULL,
  `rank_category` INT(11) NULL DEFAULT NULL,
  `rank_total` INT(11) NULL DEFAULT NULL,
  `room` CHAR(3) NULL DEFAULT NULL,
  PRIMARY KEY (`e_fyziklani_team_id`),
  INDEX `fk_e_fyziklani_team_event1_idx` (`event_id` ASC),
  INDEX `fk_e_fyziklani_team_person1_idx` (`teacher_id` ASC),
  INDEX `fk_e_fyziklani_team_e_status1_idx` (`status` ASC),
  CONSTRAINT `fk_e_fyziklani_team_event1`
    FOREIGN KEY (`event_id`)
    REFERENCES `fksdb`.`event` (`event_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_e_fyziklani_team_person1`
    FOREIGN KEY (`teacher_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_e_fyziklani_team_e_status1`
    FOREIGN KEY (`status`)
    REFERENCES `fksdb`.`event_status` (`status`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`e_fyziklani_participant`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`e_fyziklani_participant` (
  `event_participant_id` INT NOT NULL,
  `e_fyziklani_team_id` INT NOT NULL,
  PRIMARY KEY (`event_participant_id`),
  INDEX `fk_e_fyziklani_participant_e_fyziklani_team1_idx` (`e_fyziklani_team_id` ASC),
  UNIQUE INDEX `uq_team_participan` (`event_participant_id` ASC, `e_fyziklani_team_id` ASC),
  CONSTRAINT `fk_e_participant_fyziklani_event_participant1`
    FOREIGN KEY (`event_participant_id`)
    REFERENCES `fksdb`.`event_participant` (`event_participant_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_e_fyziklani_participant_e_fyziklani_team1`
    FOREIGN KEY (`e_fyziklani_team_id`)
    REFERENCES `fksdb`.`e_fyziklani_team` (`e_fyziklani_team_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`e_fyziklani_participant_with_team`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`e_fyziklani_participant_with_team` (
  `team_id` INT NULL DEFAULT NULL,
  `participant_id` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`participant_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`stored_query`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`stored_query` (
  `query_id` INT NOT NULL AUTO_INCREMENT,
  `qid` VARCHAR(16) NULL DEFAULT NULL COMMENT 'identifikátor pro URL, práva apo',
  `name` VARCHAR(32) NOT NULL COMMENT 'název dotazu, identifikace pro človkěka',
  `description` TEXT NULL DEFAULT NULL,
  `sql` TEXT NOT NULL,
  `php_post_proc` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`query_id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC),
  UNIQUE INDEX `qid_UNIQUE` (`qid` ASC))
ENGINE = InnoDB
COMMENT = 'Uložené SQL dotazy s možností parametrizace z aplikace.';


-- -----------------------------------------------------
-- Table `fksdb`.`stored_query_parameter`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`stored_query_parameter` (
  `parameter_id` INT NOT NULL AUTO_INCREMENT,
  `query_id` INT NOT NULL,
  `name` VARCHAR(16) NOT NULL COMMENT 'název parametru pro použití v SQL',
  `description` TEXT NULL DEFAULT NULL,
  `type` ENUM('integer', 'string', 'bool') NOT NULL COMMENT 'datový typ paramtru',
  `default_integer` INT(11) NULL DEFAULT NULL COMMENT 'implicitní hodnota',
  `default_string` VARCHAR(255) NULL DEFAULT NULL COMMENT 'implicitní hodnota',
  PRIMARY KEY (`parameter_id`),
  INDEX `fk_stored_query_parameter_stored_query1_idx` (`query_id` ASC),
  UNIQUE INDEX `uq_query_id_name` (`query_id` ASC, `name` ASC),
  CONSTRAINT `fk_stored_query_parameter_stored_query1`
    FOREIGN KEY (`query_id`)
    REFERENCES `fksdb`.`stored_query` (`query_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`global_session`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`global_session` (
  `session_id` CHAR(32) NOT NULL,
  `login_id` INT(11) NOT NULL COMMENT 'the only data',
  `since` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `until` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `remote_ip` VARCHAR(45) NULL DEFAULT NULL COMMENT 'IP adresa klienta',
  PRIMARY KEY (`session_id`),
  INDEX `fk_auth_token_login1_idx` (`login_id` ASC),
  CONSTRAINT `fk_auth_token_login10`
    FOREIGN KEY (`login_id`)
    REFERENCES `fksdb`.`login` (`login_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Stores global sessions for SSO (single sign-on/off)';


-- -----------------------------------------------------
-- Table `fksdb`.`flag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`flag` (
  `flag_id` INT NOT NULL,
  `fid` VARCHAR(16) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `type` ENUM('global','contest','ac_year','contest_year') NOT NULL COMMENT 'rozsah platnosti flagu',
  PRIMARY KEY (`flag_id`),
  UNIQUE INDEX `name_UNIQUE` (`fid` ASC))
ENGINE = InnoDB
COMMENT = 'general purpose flag for the person (for presentation layer)';


-- -----------------------------------------------------
-- Table `fksdb`.`contest_year`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`contest_year` (
  `contest_id` INT NOT NULL,
  `year` TINYINT(4) NOT NULL,
  `ac_year` SMALLINT(4) NOT NULL COMMENT 'první rok akademického rok',
  PRIMARY KEY (`contest_id`, `year`),
  INDEX `ac_year_idx` (`ac_year` ASC),
  CONSTRAINT `fk_contest_year_contest1`
    FOREIGN KEY (`contest_id`)
    REFERENCES `fksdb`.`contest` (`contest_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'mapování ročníků semináře na akademické roky';


-- -----------------------------------------------------
-- Table `fksdb`.`person_has_flag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`person_has_flag` (
  `person_flag_id` INT NOT NULL,
  `person_id` INT NOT NULL,
  `flag_id` INT NOT NULL,
  `contest_id` INT NULL DEFAULT NULL,
  `ac_year` SMALLINT(4) NULL DEFAULT NULL,
  `value` TINYINT NOT NULL DEFAULT 1,
  `modified` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`person_flag_id`),
  UNIQUE INDEX `person_flag_year_ct_UQ` (`person_id` ASC, `flag_id` ASC, `contest_id` ASC, `ac_year` ASC),
  INDEX `fk_person_has_flag_person_flag1_idx` (`flag_id` ASC),
  INDEX `fk_person_has_flag_contest1_idx` (`contest_id` ASC),
  INDEX `fk_person_has_flag_contest_year1_idx` (`ac_year` ASC),
  CONSTRAINT `fk_person_has_flag_person1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_person_has_flag_person_flag1`
    FOREIGN KEY (`flag_id`)
    REFERENCES `fksdb`.`flag` (`flag_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_person_has_flag_contest1`
    FOREIGN KEY (`contest_id`)
    REFERENCES `fksdb`.`contest` (`contest_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_person_has_flag_contest_year1`
    FOREIGN KEY (`ac_year`)
    REFERENCES `fksdb`.`contest_year` (`ac_year`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'person s flags are per year';


-- -----------------------------------------------------
-- Table `fksdb`.`study_year`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`study_year` (
  `study_year` TINYINT(1) NOT NULL,
  PRIMARY KEY (`study_year`))
ENGINE = InnoDB
COMMENT = 'table just enforeces referential integrity';


-- -----------------------------------------------------
-- Table `fksdb`.`person_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`person_history` (
  `person_history_id` INT NOT NULL AUTO_INCREMENT,
  `person_id` INT NOT NULL,
  `ac_year` SMALLINT(4) NOT NULL COMMENT 'první rok akademického rok',
  `school_id` INT NULL DEFAULT NULL,
  `class` VARCHAR(16) NULL DEFAULT NULL COMMENT 'označení třídy',
  `study_year` TINYINT(1) NULL DEFAULT NULL COMMENT 'ročník, který studuje',
  PRIMARY KEY (`person_history_id`),
  UNIQUE INDEX `UQ_AC_YEAR` (`person_id` ASC, `ac_year` ASC),
  INDEX `fk_person_history_school1_idx` (`school_id` ASC),
  INDEX `fk_person_history_contest_year1_idx` (`ac_year` ASC),
  INDEX `fk_person_history_study_year1_idx` (`study_year` ASC),
  CONSTRAINT `fk_person_history_school1`
    FOREIGN KEY (`school_id`)
    REFERENCES `fksdb`.`school` (`school_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_person_history_person1`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_person_history_contest_year1`
    FOREIGN KEY (`ac_year`)
    REFERENCES `fksdb`.`contest_year` (`ac_year`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_person_history_study_year1`
    FOREIGN KEY (`study_year`)
    REFERENCES `fksdb`.`study_year` (`study_year`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'atributy osoby řezané dle akademického roku';


-- -----------------------------------------------------
-- Table `fksdb`.`e_dsef_group`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`e_dsef_group` (
  `e_dsef_group_id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `name` VARCHAR(32) NOT NULL,
  `capacity` TINYINT(2) NOT NULL,
  INDEX `fk_e_dsef_group_event1_idx` (`event_id` ASC),
  PRIMARY KEY (`e_dsef_group_id`),
  CONSTRAINT `fk_e_dsef_group_event1`
    FOREIGN KEY (`event_id`)
    REFERENCES `fksdb`.`event` (`event_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`e_dsef_participant`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`e_dsef_participant` (
  `event_participant_id` INT NOT NULL,
  `e_dsef_group_id` INT NOT NULL,
  `arrival_time` TIME NULL DEFAULT NULL,
  `lunch_count` TINYINT(2) NULL DEFAULT 0,
  `message` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`event_participant_id`),
  INDEX `fk_e_dsef_participant_e_dsef_group1_idx` (`e_dsef_group_id` ASC),
  CONSTRAINT `fk_e_dsef_participant_event_participant1`
    FOREIGN KEY (`event_participant_id`)
    REFERENCES `fksdb`.`event_participant` (`event_participant_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_e_dsef_participant_e_dsef_group1`
    FOREIGN KEY (`e_dsef_group_id`)
    REFERENCES `fksdb`.`e_dsef_group` (`e_dsef_group_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`e_vikend_participant`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`e_vikend_participant` (
  `event_participant_id` INT NOT NULL,
  `answer` VARCHAR(64) NULL DEFAULT NULL,
  `gives_lecture` VARCHAR(64) NULL DEFAULT NULL,
  `gives_lecture_desc` TEXT NULL DEFAULT NULL,
  `wants_lecture` VARCHAR(64) NULL DEFAULT NULL,
  PRIMARY KEY (`event_participant_id`),
  CONSTRAINT `fk_e_vikend_participant_event_participant1`
    FOREIGN KEY (`event_participant_id`)
    REFERENCES `fksdb`.`event_participant` (`event_participant_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`e_sous_participant`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`e_sous_participant` (
  `event_participant_id` INT NOT NULL,
  PRIMARY KEY (`event_participant_id`),
  CONSTRAINT `fk_e_sous_participant_event_participant1`
    FOREIGN KEY (`event_participant_id`)
    REFERENCES `fksdb`.`event_participant` (`event_participant_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`e_tsaf_participant`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`e_tsaf_participant` (
  `event_participant_id` INT NOT NULL,
  `jumper_size` VARCHAR(20) NULL DEFAULT NULL,
  PRIMARY KEY (`event_participant_id`),
  CONSTRAINT `fk_e_tsaf_participant_event_participant1`
    FOREIGN KEY (`event_participant_id`)
    REFERENCES `fksdb`.`event_participant` (`event_participant_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`task_study_year`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`task_study_year` (
  `task_id` INT(11) NOT NULL,
  `study_year` TINYINT(1) NOT NULL,
  PRIMARY KEY (`study_year`, `task_id`),
  INDEX `fk_task_study_year_study_year1_idx` (`study_year` ASC),
  INDEX `fk_task_study_year_task1` (`task_id` ASC),
  CONSTRAINT `fk_task_study_year_task1`
    FOREIGN KEY (`task_id`)
    REFERENCES `fksdb`.`task` (`task_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_task_study_year_study_year1`
    FOREIGN KEY (`study_year`)
    REFERENCES `fksdb`.`study_year` (`study_year`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
COMMENT = 'specification of allowed study years for a task';


-- -----------------------------------------------------
-- Table `fksdb`.`event_org`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`event_org` (
  `e_org_id` INT(11) NOT NULL,
  `pozn` TEXT(32) NULL DEFAULT NULL,
  `event_id` INT(11) NOT NULL,
  `person_id` INT(11) NOT NULL,
  PRIMARY KEY (`e_org_id`),
  INDEX `event_id` (`event_id` ASC),
  INDEX `person_id` (`person_id` ASC),
  CONSTRAINT `event_id`
    FOREIGN KEY (`event_id`)
    REFERENCES `fksdb`.`event` (`event_id`),
  CONSTRAINT `person_id`
    FOREIGN KEY (`person_id`)
    REFERENCES `fksdb`.`person` (`person_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`fyziklani_task`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`fyziklani_task` (
  `fyziklani_task_id` INT NOT NULL AUTO_INCREMENT,
  `event_id` INT NOT NULL,
  `label` CHAR(2) NULL DEFAULT NULL,
  `name` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`fyziklani_task_id`),
  INDEX `event_id_idx` (`event_id` ASC),
  CONSTRAINT `event_id`
    FOREIGN KEY (`event_id`)
    REFERENCES `fksdb`.`event` (`event_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `fksdb`.`fyziklani_submit`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fksdb`.`fyziklani_submit` (
  `fyziklani_submit_id` INT NOT NULL AUTO_INCREMENT,
  `fyziklani_task_id` INT NOT NULL,
  `points` TINYINT NOT NULL,
  `submitted_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `e_fyziklani_team_id` INT NOT NULL,
  PRIMARY KEY (`fyziklani_submit_id`),
  INDEX `fyziklani_task_id_idx` (`fyziklani_task_id` ASC),
  INDEX `e_fyziklani_team_id_idx` (`e_fyziklani_team_id` ASC),
  CONSTRAINT `fyziklani_task_id`
    FOREIGN KEY (`fyziklani_task_id`)
    REFERENCES `fksdb`.`fyziklani_task` (`fyziklani_task_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `e_fyziklani_team_id`
    FOREIGN KEY (`e_fyziklani_team_id`)
    REFERENCES `fksdb`.`e_fyziklani_team` (`e_fyziklani_team_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
