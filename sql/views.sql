set names 'utf8';

CREATE OR REPLACE VIEW v_task_stats as
(SELECT task.*, avg(raw_points) as task_avg, count(raw_points) as task_count from task 
  LEFT JOIN submit ON submit.task_id=task.task_id
  GROUP BY task_id
);

create or replace view v_contestant as (
    select p.name, p.name_lex, p.gender, ct.ct_id, ct.person_id, ct.contest_id, ct.year, ph.study_year, ph.school_id, ph.class, s.name as `school_name`
    from contestant_base ct
    inner join v_person p on p.person_id = ct.person_id
    left join contest_year cy on cy.contest_id = ct.contest_id and cy.year = ct.year
    left join person_history ph on ph.person_id = ct.person_id and ph.ac_year = cy.ac_year
    left join v_school s on s.school_id = ph.school_id
);

CREATE OR REPLACE VIEW v_gooddata as (
-- Reseni;Rocnik;Serie;CisloUlohy;Uloha;MaxBodu;Bodu;Resitel;Rokmaturity;Pohlavi;Skola;Mesto;Stat

select s.submit_id AS Reseni, t.year AS Rocnik, t.series AS Serie, t.tasknr AS CisloUlohy, t.label AS Uloha, t.points AS MaxBodu, s.raw_points AS Bodu,
IF(p.display_name is null, concat(p.other_name, ' ', p.family_name), p.display_name) AS Resitel, 
(IF(ct.contest_id = 1, 1991 + ct.year, 2015 + ct.year) - IF(ct.study_year between 1 and 4, ct.study_year, ct.study_year - 9) ) AS RokMaturity,
p.gender AS Pohlavi, sch.name_abbrev AS Skola, scha.city AS Mesto, reg.country_iso AS Stat, cst.name AS Seminar

from submit s
left join task t on t.task_id = s.task_id
left join v_contestant ct on ct.ct_id = s.ct_id
left join person p on p.person_id = ct.person_id
left join school sch on ct.school_id = sch.school_id
left join address scha on scha.address_id = sch.address_id
left join region reg on scha.region_id = reg.region_id
left join contest cst on cst.contest_id = ct.contest_id
);

CREATE OR REPLACE VIEW v_person as (
	select person_id, concat(family_name, other_name) AS name_lex, IF(display_name is null, concat(other_name, ' ', family_name), display_name) as name, gender
	from person
);

CREATE OR REPLACE VIEW v_post_contact as (
	select p.person_id, IF(ad.address_id is null, ap.address_id, ad.address_id) AS address_id
	from person p
	left join post_contact ap on ap.person_id = p.person_id and ap.type = 'P'
	left join post_contact ad on ad.person_id = p.person_id and ad.type = 'D'
	where not (ap.address_id is null and ad.address_id is null)
);

CREATE OR REPLACE VIEW v_person_envelope as (
	SELECT `pc`.`person_id` AS `person_id`,
			p.name AS `CeleJmeno`,
	       `a`.`first_row` AS `PrvniRadek`,
	       `a`.`second_row` AS `DruhyRadek`,
	       `a`.`target` AS `TretiRadek`,
	       `a`.`city` AS `Mesto`,
	       `a`.`postal_code` AS `PSC`,
	       if((`r`.`country_iso` = 'SK'),'Slovakia',NULL) AS `Stat`
	FROM v_post_contact pc
	inner join v_person p on p.person_id = pc.person_id
	inner join address a on pc.address_id = a.address_id
	inner join region r on a.region_id = r.region_id and r.country_iso in ('CZ', 'SK')
);

create or replace view v_school as (
	select s.school_id, s.address_id, coalesce(s.name_abbrev, s.name, s.name_full) as name, s.email, s.izo, s.ic
	from school s
);


