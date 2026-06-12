<?php
/**
 * Carga datos de Elementor (_elementor_data) desde un archivo JSON a un post existente.
 *
 * Uso:
 *   docker compose run --rm wp-cli eval-file scripts/import-elementor-template.php \
 *     219 elementor-templates/home.json [template-type]
 *
 * El tercer argumento opcional es el _elementor_template_type (default: wp-page).
 */

if ( count( $args ) < 2 ) {
	WP_CLI::error( 'Uso: <ID-post> <ruta-al-json> [template-type]' );
}

$post_id       = (int) $args[0];
$file          = $args[1];
$template_type = $args[2] ?? 'wp-page';

if ( ! get_post( $post_id ) ) {
	WP_CLI::error( "No existe el post {$post_id}." );
}

if ( ! file_exists( $file ) ) {
	WP_CLI::error( "No existe el archivo {$file}." );
}

$json = file_get_contents( $file );
$data = json_decode( $json );

if ( null === $data ) {
	WP_CLI::error( 'El archivo no contiene JSON válido: ' . json_last_error_msg() );
}

update_post_meta( $post_id, '_elementor_data', wp_slash( $json ) );
update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
update_post_meta( $post_id, '_elementor_template_type', $template_type );

if ( defined( 'ELEMENTOR_VERSION' ) ) {
	update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
}

if ( class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}

WP_CLI::success( "Datos de Elementor importados al post {$post_id} desde {$file}." );
