SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP TABLE IF EXISTS `e_status` ;

-- -----------------------------------------------------
-- Table `event_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `event_type` ;

CREATE TABLE IF NOT EXISTS `event_type` (
  `event_type_id` INT NOT NULL AUTO_INCREMENT,
  `contest_id` INT(11) NOT NULL,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`event_type_id`),
  INDEX `fk_event_type_contest1_idx` (`contest_id` ASC),
  CONSTRAINT `fk_event_type_contest1`
    FOREIGN KEY (`contest_id`)
    REFERENCES `contest` (`contest_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `event`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `event` ;

CREATE TABLE IF NOT EXISTS `event` (
  `event_id` INT(11) NOT NULL AUTO_INCREMENT,
  `year` TINYINT(4) NOT NULL COMMENT 'ročník semináře',
  `event_year` TINYINT(4) NOT NULL COMMENT 'ročník akce',
  `begin` DATE NOT NULL COMMENT 'první den akce',
  `end` DATE NOT NULL COMMENT 'poslední den akce, u jednodenní akce shodný s begin',
  `registration_begin` DATE NULL DEFAULT NULL COMMENT 'případný počátek webové registrace',
  `registration_end` DATE NULL DEFAULT NULL COMMENT 'případný konec webové registrace',
  `name` VARCHAR(255) NOT NULL COMMENT 'název akce',
  `fb_album_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'id galerie na Facebooku',
  `report` TEXT NULL DEFAULT NULL COMMENT '(HTML) zápis z proběhlé akce',
  `event_type_id` INT NOT NULL,
  PRIMARY KEY (`event_id`),
  INDEX `fk_event_event_type1_idx` (`event_type_id` ASC),
  UNIQUE INDEX `UQ_EVENT_YEAR` (`event_year` ASC, `event_type_id` ASC),
  CONSTRAINT `fk_event_event_type1`
    FOREIGN KEY (`event_type_id`)
    REFERENCES `event_type` (`event_type_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `event_status`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `event_status` ;

CREATE TABLE IF NOT EXISTS `event_status` (
  `status` VARCHAR(20) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`status`))
ENGINE = InnoDB
COMMENT = 'list of allowed statuses (for data integrity)';


-- -----------------------------------------------------
-- Table `event_participant`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `event_participant` ;

CREATE TABLE IF NOT EXISTS `event_participant` (
  `participant_id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `person_id` INT(11) NOT NULL,
  `note` TEXT NULL DEFAULT NULL COMMENT 'poznámka',
  `status` VARCHAR(20) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'čas vytvoření přihlášky',
  PRIMARY KEY (`participant_id`),
  INDEX `action_id` (`event_id` ASC),
  INDEX `person_id` (`person_id` ASC),
  INDEX `fk_event_participant_e_status1_idx` (`status` ASC),
  CONSTRAINT `action_application_ibfk_1`
    FOREIGN KEY (`event_id`)
    REFERENCES `event` (`event_id`),
  CONSTRAINT `action_application_ibfk_2`
    FOREIGN KEY (`person_id`)
    REFERENCES `person` (`person_id`),
  CONSTRAINT `fk_event_participant_e_status1`
    FOREIGN KEY (`status`)
    REFERENCES `event_status` (`status`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `event_has_org`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `event_has_org` ;

CREATE TABLE IF NOT EXISTS `event_has_org` (
  `event_id` INT(11) NOT NULL,
  `person_id` INT(11) NOT NULL,
  PRIMARY KEY (`event_id`, `person_id`),
  INDEX `fk_event_has_org_person1_idx` (`person_id` ASC),
  CONSTRAINT `fk_action_has_org_event1`
    FOREIGN KEY (`event_id`)
    REFERENCES `event` (`event_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_event_has_org_person1`
    FOREIGN KEY (`person_id`)
    REFERENCES `person` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `e_fyziklani_team`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `e_fyziklani_team` ;

CREATE TABLE IF NOT EXISTS `e_fyziklani_team` (
  `team_id` INT NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `liaison_id` INT(11) NULL COMMENT 'kontaktní osoba',
  `teacher_will_stay` TINYINT(1) NOT NULL,
  `teacher_present` TINYINT(1) NOT NULL,
  PRIMARY KEY (`team_id`),
  INDEX `fk_e_fyziklani_team_event1_idx` (`event_id` ASC),
  INDEX `fk_e_fyziklani_team_person1_idx` (`liaison_id` ASC),
  INDEX `fk_e_fyziklani_team_e_status1_idx` (`status` ASC),
  CONSTRAINT `fk_e_fyziklani_team_event1`
    FOREIGN KEY (`event_id`)
    REFERENCES `event` (`event_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_e_fyziklani_team_person1`
    FOREIGN KEY (`liaison_id`)
    REFERENCES `person` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_e_fyziklani_team_e_status1`
    FOREIGN KEY (`status`)
    REFERENCES `event_status` (`status`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `e_fyziklani_participant`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `e_fyziklani_participant` ;

CREATE TABLE IF NOT EXISTS `e_fyziklani_participant` (
  `participant_id` INT NOT NULL,
  `team_id` INT NOT NULL,
  PRIMARY KEY (`participant_id`),
  INDEX `fk_e_fyziklani_participant_e_fyziklani_team1_idx` (`team_id` ASC),
  UNIQUE INDEX `uq_team_participan` (`participant_id` ASC, `team_id` ASC),
  CONSTRAINT `fk_e_participant_fyziklani_event_participant1`
    FOREIGN KEY (`participant_id`)
    REFERENCES `event_participant` (`participant_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_e_fyziklani_participant_e_fyziklani_team1`
    FOREIGN KEY (`team_id`)
    REFERENCES `e_fyziklani_team` (`team_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
