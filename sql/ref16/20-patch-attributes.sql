DELETE FROM e_fyziklani_participant;

DELETE FROM event_participant;

DELETE FROM e_fyziklani_team;

DROP TABLE  e_fyziklani_participant;
DROP TABLE event_participant;
DROP TABLE e_fyziklani_team;

CREATE TABLE `event_participant` (
  `event_participant_id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `person_id` INT(11) NOT NULL,
  `note` TEXT NULL DEFAULT NULL COMMENT 'poznámka',
  `status` VARCHAR(20) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'čas vytvoření přihlášky',
  `accomodation` TINYINT(1) NULL,
  PRIMARY KEY (`event_participant_id`),
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


CREATE TABLE IF NOT EXISTS `e_fyziklani_team` (
  `e_fyziklani_team_id` INT NOT NULL AUTO_INCREMENT,
  `event_id` INT(11) NOT NULL,
  `name` VARCHAR(30) NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `teacher_id` INT(11) NULL COMMENT 'kontaktní osoba',
  `teacher_accomodation` TINYINT(1) NOT NULL,
  `teacher_present` TINYINT(1) NOT NULL,
  `category` CHAR(1) NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `phone` VARCHAR(30) NULL,
  `note` TEXT NULL,
  PRIMARY KEY (`e_fyziklani_team_id`),
  INDEX `fk_e_fyziklani_team_event1_idx` (`event_id` ASC),
  INDEX `fk_e_fyziklani_team_person1_idx` (`teacher_id` ASC),
  INDEX `fk_e_fyziklani_team_e_status1_idx` (`status` ASC),
  CONSTRAINT `fk_e_fyziklani_team_event1`
    FOREIGN KEY (`event_id`)
    REFERENCES `event` (`event_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_e_fyziklani_team_person1`
    FOREIGN KEY (`teacher_id`)
    REFERENCES `person` (`person_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_e_fyziklani_team_e_status1`
    FOREIGN KEY (`status`)
    REFERENCES `event_status` (`status`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE `e_fyziklani_participant` (
  `event_participant_id` INT NOT NULL,
  `team_id` INT NOT NULL,
  PRIMARY KEY (`event_participant_id`),
  INDEX `fk_e_fyziklani_participant_e_fyziklani_team1_idx` (`team_id` ASC),
  UNIQUE INDEX `uq_team_participan` (`event_participant_id` ASC, `team_id` ASC),
  CONSTRAINT `fk_e_participant_fyziklani_event_participant1`
    FOREIGN KEY (`event_participant_id`)
    REFERENCES `event_participant` (`event_participant_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_e_fyziklani_participant_e_fyziklani_team1`
    FOREIGN KEY (`team_id`)
    REFERENCES `e_fyziklani_team` (`e_fyziklani_team_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

ALTER TABLE `e_fyziklani_participant`
DROP FOREIGN KEY `fk_e_fyziklani_participant_e_fyziklani_team1`;

ALTER TABLE `e_fyziklani_participant`
CHANGE `team_id` `e_fyziklani_team_id` int(11) NOT NULL AFTER `event_participant_id`,
ADD FOREIGN KEY (`e_fyziklani_team_id`) REFERENCES `e_fyziklani_team` (`e_fyziklani_team_id`),
COMMENT='';


