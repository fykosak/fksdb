#FROM php:8.2-apache-bookworm
FROM debian:bookworm

# add sury repo
RUN apt-get update && apt-get -y install lsb-release ca-certificates curl \
	&& curl -sSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb \
	&& dpkg -i /tmp/debsuryorg-archive-keyring.deb \
	&& sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
	&& rm /tmp/debsuryorg-archive-keyring.deb

# install needed packages
ARG PHP_VERSION="8.1"
RUN apt-get update && apt install -y --no-install-suggests \
	# php
	apache2 \
	php${PHP_VERSION} \
	php${PHP_VERSION}-cli \
	php${PHP_VERSION}-common \
	php${PHP_VERSION}-curl \
	php${PHP_VERSION}-gd \
	php${PHP_VERSION}-mbstring \
	php${PHP_VERSION}-mysql \
	php${PHP_VERSION}-opcache \
	php${PHP_VERSION}-readline \
	php${PHP_VERSION}-soap \
	php${PHP_VERSION}-sqlite3 \
	php${PHP_VERSION}-xdebug \
	php${PHP_VERSION}-xml \
	php${PHP_VERSION}-xmlrpc \
	# for translations
	locales \
	# for tests and local development
	composer \
	npm \
	mariadb-client

# generate locales
RUN echo 'cs_CZ.UTF-8 UTF-8' >> /etc/locale.gen && \
	echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen && \
	locale-gen

# setup Apache
ENV APACHE_RUN_DIR=/var/run/apache2
ENV APACHE_PID_FILE=${APACHE_RUN_DIR}/apache2.pid
ENV APACHE_RUN_USER=fksdb
ENV APACHE_RUN_GROUP=fksdb
ENV APACHE_LOG_DIR=/var/log/apache2

COPY docker/config/apache.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

# install mariadb client config
COPY docker/config/mysql-client.cnf /etc/mysql/mariadb.conf.d/90-client.cnf

# set bash to use colors
ENV TERM xterm-256color

# create non-root user
ARG UID=1000
RUN useradd -ms /bin/bash fksdb --uid ${UID}
RUN chown -R fksdb:fksdb ${APACHE_RUN_DIR}
RUN chown -R fksdb:fksdb ${APACHE_LOG_DIR}

USER fksdb

WORKDIR /app

ENTRYPOINT /usr/sbin/apache2 -D FOREGROUND
