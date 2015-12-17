CREATE OR REPLACE VIEW v_person as (
	select p.person_id, concat(family_name, other_name) AS name_lex, IF(display_name is null, concat(other_name, ' ', family_name), display_name) as name, gender, email
	from person p
	left join person_info pi on pi.person_id = p.person_id
);

create or replace view v_school as (
	select s.school_id, s.address_id, coalesce(s.name_abbrev, s.name, s.name_full) as name, s.email, s.izo, s.ic, a.target, a.city, r.country_iso
	from school s
        left join address a on a.address_id = s.address_id
        left join region r on r.region_id = a.region_id
);

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

select s.submit_id AS Reseni, t.year AS Rocnik, t.series AS Serie, t.tasknr AS CisloUlohy, t.label AS Uloha, t.points AS MaxBodu, s.calc_points AS Bodu,
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

create or replace view v_series_points as (
	select ct.contest_id, ct.year, t.series, ct.ct_id, ct.person_id,
	sum(s.calc_points) as points,
	sum(IF(t.contest_id = 2,
		IF(t.year >= 4,
			IF(t.label = '1' AND ct.study_year NOT IN (6, 7), NULL, t.points),
			t.points
		),
		t.points
	)) as max_points
    from task t
	right join v_contestant ct on ct.contest_id = t.contest_id and ct.year = t.year
	left join submit s on s.task_id = t.task_id and s.ct_id = ct.ct_id
	group by ct.contest_id, ct.year, t.series, ct.ct_id, ct.person_id
);

create or replace view v_aesop_person as (
    select
            p.other_name as `name`,
            p.family_name as surname,
            p.person_id as `id`,
            a.target as street,
            a.city as town,
            a.postal_code as postcode,
            r.country_iso as country,
            p.display_name as fullname,
            p.gender as gender,
            if(pi.born is null, null, concat(year(pi.born), '-', lpad(month(pi.born), 2, '0'), '-', lpad(day(pi.born), 2, '0'))) as born,

            coalesce(
                if(sar.country_iso = 'cz', concat('red-izo:', s.izo), null),
                if(sar.country_iso = 'sk', concat('sk:', s.izo), null),
                null, -- TODO AESOP id
                if(s.school_id is null, null, 'ufo')
            ) as school,
            s.name_abbrev as `school-name`,
            if(ph.study_year between 1 and 4, ph.ac_year + 5 - ph.study_year,
            if(ph.study_year between 5 and 9, ph.ac_year + 14 - ph.study_year,
            null)) as `end-year`,
            pi.email as email,
            if(phf.value = 1, 'Y', if(phf.value = 0, 'N', null)) as `spam-flag`,
            DATE_FORMAT(phf.modified, '%Y-%m-%d') as `spam-date`, -- returns varchar instead of function date()
            p.person_id as `x-person_id`,
            pi.birthplace as `x-birthplace`,
            ph.ac_year as `x-ac_year`
    from person p
    left join v_post_contact pc on pc.person_id = p.person_id
    left join address a on a.address_id = pc.address_id
    left join region r on r.region_id = a.region_id
    left join person_history ph on ph.person_id = p.person_id
    left join school s on s.school_id = ph.school_id
    left join address sa on sa.address_id = s.address_id
    left join region sar on sar.region_id = sa.region_id
    left join person_info pi on pi.person_id = p.person_id
    left join flag f on f.fid = 'spam_mff'
    left join person_has_flag phf on p.person_id = phf.person_id and phf.flag_id = f.flag_id
);

-- Note: selects also contestant with no points.
create or replace view v_aesop_points as (
	select
		sp.contest_id,
		cy.ac_year,
		sp.person_id,
		sum(sp.points) as points,
                sum(sp.points) / sum(sp.max_points) as points_ratio,
		null as rank
	from v_series_points sp
	left join contest_year cy on cy.year = sp.year and cy.contest_id = sp.contest_id
        where exists (
                select 1
                from submit s
                left join task t on t.task_id = s.task_id
                where s.ct_id = sp.ct_id and t.contest_id = sp.contest_id and t.year = sp.year
                    and t.series between 1 and 6) -- only contestants with any relevant submits
	group by sp.contest_id, sp.year, sp.ct_id, sp.person_id, cy.ac_year
);

create or replace view v_aesop_contestant as (
	select p.*, res.points, res.points_ratio as `x-points_ratio`, res.rank, res.contest_id as `x-contest_id`
	from v_aesop_person p
	right join v_aesop_points res on res.person_id = p.`x-person_id` and res.ac_year = p.`x-ac_year`
);	

create or replace view v_dokuwiki_user as (
    select l.login_id, l.login, l.hash, p.name, p.email, p.name_lex
    from login l
    left join v_person p on p.person_id = l.person_id
    where exists (
        select 1
        from org o
        inner join contest_year cy
            on cy.contest_id = o.contest_id
            and cy.ac_year = IF(month(NOW()) >= 9, year(NOW()), year(NOW()) - 1)
            and (o.until is null or cy.year between o.since and o.until)
        where o.person_id = l.person_id
        
    ) or l.person_id is null
);

create or replace view v_dokuwiki_group as (
	select role_id, name
	from role
);

create or replace view v_dokuwiki_user_group as
	select login_id, role_id, contest_id
	from `grant`
	union
	select l.login_id, 8 as `role_id`, o.contest_id -- hardcoded 8 is 'org'
	from org o
	inner join login l on l.person_id = o.person_id
;
