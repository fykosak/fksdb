#!/bin/bash

DB_NAME=fksdb_test

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)


COUNT=8

if [ "x$1" != "x" ] ; then
	COUNT=$1
fi

for i in `seq 1 $COUNT` ; do

mysql $DB_NAME$i <<EOD
DELETE FROM email_message;
DELETE FROM submit;
DELETE FROM task;

DELETE FROM fyziklani_submit;
DELETE FROM fyziklani_task;

DELETE FROM person_schedule;
DELETE FROM schedule_item;
DELETE FROM schedule_group;

DELETE FROM event_participant;
DELETE FROM fyziklani_team_teacher;
DELETE FROM fyziklani_team_member;
DELETE FROM fyziklani_team;
DELETE FROM fyziklani_game_setup;
DELETE FROM event_org;
DELETE FROM event_grant;
DELETE FROM event;

DELETE FROM org;
DELETE FROM person_history;
DELETE FROM contestant;
DELETE FROM contest_year;
DELETE FROM school;
DELETE FROM address;
DELETE FROM auth_token;
DELETE FROM login;
DELETE FROM person;
EOD
	echo "Cleared $DB_NAME$i"

done

