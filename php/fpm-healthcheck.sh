#!/bin/sh
# Healthcheck real de PHP-FPM: comprueba que el pool ACEPTA y RESPONDE una
# peticion FastCGI, no solo que el proceso maestro exista.
#
# Un `pidof php-fpm` pasa aunque los 12 workers esten bloqueados y nginx
# devuelva 502 a todo el mundo; este chequeo falla en ese escenario y permite
# que Docker/EasyPanel reinicien el contenedor.
set -eu

ADDR="${FPM_PING_ADDR:-127.0.0.1:9000}"
PATH_="${FPM_PING_PATH:-/ping}"

OUT=$(
  env -i \
    SCRIPT_NAME="$PATH_" \
    SCRIPT_FILENAME="$PATH_" \
    REQUEST_METHOD=GET \
    QUERY_STRING= \
    cgi-fcgi -bind -connect "$ADDR" 2>&1
) || exit 1

echo "$OUT" | grep -q "pong" || exit 1
