FROM nginx:latest

RUN ["mv", "/etc/nginx/conf.d/default.conf", "/"]

COPY ./docker/nginx/symfony3.conf /etc/nginx/conf.d/symfony3.conf

COPY . /app
