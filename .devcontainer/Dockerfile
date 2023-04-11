FROM mcr.microsoft.com/devcontainers/php:0-8.1-bullseye

RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y mariadb-client libxml2-dev libxslt-dev \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mysqli pdo pdo_mysql gettext soap xsl

COPY ./apache-site.conf /etc/apache2/sites-enabled/000-default.conf
RUN sudo a2enmod rewrite
