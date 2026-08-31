# Production outage — 2026-08-31

> **Alcance de este documento.** El análisis del host (journalctl, docker events,
> logs de EasyPanel) **no se ejecutó desde esta sesión**: no hay acceso al VPS
> desde el entorno de trabajo. Toda la evidencia de runtime que aparece aquí
> proviene del reporte de investigación aportado por el operador.
> Lo que **sí** se verificó de primera mano es la configuración versionada de
> este repositorio, y ahí se encontraron dos causas confirmadas por código.
> Además, **la causa del 502 persistente se reprodujo y se validó
> experimentalmente** en el stack de testing local (ver *Validación
> experimental*). Las secciones marcadas `NO VERIFICADO EN ESTA SESIÓN`
> requieren ejecutarse en el host.

## Executive summary

El sitio de producción quedó fuera de servicio devolviendo **HTTP 502**. La base
de datos y Redis se apagaron de forma limpia, mientras que el contenedor de
WordPress/PHP tuvo que ser matado a la fuerza. nginx siguió vivo todo el tiempo,
pero quedó apuntando a una dirección de PHP que ya no existía, de modo que
**siguió devolviendo 502 aun después de que PHP volviera a estar disponible**.

Hay dos problemas distintos, y conviene no mezclarlos:

1. **La caída del 31 de agosto.** Algo externo detuvo el stack. La configuración
   del repositorio garantizaba que, una vez detenido, el sitio **no se
   recuperara solo**: nginx cacheaba la IP de PHP de por vida.
2. **Un OOM semanal de `wp-cron`**, presente desde antes (15, 22 y 29 de agosto).
   Es un problema real y confirmado, pero **no es la causa de la caída del 31**.

## User-visible impact

- Sitio público inaccesible; HTTP 502 Bad Gateway en todas las rutas dinámicas.
- Inicio de la indisponibilidad: **2026-08-31 18:59:54 UTC** (14:59:54 Chile, UTC-4),
  según `FinishedAt` del contenedor de WordPress.
- Duración: **desconocida**. Se recuperó mediante intervención manual; no hay
  marca de tiempo registrada de la recuperación. Ver *Remaining unknowns*.
- Primera caída total en ~8 meses de operación.

## Timeline

| UTC | Chile | Evento | Evidencia | Clasificación |
|---|---|---|---|---|
| 2026-08-15 | — | OOM de un proceso PHP en `wp-cron` (~380-390 MiB RSS) | `journalctl -k`: *Memory cgroup out of memory: Killed process (php)* | CONFIRMED FACT |
| 2026-08-22 | — | Ídem | Ídem | CONFIRMED FACT |
| 2026-08-29 | — | Ídem | Ídem | CONFIRMED FACT |
| (permanente) | — | El healthcheck de nginx fallaba **siempre**, desde antes del incidente | Reproducido en testing: 403 por `$is_bad_bot` + `localhost`→IPv6 | CONFIRMED FACT |
| ~18:59 | ~14:59 | `db` recibe terminación y sale con **ExitCode 0** | `docker inspect` | CONFIRMED FACT |
| ~18:59 | ~14:59 | `redis` sale con **ExitCode 0** | `docker inspect` | CONFIRMED FACT |
| 18:59:54 | 14:59:54 | `wordpress` termina con **ExitCode 137**, `OOMKilled=false` | `docker inspect` (`FinishedAt=2026-08-31T18:59:54Z`) | CONFIRMED FACT |
| 18:59:54+ | 14:59:54+ | nginx sigue Up (unhealthy) y falla al conectar a `fastcgi://172.23.0.5:9000` | log de nginx: *connect() failed (113: Host is unreachable)* | CONFIRMED FACT |
| 18:59:54+ | 14:59:54+ | Respuesta pública: HTTP 502 | Observación del operador | CONFIRMED FACT |
| desconocida | desconocida | Recuperación manual | Sin registro | UNCONFIRMED |

Nota: los *Aborted connection* de MariaDB citados en el reporte llevan marcas
`01:03`–`01:08`, que **no caen dentro de la ventana del incidente**. Su zona
horaria no está declarada; ver *Database communication warnings*.

## Confirmed facts

De la evidencia de runtime aportada:

1. nginx estaba corriendo y no pudo abrir TCP contra PHP-FPM (error 113).
   **Matiz importante:** el estado `unhealthy` que mostraba `ecopc_ecopc-prod-nginx-1`
   **no es evidencia del incidente**. Ese contenedor figuraba como *unhealthy*
   de forma permanente por un healthcheck roto (punto 12). Es decir, durante la
   caída nadie tenía forma de distinguir un nginx sano de uno roto.
2. `db` y `redis` salieron con código 0 — **apagado limpio**, no un crash.
3. `wordpress` salió con **137 = 128 + SIGKILL(9)** y con `OOMKilled=false`.
   Es decir: **fue matado con SIGKILL, pero no por el OOM killer**.
4. `wp-cron` y `nginx` siguieron corriendo; el stack **no** se detuvo entero.
5. El host tenía 6.2 GiB disponibles tras el incidente y **swap = 0 B**.
6. Límites de cgroup en el momento del incidente: `wp-cron` 384 MiB,
   `wordpress` 3 GiB.
7. Existen OOM históricos de `wp-cron` con RSS de 380-390 MiB contra su
   límite de 384 MiB.

De la revisión de código hecha en esta sesión (**evidencia nueva, no incluida
en el reporte original**):

8. `nginx/nginx.conf` declaraba `upstream php { server wordpress:9000; }`.
   nginx open source resuelve los nombres de un bloque `upstream` **una sola vez,
   al arrancar**, y cachea la IP indefinidamente.
9. Ningún servicio declaraba `stop_grace_period`. El valor por defecto de Docker
   es **10 segundos**.
10. El contenedor `wordpress` corría con `pm = static` y `pm.max_children = 24`
    (aplicado con `sed` sobre `www.conf` desde el `command`), con
    `request_terminate_timeout = 120`.
11. La suma de límites de memoria del stack era **7.75 GiB** sobre un host de
    **7.5 GiB**, sin swap y con EasyPanel + Traefik + el SO compitiendo por la
    misma RAM.
12. **El healthcheck de nginx no podía pasar nunca**, por dos motivos
    acumulados y ambos anteriores al incidente:
    (a) apuntaba a `http://localhost/...` y `localhost` resuelve primero a
    `::1`, donde nginx no escucha (`listen 80;` es solo IPv4);
    (b) incluso por IPv4 devolvía **403**, porque el `map $http_user_agent
    $is_bad_bot` bloquea el User-Agent `wget`, que es el que usa el propio
    healthcheck. Reproducido y corregido — ver *Validación experimental*.
13. El healthcheck que se añadió inicialmente para `wp-cron` usaba `pgrep`,
    **que no existe en la imagen** `wordpress:6.9.4-php8.2-fpm`. Detectado al
    probar y sustituido por un heartbeat basado en fichero.

## Root cause assessment

El incidente tiene **dos causas encadenadas**, y conviene separarlas porque la
segunda es la que convirtió un reinicio en una caída.

### Causa del apagado (disparador)

**Un evento externo de gestión del stack** (deploy, stop o recreación desde
EasyPanel / Docker) detuvo `db`, `redis` y `wordpress` casi simultáneamente.

- **Confianza: MEDIA.** Es la única explicación consistente con dos salidas
  limpias (código 0) y una salida forzada, todas a la misma hora, sin OOM y sin
  presión de memoria en el host. Pero **no está probada**: falta identificar el
  actor. Ver *Remaining unknowns*.

**Por qué WordPress salió con 137 y no con 0** — esto sí queda explicado por el
código (punto 9 y 10): con `stop_grace_period` en su valor por defecto de 10 s,
un php-fpm con `pm = static`, 24 workers y peticiones de hasta 120 s no alcanza
a terminar. Docker espera 10 s, se agota la paciencia y manda SIGKILL → **137
con `OOMKilled=false`**, exactamente lo observado. Encaja además con que `db` y
`redis`, mucho más rápidos en cerrar, sí salieran con 0.

- **Confianza: ALTA.**

### Causa de la duración (lo que impidió la recuperación automática)

**El upstream estático de nginx** (punto 8). Cuando el contenedor `wordpress`
se recreó, Docker le asignó una IP nueva; nginx siguió conectando a la vieja
(`172.23.0.5`), devolviendo `113: Host is unreachable` de forma indefinida
**aunque PHP ya estuviera sano**. La única salida era recargar nginx a mano —
que es precisamente la forma en que se recuperó el sitio.

- **Confianza: ALTA.** Es determinista y reproducible: con esa configuración,
  cualquier recreación del contenedor de PHP que cambie su IP produce un 502
  permanente.

## Alternative hypotheses

| Hipótesis | Evidencia a favor | Evidencia en contra | Conclusión |
|---|---|---|---|
| **A — Stop/redeploy del stack** | 3 servicios detenidos a la misma hora; 2 con exit 0 | Ningún log identifica al actor | **Más probable**, sin confirmar |
| **B — Evento del daemon Docker** | Consistente con el patrón | `nginx`, `wp-cron`, Traefik y EasyPanel sobrevivieron — un reinicio del daemon los habría tocado a todos | Poco probable |
| **C — EasyPanel inició deploy/stop** | Es el orquestador; explicaría A | Sin revisar sus logs | **Pendiente de verificar** — la vía de investigación más prometedora |
| **D — IP obsoleta en nginx** | **Reproducida experimentalmente** (A/B: la config antigua da 502, la nueva 302) | No explica por qué se detuvieron db/redis/wordpress | **Confirmada como causa de la duración**, no del disparador |
| **E — Reinicio del host** | — | nginx, wp-cron, Traefik y EasyPanel siguieron arriba | Descartada en la práctica; verificar con `uptime -s` |
| **F — Restart policies** | — | El compose declara `restart: always` en todos los servicios de prod. Docker **no** reinicia un contenedor detenido deliberadamente con `docker stop` | No explica el evento, pero refuerza la hipótesis A |
| **G — OOM del host** | — | `OOMKilled=false`; 6.2 GiB libres tras el incidente | **No sustentada** para el 31 de agosto |
| **H — Memoria de wp-cron** | OOM confirmados el 15/22/29 | `wp-cron` **siguió corriendo** durante el incidente; el contenedor caído fue otro | **Problema real, pero independiente** |
| **I — Fallo de la base de datos** | Warnings de conexión abortada | Exit code 0 = apagado limpio | Víctima, no causa |
| **J — Disco / filesystem** | — | Sin verificar | Pendiente, bajo coste |

## Why nginx returned 502

nginx tenía dos motivos para devolver 502, uno tras otro:

1. **Durante el apagado**: PHP-FPM no existía. Un 502 aquí es correcto y esperable.
2. **Después de que PHP volviera**: nginx seguía marcando la IP antigua. Este es
   el fallo real. `upstream php { server wordpress:9000; }` resuelve el nombre
   al arrancar el proceso y **nunca vuelve a consultarlo**. Nada en la
   configuración forzaba una nueva resolución: ni `resolver`, ni un
   `fastcgi_pass` sobre variable, ni un reinicio de nginx atado a la salud de
   WordPress.

Encima de eso, `fastcgi_cache_use_stale` no incluía `updating` ni `http_502`, así
que nginx tampoco podía servir la copia cacheada de las páginas mientras PHP
estaba caído: cada visita veía el error en crudo.

## Database communication warnings

Los mensajes `Aborted connection` / `Got an error reading communication packets`
desde `172.23.0.4` son **consecuencia, no causa**:

- MariaDB salió con **código 0**: apagado ordenado, sin crash recovery.
- Cuando un proceso PHP desaparece con sockets MySQL abiertos, el servidor
  registra exactamente ese warning. Es el síntoma esperado de un cliente que se
  esfuma.

**Salvedad:** las marcas citadas (01:03–01:08) no coinciden con la ventana del
incidente y no traen zona horaria. Podrían ser de otro momento. Confirmarlo
antes de darlas por relacionadas.

## Historical wp-cron OOM issue

**Problema separado, confirmado, con confianza ALTA.** No hay evidencia que lo
vincule con la caída del 31 de agosto — `wp-cron` sobrevivió al incidente.

- Límite de cgroup: **384 MiB** (`Memory=402653184`).
- Procesos PHP matados con **380-390 MiB RSS** — es decir, chocando de lleno
  contra el techo.
- Cadencia: 15, 22 y 29 de agosto ≈ **cada 7 días**, lo que apunta a un job
  semanal (WooCommerce Action Scheduler, limpieza de BD, feeds o sincronización
  de productos son los sospechosos habituales).
- `MemorySwap=805306368` (768 MiB) es papel mojado: **el host no tiene swap**,
  así que el margen extra nunca estuvo disponible.

Queda pendiente identificar el job concreto (ver *Recommended actions*, P1).

## Infrastructure weaknesses found

Todas verificadas en el código de este repositorio:

| # | Debilidad | Consecuencia |
|---|---|---|
| 1 | `upstream` de nginx con nombre resuelto una sola vez | 502 permanente tras cualquier recreación de PHP |
| 2 | `stop_grace_period` por defecto (10 s) | SIGKILL a php-fpm y a MariaDB; riesgo de crash recovery en la BD |
| 3 | `wp-cron` limitado a 384 MiB | OOM semanal |
| 4 | Suma de límites 7.75 GiB > 7.5 GiB del host, sin swap | Un pico simultáneo deja al OOM killer del host eligiendo víctima |
| 5 | `pm = static` con 24 workers | Memoria reservada de forma permanente; apagado lento |
| 6 | Healthcheck `pidof php-fpm` | Marca *healthy* aunque el pool esté saturado y el sitio caído |
| 7 | Tuning de php-fpm vía `sed` sobre `www.conf` desde el `command` | Se rompe en silencio si cambia el texto de la imagen base |
| 8 | Sin rotación de logs declarada | La evidencia de un incidente puede perderse o llenar el disco |
| 9 | `docker-compose.yml` (dev) sin healthchecks ni restart policies | Dev no reproduce el comportamiento de prod |
| 10 | Sin monitorización externa de HTTP | La caída se detectó por observación humana |

## Recommended actions

### P0 — Aplicado en este commit

| Cambio | Motivo | Riesgo | Beneficio |
|---|---|---|---|
| nginx: `resolver 127.0.0.11 valid=10s` + `fastcgi_pass $php_upstream` en lugar del bloque `upstream` | Elimina la IP cacheada de por vida | Bajo. Requiere `nginx -t` antes de desplegar | El sitio se recupera solo en ≤10 s cuando PHP vuelve |
| `fastcgi_cache_use_stale ... updating http_502 http_504` + `background_update on` | Degradar sirviendo caché en vez de 502 | Bajo. Puede servir contenido algo rancio durante una caída | El sitio sigue navegable aunque PHP esté caído |
| `stop_signal: SIGQUIT` + `stop_grace_period: 90s` en `wordpress` | SIGQUIT es el apagado *graceful* de php-fpm; SIGTERM lo aborta en seco | Bajo | Se acaba el exit 137; las peticiones en vuelo terminan |
| `stop_grace_period: 60s` en `db` | Evita SIGKILL a InnoDB | Ninguno | Sin crash recovery al arrancar |
| `wp-cron`: 384 MiB → **768 MiB** + `php -d memory_limit=640M` | 640M < 768M: un job pesado falla con error PHP registrable en vez de morir sin rastro | Bajo | Fin del OOM semanal, con diagnóstico si reaparece |
| Rebalanceo: `db` 3G→2G (buffer pool 2G→1G), `wordpress` 3G→2G, `redis` 1G→768M, `nginx` 384M→256M | **Total 5.75 GiB**, deja ~1.75 GiB al SO/EasyPanel/Traefik | Medio — ver nota abajo | Se elimina el sobrecompromiso de RAM |
| `pm = static`/24 → `pm = dynamic`/12 vía `php/fpm-pool.*.conf` | Deja de reservar memoria permanentemente; elimina los `sed` frágiles | Bajo | Menos RAM en reposo, apagado más rápido, config trazable |
| Healthcheck real de php-fpm (`cgi-fcgi` contra `ping.path`) | `pidof` no detecta un pool saturado | Bajo. Requiere reconstruir la imagen (`libfcgi-bin`) | El orquestador ve la caída real y puede reiniciar |
| `depends_on: wordpress: {condition: service_healthy, restart: true}` en nginx | Si WordPress se recrea, nginx se reinicia | Bajo | Segunda red de seguridad sobre el resolver |
| `logging: json-file` con `max-size`/`max-file` | Retención acotada y predecible | Ninguno | Evidencia disponible en el próximo incidente |
| dev: healthchecks, `restart: unless-stopped`, `depends_on` por salud | Paridad con producción | Ninguno | Los problemas de arranque se ven en local |

> **Nota sobre el rebalanceo de memoria.** Bajar el `innodb-buffer-pool-size` de
> 2G a 1G **puede afectar al rendimiento de consultas** si el working set de la
> base supera 1 GiB. Es un intercambio deliberado: con 7.5 GiB de host,
> reservar 2G de buffer pool + 3G de PHP + 1G de Redis no cabía. Si tras el
> despliegue el hit ratio de InnoDB cae, la salida correcta es **ampliar la RAM
> del VPS**, no volver al sobrecompromiso.

### P1 — Próxima ventana de mantenimiento

1. **Responder la pregunta abierta**: revisar los logs de EasyPanel y de Docker
   alrededor de `2026-08-31T18:59Z` para identificar quién detuvo el stack.
   Mientras no se sepa, el disparador puede repetirse. Comandos en la sección
   siguiente.
2. **Añadir ~2 GiB de swap al host.** No sustituye a la RAM y no evita un OOM de
   cgroup, pero amortigua los picos. Con `swappiness=10`.
3. **Identificar el job semanal de `wp-cron`**: `wp cron event list` y las
   acciones programadas de WooCommerce, correlacionadas con las fechas de OOM.
4. **Monitorización externa de HTTP/HTTPS** contra la URL pública (no contra el
   estado del contenedor: en este incidente nginx figuraba como *Up*).
5. **Alertas** sobre 5xx, contenedor *unhealthy*, contenedor *exited*, presión
   de RAM y de disco.

### P2 — Endurecimiento opcional

6. Fijar IPs estáticas por servicio en la red de Docker (mitigación redundante
   con el resolver; solo si se quiere cinturón y tirantes).
7. Logs centralizados con retención de 30+ días.
8. `nginx -t` automatizado en CI antes de cualquier despliegue.
9. **No** implementar un reinicio automático del stack completo ante cualquier
   fallo: esconde la causa raíz y puede empeorar el incidente.

## Commands executed

### En esta sesión (revisión de configuración, entorno local)

```bash
cat docker-compose.yml docker-compose.prod.yml docker-compose.test.yml
cat nginx/nginx.conf php/Dockerfile php/uploads.ini php/opcache.ini

# Validación de sintaxis tras los cambios — los tres devolvieron OK
docker compose -f docker-compose.yml      config -q
docker compose -f docker-compose.prod.yml config -q
docker compose -f docker-compose.test.yml config -q

# Presupuesto de memoria resultante
docker compose -f docker-compose.prod.yml config | grep -E '^  [a-z-]+:|memory:'
```

### Pendientes de ejecutar en el host (NO VERIFICADO EN ESTA SESIÓN)

```bash
# 1. ¿Quién detuvo el stack? — la pregunta crítica
docker logs $(docker ps -a --format '{{.Names}}' | grep -i easypanel) \
  --since 2026-08-31T18:30:00Z --until 2026-08-31T19:30:00Z
journalctl -u docker      --since "2026-08-31 18:30:00 UTC" --until "2026-08-31 19:30:00 UTC"
journalctl -u containerd  --since "2026-08-31 18:30:00 UTC" --until "2026-08-31 19:30:00 UTC"

# 2. Descartar reinicio del host
uptime -s; who -b; last reboot | head -20; journalctl --list-boots

# 3. Descartar OOM del host en la ventana exacta
journalctl -k --since "2026-08-31 18:45:00 UTC" --until "2026-08-31 19:15:00 UTC"
dmesg -T | grep -iE 'oom|killed process|memory cgroup'

# 4. Descartar problemas de disco
df -h; df -i
journalctl -k | grep -iE 'i/o error|read-only|no space|ext4|xfs'

# 5. Identificar el job semanal de wp-cron
docker exec ecopc_ecopc-prod-wordpress-1 wp cron event list --allow-root
docker exec ecopc_ecopc-prod-wordpress-1 wp action-scheduler list --allow-root

# 6. Automatización que pudiera tocar el stack
crontab -l; cat /etc/crontab; ls -lah /etc/cron*
systemctl list-timers --all
```

## Raw evidence

```
# nginx error log
connect() failed (113: Host is unreachable) while connecting to upstream,
upstream: "fastcgi://172.23.0.5:9000"

# docker inspect — wordpress
OOMKilled=false
ExitCode=137
FinishedAt=2026-08-31T18:59:54Z

# docker inspect — límites (antes de este commit)
ecopc_ecopc-prod-wp-cron-1:   Memory=402653184   MemorySwap=805306368
ecopc_ecopc-prod-wordpress-1: Memory=3221225472  MemorySwap=6442450944

# free -h (después de la recuperación)
              total  used  free  shared  buff/cache  available
Mem:          7.5Gi  1.2Gi 4.9Gi 148Mi   1.8Gi       6.2Gi
Swap:            0B    0B    0B

# journalctl -k (histórico: 15, 22, 29 de agosto)
Memory cgroup out of memory: Killed process (...) (php)   # RSS ~380-390 MiB

# nginx/nginx.conf ANTES de este commit — causa del 502 persistente
upstream php {
    server wordpress:9000;    # resuelto una sola vez al arrancar
}
```

## Remaining unknowns

1. **Qué actor detuvo `db`, `redis` y `wordpress` a las 18:59 UTC.** Es la
   pregunta más importante y **sigue sin respuesta**. Los cambios de este commit
   hacen que el sitio se recupere solo del *síntoma*, pero no impiden que el
   disparador vuelva a ocurrir. Requiere los logs de EasyPanel y del daemon
   Docker.
2. **Duración real de la caída.** No hay marca de tiempo de la recuperación.
3. **Zona horaria de los warnings de MariaDB** (01:03–01:08) y si pertenecen
   siquiera a este incidente.
4. **Qué job semanal** provoca los OOM de `wp-cron`.
5. **Si el host se reinició.** Muy improbable dado que EasyPanel, Traefik, nginx
   y wp-cron sobrevivieron, pero no se ejecutó `uptime -s` para confirmarlo.
6. **Uso real de RAM en el instante del incidente.** `free -h` se ejecutó después
   de la recuperación y no dice nada sobre el momento del fallo.
7. ~~Validación `nginx -t` de la nueva configuración.~~ **RESUELTO**: ejecutado
   con éxito, y el stack de testing completo se levantó y se validó. Ver
   *Validación experimental*.
8. **Comportamiento bajo carga real.** La validación se hizo sobre un WordPress
   recién instalado, sin plugins ni tráfico. Que `pm.max_children = 12` y el
   límite de 2 GiB basten para la carga real de producción **no está probado**;
   hay que vigilarlo tras el despliegue.


## Validación experimental

Los cambios se probaron levantando el stack completo de testing (`docker-compose.test.yml`)
el 2026-08-31. **Los cinco servicios alcanzaron `healthy`.**

### Prueba A/B: la causa raíz del 502, reproducida

Se levantaron **dos nginx en paralelo** sobre la misma red y el mismo WordPress:
uno con la configuración de `git HEAD` (upstream estático) y otro con la nueva
(resolver dinámico). Después se forzó el escenario del incidente: parar
WordPress, ocupar su IP con otro contenedor y volver a arrancarlo, de modo que
recibiera una dirección distinta. **Ningún nginx fue reiniciado.**

| Momento | nginx LEGACY (`upstream php { server wordpress:9000; }`) | nginx NUEVO (`resolver` + `fastcgi_pass $php_upstream`) |
|---|---|---|
| Baseline, WordPress en `172.19.0.7` | `HTTP/1.1 302` | `HTTP/1.1 302` |
| WordPress movido a `172.19.0.8` | **`HTTP/1.1 502`** | **`HTTP/1.1 302`** |

Log del nginx legacy tras el cambio de IP:

```
connect() failed (111: Connection refused) while connecting to upstream,
upstream: "fastcgi://172.19.0.7:9000"      <-- la IP que ya no existe
```

Es el mismo fallo que en producción. El código de error difiere (111 aquí,
113 en producción) solo porque en la prueba otro contenedor ocupaba esa IP y
rechazaba la conexión activamente, mientras que en producción no había nadie
escuchando. La causa es idéntica: **nginx seguía marcando una dirección
obsoleta.**

Esto eleva la hipótesis D de "confirmada por lectura de código" a
**reproducida experimentalmente**.

### Apagado ordenado: fin del exit 137

```
docker compose stop wordpress   ->  0.19s
ExitCode: 0   OOMKilled=false
```

Con `stop_signal: SIGQUIT`, php-fpm cierra de forma limpia y **casi
instantánea**. El exit 137 desaparece.

### Fallos que la prueba destapó (y que se corrigieron)

Tres errores que la sola lectura del código no habría detectado:

1. **`fastcgi_cache_use_stale` no acepta `http_502` ni `http_504`.** A
   diferencia de `proxy_cache_use_stale`, esos valores no existen para FastCGI.
   `nginx -t` falló con *invalid value "http_502"*. Corregido a
   `error timeout updating invalid_header http_500 http_503` — que ya cubre el
   caso de PHP caído a través de `error` y `timeout`.
2. **El healthcheck de nginx nunca podía pasar** (ver *Confirmed facts* #12):
   `localhost` iba a IPv6 y, por IPv4, el propio `map $is_bad_bot` devolvía 403
   al User-Agent `wget`. Corregido apuntando a `127.0.0.1` y excluyendo
   `/nginx-health` del bloqueo por User-Agent.
3. **`pgrep` no existe en la imagen** de PHP. El healthcheck de `wp-cron` se
   reescribió como heartbeat: el bucle escribe un timestamp en
   `/tmp/wp-cron-heartbeat` en cada iteración y el chequeo verifica que tenga
   menos de 900 s (3 ciclos). Detecta un bucle colgado, no solo la existencia
   de un proceso.

### Consumo observado

| Servicio | Uso | Límite |
|---|---|---|
| db | 79 MiB | 1 GiB |
| nginx | 21 MiB | 256 MiB |
| redis | 14 MiB | 384 MiB |
| wordpress | 24 MiB | 1 GiB |
| wp-cron | 1 MiB | 768 MiB |

**Estas cifras no validan el dimensionamiento de producción**: es un sitio
recién instalado, sin contenido, plugins ni tráfico. Solo confirman que los
límites no ahogan el arranque. El dimensionamiento real hay que vigilarlo en
producción tras el despliegue.

### Comandos de la validación

```bash
docker run --rm -v "$PWD/nginx/nginx.conf:/etc/nginx/nginx.conf:ro" \
  -v "$PWD/nginx/security-headers.conf:/etc/nginx/security-headers.conf:ro" \
  nginx:1.28.3-alpine nginx -t                      # syntax is ok

docker compose -f docker-compose.test.yml build
docker compose -f docker-compose.test.yml up -d
docker compose -f docker-compose.test.yml ps        # los 5 healthy
docker compose -f docker-compose.test.yml exec wordpress fpm-healthcheck   # exit 0
docker compose -f docker-compose.test.yml exec wordpress php-fpm -tt       # pm=dynamic, ping.path=/ping

# A/B con la config antigua
git show HEAD:nginx/nginx.conf > /tmp/nginx-legacy.conf
docker run -d --name nginx-legacy --network ecopc_test_backend \
  -v /tmp/nginx-legacy.conf:/etc/nginx/nginx.conf:ro \
  -v "$PWD/nginx/security-headers.conf:/etc/nginx/security-headers.conf:ro" \
  -v ecopc_test_wp_data_test:/var/www/html:ro nginx:1.28.3-alpine

# forzar cambio de IP de wordpress
docker compose -f docker-compose.test.yml stop wordpress
docker run -d --rm --name ip-squatter --network ecopc_test_backend --ip 172.19.0.7 alpine sleep 240
docker compose -f docker-compose.test.yml start wordpress

# limpieza
docker rm -f nginx-legacy ip-squatter
docker compose -f docker-compose.test.yml down      # volúmenes preservados
```

## Deployment notes

Los cambios exigen **reconstruir la imagen de PHP** (`libfcgi-bin` y el script
de healthcheck son nuevos) y **recrear** los contenedores; un simple `restart`
no basta.

```bash
# 1. Validar la configuración de nginx ANTES de tocar producción
docker run --rm \
  -v "$PWD/nginx/nginx.conf:/etc/nginx/nginx.conf:ro" \
  -v "$PWD/nginx/security-headers.conf:/etc/nginx/security-headers.conf:ro" \
  nginx:1.28.3-alpine nginx -t

# 2. Probar el conjunto completo en testing primero
docker compose -f docker-compose.test.yml build
docker compose -f docker-compose.test.yml up -d
docker compose -f docker-compose.test.yml ps          # todos healthy

# 3. Verificar que el healthcheck real de FPM funciona
docker compose -f docker-compose.test.yml exec wordpress fpm-healthcheck && echo "FPM OK"

# 4. Verificar la recuperación automática: recrear PHP y comprobar
#    que nginx NO se queda en 502 (esta era la causa del incidente)
docker compose -f docker-compose.test.yml up -d --force-recreate wordpress
sleep 20
docker compose -f docker-compose.test.yml exec nginx wget -qO- http://localhost/ >/dev/null \
  && echo "RECUPERACION AUTOMATICA OK"

# 5. Solo entonces, producción
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d
```

**Vigilar tras el despliegue en producción:**
- `docker stats` — que `wordpress` se estabilice cómodamente por debajo de 2 GiB
  con `pm.max_children = 12`. Si el pool se satura bajo carga real, subir a 16
  antes que revertir a `static`.
- Hit ratio de InnoDB tras la reducción del buffer pool a 1 GiB.
- Logs de `wp-cron`: ahora imprime `[wp-cron] run <timestamp>` en cada ciclo y
  `[wp-cron] FALLO rc=N` si el job falla, lo que permitirá identificar el job
  semanal problemático sin herramientas adicionales.
