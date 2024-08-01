FROM php:8.2-apache
RUN apt-get update && apt install -y \
    gettext \
	libfreetype-dev \
	libjpeg62-turbo-dev \
	libpng-dev \
	libxml2-dev \
    locales\
	unzip
RUN EXTENSIONS="gettext soap opcache gd pdo pdo_mysql" \
	&& docker-php-ext-install $EXTENSIONS \
	&& docker-php-ext-enable $EXTENSIONS
RUN a2enmod rewrite

#RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
#RUN nvm install 20

RUN curl -fsSL https://fnm.vercel.app/install | bash && . /root/.bashrc && fnm use --install-if-missing 22

RUN echo 'cs_CZ.UTF-8 UTF-8' >> /etc/locale.gen && \
	echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen && \
	locale-gen

USER www-data
WORKDIR /var/www/fksdb