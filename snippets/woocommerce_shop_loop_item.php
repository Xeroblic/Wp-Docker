add_action('wp', 'mp_fulminar_tarjetas_nativas', 99);
function mp_fulminar_tarjetas_nativas() {
    // is_product()    -> "Productos relacionados" y "Upsells" de la ficha.
    // is_front_page() -> shortcode [products] de la portada ("Productos Destacados").
    // Estos hooks *_shop_loop_item solo corren dentro de loops de productos
    // (relacionados, upsells o el shortcode [products]), nunca en otro contenido,
    // así que activarlos en esas vistas no afecta el resto de la página.
    if (is_shop() || is_product_category() || is_product_tag() || is_search() || is_product() || is_front_page()) {
        remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
        remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
        remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
        remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
        remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

        // Astra re-agrega su propia estructura (título, precio, botón, sale flash y
        // wrappers de thumbnail) en el hook `wp` con prioridad 1-5, por lo que
        // también hay que quitarla o la tarjeta sale duplicada.
        remove_action('woocommerce_before_shop_loop_item', 'astra_woo_shop_thumbnail_wrap_start', 6);
        remove_action('woocommerce_before_shop_loop_item', 'woocommerce_show_product_loop_sale_flash', 9);
        remove_action('woocommerce_after_shop_loop_item', 'astra_woo_shop_thumbnail_wrap_end', 8);
        remove_action('woocommerce_after_shop_loop_item', 'astra_woo_woocommerce_shop_product_content', 10);
        remove_action('woocommerce_shop_loop_item_title', 'astra_woo_shop_out_of_stock', 8);
        if (class_exists('Astra_Woocommerce')) {
            remove_action('woocommerce_after_shop_loop_item', array(Astra_Woocommerce::get_instance(), 'add_modern_triggers_on_image'), 5);
        }

        add_action('woocommerce_before_shop_loop_item', 'mp_tarjeta_clon_pcfactory', 10);
    }
}

function mp_tarjeta_clon_pcfactory() {
    global $product;
    static $estilos_impresos = false;
    $id = $product->get_id();
    $sku = $product->get_sku() ? $product->get_sku() : $id;
    
    $terms = get_the_terms($id, 'product_cat');
    $brand = ($terms && !is_wp_error($terms)) ? $terms[0]->name : 'Mundo Planet';
    
    $stock_status = $product->get_stock_status();
    $stock_text = ($stock_status == 'instock') ? '+10 Unid.' : 'Agotado';
    $stock_color = ($stock_status == 'instock') ? '#888' : '#e53e3e';
    
    $regular_price = $product->get_regular_price();
    $sale_price = $product->get_price(); 
    $has_discount = $product->is_on_sale() && $regular_price;
    $discount_percent = $has_discount ? round((($regular_price - $sale_price) / $regular_price) * 100) : 0;
    
    $precio_transferencia = wc_price($sale_price);
    $precio_otros = wc_price($sale_price * 1.05);
    
    $img_src = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'woocommerce_thumbnail');
    $img_url = $img_src ? $img_src[0] : wc_placeholder_img_src();
    

    ?>
    <div class="mp-product-card" style="display:flex; flex-direction:column; height:100%; background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:20px; position:relative; transition: all 0.3s ease;">
        
        <div style="display:flex; align-items:center; gap:5px; font-size:12px; color:#64748b; margin-bottom:15px;">
            <input type="checkbox" id="comp-<?php echo $id; ?>" class="mp-compare-cb js-compare-cb" style="cursor:pointer;"
                   data-id="<?php echo $id; ?>" data-img="<?php echo esc_url($img_url); ?>" 
                   data-title="<?php echo esc_attr(get_the_title()); ?>" data-price="<?php echo esc_attr($precio_transferencia); ?>">
            <label for="comp-<?php echo $id; ?>" style="margin:0; cursor:pointer;">Comparar</label>
        </div>

        <a href="<?php echo esc_url(get_permalink()); ?>" style="text-decoration:none; display:flex; flex-direction:column; flex-grow:1;">
            
            <figure style="margin:0 0 12px 0; height:190px; display:flex; align-items:center; justify-content:center;">
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" style="max-height:100%; width:auto; object-fit:contain;">
            </figure>

            <div style="display:flex; flex-direction:column; flex-grow:1;">
                <p style="font-size:12px; color:#94a3b8; text-transform:uppercase; font-weight:600; margin:0 0 4px 0;"> <?php echo esc_html($brand); ?>&reg; </p>
                <h3 class="mp-loop-title" style="margin:0 0 8px 0;"><span><?php echo get_the_title(); ?></span></h3>

                <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:8px;">
                    <span style="color:#64748b;">ID <strong style="color:#1e293b;"><?php echo esc_html($sku); ?></strong></span>
                    <span style="color:<?php echo $stock_color; ?>; font-weight:600;"><?php echo $stock_text; ?></span>
                </div>

                <div style="display:flex; align-items:center; gap:8px; height:22px; margin-bottom:8px;">
                    <?php if ($has_discount) : ?>
                        <span style="background:#1A0DFF; color:#fff; font-size:12px; font-weight:700; padding:2px 8px; border-radius:4px;">-<?php echo $discount_percent; ?>%</span>
                        <del style="color:#94a3b8; font-size:12px;"><?php echo wc_price($regular_price); ?></del>
                    <?php endif; ?>
                </div>

                <div style="margin-top:auto;">
                    <p style="font-size:24px; font-weight:900; color:#111; margin:0; line-height:1.1;"><?php echo $precio_transferencia; ?></p>
                    <p style="font-size:12px; color:#64748b; margin:2px 0 0 0;">Transferencia / Débito</p>
                    <p style="font-size:15px; color:#475569; margin:8px 0 0 0; line-height:1.2;"><?php echo $precio_otros; ?></p>
                    <p style="font-size:12px; color:#94a3b8; margin:1px 0 0 0;">Otros medios de pago</p>
                </div>
            </div>
        </a>
        
        <div class="mp-cart-container" style="margin-top:15px;">
            <?php woocommerce_template_loop_add_to_cart(); ?>
        </div>

        <?php if (!$estilos_impresos) : $estilos_impresos = true; ?>
        <style>
            /* Ensancha el área de contenido solo en la tienda y archivos de producto */
            body.post-type-archive-product .ast-container,
            body.tax-product_cat .ast-container,
            body.tax-product_tag .ast-container {
                max-width: 1400px !important;
            }
            ul.products li.product { display: flex; min-width: 0; }
            ul.products li.product .mp-product-card { width: 100%; }

            /* Título a 2 líneas con elipsis. El clamp va en el <span> interno
               (display block) y no en el <h3>, que es flex-item y rompería el
               -webkit-box. height fija = 2 líneas para alinear las tarjetas. */
            .mp-product-card .mp-loop-title {
                font-size: 12px;
                font-weight: 700;
                color: #1e293b;
                line-height: 1.45;
                min-height: 35px; /* reserva 2 líneas para alinear las tarjetas */
            }
            .mp-product-card .mp-loop-title span {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                /* sin height fija: el clamp corta el texto a 2 líneas con su alto
                   natural, sin recortar los descensores de la 2ª línea */
            }

            /* Móvil: una columna ancha en vez de dos apretadas */
            @media (max-width: 600px) {
                ul.products {
                    display: grid !important;
                    grid-template-columns: 1fr !important;
                    gap: 16px !important;
                }
                ul.products li.product .mp-product-card { padding: 16px !important; }
            }
            .mp-product-card:hover {
                border-color: #4683b2 !important;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
            }
            .mp-cart-container .button,
            .mp-cart-container .add_to_cart_button {
                background-color: #4a5568 !important;
                color: #fff !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                width: 100% !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 12px 10px !important;
                line-height: 1.2 !important;
                text-align: center !important;
                border-radius: 4px !important;
                text-decoration: none !important;
                font-weight: 600 !important;
                font-size: 14px !important;
                border: none !important;
            }
            .mp-cart-container .button:hover,
            .mp-cart-container .add_to_cart_button:hover {
                background-color: #1a365d !important;
            }
            .mp-cart-container .added_to_cart { display: none !important; }
        </style>
        <?php endif; ?>
    </div>
    <?php
}