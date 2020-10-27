#!/bin/bash
if [ "x$3" = "x" ] ; then
	echo "Usage: $0 <URL> <request-file> <response-file> [<username> <password>]"
	echo "       Response is written to <response-file>, can be '-' for stdout."
	echo "       When <username> and <password> are set, --FILLUSERNAME-- and --FILLPASSWORD--"
	echo "       are replaced in the request file."
	exit 1
fi

if [ -n "$4" -a -n "$5" ] ; then
	POST=`mktemp`
	USERNAME=`echo -n "$4" | base64 -d`
	PASSWORD=`echo -n "$5" | base64 -d`
	sed "s/--FILLUSERNAME--/$USERNAME/g;s/--FILLPASSWORD--/$PASSWORD/g" <$2 >$POST
	HAS_TEMP=true
else
	POST="$2"
fi

DEBUG="--server-response --no-check-certificate"
wget --post-file="$POST" --header="Content-Type: text/xml" --header="SOAPAction: \"GetStats\"" $DEBUG "$1" -O "$3"
[ $? = 0 ] || rm "$3"

[ -n "$HAS_TEMP" ] && rm "$POST"

true # when the condition above fails, still have exit code 0
