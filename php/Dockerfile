FROM wordpress:6.9.4-php8.2-fpm

RUN pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear
