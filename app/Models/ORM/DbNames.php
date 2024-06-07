<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM;

class DbNames
{
    /* base + others */
    public const TAB_AUTH_TOKEN = 'auth_token';
    public const TAB_CONTEST = 'contest';
    public const TAB_CONTEST_YEAR = 'contest_year';
    public const TAB_SCHOOL = 'school';
    public const TAB_SCHOOL_LABEL = 'school_label';
    public const TAB_FLAG = 'flag';
    public const TAB_EMAIL_MESSAGE = 'email_message';
    public const TAB_DISQUALIFIED_PERSON = 'disqualified_person';
    /* person */
    public const TAB_PERSON = 'person';
    public const TAB_PERSON_HAS_FLAG = 'person_has_flag';
    public const TAB_PERSON_HISTORY = 'person_history';
    public const TAB_PERSON_INFO = 'person_info';
    /* ACL */
    public const TAB_CONTEST_GRANT = 'contest_grant';
    public const TAB_EVENT_GRANT = 'event_grant';
    /* extended person */
    public const TAB_LOGIN = 'login';
    public const TAB_TEACHER = 'teacher';
    public const TAB_CONTESTANT = 'contestant';
    public const TAB_ORGANIZER = 'org';
    /* events + single applications */
    public const TAB_EVENT = 'event';
    public const TAB_EVENT_TYPE = 'event_type';
    public const TAB_EVENT_ORGANIZER = 'event_org';
    public const TAB_EVENT_PARTICIPANT = 'event_participant';
    /* tasks & submits */
    public const TAB_SUBMIT = 'submit';
    public const TAB_SUBMIT_QUESTION = 'submit_question';
    public const TAB_SUBMIT_QUESTION_ANSWER = 'submit_question_answer';
    public const TAB_TASK = 'task';
    public const TAB_TASK_CONTRIBUTION = 'task_contribution';
    public const TAB_TASK_CATEGORY = 'task_category';
    /* address */
    public const TAB_ADDRESS = 'address';
    public const TAB_POST_CONTACT = 'post_contact';
    public const TAB_PSC_REGION = 'psc_region';
    public const TAB_COUNTRY = 'country';
    public const TAB_COUNTRY_SUBDIVISION = 'country_subdivision';
    /* stored query */
    public const TAB_STORED_QUERY = 'stored_query';
    public const TAB_STORED_QUERY_PARAM = 'stored_query_parameter';
    public const TAB_STORED_QUERY_TAG = 'stored_query_tag';
    public const TAB_STORED_QUERY_TAG_TYPE = 'stored_query_tag_type';
    /* payment */
    public const TAB_PAYMENT = 'payment';
    /* fyziklani applications */
    public const TAB_FYZIKLANI_TEAM = 'fyziklani_team';
    public const TAB_FYZIKLANI_TEAM_MEMBER = 'fyziklani_team_member';
    public const TAB_FYZIKLANI_TEAM_TEACHER = 'fyziklani_team_teacher';
    /* game */
    public const TAB_FYZIKLANI_TASK = 'fyziklani_task';
    public const TAB_FYZIKLANI_SUBMIT = 'fyziklani_submit';
    public const TAB_FYZIKLANI_GAME_SETUP = 'fyziklani_game_setup';
    public const TAB_FYZIKLANI_ROOM = 'fyziklani_room';
    public const TAB_FYZIKLANI_SEAT = 'fyziklani_seat';
    public const TAB_FYZIKLANI_TEAM_SEAT = 'fyziklani_team_seat';
    /* schedule */
    public const TAB_SCHEDULE_GROUP = 'schedule_group';
    public const TAB_SCHEDULE_ITEM = 'schedule_item';
    public const TAB_PERSON_SCHEDULE = 'person_schedule';
    public const TAB_SCHEDULE_PAYMENT = 'schedule_payment';
    /* warehouse */
    public const TAB_WAREHOUSE_PRODUCER = 'warehouse_producer';
    public const TAB_WAREHOUSE_PRODUCT = 'warehouse_product';
    public const TAB_WAREHOUSE_ITEM = 'warehouse_item';
}
