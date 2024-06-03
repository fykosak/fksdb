# Install PHP dependencies and init translations
FROM composer as composer-builder
WORKDIR /usr/src/fksdb
COPY composer.json composer.lock .
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --ignore-platform-reqs

FROM python:latest as python-builder
WORKDIR /usr/src/fksdb
COPY --from=composer-builder /usr/src/fksdb .
COPY i18n i18n
RUN ./i18n/compile-js.py

# Compile Javascript and CSS
FROM node:latest AS node-builder
WORKDIR /usr/src/fksdb
COPY package.json package-lock.json .
RUN npm install
COPY --from=python-builder /usr/src/fksdb .
COPY . .
RUN npm run build

# Final image
FROM php:8.2-apache
RUN apt-get update && apt install -y \
    gettext \
	libfreetype-dev \
	libjpeg62-turbo-dev \
	libpng-dev \
	libxml2-dev \
    locales
RUN EXTENSIONS="gettext soap opcache gd pdo pdo_mysql" \
	&& docker-php-ext-install $EXTENSIONS \
	&& docker-php-ext-enable $EXTENSIONS
RUN a2enmod rewrite

RUN echo 'cs_CZ.UTF-8 UTF-8' >> /etc/locale.gen && \
	echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen && \
	locale-gen

USER www-data
WORKDIR /var/www/fksdb
RUN mkdir temp && chmod 777 temp
COPY --chown=www-data --from=node-builder /usr/src/fksdb .
RUN ./i18n/compile.sh
