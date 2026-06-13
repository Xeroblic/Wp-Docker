<?php
/**
 * Reemplaza el código de un snippet del plugin Code Snippets por el contenido
 * de un archivo del repo (contraparte de export-snippets.php).
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
$wpdb->update($table, ['code' => $code, 'modified' => current_time('mysql')], ['id' => $id]);
wp_cache_flush();

WP_CLI::success("Snippet {$id} ({$row->name}) actualizado desde {$file}.");
