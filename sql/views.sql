set names 'utf8';

CREATE OR REPLACE VIEW v_task_stats as
(SELECT task.*, avg(raw_points) as task_avg, count(raw_points) as task_count from task 
  LEFT JOIN submit ON submit.task_id=task.task_id
  GROUP BY task_id
);

CREATE OR REPLACE VIEW v_gooddata as (
-- Reseni;Rocnik;Serie;CisloUlohy;Uloha;MaxBodu;Bodu;Resitel;Rokmaturity;Pohlavi;Skola;Mesto;Stat

select s.submit_id AS Reseni, t.year AS Rocnik, t.series AS Serie, t.tasknr AS CisloUlohy, t.label AS Uloha, t.points AS MaxBodu, s.raw_points AS Bodu,
IF(p.display_name is null, concat(p.other_name, ' ', p.family_name), p.display_name) AS Resitel, 
(IF(ct.contest_id = 1, 1991 + ct.year, 2015 + ct.year) - IF(ct.study_year between 1 and 4, ct.study_year, ct.study_year - 9) ) AS RokMaturity,
p.gender AS Pohlavi, sch.name_abbrev AS Skola, scha.city AS Mesto, reg.country_iso AS Stat, cst.name AS Seminar

from submit s
left join task t on t.task_id = s.task_id
left join contestant ct on ct.ct_id = s.ct_id
left join person p on p.person_id = ct.person_id
left join school sch on ct.school_id = sch.school_id
left join address scha on scha.address_id = sch.address_id
left join region reg on scha.region_id = reg.region_id
left join contest cst on cst.contest_id = ct.contest_id
);
