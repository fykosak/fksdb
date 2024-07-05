set -xe

git submodule init && git submodule update
composer install
npm install
npm run build
cp .devcontainer/config.local.neon app/config/config.local.neon
i18n/compile.sh
rm -rf temp/cache