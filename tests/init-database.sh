#!/bin/bash

DB_NAME=fksdb_test

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)

mysql -e "DROP DATABASE \`$DB_NAME\`" 2>/dev/null

mysql -e "CREATE DATABASE \`$DB_NAME\`"

mysql $DB_NAME <${SCRIPT_PATH}/../sql/schema.sql && echo "Created schema"

mysql $DB_NAME <${SCRIPT_PATH}/../sql/views.sql && echo "Created views"

mysql $DB_NAME <${SCRIPT_PATH}/../sql/initval.sql && echo "Initialized data"

