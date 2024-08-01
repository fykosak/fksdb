FROM mcr.microsoft.com/devcontainers/php:0-8.1-bullseye

RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y \
    gettext \
	libfreetype-dev \
	libjpeg62-turbo-dev \
	libpng-dev \
	libxml2-dev \
    locales \
    mariadb-client \
    libxslt-dev \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

RUN EXTENSIONS="gettext soap opcache gd pdo mysqli pdo_mysql xsl" \
	&& docker-php-ext-install $EXTENSIONS \
	&& docker-php-ext-enable $EXTENSIONS

RUN sudo a2enmod rewrite

COPY ./apache-site.conf /etc/apache2/sites-enabled/000-default.conf

RUN echo 'cs_CZ.UTF-8 UTF-8' >> /etc/locale.gen && \
	echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen && \
	locale-gen

USER www-data

WORKDIR /workspaces/fksdb

