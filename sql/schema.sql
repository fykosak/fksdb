SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `contest`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `contest` ;

CREATE  TABLE IF NOT EXISTS `contest` (
  `contest_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`contest_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '(sub)semináře';


-- -----------------------------------------------------
-- Table `event_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `event_type` ;

CREATE  TABLE IF NOT EXISTS `event_type` (
  `event_type_id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL ,
  PRIMARY KEY (`event_type_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `event`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `event` ;

CREATE  TABLE IF NOT EXISTS `event` (
  `event_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `contest_id` INT(11) NOT NULL COMMENT 'seminář' ,
  `year` TINYINT(4) NOT NULL COMMENT 'ročník' ,
  `begin` DATE NOT NULL COMMENT 'první den akce' ,
  `end` DATE NOT NULL COMMENT 'poslední den akce, u jednodenní akce shodný s begin' ,
  `applications` ENUM('internal','web') NOT NULL COMMENT 'způsob přihlašování na akci' ,
  `registration_begin` DATE NULL DEFAULT NULL COMMENT 'případný počátek webové registrace' ,
  `registration_end` DATE NULL DEFAULT NULL COMMENT 'případný konec webové registrace' ,
  `name` VARCHAR(255) NOT NULL COMMENT 'název akce' ,
  `fb_album_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'id galerie na Facebooku' ,
  `report` TEXT NULL DEFAULT NULL COMMENT '(HTML) zápis z proběhlé akce' ,
  `event_type_id` INT NOT NULL ,
  PRIMARY KEY (`event_id`) ,
  INDEX `contest_id` (`contest_id` ASC) ,
  INDEX `fk_event_event_type1` (`event_type_id` ASC) ,
  CONSTRAINT `action_ibfk_1`
    FOREIGN KEY (`contest_id` )
    REFERENCES `contest` (`contest_id` )
    ON DELETE CASCADE,
  CONSTRAINT `fk_event_event_type1`
    FOREIGN KEY (`event_type_id` )
    REFERENCES `event_type` (`event_type_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `person`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `person` ;

CREATE  TABLE IF NOT EXISTS `person` (
  `person_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `family_name` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_czech_ci' NOT NULL COMMENT 'Příjmení (nebo více příjmení oddělených jednou mezerou)' ,
  `other_name` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_czech_ci' NOT NULL COMMENT 'Křestní jména, von, de atd., oddělená jednou mezerou' ,
  `display_name` VARCHAR(511) CHARACTER SET 'utf8' COLLATE 'utf8_czech_ci' NULL DEFAULT NULL COMMENT 'zobrazované jméno, liší-li se od <other_name> <family_name>' ,
  `gender` ENUM('M','F') CHARACTER SET 'utf8' NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`person_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_czech_ci
COMMENT = 'řazení: <family_name><other_name>, zobrazení <other_name> <f';


-- -----------------------------------------------------
-- Table `event_participant`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `event_participant` ;

CREATE  TABLE IF NOT EXISTS `event_participant` (
  `participant_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `action_id` INT(11) NOT NULL ,
  `person_id` INT(11) NOT NULL ,
  `note` TEXT NULL DEFAULT NULL COMMENT 'poznámka' ,
  `status` SMALLINT(6) NOT NULL COMMENT 'Status: 0=pozvany, 1=potvrzeno, 2=odřekl, 3=vybrán, 4=účastnil se, viz SQL comment' ,
  `created` DATETIME NOT NULL COMMENT 'čas vytvoření přihlášky' ,
  PRIMARY KEY (`participant_id`) ,
  INDEX `action_id` (`action_id` ASC) ,
  INDEX `person_id` (`person_id` ASC) ,
  CONSTRAINT `action_application_ibfk_1`
    FOREIGN KEY (`action_id` )
    REFERENCES `event` (`event_id` ),
  CONSTRAINT `action_application_ibfk_2`
    FOREIGN KEY (`person_id` )
    REFERENCES `person` (`person_id` ))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `country`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `country` ;

CREATE  TABLE IF NOT EXISTS `country` (
  `country_iso` CHAR(2) NOT NULL COMMENT 'dvojznakový kód země dle ISO 3166-1' ,
  `name_cs` VARCHAR(255) NOT NULL ,
  `name_en` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`country_iso`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Ciselnik států';


-- -----------------------------------------------------
-- Table `region`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `region` ;

CREATE  TABLE IF NOT EXISTS `region` (
  `region_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `country_iso` CHAR(2) NOT NULL ,
  `nuts` VARCHAR(5) NULL DEFAULT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`region_id`) ,
  UNIQUE INDEX `nuts` (`nuts` ASC) ,
  INDEX `country_iso` (`country_iso` ASC) ,
  CONSTRAINT `region_ibfk_1`
    FOREIGN KEY (`country_iso` )
    REFERENCES `country` (`country_iso` ))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Ciselnik regionu pro vyber skoly v registraci';


-- -----------------------------------------------------
-- Table `address`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `address` ;

CREATE  TABLE IF NOT EXISTS `address` (
  `address_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `first_row` VARCHAR(255) NULL DEFAULT NULL COMMENT 'doplňkový řádek adresy (např. bytem u X Y)' ,
  `second_row` VARCHAR(255) NULL DEFAULT NULL COMMENT 'ještě doplňkovější řádek adresy (nikdo neví)' ,
  `target` VARCHAR(255) NOT NULL COMMENT 'ulice č.p./or., vesnice č.p./or., poštovní přihrádka atd.' ,
  `city` VARCHAR(255) NOT NULL COMMENT 'město doručovací pošty' ,
  `postal_code` CHAR(5) NULL DEFAULT NULL COMMENT 'PSČ (pro ČR a SR)' ,
  `region_id` INT(11) NOT NULL COMMENT 'detekce státu && formátovacích zvyklostí' ,
  PRIMARY KEY (`address_id`) ,
  INDEX `region_id` (`region_id` ASC) ,
  CONSTRAINT `address_ibfk_1`
    FOREIGN KEY (`region_id` )
    REFERENCES `region` (`region_id` ))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'adresa jako poštovní nikoli územní identifikátor, immutable.';


-- -----------------------------------------------------
-- Table `login`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `login` ;

CREATE  TABLE IF NOT EXISTS `login` (
  `login_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `person_id` INT(11) NULL ,
  `login` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Login name' ,
  `email` VARCHAR(255) NULL ,
  `hash` CHAR(40) NULL DEFAULT NULL COMMENT 'sha1(person_id . md5(password)) as hexadecimal' ,
  `fb_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'ID pro přihlášení přes FB' ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `last_login` DATETIME NULL DEFAULT NULL ,
  `active` TINYINT(1) NOT NULL ,
  PRIMARY KEY (`login_id`) ,
  UNIQUE INDEX `email` (`email` ASC) ,
  UNIQUE INDEX `login` (`login` ASC) ,
  UNIQUE INDEX `person_id_UNIQUE` (`person_id` ASC) ,
  CONSTRAINT `login_ibfk_1`
    FOREIGN KEY (`person_id` )
    REFERENCES `person` (`person_id` )
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `auth_token`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `auth_token` ;

CREATE  TABLE IF NOT EXISTS `auth_token` (
  `token_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `login_id` INT(11) NOT NULL ,
  `token` VARCHAR(255) NOT NULL ,
  `type` VARCHAR(31) NULL DEFAULT NULL ,
  `since` DATETIME NOT NULL ,
  `until` DATETIME NULL DEFAULT NULL ,
  INDEX `person_id` (`login_id` ASC) ,
  UNIQUE INDEX `token_UNIQUE` (`token` ASC) ,
  PRIMARY KEY (`token_id`) ,
  CONSTRAINT `auth_token_ibfk_1`
    FOREIGN KEY (`login_id` )
    REFERENCES `login` (`person_id` )
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `school`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `school` ;

CREATE  TABLE IF NOT EXISTS `school` (
  `school_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name_full` VARCHAR(255) NULL DEFAULT NULL COMMENT 'plný název školy' ,
  `name` VARCHAR(255) NOT NULL COMMENT 'zkrácený název školy (na obálku)' ,
  `name_abbrev` VARCHAR(32) NOT NULL COMMENT 'Zkratka pouzivana napr. ve vysledkove listine' ,
  `address_id` INT(11) NOT NULL ,
  `email` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Kontaktní e-mail' ,
  `ic` CHAR(8) NULL DEFAULT NULL COMMENT 'IČ (osm číslic)' ,
  `izo` VARCHAR(32) NULL DEFAULT NULL COMMENT 'IZO kód (norma?)' ,
  `active` TINYINT(1) NULL DEFAULT NULL COMMENT 'Platný záznam školy' ,
  `note` VARCHAR(255) NULL DEFAULT NULL ,
  PRIMARY KEY (`school_id`) ,
  UNIQUE INDEX `ic` (`ic` ASC) ,
  UNIQUE INDEX `izo` (`izo` ASC) ,
  INDEX `address_id` (`address_id` ASC) ,
  CONSTRAINT `school_ibfk_1`
    FOREIGN KEY (`address_id` )
    REFERENCES `address` (`address_id` ))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `contestant`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `contestant` ;

CREATE  TABLE IF NOT EXISTS `contestant` (
  `ct_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `contest_id` INT(11) NOT NULL COMMENT 'seminář' ,
  `year` TINYINT(4) NOT NULL COMMENT 'Rocnik semináře' ,
  `person_id` INT(11) NOT NULL ,
  `school_id` INT(11) NULL DEFAULT NULL ,
  `class` VARCHAR(16) NULL DEFAULT NULL COMMENT 'třída, do níž chodí, př. IV.B' ,
  `study_year` TINYINT(4) NULL DEFAULT NULL COMMENT 'ročník, který studuje 6--9 nebo 1--4' ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`ct_id`) ,
  UNIQUE INDEX `contest_id` (`contest_id` ASC, `year` ASC, `person_id` ASC) ,
  INDEX `person_id` (`person_id` ASC) ,
  INDEX `school_id` (`school_id` ASC) ,
  CONSTRAINT `contestant_ibfk_1`
    FOREIGN KEY (`person_id` )
    REFERENCES `person` (`person_id` ),
  CONSTRAINT `contestant_ibfk_2`
    FOREIGN KEY (`school_id` )
    REFERENCES `school` (`school_id` ),
  CONSTRAINT `contestant_ibfk_3`
    FOREIGN KEY (`contest_id` )
    REFERENCES `contest` (`contest_id` ))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Instance ucastnika (v konkretnim rocniku a semináři)';


-- -----------------------------------------------------
-- Table `dakos_person`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dakos_person` ;

CREATE  TABLE IF NOT EXISTS `dakos_person` (
  `dakos_id` INT(11) NOT NULL COMMENT 'Id účastníka z dakosího exportu' ,
  `person_id` INT(11) NOT NULL ,
  PRIMARY KEY (`dakos_id`) ,
  INDEX `person_id` (`person_id` ASC) ,
  CONSTRAINT `dakos_person_ibfk_1`
    FOREIGN KEY (`person_id` )
    REFERENCES `person` (`person_id` ))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Identifikace osoby z DaKoSu';


-- -----------------------------------------------------
-- Table `dakos_school`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dakos_school` ;

CREATE  TABLE IF NOT EXISTS `dakos_school` (
  `dakos_SKOLA_Id` INT(11) NOT NULL ,
  `school_id` INT(11) NOT NULL ,
  PRIMARY KEY (`dakos_SKOLA_Id`) ,
  INDEX `school_id` (`school_id` ASC) ,
  CONSTRAINT `dakos_school_ibfk_1`
    FOREIGN KEY (`school_id` )
    REFERENCES `school` (`school_id` ))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `olddb_person`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `olddb_person` ;

CREATE  TABLE IF NOT EXISTS `olddb_person` (
  `olddb_uid` INT(11) NOT NULL COMMENT 'users.id ze staré DB' ,
  `person_id` INT(11) NOT NULL ,
  `olddb_redundant` TINYINT(1) NOT NULL COMMENT 'Tato data se nezkopírovala' ,
  PRIMARY KEY (`olddb_uid`) ,
  INDEX `person_id` (`person_id` ASC) ,
  CONSTRAINT `olddb_person_ibfk_1`
    FOREIGN KEY (`person_id` )
    REFERENCES `person` (`person_id` ))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `org`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `org` ;

CREATE  TABLE IF NOT EXISTS `org` (
  `org_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `person_id` INT(11) NOT NULL ,
  `contest_id` INT(11) NOT NULL ,
  `since` TINYINT(4) NOT NULL COMMENT 'od kterého ročníku orguje' ,
  `until` TINYINT(4) NULL DEFAULT NULL COMMENT 'v kterém rončíku skončil' ,
  `role` VARCHAR(32) NULL DEFAULT NULL COMMENT 'hlavní org, úlohář, etc.' ,
  `note` TEXT NULL DEFAULT NULL ,
  `order` TINYINT(4) NOT NULL COMMENT 'pořadí pro řazení ve výpisech' ,
  `tex_signature` VARCHAR(32) NULL DEFAULT NULL COMMENT 'zkratka používaná v TeXových vzorácích' ,
  PRIMARY KEY (`org_id`) ,
  UNIQUE INDEX `contest_id` (`contest_id` ASC, `person_id` ASC) ,
  UNIQUE INDEX `contest_id_2` (`contest_id` ASC, `tex_signature` ASC) ,
  INDEX `person_id` (`person_id` ASC) ,
  UNIQUE INDEX `tex_signature_UNIQUE` (`tex_signature` ASC) ,
  CONSTRAINT `org_ibfk_1`
    FOREIGN KEY (`person_id` )
    REFERENCES `person` (`person_id` ),
  CONSTRAINT `org_ibfk_2`
    FOREIGN KEY (`contest_id` )
    REFERENCES `contest` (`contest_id` )
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `role`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `role` ;

CREATE  TABLE IF NOT EXISTS `role` (
  `role_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(16) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`role_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `grant`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `grant` ;

CREATE  TABLE IF NOT EXISTS `grant` (
  `grant_id` INT(11) NULL AUTO_INCREMENT ,
  `login_id` INT(11) NOT NULL ,
  `role_id` INT(11) NOT NULL ,
  `contest_id` INT NOT NULL ,
  INDEX `right_id` (`role_id` ASC) ,
  PRIMARY KEY (`grant_id`) ,
  UNIQUE INDEX `grant_UNIQUE` (`role_id` ASC, `contest_id` ASC, `login_id` ASC) ,
  INDEX `fk_grant_contest1` (`contest_id` ASC) ,
  INDEX `fk_grant_login1` (`login_id` ASC) ,
  CONSTRAINT `permission_ibfk_2`
    FOREIGN KEY (`role_id` )
    REFERENCES `role` (`role_id` )
    ON DELETE CASCADE,
  CONSTRAINT `fk_grant_contest1`
    FOREIGN KEY (`contest_id` )
    REFERENCES `contest` (`contest_id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_grant_login1`
    FOREIGN KEY (`login_id` )
    REFERENCES `login` (`login_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `person_info`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `person_info` ;

CREATE  TABLE IF NOT EXISTS `person_info` (
  `person_id` INT(11) NOT NULL ,
  `born` DATE NULL DEFAULT NULL COMMENT 'datum narození' ,
  `id_number` VARCHAR(32) NULL DEFAULT NULL COMMENT 'číslo OP či ekvivalent' ,
  `born_id` VARCHAR(32) NULL DEFAULT NULL COMMENT 'rodné číslo nebo ekvivalent' ,
  `phone` VARCHAR(32) NULL DEFAULT NULL COMMENT 'tel. číslo' ,
  `im` VARCHAR(32) NULL DEFAULT NULL COMMENT 'ICQ, XMPP, etc.' ,
  `note` TEXT NULL DEFAULT NULL COMMENT 'ostatní/poznámka' ,
  `uk_login` VARCHAR(8) NULL DEFAULT NULL COMMENT 'CAS login, pro orgy' ,
  `account` VARCHAR(32) NULL DEFAULT NULL COMMENT 'bankovní účet jako text' ,
  `agreed` DATETIME NULL DEFAULT NULL COMMENT 'čas posledního souhlasu ze zprac. os. ú. nebo null' ,
  `birthplace` VARCHAR(255) NULL DEFAULT NULL COMMENT 'název města narození osoby' ,
  `email` VARCHAR(255) NULL ,
  PRIMARY KEY (`person_id`) ,
  CONSTRAINT `person_info_ibfk_1`
    FOREIGN KEY (`person_id` )
    REFERENCES `person` (`person_id` )
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Podrobné informace o osobě, zde jsou všechny osobní údaje (t';


-- -----------------------------------------------------
-- Table `post_contact`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `post_contact` ;

CREATE  TABLE IF NOT EXISTS `post_contact` (
  `post_contact_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `person_id` INT(11) NOT NULL ,
  `address_id` INT(11) NOT NULL ,
  `type` ENUM('P','D') NOT NULL COMMENT 'doručovací (Delivery), trvalá (Permanent)' ,
  PRIMARY KEY (`post_contact_id`) ,
  INDEX `person_id` (`person_id` ASC) ,
  INDEX `address_id` (`address_id` ASC) ,
  CONSTRAINT `post_contact_ibfk_1`
    FOREIGN KEY (`person_id` )
    REFERENCES `person` (`person_id` )
    ON DELETE CASCADE,
  CONSTRAINT `post_contact_ibfk_2`
    FOREIGN KEY (`address_id` )
    REFERENCES `address` (`address_id` )
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Přiřazení adres lidem vztahem M:N';


-- -----------------------------------------------------
-- Table `psc_region`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `psc_region` ;

CREATE  TABLE IF NOT EXISTS `psc_region` (
  `psc` CHAR(5) NOT NULL ,
  `region_id` INT(11) NOT NULL ,
  PRIMARY KEY (`psc`) ,
  INDEX `region_id` (`region_id` ASC) ,
  CONSTRAINT `psc_region_ibfk_1`
    FOREIGN KEY (`region_id` )
    REFERENCES `region` (`region_id` )
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'mapování českých a slovenských PSČ na evidovaný region';


-- -----------------------------------------------------
-- Table `si_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `si_type` ;

CREATE  TABLE IF NOT EXISTS `si_type` (
  `type_id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(60) NOT NULL ,
  `description` VARCHAR(255) NULL COMMENT 'druh rozesílané pošty (brožurka, pozvánka, etc.)' ,
  PRIMARY KEY (`type_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `si_log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `si_log` ;

CREATE  TABLE IF NOT EXISTS `si_log` (
  `person_id` INT(11) NOT NULL ,
  `type_id` INT NOT NULL ,
  `note` VARCHAR(128) NULL DEFAULT NULL COMMENT '„24-1“… ' ,
  `time` DATETIME NOT NULL ,
  INDEX `person_id` (`person_id` ASC) ,
  INDEX `fk_si_log_si_type1` (`type_id` ASC) ,
  PRIMARY KEY (`person_id`, `type_id`) ,
  CONSTRAINT `si_log_ibfk_1`
    FOREIGN KEY (`person_id` )
    REFERENCES `person` (`person_id` )
    ON DELETE CASCADE,
  CONSTRAINT `fk_si_log_si_type1`
    FOREIGN KEY (`type_id` )
    REFERENCES `si_type` (`type_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Skript si podle logu dává pozor, aby někoho nespamoval dvakr';


-- -----------------------------------------------------
-- Table `si_settings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `si_settings` ;

CREATE  TABLE IF NOT EXISTS `si_settings` (
  `person_id` INT(11) NOT NULL ,
  `type_id` INT NOT NULL ,
  `value` ENUM('yes', 'no', 'auto-yes', 'auto-no') NULL ,
  INDEX `person_id` (`person_id` ASC) ,
  INDEX `fk_si_settings_si_type1` (`type_id` ASC) ,
  PRIMARY KEY (`person_id`, `type_id`) ,
  CONSTRAINT `si_settings_ibfk_1`
    FOREIGN KEY (`person_id` )
    REFERENCES `person` (`person_id` )
    ON DELETE CASCADE,
  CONSTRAINT `fk_si_settings_si_type1`
    FOREIGN KEY (`type_id` )
    REFERENCES `si_type` (`type_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `task`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `task` ;

CREATE  TABLE IF NOT EXISTS `task` (
  `task_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `label` VARCHAR(16) NOT NULL COMMENT 'Oznaceni ulohy, treba \"23-4-5\"' ,
  `name_cs` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Jmeno ulohy' ,
  `name_en` VARCHAR(255) NULL ,
  `contest_id` INT(11) NOT NULL COMMENT 'seminář' ,
  `year` TINYINT(4) NOT NULL COMMENT 'Rocnik seminare' ,
  `series` TINYINT(4) NOT NULL COMMENT 'Serie' ,
  `tasknr` TINYINT(4) NULL DEFAULT NULL COMMENT 'Uloha' ,
  `points` TINYINT(4) NULL DEFAULT NULL COMMENT 'Maximalni pocet bodu' ,
  `submit_start` DATETIME NULL DEFAULT NULL COMMENT 'Od kdy se smi submitovat' ,
  `submit_deadline` DATETIME NULL DEFAULT NULL COMMENT 'Do kdy' ,
  PRIMARY KEY (`task_id`) ,
  INDEX `contest_id` (`contest_id` ASC) ,
  CONSTRAINT `task_ibfk_1`
    FOREIGN KEY (`contest_id` )
    REFERENCES `contest` (`contest_id` ))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `submit`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `submit` ;

CREATE  TABLE IF NOT EXISTS `submit` (
  `submit_id` INT(11) NOT NULL AUTO_INCREMENT ,
  `ct_id` INT(11) NOT NULL COMMENT 'Contestant' ,
  `task_id` INT(11) NOT NULL COMMENT 'Task' ,
  `submitted_on` DATETIME NULL ,
  `source` ENUM('post','upload') NOT NULL COMMENT 'odkud přišlo řešení' ,
  `note` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Pocet stranek a jine poznamky' ,
  `raw_points` DECIMAL(4,2) NULL DEFAULT NULL COMMENT 'Pred prepoctem' ,
  `calc_points` DECIMAL(4,2) NULL DEFAULT NULL COMMENT 'Cache spoctenych bodu.\n' ,
  PRIMARY KEY (`submit_id`) ,
  UNIQUE INDEX `cons_uniq` (`ct_id` ASC, `task_id` ASC) ,
  INDEX `task_id` (`task_id` ASC) ,
  CONSTRAINT `submit_ibfk_1`
    FOREIGN KEY (`ct_id` )
    REFERENCES `contestant` (`ct_id` ),
  CONSTRAINT `submit_ibfk_2`
    FOREIGN KEY (`task_id` )
    REFERENCES `task` (`task_id` ))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `participant_spam`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `participant_spam` ;

CREATE  TABLE IF NOT EXISTS `participant_spam` (
  `participant_id` INT NOT NULL ,
  `school_id` INT NULL ,
  `class` VARCHAR(16) NULL ,
  `study_year` VARCHAR(45) NULL ,
  PRIMARY KEY (`participant_id`) ,
  INDEX `fk_participant_spam_school1` (`school_id` ASC) ,
  CONSTRAINT `fk_participant_spam_school1`
    FOREIGN KEY (`school_id` )
    REFERENCES `school` (`school_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_participant_spam_event_participant1`
    FOREIGN KEY (`participant_id` )
    REFERENCES `event_participant` (`participant_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `event_has_org`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `event_has_org` ;

CREATE  TABLE IF NOT EXISTS `event_has_org` (
  `event_id` INT(11) NOT NULL ,
  `org_id` INT(11) NOT NULL ,
  PRIMARY KEY (`event_id`, `org_id`) ,
  INDEX `fk_action_has_org_org1` (`org_id` ASC) ,
  CONSTRAINT `fk_action_has_org_event1`
    FOREIGN KEY (`event_id` )
    REFERENCES `event` (`event_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_action_has_org_org1`
    FOREIGN KEY (`org_id` )
    REFERENCES `org` (`org_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `org_task_contribution`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `org_task_contribution` ;

CREATE  TABLE IF NOT EXISTS `org_task_contribution` (
  `contribution_id` INT NOT NULL ,
  `org_id` INT(11) NOT NULL ,
  `task_id` INT(11) NOT NULL ,
  `type` ENUM('task', 'solution', 'grade') NOT NULL ,
  PRIMARY KEY (`contribution_id`) ,
  INDEX `fk_org_task_contribution_org1` (`org_id` ASC) ,
  INDEX `fk_org_task_contribution_task1` (`task_id` ASC) ,
  CONSTRAINT `fk_org_task_contribution_org1`
    FOREIGN KEY (`org_id` )
    REFERENCES `org` (`org_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_org_task_contribution_task1`
    FOREIGN KEY (`task_id` )
    REFERENCES `task` (`task_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
