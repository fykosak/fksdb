insert into fyziklani_team
select e_fyziklani_team_id as `fyziklani_team_id`,
       event_id,
       name,
       status              as `state`,
       category,
       created,
       phone,
       note,
       password,
       points,
       rank_category,
       rank_total,
       force_a,
       game_lang
from e_fyziklani_team eft;


insert into fyziklani_team_teacher
select teacher_id          as 'person_id',
       e_fyziklani_team_id as `fyziklani_team_id`
from e_fyziklani_team
where teacher_id is not null;

insert into fyziklani_team_member
select person_id,
       e_fyziklani_team_id as `fyziklani_team_id`
from e_fyziklani_participant
         join event_participant ep on e_fyziklani_participant.event_participant_id = ep.event_participant_id;
