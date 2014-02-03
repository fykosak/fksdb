SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `contestant_base`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `contestant_base` ;

CREATE TABLE IF NOT EXISTS `contestant_base` (
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
    REFERENCES `person` (`person_id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `contestant_base_ibfk_3`
    FOREIGN KEY (`contest_id`)
    REFERENCES `contest` (`contest_id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
COMMENT = 'Instance ucastnika (v konkretnim rocniku a semináři)';


-- -----------------------------------------------------
-- Table `contest_year`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `contest_year` ;

CREATE TABLE IF NOT EXISTS `contest_year` (
  `contest_id` INT NOT NULL,
  `year` TINYINT(4) NOT NULL,
  `ac_year` SMALLINT(4) NOT NULL COMMENT 'první rok akademického roku,\n2013/2014->2013',
  PRIMARY KEY (`contest_id`, `year`),
  INDEX `ac_year_idx` (`ac_year` ASC),
  CONSTRAINT `fk_contest_year_contest1`
    FOREIGN KEY (`contest_id`)
    REFERENCES `contest` (`contest_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'mapování ročníků semináře na akademické roky';


-- -----------------------------------------------------
-- Table `person_history`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `person_history` ;

CREATE TABLE IF NOT EXISTS `person_history` (
  `person_history_id` INT NOT NULL AUTO_INCREMENT,
  `person_id` INT NOT NULL,
  `ac_year` SMALLINT(4) NOT NULL COMMENT 'první rok akademického roku,\n2013/2014 -> 2013',
  `school_id` INT NULL,
  `class` VARCHAR(16) NULL COMMENT 'označení třídy',
  `study_year` TINYINT(1) NULL COMMENT 'ročník, který studuje',
  PRIMARY KEY (`person_history_id`),
  UNIQUE INDEX `UQ_AC_YEAR` (`person_id` ASC, `ac_year` ASC),
  INDEX `fk_person_history_school1_idx` (`school_id` ASC),
  INDEX `fk_person_history_contest_year1_idx` (`ac_year` ASC),
  CONSTRAINT `fk_person_history_school1`
    FOREIGN KEY (`school_id`)
    REFERENCES `school` (`school_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_person_history_person1`
    FOREIGN KEY (`person_id`)
    REFERENCES `person` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_person_history_contest_year1`
    FOREIGN KEY (`ac_year`)
    REFERENCES `contest_year` (`ac_year`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'atributy osoby řezané dle akademického roku';


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
