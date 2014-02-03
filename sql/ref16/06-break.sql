create table old_contestant as
select * from contestant;

drop table contestant_base;

alter table contestant drop column study_year;
ALTER TABLE `contestant` DROP FOREIGN KEY `contestant_ibfk_2`;
alter table contestant drop column school_id;
alter table contestant drop column class;

rename table contestant to contestant_base;

