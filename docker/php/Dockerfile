FROM php:7.4-fpm

RUN apt-get update
RUN apt-get install -y git libmcrypt-dev libicu-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

RUN docker-php-ext-install intl iconv
