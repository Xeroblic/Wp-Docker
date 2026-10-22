# PHP 8.2.35+ obligatorio: corrige PHP2Shell (CVE-2026-109618 / CVE-2026-109619,
# RCE sin autenticacion, CVSS 9.2). La serie 6.9.x de la imagen oficial dejo de
# reconstruirse en mayo de 2026 y quedo fijada en PHP 8.2.31 (vulnerable).
# El core de WordPress de la imagen solo se copia a un volumen vacio: en
# despliegues existentes este cambio actualiza el runtime PHP, no el sitio.
FROM wordpress:7.1.3-php8.2-fpm

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
