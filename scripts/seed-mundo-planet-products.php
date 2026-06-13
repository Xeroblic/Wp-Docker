<?php
/**
 * Seed de productos para Mundo Planet (tecnología y hogar).
 * Uso: docker compose run --rm wp-cli eval-file /repo/scripts/seed-mundo-planet-products.php
 * Idempotente: si ya existe un producto con el mismo slug, lo omite.
 */

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$cats = [
    'cocina'      => 27,
    'escritorio'  => 24,
    'gamer'       => 23,
    'notebooks'   => 25,
    'perifericos' => 26,
    'utiles'      => 28,
];

$products = [
    [
        'name'        => 'Audífonos Inalámbricos Bluetooth con Cancelación de Ruido',
        'slug'        => 'audifonos-inalambricos-bluetooth-anc',
        'price'       => 29990,
        'sale'        => 19990,
        'cats'        => ['perifericos'],
        'image_tag'   => 'headphones',
        'short'       => 'Sonido envolvente, cancelación activa de ruido y hasta 30 horas de batería.',
        'description' => '<p>Desconéctate del ruido y conéctate con tu música. Estos audífonos inalámbricos con cancelación activa de ruido (ANC) entregan graves profundos y agudos nítidos, con hasta 30 horas de autonomía y carga rápida USB-C.</p><ul><li>Bluetooth 5.3 de conexión estable</li><li>Micrófono integrado para llamadas y videollamadas</li><li>Almohadillas acolchadas para uso prolongado</li></ul>',
    ],
    [
        'name'        => 'Teclado Mecánico Gamer RGB Switch Red',
        'slug'        => 'teclado-mecanico-gamer-rgb',
        'price'       => 34990,
        'sale'        => 24990,
        'cats'        => ['gamer', 'perifericos'],
        'image_tag'   => 'keyboard',
        'short'       => 'Switches red lineales, retroiluminación RGB personalizable y estructura reforzada.',
        'description' => '<p>Lleva tu juego al siguiente nivel. Teclado mecánico con switches red lineales de respuesta rápida, anti-ghosting completo y retroiluminación RGB con efectos configurables. Estructura con placa metálica para máxima durabilidad.</p><ul><li>Formato completo con teclado numérico</li><li>Keycaps de doble inyección resistentes al desgaste</li><li>Cable trenzado desmontable USB-C</li></ul>',
    ],
    [
        'name'        => 'Mouse Gamer Óptico 12.800 DPI',
        'slug'        => 'mouse-gamer-optico-12800-dpi',
        'price'       => 15990,
        'sale'        => null,
        'cats'        => ['gamer', 'perifericos'],
        'image_tag'   => 'computer,mouse',
        'short'       => 'Sensor óptico de precisión, 7 botones programables e iluminación RGB.',
        'description' => '<p>Precisión quirúrgica para cada partida. Sensor óptico de hasta 12.800 DPI ajustables al vuelo, 7 botones programables y diseño ergonómico con grip lateral texturizado.</p><ul><li>Polling rate de 1000 Hz</li><li>Software de macros y perfiles</li><li>Pies de PTFE para deslizamiento suave</li></ul>',
    ],
    [
        'name'        => 'Notebook 15.6" Intel Core i5 16GB RAM 512GB SSD',
        'slug'        => 'notebook-156-i5-16gb-512gb',
        'price'       => 549990,
        'sale'        => 479990,
        'cats'        => ['notebooks'],
        'image_tag'   => 'laptop',
        'short'       => 'Potencia para el trabajo y el estudio: pantalla Full HD, 16GB de RAM y SSD ultrarrápido.',
        'description' => '<p>El equilibrio perfecto entre rendimiento y portabilidad. Procesador Intel Core i5 de última generación, 16GB de RAM para multitarea fluida y SSD NVMe de 512GB que enciende tu sistema en segundos.</p><ul><li>Pantalla 15.6" Full HD antirreflejo</li><li>Batería para toda la jornada</li><li>Windows 11 incluido</li></ul>',
    ],
    [
        'name'        => 'Monitor 27" Full HD 100Hz',
        'slug'        => 'monitor-27-fullhd-100hz',
        'price'       => 129990,
        'sale'        => null,
        'cats'        => ['escritorio'],
        'image_tag'   => 'monitor,desk',
        'short'       => 'Panel IPS de 27 pulgadas con 100Hz de refresco y bordes ultradelgados.',
        'description' => '<p>Más espacio, más fluidez. Monitor IPS de 27" Full HD con tasa de refresco de 100Hz, colores fieles y diseño sin bordes ideal para configuraciones de doble pantalla.</p><ul><li>Modo lectura con filtro de luz azul</li><li>Entradas HDMI y VGA</li><li>Soporte compatible con VESA</li></ul>',
    ],
    [
        'name'        => 'Hervidor Eléctrico 1.7L Acero Inoxidable',
        'slug'        => 'hervidor-electrico-17l-acero',
        'price'       => 18990,
        'sale'        => 12990,
        'cats'        => ['cocina'],
        'image_tag'   => 'kettle,kitchen',
        'short'       => 'Hierve en minutos, con apagado automático y jarra de acero inoxidable.',
        'description' => '<p>El aliado infaltable de tu cocina. Hervidor eléctrico de 1.7 litros con cuerpo de acero inoxidable, base giratoria 360° y apagado automático de seguridad al alcanzar la ebullición.</p><ul><li>Potencia de 2.200W: hierve en minutos</li><li>Protección contra funcionamiento en seco</li><li>Filtro antical removible</li></ul>',
    ],
    [
        'name'        => 'Freidora de Aire Digital 5L',
        'slug'        => 'freidora-de-aire-digital-5l',
        'price'       => 49990,
        'sale'        => 39990,
        'cats'        => ['cocina'],
        'image_tag'   => 'airfryer,kitchen',
        'short'       => 'Cocina crujiente con hasta 85% menos aceite. Panel táctil y 8 programas.',
        'description' => '<p>Sabor crujiente, sin culpas. Freidora de aire de 5 litros con panel táctil digital, 8 programas preestablecidos y tecnología de circulación de aire 360° que cocina con hasta 85% menos aceite.</p><ul><li>Canasta antiadherente apta para lavavajillas</li><li>Temporizador de 60 minutos con apagado automático</li><li>Temperatura regulable 80–200°C</li></ul>',
    ],
    [
        'name'        => 'Lámpara LED de Escritorio con Carga Inalámbrica',
        'slug'        => 'lampara-led-escritorio-carga-inalambrica',
        'price'       => 12990,
        'sale'        => null,
        'cats'        => ['escritorio', 'utiles'],
        'image_tag'   => 'lamp,desk',
        'short'       => 'Tres tonos de luz regulables, brazo flexible y base con carga inalámbrica para tu teléfono.',
        'description' => '<p>Ilumina tu espacio de trabajo con estilo. Lámpara LED con tres temperaturas de color y brillo regulable, brazo flexible y base con cargador inalámbrico Qi integrado para tu smartphone.</p><ul><li>Luz pareja sin parpadeo, cuida tu vista</li><li>Control táctil</li><li>Puerto USB adicional de carga</li></ul>',
    ],
];

foreach ($products as $i => $p) {
    if (get_page_by_path($p['slug'], OBJECT, 'product')) {
        WP_CLI::log("Omitido (ya existe): {$p['name']}");
        continue;
    }

    $product = new WC_Product_Simple();
    $product->set_name($p['name']);
    $product->set_slug($p['slug']);
    $product->set_regular_price((string) $p['price']);
    if ($p['sale']) {
        $product->set_sale_price((string) $p['sale']);
    }
    $product->set_short_description($p['short']);
    $product->set_description($p['description']);
    $product->set_category_ids(array_map(fn($c) => $cats[$c], $p['cats']));
    $product->set_status('publish');
    $product->set_manage_stock(true);
    $product->set_stock_quantity(rand(8, 40));
    $id = $product->save();

    // Foto placeholder temática (lock = imagen estable por producto)
    $url = "https://loremflickr.com/800/800/{$p['image_tag']}?lock=" . ($i + 11);
    $att = media_sideload_image($url, $id, $p['name'], 'id');
    if (is_wp_error($att)) {
        WP_CLI::warning("Sin imagen para {$p['name']}: " . $att->get_error_message());
    } else {
        set_post_thumbnail($id, $att);
    }

    WP_CLI::log("Creado: {$p['name']} (ID {$id})" . (is_wp_error($att) ? '' : ' con imagen'));
}

WP_CLI::success('Seed de productos Mundo Planet completado.');
