#!/bin/bash

LOCKFILE=.install-lock

if [ "x$1" = "x" ] ; then
	echo "Usage: $0 branch-name"
	exit 1
fi

branch=$1

function mark_unavailabe {
	sed "s#^// require '\.main#require '.main#" -i www/index.php
	echo `date` "Website marked unavailable."
}

function mark_availabe {
	sed "s#^require '\.main#// require '.main#" -i www/index.php
	echo `date` "Website marked available."
}

function current_branch {
	git branch | sed -n -e "/^\*/{s/^\* //;p}"
}

function update_files {
	git fetch origin
	echo "Fetched data from $branch"
	if git diff --quiet $branch origin/$branch ; then
		echo "No new commits on $branch."
		return 1
	else
		git merge --ff-only origin/$branch || return 1
		git submodule init || return 1
		git submodule update || return 1
		echo "Merged data from origin/$branch into $branch"
		return 0
	fi
}

function post_update {
	rm -rf temp/*
	i18n/compile.sh
}

	

if [ `current_branch` != $branch ] ; then
	echo "Working copy not on branch $branch."
	exit 1
fi

(
	flock -n -w 10 9 || exit 1 
	mark_unavailabe
	if update_files ; then
		post_update
	fi

	mark_availabe
	rev=`git rev-parse HEAD`
	echo "Installed revision $rev"
) 9>$LOCKFILE 
