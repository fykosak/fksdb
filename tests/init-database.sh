#!/bin/bash

DB_NAME=fksdb_test

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)
COUNT=8

if [ "x$1" != "x" ] ; then
	COUNT=$1
fi

for i in `seq 1 $COUNT` ; do
	INS_NAME=$DB_NAME$i
	mysql -h 127.0.0.1 -e "DROP DATABASE \`$INS_NAME\`" 2>/dev/null

	mysql -h 127.0.0.1 -e "CREATE DATABASE \`$INS_NAME\`"

	mysql -h 127.0.0.1 $INS_NAME <${SCRIPT_PATH}/../sql/schema.sql && echo "Created schema $INS_NAME"

	mysql -h 127.0.0.1 $INS_NAME <${SCRIPT_PATH}/../sql/views.sql && echo "Created views $INS_NAME"

	mysql -h 127.0.0.1 $INS_NAME <${SCRIPT_PATH}/../sql/initval.sql && echo "Initialized data $INS_NAME"
done

