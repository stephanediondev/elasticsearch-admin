FROM php:7.4-fpm

RUN apt-get update
#RUN apt-get install -y git libmcrypt-dev libicu-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

#RUN docker-php-ext-install intl iconv

ARG USER_ID=501
ARG GROUP_ID=20

RUN userdel -f www-data &&\
    if getent group www-data ; then groupdel www-data; fi &&\
    groupadd -g ${GROUP_ID} www-data &&\
    useradd -l -u ${USER_ID} -g www-data www-data &&\
    install -d -m 0755 -o www-data -g www-data /home/www-data
