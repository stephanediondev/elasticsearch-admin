FROM ubuntu:latest

RUN apt-get update

RUN apt-get --yes install nginx php-fpm

RUN apt-get --yes install apt-utils libicu-dev libzip-dev libonig-dev libcurl4-openssl-dev unzip

RUN docker-php-ext-install zip intl curl

COPY nginx/elasticsearch-admin.conf /etc/nginx/conf.d/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -
RUN apt-get install --yes nodejs

EXPOSE 80

#USER www-data

#COPY . /var/www/html/

#RUN composer install --no-dev --no-interaction --optimize-autoloader

#RUN npm install --silent
#RUN npm run --silent build
