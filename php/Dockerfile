FROM wordpress:6.9.4-php8.2-fpm

RUN apt-get update -qq \
    && apt-get install -y -qq liblz4-dev \
    && pecl install redis -- --enable-redis-lz4 --with-liblz4 \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/pear
