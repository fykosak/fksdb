CREATE OR REPLACE VIEW v_person as
(
select p.person_id,
       concat(family_name, other_name)                                              AS name_lex,
       IF(display_name is null, concat(other_name, ' ', family_name), display_name) as name,
       gender,
       email
from person p
         left join person_info pi on pi.person_id = p.person_id
    );

create or replace view v_contestant as
(
select IF(display_name is null, concat(other_name, ' ', family_name), display_name) as name,
       concat(family_name, other_name)                                              as name_lex,
       p.gender,
       ct.contestant_id,
       ct.person_id,
       ct.contest_id,
       ct.year,
       ph.study_year_new,
       ph.school_id,
       ph.class,
       s.name                                                                       as `school_name`
from contestant ct
         inner join person p on p.person_id = ct.person_id
         left join contest_year cy on cy.contest_id = ct.contest_id and cy.year = ct.year
         left join person_history ph on ph.person_id = ct.person_id and ph.ac_year = cy.ac_year
         left join school s on s.school_id = ph.school_id
    );
CREATE OR REPLACE VIEW v_post_contact as
(
select p.person_id, IF(ad.address_id is null, ap.address_id, ad.address_id) AS address_id
from person p
         left join post_contact ap on ap.person_id = p.person_id and ap.type = 'P'
         left join post_contact ad on ad.person_id = p.person_id and ad.type = 'D'
where not (ap.address_id is null and ad.address_id is null)
    );

CREATE OR REPLACE VIEW v_person_envelope as
(
SELECT pc.person_id                                                                 AS `person_id`,
       IF(display_name is null, concat(other_name, ' ', family_name), display_name) AS `CeleJmeno`,
       a.first_row                                                                  AS `PrvniRadek`,
       a.second_row                                                                 AS `DruhyRadek`,
       a.target                                                                     AS `TretiRadek`,
       a.city                                                                       AS `Mesto`,
       a.postal_code                                                                AS `PSC`,
       if((c.alpha_2 = 'SK'), 'Slovakia', NULL)                                     AS `Stat`
FROM v_post_contact pc
         inner join person p on p.person_id = pc.person_id
         inner join address a on pc.address_id = a.address_id
         inner join country c on c.country_id = a.country_id and c.country_id in ('CZ', 'SK')
    );

create or replace view v_series_points as
(
select ct.contest_id,
       ct.year,
       t.series,
       ct.contestant_id,
       ct.person_id,
       sum(s.calc_points) as points,
       sum(IF(t.contest_id = 2,
              IF(t.year >= 4,
                 IF(t.label = '1' AND ph.study_year_new NOT IN ('P_5', 'P_6', 'P_7'), NULL, t.points),
                 t.points
                  ),
              t.points
           ))             as max_points
from task t
         right join contestant ct on ct.contest_id = t.contest_id and ct.year = t.year
         left join contest_year cy on cy.contest_id = ct.contest_id and cy.year = ct.year
         left join person_history ph on ph.person_id = ct.person_id and ph.ac_year = cy.ac_year
         left join submit s on s.task_id = t.task_id and s.contestant_id = ct.contestant_id
group by ct.contest_id, ct.year, t.series, ct.contestant_id, ct.person_id
    );

create or replace view v_aesop_person as
(
select p.other_name                                                                                  as `name`,
       p.family_name                                                                                 as surname,
       p.person_id                                                                                   as `id`,
       a.target                                                                                      as street,
       a.city                                                                                        as town,
       a.postal_code                                                                                 as postcode,
       c.alpha_2                                                                                     as country,
       p.display_name                                                                                as fullname,
       p.gender                                                                                      as gender,
       if(pi.born is null, null,
          concat(year(pi.born), '-', lpad(month(pi.born), 2, '0'), '-', lpad(day(pi.born), 2, '0'))) as born,

       coalesce(
               if(sac.alpha_2 = 'cz', concat('red-izo:', s.izo), null),
               if(sac.alpha_2 = 'sk', concat('sk:', s.izo), null),
               if(s.school_id is null, null, 'ufo')
           )                                                                                         as school,
       s.name_abbrev                                                                                 as `school-name`,
       IF(
                   ph.study_year_new IN ('U_ALL', 'NONE'),
                   NULL,
                   (
                               ph.ac_year + IF(SUBSTRING_INDEX(study_year_new, '_', 1) = 'P', 14, 5) -
                               (SUBSTRING_INDEX(study_year_new, '_', -1) * 1)
                       )
           )                                                                                         as `end-year`,
       pi.email                                                                                      as email,
       if(phf.value = 1, 'Y', if(phf.value = 0, 'N', null))                                          as `spam-flag`,
       DATE_FORMAT(phf.modified, '%Y-%m-%d')                                                         as `spam-date`, -- returns varchar instead of function date()
       p.person_id                                                                                   as `x-person_id`,
       pi.birthplace                                                                                 as `x-birthplace`,
       ph.ac_year                                                                                    as `x-ac_year`
from person p
         left join v_post_contact pc on pc.person_id = p.person_id
         left join address a on a.address_id = pc.address_id
         left join country c on c.country_id = a.country_id
         left join person_history ph on ph.person_id = p.person_id
         left join school s on s.school_id = ph.school_id
         left join address sa on sa.address_id = s.address_id
         left join country sac on sac.country_id = sa.country_id
         left join person_info pi on pi.person_id = p.person_id
         left join flag f on f.fid = 'spam_mff'
         left join person_has_flag phf on p.person_id = phf.person_id and phf.flag_id = f.flag_id
    );

-- Note: selects also contestant with no points.
create or replace view v_aesop_points as
(
select sp.contest_id,
       cy.ac_year,
       sp.person_id,
       sum(sp.points)                      as points,
       sum(sp.points) / sum(sp.max_points) as points_ratio,
       null                                as `rank`
from v_series_points sp
         left join contest_year cy on cy.year = sp.year and cy.contest_id = sp.contest_id
where exists(
              select 1
              from submit s
                       left join task t on t.task_id = s.task_id
              where s.contestant_id = sp.contestant_id
                and t.contest_id = sp.contest_id
                and t.year = sp.year
                and t.series between 1 and 6) -- only contestants with any relevant submits
group by sp.contest_id, sp.year, sp.contestant_id, sp.person_id, cy.ac_year
    );

create or replace view v_aesop_contestant as
(
select p.*, res.points, res.points_ratio as `x-points_ratio`, res.rank, res.contest_id as `x-contest_id`
from v_aesop_person p
         right join v_aesop_points res on res.person_id = p.`x-person_id` and res.ac_year = p.`x-ac_year`
    );

create or replace view v_dokuwiki_user as
(
select l.login_id,
       l.login,
       l.hash,
       IF(display_name is null, concat(other_name, ' ', family_name), display_name) as name,
       pi.email,
       concat(family_name, other_name)                                              as name_lex
from login l
         left join person p on p.person_id = l.person_id
         join person_info pi on p.person_id = pi.person_id
where exists(
        select 1
        from org o
                 inner join contest_year cy
                            on cy.contest_id = o.contest_id
                                and cy.ac_year = IF(month(NOW()) >= 9, year(NOW()), year(NOW()) - 1)
                                and (o.until is null or cy.year between o.since and o.until)
        where o.person_id = l.person_id
    )
   or l.person_id is null
    );

create or replace view v_dokuwiki_group as
(
select distinct `role` as `role_id`, `role` as `name`
from `contest_grant`
union
select distinct `role` as `role_id`, `role` as `name`
from `base_grant`
union select 'organizer' as `role`, 'organizer' as `name` -- hardcoded 'organizer'
);

create or replace view v_dokuwiki_user_group as
(
select cg.login_id, cg.`role` as 'role_id', cg.contest_id
from `contest_grant` cg
union
select bg.login_id, bg.`role` as 'role_id', c.contest_id
from `base_grant` bg
cross join contest c
union
select l.login_id, 'organizer' as `role_id`, o.contest_id -- hardcoded 'organizer'
from org o
inner join login l on l.person_id = o.person_id
);
