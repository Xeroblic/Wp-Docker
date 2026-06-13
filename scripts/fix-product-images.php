<?php
/**
 * Asigna imagen destacada placeholder a productos que no tengan una.
 * Descarga de loremflickr vía download_url() + media_handle_sideload()
 * (media_sideload_image rechaza URLs sin extensión de imagen).
 * Uso: docker compose run --rm wp-cli eval-file /repo/scripts/fix-product-images.php
 */

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$tags = [
    'audifonos-inalambricos-bluetooth-anc'     => 'headphones',
    'teclado-mecanico-gamer-rgb'               => 'keyboard',
    'mouse-gamer-optico-12800-dpi'             => 'computer,mouse',
    'notebook-156-i5-16gb-512gb'               => 'laptop',
    'monitor-27-fullhd-100hz'                  => 'monitor,desk',
    'hervidor-electrico-17l-acero'             => 'kettle,kitchen',
    'freidora-de-aire-digital-5l'              => 'airfryer,kitchen',
    'lampara-led-escritorio-carga-inalambrica' => 'lamp,desk',
];

$i = 10;
foreach ($tags as $slug => $tag) {
    $i++;
    $post = get_page_by_path($slug, OBJECT, 'product');
    if (!$post) {
        WP_CLI::warning("Producto no encontrado: {$slug}");
        continue;
    }
    if (has_post_thumbnail($post->ID)) {
        WP_CLI::log("Ya tiene imagen: {$slug}");
        continue;
    }

    $url = "https://loremflickr.com/800/800/{$tag}?lock={$i}";
    $tmp = download_url($url, 60);
    if (is_wp_error($tmp)) {
        WP_CLI::warning("Descarga falló para {$slug}: " . $tmp->get_error_message());
        continue;
    }

    $file = ['name' => "{$slug}.jpg", 'tmp_name' => $tmp];
    $att  = media_handle_sideload($file, $post->ID, $post->post_title);
    if (is_wp_error($att)) {
        @unlink($tmp);
        WP_CLI::warning("Sideload falló para {$slug}: " . $att->get_error_message());
        continue;
    }

    set_post_thumbnail($post->ID, $att);
    WP_CLI::log("Imagen asignada a {$slug} (att {$att})");
}

WP_CLI::success('Imágenes de productos procesadas.');
