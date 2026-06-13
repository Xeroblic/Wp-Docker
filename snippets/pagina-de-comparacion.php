
add_shortcode('comparador_mundo_planet', 'mp_generar_pagina_comparacion');

function mp_generar_pagina_comparacion() {
    if (!isset($_GET['ids']) || empty($_GET['ids'])) {
        return '<div style="text-align:center; padding: 50px; font-family: Montserrat;">
                    <h2>No hay productos seleccionados</h2>
                    <p>Vuelve a la tienda y selecciona productos para comparar.</p>
                    <a href="/categorias/" style="background:#23c16b; color:#fff; padding:10px 20px; border-radius:4px; text-decoration:none; display:inline-block; margin-top:15px; font-weight:bold;">Ir a la tienda</a>
                </div>';
    }

    $ids_raw     = sanitize_text_field($_GET['ids']);
    $product_ids = explode(',', $ids_raw);

    $products       = [];
    $all_attributes = [];

    foreach ($product_ids as $id) {
        $product = wc_get_product(intval($id));
        if ($product) {
            $products[] = $product;
            foreach ($product->get_attributes() as $attribute) {
                $attr_name = wc_attribute_label($attribute->get_name());
                if (!in_array($attr_name, $all_attributes)) {
                    $all_attributes[] = $attr_name;
                }
            }
        }
    }

    if (empty($products)) {
        return '<p>Productos no encontrados.</p>';
    }

    ob_start();
    ?>
    <style>
        /* =====================================================
           COMPARADOR - BASE
           ===================================================== */
        .mp-comp-page-wrapper {
            font-family: 'Montserrat', Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 15px 60px 15px;
        }

        .mp-comp-page-title {
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 25px;
        }

        .mp-comp-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .mp-comp-back:hover { color: #1a365d; }

        /* =====================================================
           TABLA DESKTOP
           ===================================================== */
        .mp-comp-table-wrap { overflow-x: auto; }

        .mp-comp-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
            table-layout: fixed;
        }
        .mp-comp-table th,
        .mp-comp-table td {
            padding: 20px;
            text-align: center;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .mp-comp-table td.mp-attr-title {
            background: #f8fafc;
            font-weight: 700;
            text-align: left;
            color: #475569;
            width: 200px;
        }
        .mp-spec-header {
            background: #f1f5f9;
            text-align: left !important;
            font-size: 15px;
            font-weight: 800;
            color: #1e293b;
            padding: 15px 20px !important;
        }
        .mp-spec-value { font-size: 14px; color: #334155; }

        /* Cabecera tarjeta en tabla */
        .mp-comp-head-card {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .mp-comp-head-img   { width: 130px; height: 130px; object-fit: contain; margin-bottom: 12px; }
        .mp-comp-head-brand { font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; margin: 0; }
        .mp-comp-head-title { font-size: 14px; font-weight: 700; color: #1e293b; line-height: 1.3; margin: 5px 0 12px 0; }
        .mp-comp-head-price { font-size: 22px; font-weight: 900; color: #111; margin: 0; line-height: 1; }
        .mp-comp-head-subprice { font-size: 11px; color: #64748b; margin-top: 4px; }

        .mp-comp-remove {
            position: absolute;
            top: -10px; right: -10px;
            color: #94a3b8;
            font-size: 18px;
            cursor: pointer;
            text-decoration: none;
            line-height: 1;
        }
        .mp-comp-remove:hover { color: #e53e3e; }

        .mp-comp-cart-btn {
            background: #4a5568;
            color: #fff;
            padding: 10px 15px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            border-radius: 4px;
            margin-top: 15px;
            display: block;
            width: 100%;
            box-sizing: border-box;
            transition: background 0.3s;
            text-align: center;
        }
        .mp-comp-cart-btn:hover { background: #23c16b; color: #fff; }

        /* Celda "agregar otro" */
        .mp-comp-add-slot {
            vertical-align: middle !important;
            border: 1px dashed #cbd5e1 !important;
            background: #f8fafc !important;
        }
        .mp-comp-add-slot-inner { color: #94a3b8; }
        .mp-comp-add-slot-icon  { font-size: 36px; margin-bottom: 8px; }
        .mp-comp-add-slot-label { font-size: 13px; font-weight: 600; }

        /* Ocultar layout mobile en desktop */
        .mp-comp-mobile { display: none; }

        /* =====================================================
           MOBILE
           ===================================================== */
        @media (max-width: 768px) {

            /* Ocultar tabla en mobile */
            .mp-comp-table-wrap { display: none; }

            /* Mostrar layout mobile */
            .mp-comp-mobile { display: block; }

            /* Carrusel de tarjetas de producto */
            .mp-comp-cards-scroll {
                display: flex;
                gap: 12px;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                padding: 5px 0 15px 0;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }
            .mp-comp-cards-scroll::-webkit-scrollbar { display: none; }

            .mp-comp-mobile-card {
                flex: 0 0 75%;
                scroll-snap-align: start;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 20px 15px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.06);
                position: relative;
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .mp-comp-mobile-card .mp-comp-remove {
                position: absolute;
                top: 10px; right: 10px;
                font-size: 20px;
            }

            .mp-comp-mobile-card img {
                width: 110px;
                height: 110px;
                object-fit: contain;
                margin-bottom: 10px;
            }

            .mp-comp-mobile-brand {
                font-size: 10px;
                color: #94a3b8;
                font-weight: 700;
                text-transform: uppercase;
                margin: 0 0 4px 0;
            }

            .mp-comp-mobile-title {
                font-size: 13px;
                font-weight: 700;
                color: #1e293b;
                line-height: 1.3;
                margin: 0 0 10px 0;
            }

            .mp-comp-mobile-price {
                font-size: 22px;
                font-weight: 900;
                color: #111;
                margin: 0;
            }

            .mp-comp-mobile-subprice {
                font-size: 11px;
                color: #64748b;
                margin: 3px 0 0 0;
            }

            .mp-comp-mobile-btn {
                display: block;
                background: #4a5568;
                color: #fff;
                padding: 10px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: 700;
                font-size: 13px;
                margin-top: 15px;
                width: 100%;
                box-sizing: border-box;
                transition: background 0.3s;
                text-align: center;
            }
            .mp-comp-mobile-btn:hover { background: #23c16b; color: #fff; }

            /* Hint scroll */
            .mp-comp-scroll-hint {
                text-align: center;
                font-size: 11px;
                color: #a0aec0;
                margin-bottom: 20px;
            }

            /* Especificaciones en mobile: lista de filas */
            .mp-comp-specs-mobile {
                margin-top: 10px;
            }

            .mp-comp-specs-section-title {
                background: #f1f5f9;
                font-size: 14px;
                font-weight: 800;
                color: #1e293b;
                padding: 12px 15px;
                border-radius: 6px;
                margin: 20px 0 10px 0;
            }

            .mp-comp-spec-row {
                border-bottom: 1px solid #f1f5f9;
                padding: 10px 0;
            }

            .mp-comp-spec-label {
                font-size: 12px;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                margin-bottom: 8px;
            }

            .mp-comp-spec-values {
                display: flex;
                gap: 8px;
            }

            .mp-comp-spec-val {
                flex: 1;
                background: #f8fafc;
                border-radius: 4px;
                padding: 8px;
                font-size: 13px;
                color: #334155;
                text-align: center;
                font-weight: 600;
            }

            .mp-comp-spec-val-label {
                font-size: 10px;
                color: #94a3b8;
                font-weight: 400;
                display: block;
                margin-bottom: 3px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }

        @media (max-width: 480px) {
            .mp-comp-mobile-card { flex: 0 0 85%; }
        }
    </style>

    <div class="mp-comp-page-wrapper">

        <a href="javascript:history.back()" class="mp-comp-back">❮ Volver a la tienda</a>
        <h1 class="mp-comp-page-title">Estás comparando: Productos</h1>

        <!-- ================================================
             DESKTOP: tabla
             ================================================ -->
        <div class="mp-comp-table-wrap">
            <table class="mp-comp-table">
                <thead>
                    <tr>
                        <td style="border:none; background:transparent;"></td>

                        <?php foreach ($products as $prod) :
                            $terms   = get_the_terms($prod->get_id(), 'product_cat');
                            $brand   = ($terms && !is_wp_error($terms)) ? $terms[0]->name : 'Mundo Planet';
                            $img_src = wp_get_attachment_image_src($prod->get_image_id(), 'woocommerce_thumbnail');
                            $img_url = $img_src ? $img_src[0] : wc_placeholder_img_src();

                            $current_ids = $product_ids;
                            if (($key = array_search((string)$prod->get_id(), $current_ids)) !== false) {
                                unset($current_ids[$key]);
                            }
                            $remove_url = empty($current_ids) ? '/categorias/' : '?ids=' . implode(',', $current_ids);
                        ?>
                            <th>
                                <div class="mp-comp-head-card">
                                    <a href="<?php echo esc_url($remove_url); ?>" class="mp-comp-remove" title="Quitar">✕</a>
                                    <img src="<?php echo esc_url($img_url); ?>" class="mp-comp-head-img" alt="">
                                    <p class="mp-comp-head-brand"><?php echo esc_html($brand); ?></p>
                                    <a href="<?php echo esc_url(get_permalink($prod->get_id())); ?>" style="text-decoration:none;">
                                        <h3 class="mp-comp-head-title"><?php echo esc_html($prod->get_name()); ?></h3>
                                    </a>
                                    <p class="mp-comp-head-price"><?php echo wc_price($prod->get_price()); ?></p>
                                    <p class="mp-comp-head-subprice">Transferencia / Débito</p>
                                    <a href="?add-to-cart=<?php echo $prod->get_id(); ?>" class="mp-comp-cart-btn">Agregar al carro</a>
                                </div>
                            </th>
                        <?php endforeach; ?>

                        <?php if (count($products) < 3) : ?>
                            <th class="mp-comp-add-slot">
                                <div class="mp-comp-add-slot-inner">
                                    <div class="mp-comp-add-slot-icon">+</div>
                                    <p class="mp-comp-add-slot-label">Agrega otro producto</p>
                                </div>
                            </th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="<?php echo count($products) + (count($products) < 3 ? 2 : 1); ?>" class="mp-spec-header">
                            Especificaciones Generales
                        </td>
                    </tr>
                    <tr>
                        <td class="mp-attr-title">ID</td>
                        <?php foreach ($products as $prod) : ?>
                            <td class="mp-spec-value"><?php echo $prod->get_sku() ? esc_html($prod->get_sku()) : $prod->get_id(); ?></td>
                        <?php endforeach; ?>
                        <?php if (count($products) < 3) echo '<td style="border:none;"></td>'; ?>
                    </tr>
                    <?php foreach ($all_attributes as $attr_name) : ?>
                        <tr>
                            <td class="mp-attr-title"><?php echo esc_html($attr_name); ?></td>
                            <?php foreach ($products as $prod) :
                                $val = $prod->get_attribute($attr_name);
                            ?>
                                <td class="mp-spec-value"><?php echo empty($val) ? '-' : esc_html($val); ?></td>
                            <?php endforeach; ?>
                            <?php if (count($products) < 3) echo '<td style="border:none;"></td>'; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ================================================
             MOBILE: tarjetas + specs
             ================================================ -->
        <div class="mp-comp-mobile">

            <!-- Carrusel de tarjetas -->
            <div class="mp-comp-cards-scroll">
                <?php foreach ($products as $prod) :
                    $terms   = get_the_terms($prod->get_id(), 'product_cat');
                    $brand   = ($terms && !is_wp_error($terms)) ? $terms[0]->name : 'Mundo Planet';
                    $img_src = wp_get_attachment_image_src($prod->get_image_id(), 'woocommerce_thumbnail');
                    $img_url = $img_src ? $img_src[0] : wc_placeholder_img_src();

                    $current_ids = $product_ids;
                    if (($key = array_search((string)$prod->get_id(), $current_ids)) !== false) {
                        unset($current_ids[$key]);
                    }
                    $remove_url = empty($current_ids) ? '/categorias/' : '?ids=' . implode(',', $current_ids);
                ?>
                    <div class="mp-comp-mobile-card">
                        <a href="<?php echo esc_url($remove_url); ?>" class="mp-comp-remove" title="Quitar">✕</a>
                        <img src="<?php echo esc_url($img_url); ?>" alt="">
                        <p class="mp-comp-mobile-brand"><?php echo esc_html($brand); ?></p>
                        <a href="<?php echo esc_url(get_permalink($prod->get_id())); ?>" style="text-decoration:none;">
                            <h3 class="mp-comp-mobile-title"><?php echo esc_html($prod->get_name()); ?></h3>
                        </a>
                        <p class="mp-comp-mobile-price"><?php echo wc_price($prod->get_price()); ?></p>
                        <p class="mp-comp-mobile-subprice">Transferencia / Débito</p>
                        <a href="?add-to-cart=<?php echo $prod->get_id(); ?>" class="mp-comp-mobile-btn">Agregar al carro</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($products) > 1) : ?>
                <p class="mp-comp-scroll-hint">← desliza para ver todos →</p>
            <?php endif; ?>

            <!-- Especificaciones -->
            <div class="mp-comp-specs-mobile">
                <div class="mp-comp-specs-section-title">Especificaciones Generales</div>

                <!-- ID -->
                <div class="mp-comp-spec-row">
                    <div class="mp-comp-spec-label">ID</div>
                    <div class="mp-comp-spec-values">
                        <?php foreach ($products as $prod) : ?>
                            <div class="mp-comp-spec-val">
                                <span class="mp-comp-spec-val-label"><?php echo esc_html(mb_strimwidth($prod->get_name(), 0, 12, '…')); ?></span>
                                <?php echo $prod->get_sku() ? esc_html($prod->get_sku()) : $prod->get_id(); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php foreach ($all_attributes as $attr_name) : ?>
                    <div class="mp-comp-spec-row">
                        <div class="mp-comp-spec-label"><?php echo esc_html($attr_name); ?></div>
                        <div class="mp-comp-spec-values">
                            <?php foreach ($products as $prod) :
                                $val = $prod->get_attribute($attr_name);
                            ?>
                                <div class="mp-comp-spec-val">
                                    <span class="mp-comp-spec-val-label"><?php echo esc_html(mb_strimwidth($prod->get_name(), 0, 12, '…')); ?></span>
                                    <?php echo empty($val) ? '-' : esc_html($val); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const currentIds = <?php echo json_encode($product_ids); ?>;
            let memoryList   = JSON.parse(localStorage.getItem('mp_compare_list')) || [];
            memoryList       = memoryList.filter(item => currentIds.includes(item.id.toString()));
            localStorage.setItem('mp_compare_list', JSON.stringify(memoryList));
        });
    </script>
    <?php
    return ob_get_clean();
}