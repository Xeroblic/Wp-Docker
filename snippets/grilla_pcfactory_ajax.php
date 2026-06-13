
add_shortcode('grilla_pcfactory_ajax', 'mundoplanet_ajax_products_shortcode');

function mundoplanet_ajax_products_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit'   => 12,
        'columns' => 4,
    ), $atts, 'grilla_pcfactory_ajax');

    $paged = 1;
    if ( get_query_var('paged') ) { $paged = get_query_var('paged'); }
    elseif ( get_query_var('page') ) { $paged = get_query_var('page'); }
    if ( isset($_GET['paged']) ) { $paged = intval($_GET['paged']); }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $atts['limit'],
        'paged'          => $paged,
        'post_status'    => 'publish',
    );

    $loop = new WP_Query($args);

    if (!$loop->have_posts()) {
        return '<p>No se encontraron productos.</p>';
    }

    ob_start();
    ?>

    <div id="mp-ajax-container" class="mp-grilla-wrapper" data-columns="<?php echo esc_attr($atts['columns']); ?>">
        <ul class="mp-products-grid columns-<?php echo esc_attr($atts['columns']); ?>">
            <?php while ($loop->have_posts()) : $loop->the_post(); global $product;

                $product_id       = $product->get_id();
                $sku              = $product->get_sku() ? $product->get_sku() : $product_id;
                $terms            = get_the_terms($product_id, 'product_cat');
                $brand            = ($terms && !is_wp_error($terms)) ? $terms[0]->name : 'Mundo Planet';
                $stock_status     = $product->get_stock_status();
                $stock_text       = ($stock_status == 'instock') ? '+10 Unid.' : 'Agotado';
                $stock_color      = ($stock_status == 'instock') ? '#888' : '#e53e3e';
                $regular_price    = $product->get_regular_price();
                $sale_price       = $product->get_sale_price();
                $active_price     = $product->get_price();
                $has_discount     = $product->is_on_sale() && $regular_price;
                $discount_percent = 0;
                if ($has_discount) {
                    $discount_percent = round((($regular_price - $sale_price) / $regular_price) * 100);
                }
                $img_src     = wp_get_attachment_image_src(get_post_thumbnail_id(), 'thumbnail');
                $img_url     = $img_src ? $img_src[0] : wc_placeholder_img_src();
                $title_clean = esc_attr(get_the_title());
                $price_clean = wc_price($active_price);
            ?>
                <li class="mp-product-card">

                    <div class="mp-compare-wrap">
                        <input type="checkbox" class="mp-compare-cb js-compare-cb"
                               id="comp-<?php echo $product_id; ?>"
                               data-id="<?php echo $product_id; ?>"
                               data-img="<?php echo esc_url($img_url); ?>"
                               data-title="<?php echo $title_clean; ?>"
                               data-price="<?php echo esc_attr($price_clean); ?>">
                        <label for="comp-<?php echo $product_id; ?>">Comparar</label>
                    </div>

                    <a href="<?php echo esc_url(get_permalink()); ?>" class="mp-card-link">
                        <figure class="mp-product-image">
                            <?php echo woocommerce_get_product_thumbnail('woocommerce_thumbnail'); ?>
                        </figure>

                        <div class="mp-product-info">
                            <p class="mp-brand"><?php echo esc_html($brand); ?>&reg;</p>
                            <h3 class="mp-title"><?php echo get_the_title(); ?></h3>

                            <div class="mp-meta-row">
                                <span class="mp-sku">ID <strong><?php echo esc_html($sku); ?></strong></span>
                                <span class="mp-stock" style="color: <?php echo $stock_color; ?>"><?php echo $stock_text; ?></span>
                            </div>

                            <div class="mp-discount-row">
                                <?php if ($has_discount) : ?>
                                    <span class="mp-badge-discount">-<?php echo $discount_percent; ?>%</span>
                                    <del class="mp-old-price"><?php echo wc_price($regular_price); ?></del>
                                <?php else: ?>
                                    <span class="mp-badge-placeholder"></span>
                                <?php endif; ?>
                            </div>

                            <div class="mp-main-price-wrap">
                                <p class="mp-price-giant"><?php echo wc_price($active_price); ?></p>
                                <span class="mp-price-subtitle">Transferencia / Débito</span>
                            </div>
                        </div>
                    </a>

                    <div class="mp-cart-btn-wrap">
                        <?php echo woocommerce_template_loop_add_to_cart(); ?>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>

        <?php
        $total_pages = $loop->max_num_pages;
        if ($total_pages > 1) :
            $next_page = $paged + 1;
            $prev_page = $paged - 1;
        ?>

            <!-- Flechas laterales (solo desktop) -->
            <nav class="mp-pagination mp-pagination-desktop">
                <?php echo paginate_links(array(
                    'base'      => add_query_arg('paged', '%#%'),
                    'format'    => '',
                    'current'   => $paged,
                    'total'     => $total_pages,
                    'prev_text' => '❮',
                    'next_text' => '❯',
                    'type'      => 'list',
                )); ?>
            </nav>

            <!-- Botón Ver más (solo mobile) -->
            <div class="mp-load-more-wrap">
                <?php if ($paged > 1) : ?>
                    <a href="<?php echo esc_url(add_query_arg('paged', $prev_page)); ?>"
                       class="mp-load-more-btn js-mp-load-more">
                        ← Anteriores
                    </a>
                <?php endif; ?>

                <span class="mp-page-info">
                    Página <?php echo $paged; ?> de <?php echo $total_pages; ?>
                </span>

                <?php if ($paged < $total_pages) : ?>
                    <a href="<?php echo esc_url(add_query_arg('paged', $next_page)); ?>"
                       class="mp-load-more-btn js-mp-load-more">
                        Ver más productos →
                    </a>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>

    <style>

        .mp-grilla-wrapper {
            position: relative;
            max-width: 1140px;
            margin: 0 auto 50px;
            padding: 0 45px;
            font-family: 'Montserrat', Arial, sans-serif;
        }

        .mp-products-grid {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 20px !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
        }

        .mp-product-card {
            display: flex !important;
            flex-direction: column !important;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px 15px;
            position: relative;
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
        }
        .mp-product-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #1a365d;
        }

        .mp-card-link {
            text-decoration: none !important;
            color: inherit !important;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .mp-compare-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-size: 12px;
            color: #718096;
            margin-bottom: 10px;
            cursor: pointer;
        }
        .mp-compare-wrap input  { cursor: pointer; width: 16px; height: 16px; accent-color: #23c16b; }
        .mp-compare-wrap label  { cursor: pointer; margin: 0; font-weight: 600; }
        .mp-compare-wrap:hover  { color: #1a365d; }

        .mp-product-image {
            margin: 0 0 15px 0;
            text-align: center;
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mp-product-image img { max-height: 100%; width: auto; object-fit: contain; }

        .mp-brand           { font-size: 11px; color: #718096; font-weight: 700; margin: 0 0 5px 0; text-transform: uppercase; }
        .mp-title           { font-size: 14px !important; font-weight: 600 !important; color: #2d3748 !important; line-height: 1.3 !important; margin: 0 0 15px 0 !important; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 36px; }
        .mp-meta-row        { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 15px; color: #718096; }
        .mp-discount-row    { display: flex; align-items: center; gap: 10px; height: 24px; margin-bottom: 5px; }
        .mp-badge-discount  { background: #1a365d; color: #fff; font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 4px; }
        .mp-old-price       { color: #a0aec0; font-size: 13px; }
        .mp-badge-placeholder { height: 24px; display: block; }
        .mp-main-price-wrap { margin-bottom: 20px; }
        .mp-price-giant     { font-size: 24px; font-weight: 800; color: #111; margin: 0; line-height: 1.1; }
        .mp-price-subtitle  { font-size: 11px; color: #718096; }

        .mp-cart-btn-wrap { margin-top: auto; }
        .mp-cart-btn-wrap a.button {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            background: #4a5568 !important;
            color: #fff !important;
            width: 100% !important;
            padding: 12px 0 !important;
            border-radius: 4px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            text-transform: none !important;
            transition: background 0.3s !important;
            border: none !important;
        }
        .mp-cart-btn-wrap a.button:hover { background: #1a365d !important; }
        .mp-cart-btn-wrap a.added        { display: none !important; }

   
        .mp-pagination-desktop {
            position: absolute;
            top: 40%;
            left: 0;
            width: 100%;
            pointer-events: none;
        }
        .mp-pagination-desktop ul {
            display: flex;
            justify-content: space-between;
            margin: 0; padding: 0; list-style: none;
        }
        .mp-pagination-desktop ul li { pointer-events: auto; }
        .mp-pagination-desktop ul li a.page-numbers:not(.prev):not(.next),
        .mp-pagination-desktop ul li span.page-numbers { display: none !important; }
        .mp-pagination-desktop ul li a.prev,
        .mp-pagination-desktop ul li a.next {
            background: #929ba2;
            color: #fff;
            width: 35px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border-radius: 4px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .mp-pagination-desktop ul li a.prev:hover,
        .mp-pagination-desktop ul li a.next:hover { background: #1a365d; }

        .mp-load-more-wrap { display: none; }

   
        @media (max-width: 1024px) {
            .mp-products-grid { grid-template-columns: repeat(3, 1fr) !important; }
        }

  
        @media (max-width: 768px) {

            .mp-grilla-wrapper {
                padding: 0 !important;
                overflow: hidden;
            }

            .mp-products-grid {
                display: flex !important;
                flex-wrap: nowrap !important;
                overflow-x: scroll;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                gap: 12px !important;
                padding: 10px 15px !important;
                justify-content: flex-start !important;
                align-items: stretch;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }
            .mp-products-grid::-webkit-scrollbar { display: none; }

            .mp-product-card {
                flex: 0 0 80% !important;
                width: 80% !important;
                min-width: 0 !important;
                scroll-snap-align: start !important;
                margin: 0 !important;
                height: auto !important;
                box-sizing: border-box !important;
            }

            .mp-product-image { height: 140px; }
            .mp-price-giant   { font-size: 20px; }

            /* Hint de scroll */
            .mp-grilla-wrapper::after {
                content: "← desliza →";
                display: block;
                text-align: center;
                font-size: 11px;
                color: #a0aec0;
                margin-top: 8px;
                font-family: 'Montserrat', Arial, sans-serif;
            }

            .mp-pagination-desktop { display: none !important; }

            .mp-load-more-wrap {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
                padding: 15px 20px;
                margin-top: 5px;
            }

            .mp-load-more-btn {
                display: block;
                background: #4b5668;
                color: #fff;
                padding: 12px 30px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: 700;
                font-size: 14px;
                font-family: 'Montserrat', Arial, sans-serif;
                transition: background 0.3s;
                text-align: center;
                width: 100%;
                box-sizing: border-box;
            }
            .mp-load-more-btn:hover { background: #2d4a7a; color: #fff; }

            .mp-page-info {
                font-size: 12px;
                color: #718096;
                font-family: 'Montserrat', Arial, sans-serif;
            }
        }

        @media (max-width: 480px) {
            .mp-product-card {
                flex: 0 0 85% !important;
                width: 85% !important;
            }
        }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", function() {

        jQuery(document).on('adding_to_cart', function(e, $button) {
            if (!$button || $button.length === 0) return;
            $button.html('Cargando <i class="fas fa-spinner fa-spin" style="margin-left:8px;"></i>');
            $button.css('pointer-events', 'none');
        });

        jQuery(document).on('added_to_cart', function(e, fragments, cart_hash, $button) {
            if (!$button || $button.length === 0) return;
            $button.css({ 'background-color': '#23c16b', 'border-color': '#23c16b' });
            $button.html('¡Agregado! <i class="fas fa-check" style="margin-left:8px;"></i>');
            setTimeout(function() {
                $button.css({ 'background-color': '', 'border-color': '', 'pointer-events': 'auto' });
                $button.html('Agregar al carrito');
            }, 3000);
        });

        if (window.mpAjaxIniciado) return;
        window.mpAjaxIniciado = true;

        document.body.addEventListener('click', function(e) {
            const link = e.target.closest(
                '#mp-ajax-container .mp-pagination-desktop a, ' +
                '#mp-ajax-container .js-mp-load-more'
            );
            if (!link) return;

            e.preventDefault();
            const url  = link.href;
            const container = document.getElementById('mp-ajax-container');
            if (!container) return;

            container.style.opacity      = '0.5';
            container.style.pointerEvents = 'none';

            fetch(url)
                .then(r => r.text())
                .then(html => {
                    const parser     = new DOMParser();
                    const doc        = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('mp-ajax-container');

                    if (newContent && container) {
                        container.innerHTML = newContent.innerHTML;

                        const memList = JSON.parse(localStorage.getItem('mp_compare_list')) || [];
                        document.querySelectorAll('.js-compare-cb').forEach(cb => {
                            cb.checked = memList.some(i => i.id === cb.dataset.id);
                        });

                        const grid = container.querySelector('.mp-products-grid');
                        if (grid) grid.scrollLeft = 0;

                    } else {
                        window.location.href = url;
                    }
                })
                .catch(() => window.location.href = url)
                .finally(() => {
                    if (container) {
                        container.style.opacity      = '1';
                        container.style.pointerEvents = 'auto';
                    }
                });
        });
    });
    </script>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}