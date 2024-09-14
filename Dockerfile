ARG ALPINE_VERSION=3.20
FROM alpine:${ALPINE_VERSION}
LABEL Maintainer="Tim de Pater <code@trafex.nl>"
LABEL Description="Lightweight container with Nginx 1.22 & PHP 8.2 based on Alpine Linux edge"

ENV APP_ENV=prod
ENV INSTALLATION_TYPE=docker
ENV ELASTICSEARCH_URL=$ELASTICSEARCH_URL
ENV ELASTICSEARCH_USERNAME=$ELASTICSEARCH_USERNAME
ENV ELASTICSEARCH_PASSWORD=$ELASTICSEARCH_PASSWORD
ENV ELASTICSEARCH_API_KEY=$ELASTICSEARCH_API_KEY
ENV SSL_VERIFY_PEER=$SSL_VERIFY_PEER
ENV SSL_VERIFY_HOST=$SSL_VERIFY_HOST

ENV SECRET_REGISTER=$SECRET_REGISTER

# Install packages and remove default server definition
RUN apk --no-cache add php82 php82-fpm php82-opcache php82-json php82-openssl php82-curl php82-zlib php82-fileinfo \
    php82-xml php82-simplexml php82-phar php82-intl php82-dom php82-xmlreader php82-ctype php82-session php82-gd \
    php82-tokenizer php82-pdo php82-pdo_mysql php82-pdo_pgsql php82-iconv php82-zip php82-gmp php82-mbstring php82-xmlwriter \
    nginx supervisor curl

# Create symlink so programs depending on `php` still function
RUN ln -s /usr/bin/php82 /usr/bin/php

# Configure nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/privkey.pem /etc/nginx/privkey.pem
COPY docker/fullchain.pem /etc/nginx/fullchain.pem
RUN rm -f /etc/nginx/conf.d/default.conf

# Configure PHP-FPM
COPY docker/fpm-pool.conf /etc/php82/php-fpm.d/www.conf
COPY docker/php.ini /etc/php82/conf.d/custom.ini

# Configure supervisord
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create folders
RUN mkdir -p /var/www/html && mkdir -p /.composer

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody.nobody /var/www/html && \
  chown -R nobody.nobody /.composer && \
  chown -R nobody.nobody /etc/nginx && \
  chown -R nobody.nobody /run && \
  chown -R nobody.nobody /var/lib/nginx && \
  chown -R nobody.nobody /var/log/nginx

# Switch to use a non-root user from here on
USER nobody

# Add application
WORKDIR /var/www/html
COPY --chown=nobody . /var/www/html/

# Install composer from the official image
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Run composer install to install the dependencies
RUN composer install --optimize-autoloader --no-interaction --no-progress --no-dev

RUN bin/console asset-map:compile

COPY --chown=nobody .env.dist .env

# Expose the port nginx is reachable on
EXPOSE 8080

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping
