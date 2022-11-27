#!/bin/bash

DB_NAME=fksdb_test

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)
COUNT=8

if [ "x$1" != "x" ] ; then
	COUNT=$1
fi

for i in `seq 1 $COUNT` ; do
	INS_NAME=$DB_NAME$i
	mysql -e "DROP DATABASE \`$INS_NAME\`" 2>/dev/null

	mysql -e "CREATE DATABASE \`$INS_NAME\` CHARACTER SET utf8  COLLATE utf8_czech_ci"

	for s in ${SCRIPT_PATH}/../sql/*.sql ; do
		mysql -e $INS_NAME <$s && echo "Executed $s"
	done

done

