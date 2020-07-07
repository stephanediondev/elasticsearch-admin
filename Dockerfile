FROM nginx:latest

RUN rm /etc/nginx/conf.d/default.conf

COPY nginx/elasticsearch-admin.conf /etc/nginx/conf.d/

FROM php:7.4-fpm

RUN apt-get update

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

RUN composer install --no-dev --no-interaction --optimize-autoloader

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -
RUN apt-get install -y nodejs

RUN npm install --silent
RUN npm run --silent build
