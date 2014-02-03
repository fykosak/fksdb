insert into contestant_base (ct_id, contest_id, year, person_id, created)
select ct_id, contest_id, year, person_id, created
from contestant;

