DELIMITER $$
CREATE TRIGGER trg_ins_e_fyziklani_team__team
    AFTER INSERT
    ON e_fyziklani_team
    FOR EACH ROW
BEGIN
    insert into fyziklani_team (`fyziklani_team_id`,
                                `event_id`,
                                `name`,
                                `state`,
                                `category`,
                                `created`,
                                `phone`,
                                `note`,
                                `password`,
                                `points`,
                                `rank_category`,
                                `rank_total`,
                                `force_a`,
                                `game_lang`)
    VALUES (NEW.e_fyziklani_team_id,
            NEW.event_id,
            NEW.name,
            NEW.status,
            NEW.category,
            NEW.created,
            NEW.phone,
            NEW.note,
            NEW.password,
            NEW.points,
            NEW.rank_category,
            NEW.rank_total,
            NEW.force_a,
            NEW.game_lang);
    IF NEW.teacher_id IS NOT NULL THEN
        insert into fyziklani_team_teacher (person_id, fyziklani_team_id)
        VALUES (NEW.teacher_id, NEW.e_fyziklani_team_id);
    END IF;
END$$


CREATE TRIGGER trg_ins_e_fyziklani_participant__member
    AFTER INSERT
    ON e_fyziklani_participant
    FOR EACH ROW
BEGIN
    insert into fyziklani_team_member (person_id, fyziklani_team_id)
    VALUES ((select person_id
             from event_participant ep
             where ep.event_participant_id = NEW.event_participant_id),
            NEW.e_fyziklani_team_id);
END$$

CREATE TRIGGER trg_upd_e_fyziklani_team__team
    AFTER UPDATE
    ON e_fyziklani_team
    FOR EACH ROW
BEGIN
    UPDATE fyziklani_team
    SET `fyziklani_team_id` = NEW.e_fyziklani_team_id,
        `event_id`          = NEW.event_id,
        `name`              = NEW.name,
        `state`             = NEW.status,
        `category`          = NEW.category,
        `created`           = NEW.created,
        `phone`             = NEW.phone,
        `note`              = NEW.note,
        `password`          = NEW.password,
        `points`            = NEW.points,
        `rank_category`     = NEW.rank_category,
        `rank_total`        = NEW.rank_total,
        `force_a`           = NEW.force_a,
        `game_lang`         = NEW.game_lang
    WHERE NEW.e_fyziklani_team_id = fyziklani_team_id;
    IF NEW.teacher_id IS NOT NULL THEN
        UPDATE fyziklani_team_teacher
        SET person_id         = NEW.teacher_id,
            fyziklani_team_id = NEW.e_fyziklani_team_id
        WHERE fyziklani_team_id = NEW.e_fyziklani_team_id;
    END IF;
END$$
DELIMITER ;
