use fksdb;
set names 'utf8';

CREATE OR REPLACE VIEW v_task_stats as
(SELECT task.*, avg(raw_points) as task_avg, count(raw_points) as task_count from task 
  LEFT JOIN submit ON submit.task_id=task.task_id
  GROUP BY task_id
);
