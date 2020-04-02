#!/bin/bash

SCRIPT_PATH=$(cd `dirname "${BASH_SOURCE[0]}"` && pwd)

if [ "x$1" == "x" ] ; then
  $SCRIPT_PATH/../vendor/bin/tester -p php -s -c $SCRIPT_PATH/php.ini $SCRIPT_PATH
else
  $SCRIPT_PATH/../vendor/bin/tester -p php -s -c $SCRIPT_PATH/php.ini $*
<<<<<<< HEAD
fi
=======
>>>>>>> 7b8f6df967fd2377617e4ba788e9c60c2af889f4

fi
