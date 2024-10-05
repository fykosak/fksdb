#!/bin/bash
if [ ! -f app/config/config.local.neon ]; then
	cp app/config/config.local.neon.sample app/config/config.local.neon
fi
./i18n/compile.sh
python3 ./i18n/compile-js.py
composer install
npm install
npm run build