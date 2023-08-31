#!/bin/bash

# Space separated list of directories to scan for various files
PHP_FILES="app libs"
LATTE_FILES="app libs data"
NEON_FILES="app/config data/events"
TSX_FILES="app"

# Output
POT_FILE=i18n/messages.pot

# Locale directory
LOCALE=i18n/locale

# ---------------------
# implementation
# ---------------------

LATTE_SUFFIX=latte2php
NEON_SUFFIX=neon2php
TSX_SUFFIX=tsx2php
TS_SUFFIX=ts2php
ROOT=`dirname ${BASH_SOURCE[0]}`/..

# TODO find ngettext when on multiple lines
# TODO catch bad matches when ) in msgId
function latte2php {
	perl -0777 -pe "s/{?_\(?'([^}]*)'\)?}?/<?php _('\1') ?>/g" $1 |\
	perl -0777 -pe "s/{?_\(?\"([^}]*)\"\)?}?/<?php _(\"\1\") ?>/g" |\
	perl -0777 -pe "s/ngettext\((.*),(.*),(.*)\)/<?php ngettext(\1,\2,\3) ?>/g" >$1.$LATTE_SUFFIX
}

function neon2php {
	sed "s/_(\(['\"].*\))[^)]*\$/<?php _(\1) ?>/" $1 | \
	sed "s/_(\([^'\"].*\))[^)]*\$/<?php _('\1') ?>/" >$1.$NEON_SUFFIX
}

function tsx2php {
	sed "s/translator.getLocalizedText(\('.*'\),\s?'.*')/\<\?php _(\1)\?\>/" $1 | \
	sed "s/translator.getText(\('.*'\))/\<\?php _(\1)\?\>/" | \
	sed "s/translator.nGetText(\(.*\),\(.*\),\(.*\))/\<\?php ngettext(\1,\2,\3)\?\>/" >$1.$TSX_SUFFIX
}

function ts2php {
	sed "s/translator.getLocalizedText(\('.*'\),\s'.*')/\<\?php _(\1)\?\>/" $1 | \
	sed "s/translator.getText(\('.*'\))/\<\?php _(\1)\?\>/" >$1.$TS_SUFFIX
}

PHP_FILES=`echo "$PHP_FILES" | sed 's#^#'$ROOT'/#;s# # '$ROOT'/#g'`
LATTE_FILES=`echo "$LATTE_FILES" | sed 's#^#'$ROOT'/#;s# # '$ROOT'/#g'`
NEON_FILES=`echo "$NEON_FILES" | sed 's#^#'$ROOT'/#;s# # '$ROOT'/#g'`
TSX_FILES=`echo "$TSX_FILES" | sed 's#^#'$ROOT'/#;s# # '$ROOT'/#g'`
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

for file in `find $NEON_FILES -iname "*.neon"` ; do
	neon2php "$file"
done

find $NEON_FILES -iname "*.$NEON_SUFFIX" | xargs xgettext -L PHP --from-code=utf-8 -j -o $POT_FILE
find $NEON_FILES -iname "*.$NEON_SUFFIX" | xargs rm

for file in `find $TSX_FILES -iname "*.tsx"` ; do
	tsx2php "$file"
done
find $TSX_FILES -iname "*.$TSX_SUFFIX" | xargs xgettext -L PHP --from-code=utf-8 -j -o $POT_FILE
find $TSX_FILES -iname "*.$TSX_SUFFIX" | xargs rm

for file in `find $TSX_FILES -iname "*.ts"` ; do
	ts2php "$file"
done

find $TSX_FILES -iname "*.$TS_SUFFIX" | xargs xgettext -L PHP --from-code=utf-8 -j -o $POT_FILE
find $TSX_FILES -iname "*.$TS_SUFFIX" | xargs rm

#
# Merge to PO files
#
find $ROOT/$LOCALE -iname "messages.po" -exec msgmerge --sort-by-file -U {} $POT_FILE \;

