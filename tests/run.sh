#!/bin/bash

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)

$SCRIPT_PATH/../tester/Tester/tester -p php5 -c $SCRIPT_PATH/php.ini $SCRIPT_PATH

