<?php
/**
 * Vuelca _elementor_data de un post a un archivo JSON, listo para versionar/editar.
 *
 * Uso:
 *   docker compose run --rm wp-cli eval-file scripts/export-elementor-template.php \
 *     219 elementor-templates/home.json
 */

if ( count( $args ) < 2 ) {
	WP_CLI::error( 'Uso: <ID-post> <ruta-destino-json>' );
}

$post_id = (int) $args[0];
$file    = $args[1];

$data = get_post_meta( $post_id, '_elementor_data', true );

if ( empty( $data ) ) {
	WP_CLI::error( "El post {$post_id} no tiene _elementor_data." );
}

// Reformatea con indentación para que el diff en git sea legible.
$decoded = json_decode( $data );
$pretty  = json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

if ( false === file_put_contents( $file, $pretty ) ) {
	WP_CLI::error( "No se pudo escribir {$file}." );
}

WP_CLI::success( "Datos de Elementor del post {$post_id} exportados a {$file}." );
