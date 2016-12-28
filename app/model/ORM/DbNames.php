<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class DbNames {

    const TAB_ADDRESS = 'address';
    const TAB_AUTH_TOKEN = 'auth_token';
    const TAB_CONTEST = 'contest';
    const TAB_CONTEST_YEAR = 'contest_year';
    const TAB_CONTESTANT_BASE = 'contestant_base';
    const TAB_EVENT = 'event';
    const TAB_EVENT_HAS_ORG = self::TAB_EVENT_ORG;
    const TAB_EVENT_ORG = 'event_org';
    const TAB_EVENT_PARTICIPANT = 'event_participant';
    const TAB_EVENT_TYPE = 'event_type';
    const TAB_FLAG = 'flag';
    const TAB_GLOBAL_SESSION = 'global_session';
    const TAB_GRANT = 'grant';
    const TAB_LOGIN = 'login';
    const TAB_ORG = 'org';
    const TAB_PERSON = 'person';
    const TAB_PERSON_HAS_FLAG = 'person_has_flag';
    const TAB_PERSON_HISTORY = 'person_history';
    const TAB_PERSON_INFO = 'person_info';
    const TAB_POST_CONTACT = 'post_contact';
    const TAB_REGION = 'region';
    const TAB_ROLE = 'role';
    const TAB_SCHOOL = 'school';
    const TAB_SPAMEE = 'si_spamee'; // obsolete
    const TAB_STORED_QUERY = 'stored_query';
    const TAB_STORED_QUERY_PARAM = 'stored_query_parameter';
    const TAB_STUDY_YEAR = 'study_year';
    const TAB_SUBMIT = 'submit';
    const TAB_TASK = 'task';
    const TAB_TASK_CONTRIBUTION = 'task_contribution';
    const TAB_TASK_STUDY_YEAR = 'task_study_year';
    const VIEW_CONTESTANT = 'v_contestant';

    /* Specified tables for events */
    const TAB_E_DSEF_GROUP = 'e_dsef_group';
    const TAB_E_DSEF_PARTICIPANT = 'e_dsef_participant';
    const TAB_E_FYZIKLANI_TEAM = 'e_fyziklani_team';
    const TAB_E_FYZIKLANI_PARTICIPANT = 'e_fyziklani_participant';
    const TAB_E_SOUS_PARTICIPANT = 'e_sous_participant';
    const TAB_E_TSAF_PARTICIPANT = 'e_tsaf_participant';
    const TAB_E_VIKEND_PARTICIPANT = 'e_vikend_participant';
    /* For fyziklani */
    const TAB_FYZIKLANI_TASK = 'fyziklani_task';
    const TAB_FYZIKLANI_SUBMIT = 'fyziklani_submit';

}
