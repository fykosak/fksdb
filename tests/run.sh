#!/bin/bash

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)


if [ "x$1" == "x" ] ; then
	$SCRIPT_PATH/../tester/Tester/tester -p php -c $SCRIPT_PATH/php.ini $SCRIPT_PATH
else
	$SCRIPT_PATH/../tester/Tester/tester -p php -c $SCRIPT_PATH/php.ini $*
fi

