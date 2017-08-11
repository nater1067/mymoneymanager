FROM php:7.0-fpm

RUN /usr/local/bin/docker-php-ext-install pdo pdo_mysql
RUN /usr/local/bin/docker-php-ext-install bcmath

COPY . /app
COPY ./parameters-production.yml /app/config/parameters.yml

RUN chown www-data -R /app/var

WORKDIR /app
