CREATE TABLE IF NOT EXISTS `payment_accommodation`
(
    `payment_accommodation_id`      INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `payment_id`                    INT(11) NOT NULL,
    `event_person_accommodation_id` INT(11) NOT NULL,
    UNIQUE INDEX `UC_payment_accommodation_1` (event_person_accommodation_id),
    INDEX `fk_accommodation_payment_1_idx` (`payment_id` ASC),
    INDEX `fk_accommodation_payment_2_idx` (`event_person_accommodation_id` ASC),
    CONSTRAINT `fk_accommodation_payment_event_person_accommodation1`
        FOREIGN KEY (`event_person_accommodation_id`)
            REFERENCES `event_person_accommodation` (`event_person_accommodation_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT `fk_accommodation_payment_payment_1`
        FOREIGN KEY (`payment_id`)
            REFERENCES `payment` (`payment_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = 'InnoDB';

-- -----------------------------------------------------
-- Table `event_accommodation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `event_accommodation`
(
    `event_accommodation_id` INT  NOT NULL AUTO_INCREMENT,
    `event_id`               INT  NOT NULL,
    `address_id`             INT  NOT NULL,
    `capacity`               INT  NOT NULL,
    `name`                   VARCHAR(45) CHARACTER SET 'utf8'
        COLLATE 'utf8_czech_ci'   NOT NULL,
    `price_kc`               INT  NULL,
    `price_eur`              INT  NULL,
    `date`                   DATE NOT NULL,
    PRIMARY KEY (`event_accommodation_id`),
    INDEX `fk_event_accommodation_1_idx` (`event_id` ASC),
    INDEX `fk_event_accommodation_2_idx` (`address_id` ASC),
    CONSTRAINT `fk_event_accommodation_1`
        FOREIGN KEY (`event_id`)
            REFERENCES `event` (`event_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT `fk_event_accommodation_2`
        FOREIGN KEY (`address_id`)
            REFERENCES `address` (`address_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `event_person_accommodation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `event_person_accommodation`
(
    `event_person_accommodation_id` INT         NOT NULL AUTO_INCREMENT,
    `person_id`                     INT         NOT NULL,
    `event_accommodation_id`        INT         NOT NULL,
    `status`                        VARCHAR(14) NULL,
    PRIMARY KEY (`event_person_accommodation_id`),
    INDEX `fk_event_person_accommodation_1_idx` (`event_accommodation_id` ASC),
    INDEX `fk_event_person_accommodation_2_idx` (`person_id` ASC),
    CONSTRAINT `fk_event_person_accommodation_1`
        FOREIGN KEY (`event_accommodation_id`)
            REFERENCES `event_accommodation` (`event_accommodation_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT `fk_event_person_accommodation_2`
        FOREIGN KEY (`person_id`)
            REFERENCES `person` (`person_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = InnoDB;
