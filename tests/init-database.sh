#!/bin/bash

DB_NAME=fksdb_test
COUNT=8

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)

source $SCRIPT_PATH/functions.sh

if [ "x$1" != "x" ] ; then
	COUNT=$1
fi

sql -e "DROP USER '$MYSQL_USER-ro'@'%'" 2>/dev/null
sql -e "CREATE USER '$MYSQL_USER-ro'@'%' IDENTIFIED BY '$MYSQL_PASSWORD'; GRANT SELECT ON *.* TO '$MYSQL_USER-ro'@'%'"

for i in `seq 1 $COUNT` ; do
	INS_NAME=$DB_NAME$i
	sql -e "DROP DATABASE \`$INS_NAME\`" 2>/dev/null

	sql -e "CREATE DATABASE \`$INS_NAME\` CHARACTER SET utf8  COLLATE utf8_czech_ci"

	for s in ${SCRIPT_PATH}/../sql/*.sql ; do
		sql $INS_NAME <$s && echo "Executed $s"
	done
done
