FROM debian:bullseye

# Base system
# TODO  bump php to 8.1
RUN apt-get update && \
	apt-get install -y --no-install-suggests \
		apache2 \
		php7.4 \
		php7.4-cli \
		php7.4-common \
		php7.4-curl \
		php7.4-json \
		php7.4-mbstring \
		php7.4-mysql \
		php7.4-opcache \
		php7.4-readline \
		php7.4-soap \
		php7.4-sqlite3 \
		php7.4-xdebug \
		php7.4-xml \
		php7.4-xmlrpc \
		# for app
		composer \
		npm \
		# for tests
		mariadb-client

RUN a2enmod rewrite

# Prepare app environment
COPY . /srv/fksdb

WORKDIR /srv/fksdb
RUN composer install
RUN npm install
RUN npm run build
# BEWARE this tranceeds to host too when bind mounted(?)
RUN chown -R www-data log temp upload
# this must be bind-mounted from dev, prod config TBD
#COPY containers/config.local.neon app/config/config.local.neon

# Small files at the end
COPY containers/app/ports.conf /etc/apache2/ports.conf
COPY containers/app/fksdb.conf /etc/apache2/sites-enabled/fksdb.conf



ENTRYPOINT /usr/sbin/apache2ctl -DFOREGROUND -k start


