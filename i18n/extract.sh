#!/bin/bash

# Space separated list of directories to scan for PHP/Latte files
PHP_FILES="app libs"
LATTE_FILES="app libs"

# Output
POT_FILE=i18n/messages.pot

# Locale directory
LOCALE=i18n/locale


# ---------------------
# implementation
# ---------------------

LATTE_SUFFIX=latte2php
ROOT=`dirname ${BASH_SOURCE[0]}`/..

function latte2php {
	sed "/{_'/{:next;/'}/{s/{_'\([^}]*\)'}/<?php _('\1') ?>/g;b;};N;b next;}" $1 | \
	sed "/{_\"/{:next;/\"}/{s/{_\"\([^}]*\)\"}/<?php _(\"\1\") ?>/g;b;};N;b next;}" >$1.$LATTE_SUFFIX
}

PHP_FILES=`echo "$PHP_FILES" | sed 's#^#'$ROOT'/#;s# # '$ROOT'/#g'`
LATTE_FILES=`echo "$LATTE_FILES" | sed 's#^#'$ROOT'/#;s# # '$ROOT'/#g'`
POT_FILE=$ROOT/$POT_FILE

#
# Generate POT files
#
find $PHP_FILES -iname "*.php" | xargs xgettext -L PHP --from-code=utf-8 -o $POT_FILE

for file in `find $LATTE_FILES -iname "*.latte"` ; do
	latte2php "$file"
done

find $LATTE_FILES -iname "*.$LATTE_SUFFIX" | xargs xgettext -L PHP --from-code=utf-8 -j -o $POT_FILE
find $LATTE_FILES -iname "*.$LATTE_SUFFIX" | xargs rm

#
# Merge to PO files
#
find $ROOT/$LOCALE -iname "messages.po" -exec msgmerge -U {} $POT_FILE \;

