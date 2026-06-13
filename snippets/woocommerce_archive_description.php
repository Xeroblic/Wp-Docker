add_action('wp_head', function() {
    if ( is_product_category() ) {
        echo '<style>
            .ast-archive-entry-banner, .woocommerce-products-header, 
            header.woocommerce-products-header, h1.woocommerce-products-header__title, 
            .ast-woo-shop-archive-description { display: none !important; margin: 0 !important; padding: 0 !important; }

            @media (max-width: 1199px) {
                .site-content > .ast-container { 
                    display: flex !important; 
                    flex-direction: column !important; 
                    width: 100% !important;
                }
                .mp-top-master-wrapper { order: 1 !important; width: 100% !important; margin-top: 20px !important; margin-bottom: 20px !important; }
                #secondary.widget-area { order: 2 !important; width: 100% !important; margin-bottom: 20px !important; }
                #primary.content-area { order: 3 !important; width: 100% !important; }
            }

            @media (min-width: 1200px) {
                .site-content > .ast-container { 
                    display: flex !important; 
                    flex-direction: row !important; 
                    flex-wrap: wrap !important; 
                    align-items: flex-start !important; 
                }
                .mp-top-master-wrapper { order: 1 !important; flex: 0 0 100% !important; width: 100% !important; margin-top: 25px !important; margin-bottom: 30px !important; }
                #secondary.widget-area { order: 2 !important; flex: 0 0 18% !important; max-width: 18% !important; }
                #primary.content-area { order: 3 !important; flex: 0 0 82% !important; max-width: 82% !important; padding-left: 30px !important; }
            }

            .mp-pcf-hero {
                position: relative; width: 100%; height: 300px;
                border-radius: 8px; overflow: hidden; margin-bottom: 20px;
                background-color: #f1f5f9; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            }

            .mp-pcf-slider-wrap { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
            .mp-pcf-slider-wrap .n2-section-smartslider { height: 100% !important; }

            .mp-pcf-label {
                position: absolute; bottom: 30px; left: 0; z-index: 10;
                background-color: #0060df; padding: 10px 30px; display: flex; align-items: center; gap: 10px;
                border-top-right-radius: 8px; border-bottom-right-radius: 8px;
                box-shadow: 5px 5px 20px rgba(0,0,0,0.3); border-left: 8px solid #23c16b;
            }
            .mp-pcf-label h1 { margin: 0 !important; color: #fff !important; font-size: 24px !important; font-weight: 800 !important; text-transform: none; }

            .mp-pcf-subcats {
                background-color: #f8f9fa; border-radius: 8px; padding: 20px 15px;
                display: flex; gap: 20px; overflow-x: auto; scrollbar-width: none;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }
            .mp-pcf-subcats::-webkit-scrollbar { display: none; }
            .mp-pcf-circle-item { width: 90px; flex-shrink: 0; text-align: center; transition: 0.2s; }
            .mp-pcf-circle-item a { text-decoration: none !important; }
            .mp-pcf-circle-item .img-box {
                width: 70px; height: 70px; border-radius: 50%; background: #23c16b;
                margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; transition: 0.3s;
            }
            .mp-pcf-circle-item img { width: 100%; height: 100%; object-fit: contain; padding: 12px; filter: brightness(0) invert(1); }
            .mp-pcf-circle-item h2 { font-size: 12px !important; color: #4b5155 !important; font-weight: 700 !important; margin: 0 !important; line-height: 1.2; }
            .mp-pcf-circle-item:hover .img-box { transform: scale(1.05); box-shadow: 0 5px 15px rgba(35, 193, 107, 0.4); }

            @media (max-width: 768px) {
                .mp-pcf-hero {
                    height: 200px;
                    border-radius: 0;
                    margin-left: -20px;
                    margin-right: -20px;
                    width: calc(100% + 40px);
                }
                .mp-pcf-label {
                    bottom: 15px;
                    padding: 8px 20px;
                }
                .mp-pcf-label h1 {
                    font-size: 18px !important;
                }
                 .mp-pcf-label i {
                    font-size: 16px !important;
                }
                .mp-pcf-subcats {
                    border-radius: 0;
                    margin-left: -20px;
                    margin-right: -20px;
                    padding-left: 20px;
                    padding-right: 20px;
                }
                .mp-pcf-circle-item { width: 80px; }
                .mp-pcf-circle-item .img-box { width: 60px; height: 60px; }
            }
        </style>';
    }
}, 999);

add_action( 'woocommerce_before_main_content', 'mp_inyectar_arriba_v17', 9 );

function mp_inyectar_arriba_v17() {
    if ( is_product_category() ) {
        $categoria = get_queried_object();
        $descripcion = trim( $categoria->description );
        $subcats = get_terms( array('taxonomy' => 'product_cat', 'parent' => $categoria->term_id, 'hide_empty' => false));

        echo '<div class="mp-top-master-wrapper">';

            if ( ! empty( $descripcion ) ) {
                echo '<div class="mp-pcf-hero">';
                    echo '<div class="mp-pcf-label">';
                        echo '<h1>' . esc_html( $categoria->name ) . '_</h1>';
                        echo '<i class="fas fa-laptop" style="color:#fff; font-size:20px;"></i>';
                    echo '</div>';

                    echo '<div class="mp-pcf-slider-wrap">';
                        $clean_desc = str_replace( array('<p>', '</p>', '<br>', '<br />'), '', $descripcion );
                        echo do_shortcode( trim( $clean_desc ) );
                    echo '</div>';
                echo '</div>';
            }

            if ( ! empty( $subcats ) && ! is_wp_error( $subcats ) ) {
                echo '<div class="mp-pcf-subcats">';
                foreach ( $subcats as $subcat ) {
                    $thumb_id = get_term_meta( $subcat->term_id, 'thumbnail_id', true );
                    $img_url = wp_get_attachment_url( $thumb_id ) ?: wc_placeholder_img_src();
                    echo '<div class="mp-pcf-circle-item">
                            <a href="' . esc_url( get_term_link( $subcat ) ) . '">
                                <div class="img-box"><img src="' . esc_url( $img_url ) . '"></div>
                                <h2>' . esc_html( $subcat->name ) . '</h2>
                            </a>
                          </div>';
                }
                echo '</div>';
            }

        echo '</div>';
    }
}