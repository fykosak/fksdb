CREATE TABLE IF NOT EXISTS `fksdb`.`study_year` (
  `study_year` TINYINT(1) NOT NULL,
  PRIMARY KEY (`study_year`))
ENGINE = InnoDB
COMMENT = 'table just enforeces referential integrity';

CREATE TABLE IF NOT EXISTS `fksdb`.`task_study_year` (
  `task_id` INT(11) NOT NULL,
  `study_year` TINYINT(1) NOT NULL,
  PRIMARY KEY (`task_id`, `study_year`),
  INDEX `fk_task_study_year_study_year1_idx` (`study_year` ASC),
  CONSTRAINT `fk_task_study_year_task1`
    FOREIGN KEY (`task_id`)
    REFERENCES `fksdb`.`task` (`task_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_task_study_year_study_year1`
    FOREIGN KEY (`study_year`)
    REFERENCES `fksdb`.`study_year` (`study_year`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'specification of allowed study years for a task';

INSERT INTO study_year (study_year) VALUES 
(1),
(2),
(3),
(4),
(6),
(7),
(8),
(9);


ALTER TABLE `person_history`
ADD FOREIGN KEY (`study_year`) REFERENCES `study_year` (`study_year`) ON DELETE RESTRICT ON UPDATE CASCADE;

insert into task_study_year (task_id, study_year)
select *
from (
	select task_id
	from task
	where contest_id = 1
) tasks,
(
	select study_year
	from study_year
	where study_year in (8,9,1,2,3,4)
) years;

insert into task_study_year (task_id, study_year)
select *
from (
	select task_id
	from task
	where contest_id = 2
) tasks,
(
	select study_year
	from study_year
	where study_year in (6,7,8,9)
) years;