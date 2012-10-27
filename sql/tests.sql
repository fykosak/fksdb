SET NAMES 'utf8';

SELECT task.label, task.year, task.series, task.tasknr, submit.raw_points FROM submit
LEFT JOIN contestant
ON submit.ct_id=contestant.ct_id
LEFT JOIN person
ON contestant.person_id=person.person_id
LEFT JOIN task
ON task.task_id = submit.task_id
where person.display_name='Marek Nečada';
