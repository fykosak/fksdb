#!/bin/bash

find "$1" -name "*.php" | while read f ; do
	NS=`dirname $f`
	NaS=`echo $NS | sed 's_/_\\\\\\\\_g'`
	echo "$NaS"
	sed -i "s/^namespace .*\$/namespace $NaS;/" $f
done
