FROM php:fpm-alpine

RUN apk add --no-cache git postgresql-dev zip unzip zlib-dev \
	&& docker-php-ext-install pdo_pgsql zip

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

COPY .bash_aliases /root/.bash_aliases
RUN echo '. /root/.bash_aliases' >> ~/.bashrc

RUN rm -rf /var/cache/apk/*