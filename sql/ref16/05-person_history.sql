insert into person_history (person_id, school_id, class, study_year, ac_year)
select
	ctf.person_id,
	coalesce(ctf.school_id, ctv.school_id),
	coalesce(ctf.class, ctv.class),
	coalesce(ctf.study_year, ctv.study_year),
	cyf.ac_year
from contestant ctf
left join contest_year cyf on cyf.contest_id = ctf.contest_id and cyf.year = ctf.year
left join contestant ctv on ctv.person_id = ctf.person_id
left join contest_year cyv on cyv.contest_id = ctv.contest_id and cyv.year = ctv.year
where ctf.contest_id = 1 and ctv.contest_id = 2 and cyf.ac_year = cyv.ac_year;

insert into person_history (person_id, school_id, class, study_year, ac_year)
select
	ct.person_id,
	ct.school_id,
	ct.class,
	ct.study_year,
	cy.ac_year
from contestant ct
left join contest_year cy on cy.contest_id = ct.contest_id and cy.year = ct.year
where ct.person_id not in (
select
	ctf.person_id
from contestant ctf
left join contest_year cyf on cyf.contest_id = ctf.contest_id and cyf.year = ctf.year
left join contestant ctv on ctv.person_id = ctf.person_id
left join contest_year cyv on cyv.contest_id = ctv.contest_id and cyv.year = ctv.year
where ctf.contest_id = 1 and ctv.contest_id = 2 and cyf.ac_year = cyv.ac_year
);
