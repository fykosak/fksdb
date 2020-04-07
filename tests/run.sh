#!/bin/bash

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)

if [ "x$1" == "x" ] ; then
  $SCRIPT_PATH/../vendor/bin/tester -p php -s -c $SCRIPT_PATH/php.ini $SCRIPT_PATH
else
  $SCRIPT_PATH/../vendor/bin/tester -p php -s -c $SCRIPT_PATH/php.ini $*
fi
