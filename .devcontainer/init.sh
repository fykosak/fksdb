set -xe

git submodule init && git submodule update
composer install
npm install
npm run build
cp .devcontainer/config.local.neon app/config/config.local.neon
cat sql/schema.sql sql/views.sql sql/initval.sql sql/stored_query.sql | mysql -h 127.0.0.1 -u fksdb -pfksdb fksdb
i18n/compile.sh
rm -rf temp/cache