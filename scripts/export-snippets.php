<?php
/**
 * Exporta todos los snippets del plugin Code Snippets a archivos en /repo/snippets/.
 * El contenido de cada archivo es byte-idéntico a la columna `code` de la BD,
 * de modo que puede volver a importarse sin diffs falsos. Los metadatos
 * (scope, estado, prioridad, descripción) van al README.md de la carpeta.
 *
 * Uso: docker compose run --rm wp-cli eval-file /repo/scripts/export-snippets.php
 */

global $wpdb;

$dir  = '/repo/snippets';
$rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}snippets ORDER BY id", ARRAY_A);

if (!$rows) {
    WP_CLI::error('No se encontraron snippets.');
}

$manifest  = "# Snippets del sitio (plugin Code Snippets)\n\n";
$manifest .= "Copias versionables de los snippets que viven en la tabla `wp_snippets` de la BD.\n";
$manifest .= "La BD es la fuente de verdad: WordPress ejecuta lo que está en la tabla, no estos archivos.\n\n";
$manifest .= "Exportar: `docker compose run --rm wp-cli eval-file /repo/scripts/export-snippets.php`\n\n";
$manifest .= "Importar un snippet editado (reemplaza el código en la BD por el del archivo):\n";
$manifest .= "`docker compose run --rm wp-cli eval-file /repo/scripts/import-snippet.php <id> <archivo>`\n\n";
$manifest .= "| ID | Nombre | Archivo | Scope | Activo | Modificado |\n";
$manifest .= "|----|--------|---------|-------|--------|------------|\n";

foreach ($rows as $r) {
    $slug = sanitize_title($r['name']);
    $file = "{$dir}/{$slug}.php";
    file_put_contents($file, $r['code']);
    $manifest .= sprintf(
        "| %d | %s | `%s.php` | %s | %s | %s |\n",
        $r['id'],
        $r['name'],
        $slug,
        $r['scope'],
        $r['active'] ? 'sí' : 'no',
        $r['modified']
    );
    WP_CLI::log("[{$r['id']}] {$r['name']} => snippets/{$slug}.php" . ($r['active'] ? '' : ' (inactivo)'));
}

file_put_contents("{$dir}/README.md", $manifest);
WP_CLI::success(count($rows) . ' snippets exportados a snippets/ (+ README.md).');
