FROM alpine:3.14
LABEL Maintainer="Tim de Pater <code@trafex.nl>"
LABEL Description="Lightweight container with Nginx 1.20 & PHP 8.0 based on Alpine Linux."

ENV APP_ENV=prod
ENV INSTALLATION_TYPE=docker
ENV ELASTICSEARCH_URL=$ELASTICSEARCH_URL
ENV ELASTICSEARCH_USERNAME=$ELASTICSEARCH_USERNAME
ENV ELASTICSEARCH_PASSWORD=$ELASTICSEARCH_PASSWORD
ENV SSL_VERIFY_PEER=$SSL_VERIFY_PEER
ENV SSL_VERIFY_HOST=$SSL_VERIFY_HOST

ENV SECRET_REGISTER=$SECRET_REGISTER

# Install packages and remove default server definition
RUN apk --no-cache add php8 php8-fpm php8-opcache php8-json php8-openssl php8-curl \
    php8-zlib php8-xml php8-simplexml php8-phar php8-intl php8-dom php8-xmlreader php8-ctype php8-session \
    php8-tokenizer php8-pdo php8-pdo_mysql php8-pdo_pgsql php8-iconv php8-zip \
    php8-gmp php8-mbstring nginx supervisor nodejs nodejs npm curl

# Create symlink so programs depending on `php` still function
RUN ln -s /usr/bin/php8 /usr/bin/php

# Configure nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/privkey.pem /etc/nginx/privkey.pem
COPY docker/fullchain.pem /etc/nginx/fullchain.pem
RUN rm -f /etc/nginx/conf.d/default.conf

# Configure PHP-FPM
COPY docker/fpm-pool.conf /etc/php8/php-fpm.d/www.conf
COPY docker/php.ini /etc/php8/conf.d/custom.ini

# Configure supervisord
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create folders
RUN mkdir -p /var/www/html && mkdir -p /.composer && mkdir -p /.npm

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody.nobody /var/www/html && \
  chown -R nobody.nobody /.composer && \
  chown -R nobody.nobody /.npm && \
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

RUN npm install
RUN npm run build

COPY --chown=nobody .env.dist .env

# Expose the port nginx is reachable on
EXPOSE 8080

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping
