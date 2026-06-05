# WordPress Dockerizado de Alto Rendimiento

Infraestructura de WordPress optimizada para entornos de alta concurrencia y producción mediante contenedores Docker. Este repositorio implementa una pila tecnológica afinada para maximizar la velocidad de carga, la seguridad y la eficiencia en el uso de recursos.

## Componentes de la Arquitectura

La arquitectura se compone de los siguientes servicios interconectados:

*   **Nginx (1.28.3-alpine)**: Configurado como servidor web de cara al público y proxy inverso para FastCGI. Implementa almacenamiento en caché de páginas completas (FastCGI Cache) con reglas inteligentes para omitir el caché en páginas dinámicas (carrito, mi cuenta, administración) y mantenerlo para campañas con parámetros de rastreo (UTM, gclid, fbclid).
*   **WordPress (PHP-FPM 8.2)**: Contenedor personalizado basado en la imagen oficial, modificado para compilar la extensión Redis con soporte para compresión LZ4, además de incluir configuraciones optimizadas de OPcache (con JIT activo) y límites de ejecución adaptados para cargas pesadas.
*   **Redis (7.2-alpine)**: Almacenamiento en caché de objetos en memoria intermedia para evitar consultas repetitivas a la base de datos, configurado con políticas de desalojo LRU (Least Recently Used) y persistencia selectiva.
*   **MariaDB (10.6.18)**: Motor de base de datos relacional ajustado con directrices específicas de rendimiento (InnoDB buffer pool, log file size, límites de conexiones).
*   **Cron Desacoplado**: Un contenedor dedicado ejecuta las tareas cron de WordPress (`wp-cron.php`) en segundo plano cada 5 minutos (o 60 segundos en local) para evitar la latencia que produce la ejecución nativa en las visitas de los usuarios.

---

## Estructura del Repositorio

*   **`nginx/`**
    *   `nginx.conf`: Configuración optimizada de Nginx con limitación de tasa (rate limiting), bloqueo de bots maliciosos, exclusión de ejecución de PHP en directorios de subidas y cabeceras de seguridad.
*   **`php/`**
    *   `Dockerfile`: Definición de la imagen de PHP con soporte Redis y compresión LZ4.
    *   `opcache.ini`: Optimización de OPcache y JIT.
    *   `uploads.ini`: Directivas de PHP para aumentar los límites de subida de archivos (512MB), variables de entrada y tiempo máximo de ejecución.
    *   `redis-config.php`: Archivo autoejecutado en PHP que preconfigura la conexión con Redis.
*   **Archivos de Docker Compose**:
    *   `docker-compose.yml`: Configuración para desarrollo local con soporte de Cloudflare Tunnel.
    *   `docker-compose.prod.yml`: Configuración endurecida para producción con asignación estricta de memoria/CPU, comprobaciones de estado (healthchecks) y segmentación de redes.
    *   `docker-compose.test.yml`: Entorno aislado para pruebas y desarrollo paralelo.

---

## Características de Rendimiento y Optimización

### 1. Nginx FastCGI Cache
El almacenamiento en caché a nivel de servidor reduce drásticamente el tiempo de respuesta inicial (TTFB).
*   **Bypass de Caché**: Se omite automáticamente para usuarios conectados, peticiones POST, feeds de sindicación, panel de administración y páginas críticas de comercio electrónico (WooCommerce).
*   **Tratamiento de Tracking**: La configuración analiza los parámetros de consulta de campañas de marketing (como `gclid`, `fbclid` o parámetros `utm_*`). Si la petición solo contiene estas variables, Nginx sirve la versión en caché en lugar de derivar la petición a PHP.
*   **Purga de Caché**: El volumen compartido `/var/cache/nginx` permite que plugins de WordPress (como Nginx Helper) purguen el caché del servidor directamente desde el panel de administración de WordPress.

### 2. Redis Object Cache con Compresión LZ4
El controlador Redis se compila con soporte de compresión LZ4. Esto permite almacenar estructuras de datos complejas en Redis ocupando hasta un 70% menos de memoria RAM, reduciendo el ancho de banda interno y mejorando los tiempos de serialización.

### 3. Ajustes de PHP y OPcache JIT
Se habilita el compilador Just-In-Time (JIT) en OPcache para acelerar la ejecución del código PHP repetitivo. Los límites en `uploads.ini` y la configuración dinámica de `docker-compose.prod.yml` ajustan la memoria disponible del sistema de manera óptima para WordPress (WP_MEMORY_LIMIT establecido en hasta 512M / 1024M).

### 4. Ejecución Estática de PHP-FPM en Producción
En lugar de utilizar un gestor de procesos dinámico que crea e inicializa hilos bajo demanda, en producción PHP-FPM se configura de forma estática (`pm = static` con `pm.max_children = 24`). Esto garantiza que los recursos estén preasignados y listos para responder inmediatamente sin retrasos por inicialización de procesos.

### 5. Seguridad Hardened
*   Se deshabilita la firma de versión de Nginx y de PHP (`expose_php = Off`, `server_tokens off`).
*   Se prohíbe la ejecución de scripts PHP dentro del directorio `/wp-content/uploads/` y `/wp-content/plugins/` o `themes/` para mitigar exploits comunes de subida de archivos.
*   Se bloquea el acceso directo a archivos sensibles como `.env`, `.git` y `wp-config.php`.
*   Implementación de Rate Limiting para evitar ataques de fuerza bruta en el backend y llamadas excesivas a la API de WooCommerce.

---

## Requisitos de Entorno

*   Docker Engine 20.10+
*   Docker Compose v2.0+

---

## Instrucciones de Instalación y Despliegue

### 1. Configurar las Variables de Entorno
Cree una copia del archivo `.env.example` en la raíz del proyecto con el nombre `.env`:

```bash
cp .env.example .env
```

Edite las variables según su entorno (contraseñas de base de datos y dominio principal):
*   `MYSQL_ROOT_PASSWORD`: Contraseña para el usuario root de MariaDB.
*   `MYSQL_DATABASE`: Nombre de la base de datos de WordPress.
*   `MYSQL_USER`: Nombre del usuario de la base de datos.
*   `MYSQL_PASSWORD`: Contraseña del usuario de la base de datos.
*   `DOMAIN`: Nombre de dominio de producción (ej. `midominio.com`).

### 2. Ejecutar en Entorno de Desarrollo Local
Para levantar el entorno local:

```bash
docker compose up -d
```

Este comando levantará los servicios locales y expondrá WordPress en el puerto `8081` de su máquina local (`http://localhost:8081`). También iniciará una instancia de Cloudflare Tunnel (`cloudflared`) orientada al contenedor de WordPress.

### 3. Ejecutar en Entorno de Producción
Para desplegar la infraestructura optimizada de producción:

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

*Nota: La bandera `--build` es necesaria para compilar el Dockerfile personalizado que incorpora el soporte de compresión LZ4.*

### 4. Ejecutar en Entorno de Pruebas
Si desea ejecutar una suite de pruebas o desarrollo aislado:

```bash
docker compose -f docker-compose.test.yml up -d --build
```

---

## Mantenimiento y Operaciones Comunes

### Acceder a la Consola de la Base de Datos
Para interactuar con la base de datos MariaDB:

```bash
docker compose exec db mysql -u wp_admin -p wordpress
```

### Limpieza Manual de Caché de FastCGI
En caso de requerir una purga completa y manual del caché de páginas de Nginx desde la línea de comandos:

```bash
docker compose exec nginx rm -rf /var/cache/nginx/fastcgi/*
```

### Monitoreo de Logs en Tiempo Real
Para visualizar las peticiones y posibles errores en caliente:

```bash
docker compose logs -f
```

O bien para ver un servicio específico (por ejemplo, el servidor web):

```bash
docker compose logs -f nginx
```
