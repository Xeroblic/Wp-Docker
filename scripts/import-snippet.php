<?php
/**
 * Reemplaza el código de un snippet del plugin Code Snippets por el contenido
 * de un archivo del repo (contraparte de export-snippets.php).
 *
 * Detalles importantes de Code Snippets:
 *  - La columna `modified` se almacena en UTC (el plugin la escribe con gmdate()).
 *    Por eso aquí usamos gmdate() y NO current_time('mysql'); si se usara hora
 *    local, el admin mostraría el cambio desfasado por el gmt_offset del sitio.
 *  - El plugin cachea la lista de snippets activos en el object-cache (grupo
 *    'code_snippets'); wp_cache_flush() lo invalida para que el código nuevo se
 *    ejecute en la siguiente request.
 *  - Su API (Code_Snippets\save_snippet) vive en un namespace y depende de la
 *    instancia code_snippets(), que NO se inicializa bajo WP-CLI, así que aquí
 *    el camino correcto es el UPDATE directo.
 *
 * Uso:
 *   docker compose run --rm wp-cli eval-file /repo/scripts/import-snippet.php \
 *     <id-snippet> /repo/snippets/<archivo>.php
 */

if (count($args) < 2) {
    WP_CLI::error('Uso: <id-snippet> <ruta-al-archivo>');
}

$id   = (int) $args[0];
$file = $args[1];

if (!file_exists($file)) {
    WP_CLI::error("No existe el archivo {$file}.");
}

global $wpdb;
$table = $wpdb->prefix . 'snippets';
$row   = $wpdb->get_row($wpdb->prepare("SELECT id, name FROM {$table} WHERE id = %d", $id));

if (!$row) {
    WP_CLI::error("No existe el snippet {$id}.");
}

$code = file_get_contents($file);
$wpdb->update(
    $table,
    ['code' => $code, 'modified' => gmdate('Y-m-d H:i:s')], // UTC, como espera el plugin
    ['id' => $id]
);

wp_cache_flush(); // invalida el caché de snippets activos del plugin

WP_CLI::success("Snippet {$id} ({$row->name}) actualizado desde {$file} (fecha UTC, caché limpiado).");
