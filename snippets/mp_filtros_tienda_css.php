add_action('wp_head', 'mp_filtros_tienda_css', 99);
function mp_filtros_tienda_css() {
    if (!(is_shop() || is_product_category() || is_product_tag() || is_search())) {
        return;
    }
    ?>
    <style>
        /* === Sidebar de filtros (bloques Product Filters de WooCommerce) === */

        /* Títulos */
        .ast-filter-wrap h2.wp-block-heading { font-size: 22px; }
        .ast-filter-wrap h3.wp-block-heading { font-size: 17px; margin-bottom: 0.6rem; }

        /* Cajas de texto del filtro de precio.
           OJO: el riel del slider usa <input type="range"> con las mismas
           clases .min/.max — no tocarlos o la barra se rompe. */
        .ast-filter-wrap .wc-block-product-filter-price-slider__left input[type="text"],
        .ast-filter-wrap .wc-block-product-filter-price-slider__right input[type="text"] {
            font-size: 14px !important;
            width: 105px !important;
            min-width: 105px !important;
            padding: 8px 6px !important;
            text-align: center;
            box-sizing: border-box;
        }

        /* Lista de categorías / estado: texto y casillas más grandes */
        .ast-filter-wrap .wc-block-product-filter-checkbox-list__text {
            font-size: 15px !important;
            line-height: 1.4;
        }
        .ast-filter-wrap .wc-block-product-filter-checkbox-list__label {
            align-items: center;
            gap: 10px;
        }
        .ast-filter-wrap .wc-block-product-filter-checkbox-list__input {
            width: 18px !important;
            height: 18px !important;
        }
        .ast-filter-wrap .wc-block-product-filter-checkbox-list__item {
            margin-bottom: 10px;
        }
    </style>
    <?php
}
