#!/bin/bash


if [ "x$2" = "x" ] ; then
	echo "Usage: $0 fksdb-dir branch-name [remote]"
	exit 1
fi

function check_args {
	if [ "$fksdb_dir" = "${fksdb_dir#/}" ] ; then
		echo "$fksdb_dir is not absolute path"
		return 1
	fi
	if [ ! -d "$fksdb_dir" ] ; then
		echo "$fksdb_dir is doesn't exist"
		return 1
	fi
}

function mark_unavailabe {
	sed "s#^// require '\.main#require '.main#" -i "$fksdb_dir/www/index.php"
	echo `date` "Website marked unavailable."
}

function mark_availabe {
	sed "s#^require '\.main#// require '.main#" -i "$fksdb_dir/www/index.php"
	echo `date` "Website marked available."
}

function current_branch {
	git branch | sed -n -e "/^\*/{s/^\* //;p}"
}

function update_files {
	cd "$fksdb_dir"
	git fetch $remote
	echo "Fetched data from $remote"
	if git diff --quiet $branch $remote/$branch ; then
		echo "No new commits on $branch."

		cd -
		return 1
	else
		mark_unavailabe
		git merge --ff-only $remote/$branch || return 1
		git submodule init || return 1
		git submodule update || return 1
		echo "Merged data from $remote/$branch into $branch"
		mark_availabe
		rev=`git rev-parse HEAD`
		echo "Installed revision $rev"

		cd -
		return 0
	fi
}

function post_update {
	rm -rf "$fksdb_dir"/temp/*
	"$fksdb_dir/i18n/compile.sh"
}

#
# End of functions, begin work
#

fksdb_dir="$1"
branch="$2"
remote="${3:-origin}"

lockfile="$fksdb_dir/.install-lock"

check_args || exit 1
	
export GIT_DIR="$fksdb_dir/.git"

if [ `current_branch` != $branch ] ; then
	echo "Working copy not on branch $branch."
	exit 1
fi

(
	flock -n -w 10 9 || exit 1 
	if update_files ; then
		post_update
	fi
) 9>$lockfile 
