FROM php:7.0-fpm

RUN useradd -ms /bin/bash vagrant

COPY . /app

RUN chown www-data -R /app/var

WORKDIR /app
