update login set email = null where email = '';
update login set fb_id = null where fb_id = '';
update login set linkedin_id = null where linkedin_id  = '';

update person_info set linkedin_id = null;
update person_info set fb_id = null;

INSERT INTO person_info(person_id, email, fb_id, linkedin_id)

select person_id, email, fb_id, linkedin_id
from login l
where
(email is not null
	or fb_id is not null
	or linkedin_id is not null)
and person_inforson_id is not null

ON DUPLICATE KEY UPDATE 
email = l.email,
fb_id = l.fb_id,
linkedin_id = l.linkedin_id;
	
