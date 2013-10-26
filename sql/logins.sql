update login set active = 1
where person_id in (
	select person_id from v_contestant
	where 
	(contest_id = 1 and year in (26, 27))
	or (contest_id = 2 and year in (2, 3))
);

update login set active = 1
where person_id in (
	select person_id from org
	where 
	(contest_id = 1 and until is null)
	or (contest_id = 2 and until is null)
);

