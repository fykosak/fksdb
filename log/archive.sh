#!/bin/bash

DATE=`date +%Y-%m-%d`
DIR=archive-$DATE

if [ -d $DIR ] ; then
	echo "Already archived today"
	exit 1
fi

mkdir -p $DIR/mail

mv *.log exception-* -t $DIR
mv mail/*.txt -t $DIR/mail

tar -cjf ${DIR}.tbz $DIR && rm -r $DIR

