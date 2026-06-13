<?php
/**
 * Sube imágenes placeholder para las tarjetas de categorías de la Home.
 * Imprime "slug => ID URL" para referenciarlas desde el JSON de Elementor.
 * Uso: docker compose run --rm --user 33:33 wp-cli eval-file /repo/scripts/upload-category-images.php
 */

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$cats = [
    'cat-gamer'      => 'gaming,keyboard',
    'cat-cocina'     => 'kitchen,appliance',
    'cat-escritorio' => 'desk,office',
    'cat-notebooks'  => 'laptop,computer',
];

$i = 40;
foreach ($cats as $slug => $tag) {
    $i++;
    $existing = get_page_by_path($slug, OBJECT, 'attachment');
    if ($existing) {
        WP_CLI::log("$slug => {$existing->ID} " . wp_get_attachment_url($existing->ID));
        continue;
    }

    $url = "https://loremflickr.com/600/600/{$tag}?lock={$i}";
    $tmp = download_url($url, 60);
    if (is_wp_error($tmp)) {
        WP_CLI::warning("Descarga falló para {$slug}: " . $tmp->get_error_message());
        continue;
    }

    $file = ['name' => "{$slug}.jpg", 'tmp_name' => $tmp];
    $att  = media_handle_sideload($file, 0, $slug);
    if (is_wp_error($att)) {
        @unlink($tmp);
        WP_CLI::warning("Sideload falló para {$slug}: " . $att->get_error_message());
        continue;
    }
    wp_update_post(['ID' => $att, 'post_name' => $slug]);
    WP_CLI::log("$slug => {$att} " . wp_get_attachment_url($att));
}
