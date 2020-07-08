FROM php:fpm-alpine3.11

LABEL Maintainer="Tim de Pater <code@trafex.nl>" \
      Description="Lightweight container with Nginx 1.18 & PHP-FPM 7.3 based on Alpine Linux."

#https://github.com/codecasts/php-alpine
ADD https://dl.bintray.com/php-alpine/key/php-alpine.rsa.pub /etc/apk/keys/php-alpine.rsa.pub

RUN apk --update add ca-certificates && \
    echo "https://dl.bintray.com/php-alpine/v3.11/php-7.4" >> /etc/apk/repositories

# Install packages and remove default server definition
RUN apk --update add php php-fpm php-opcache php-json php-openssl php-curl \
    php-zlib php-xml php-phar php-intl php-dom php-xmlreader php-ctype php-session \
    php-tokenizer php-pdo php-pdo_mysql php-pdo_pgsql php-iconv \
    php-mbstring nginx supervisor nodejs nodejs npm curl && \
    rm /etc/nginx/conf.d/default.conf

RUN apk add libzip-dev && docker-php-ext-configure zip && docker-php-ext-install zip

# Configure nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
COPY docker/fpm-pool.conf /etc/php7/php-fpm.d/www.conf
COPY docker/php.ini /etc/php7/conf.d/custom.ini

# Configure supervisord
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup document root
RUN mkdir -p /var/www/html
RUN mkdir -p /.composer
RUN mkdir -p /.npm

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R nobody.nobody /var/www/html && \
  chown -R nobody.nobody /.composer && \
  chown -R nobody.nobody /.npm && \
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
RUN composer install --optimize-autoloader --no-interaction --no-progress

RUN npm install --silent
RUN npm run --silent build

COPY --chown=nobody .env.dist .env

# Expose the port nginx is reachable on
EXPOSE 8080

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping
