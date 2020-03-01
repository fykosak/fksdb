#!/bin/bash

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)


if [ "x$1" == "x" ] ; then
  ./vendor/bin/tester -p php -s -c ./tests/php.ini ./tests
	#$SCRIPT_PATH/../vendor/nette/tester/src/tester -p php -c $SCRIPT_PATH/php.ini $SCRIPT_PATH
else
  ./vendor/bin/tester -p php -s -c ./tests/php.ini ./tests
	#$SCRIPT_PATH/../vendor/nette/tester/src/tester -p php -c $SCRIPT_PATH/php.ini $*
fi

