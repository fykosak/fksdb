#!/bin/bash

set -e

# check required variables
if [ -z "$PUID" ]; then
	echo 'Environment variable $PUID not specified'
	exit 1
fi

if [ -z "$GUID" ]; then
	echo 'Environment variable $GUID not specified'
	exit 1
fi

# create user
if [ ! $(getent group fksdb) ] && [ ! $(getent group $GUID) ]; then
	groupadd --gid $GUID fksdb
	echo "Group fksdb with GID $GUID created."
fi
if [ ! $(getent passwd fksdb) ] && [ ! $(getent passwd $PUID) ]; then
	useradd --uid $PUID --gid $GUID fksdb
	echo "User fksdb with UID $PUID created."
fi

# ensure access to writable directories
chown "$PUID:$GUID" /app/temp /app/log /app/upload /var/log/apache2/other_vhosts_access.log

# register cron
echo "Register cron"
printenv | grep "MAILTO" >> /etc/environment # apply whitelisted ENV for cron
crontab -u fksdb /app/docker/config/crontab

# run cron
echo "Starting cron"
cron -f &

# run apache
echo "Starting apache"
su -s '/bin/bash' -c "apache2-foreground" fksdb
