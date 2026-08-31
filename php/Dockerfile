FROM wordpress:6.9.4-php8.2-fpm

# libfcgi-bin aporta `cgi-fcgi`, necesario para el healthcheck real del pool
# PHP-FPM (consulta ping.path). Sin el, el unico chequeo posible era
# `pidof php-fpm`, que devuelve "healthy" aunque el pool este saturado y
# nginx este devolviendo 502.
RUN apt-get update -qq \
    && apt-get install -y -qq liblz4-dev libfcgi-bin \
    && pecl install redis -- --enable-redis-lz4 --with-liblz4 \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY fpm-healthcheck.sh /usr/local/bin/fpm-healthcheck
RUN chmod +x /usr/local/bin/fpm-healthcheck
