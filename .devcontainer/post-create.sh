#!/bin/bash
cp app/config/config.local.neon.sample app/config/config.local.neon
./i18n/compile.sh
python3 ./i18n/compile-js.py
composer install
npm install
npm run build