#!/bin/bash

DB_NAME=fksdb_test

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)


COUNT=8

if [ "x$1" != "x" ] ; then
	COUNT=$1
fi

for i in `seq 1 $COUNT` ; do

mysql $DB_NAME$i <<EOD
DELETE FROM submit;
DELETE FROM task;

DELETE FROM fyziklani_submit;
DELETE FROM fyziklani_task;

DELETE FROM person_schedule;
DELETE FROM schedule_item;
DELETE FROM schedule_group;

DELETE FROM e_tsaf_participant;
DELETE FROM e_dsef_participant;
DELETE FROM e_dsef_group;
DELETE FROM e_fyziklani_participant;
DELETE FROM event_participant;
DELETE FROM e_fyziklani_team;
DELETE FROM fyziklani_game_setup;
DELETE FROM event;

DELETE FROM org;
DELETE FROM person_history;
DELETE FROM contestant_base;
DELETE FROM contest_year;
DELETE FROM school;
DELETE FROM address;
DELETE FROM global_session;
DELETE FROM auth_token;
DELETE FROM login;
DELETE FROM person;
EOD
	echo "Cleared $DB_NAME$i"

done

