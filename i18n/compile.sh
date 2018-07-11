#!/bin/bash


# Locale directory
LOCALE=i18n/locale


# ---------------------
# implementation
# ---------------------

ROOT=`dirname ${BASH_SOURCE[0]}`/..
for file in `find $ROOT/$LOCALE -iname "messages.po"` ; do
  msgfmt -o ${file%.po}.mo $file
done

