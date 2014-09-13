#!/bin/bash

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)

export LC_ALL="cs_CZ.utf8"
export LC_MESSAGES="cs_CZ.utf8"

if [ "x$1" == "x" ] ; then
	$SCRIPT_PATH/../tester/Tester/tester -p php5 -c $SCRIPT_PATH/php.ini $SCRIPT_PATH
else
	$SCRIPT_PATH/../tester/Tester/tester -p php5 -c $SCRIPT_PATH/php.ini $*
fi

