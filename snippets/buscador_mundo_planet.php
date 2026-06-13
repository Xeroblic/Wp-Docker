add_shortcode('buscador_mundo_planet', 'generar_buscador_mundo_planet_ajax');

function generar_buscador_mundo_planet_ajax() {
    ob_start();
    $ajax_url = admin_url('admin-ajax.php');
    ?>
    
    <div class="mp-buscador-wrapper">
        <form role="search" method="get" class="mp-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <input type="search" id="mp-search-input" class="mp-search-field" placeholder="Busca productos o categorías..." value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
            <button type="submit" class="mp-search-btn"><i class="fas fa-search"></i></button>
            <input type="hidden" name="post_type" value="product" />
        </form>
        
        <div id="mp-search-results" class="mp-search-results-dropdown"></div>
    </div>

<style>
        .mp-buscador-wrapper {
            width: 100%;
            position: relative; 
            font-family: 'Montserrat', Arial, sans-serif;
            z-index: 100;
        }

        .mp-search-form {
            display: flex;
            width: 100%;
            background: #ffffff;
            border-radius: 4px;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .mp-search-form:focus-within {
            transform: scale(1.02) translateY(-2px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .mp-search-form::after {
            content: '';
            position: absolute;
            bottom: -10px;
            border-left: 12px solid transparent;
            border-right: 12px solid transparent;
            border-top: 12px solid #ffffff;
            filter: drop-shadow(0px 4px 2px rgba(0,0,0,0.04));
        }

        .mp-search-field {
            flex-grow: 1;
            border: none !important;
            outline: none !important;
            background: transparent !important;
            margin: 0 !important;
            box-shadow: none !important;
            color: #333;
        }

        .mp-search-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            color: #4a5568;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .mp-search-btn:hover {
            color: #1a365d;
            transform: scale(1.15) rotate(5deg);
        }

        .mp-search-results-dropdown {
            display: none; 
            position: absolute;
            left: 0;
            width: 100%;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.18);
            z-index: 99999;
            overflow-y: auto;
            animation: slideDownPcf 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        @keyframes slideDownPcf {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .mp-search-section-title { background: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 8px 15px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb; }
        .mp-search-section-title:first-child { border-top: none; }
        .mp-search-result-item { display: flex; align-items: center; padding: 12px 15px; border-bottom: 1px solid #f3f4f6; text-decoration: none !important; color: #333 !important; transition: background 0.2s; }
        .mp-search-result-item:last-child { border-bottom: none; }
        .mp-search-result-item:hover { background: #f4fcf7; }
        .mp-search-result-item img { width: 45px; height: 45px; object-fit: cover; border-radius: 4px; margin-right: 15px; border: 1px solid #eee; }
        .mp-search-cat-icon { width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background: #edf2f7; color: #4a5568; border-radius: 4px; margin-right: 15px; font-size: 18px; transition: all 0.2s ease; }
        .mp-search-result-item:hover .mp-search-cat-icon { background: #1a365d; color: #ffffff; transform: scale(1.05); }
        .mp-search-result-info { display: flex; flex-direction: column; }
        .mp-search-result-title { font-size: 14px; font-weight: 600; line-height: 1.2; margin-bottom: 4px; color: #111; }
        .mp-search-result-price { font-size: 13px; color: #23c16b; font-weight: 700; }
        .mp-search-result-price del { color: #999; font-size: 11px; font-weight: normal; margin-right: 5px; }
        .mp-search-loading { padding: 20px; text-align: center; color: #666; font-size: 14px; }


        @media (min-width: 769px) {
            .mp-buscador-wrapper {
                min-width: 400px;
                margin: 0 20px;
            }
            .mp-search-form::after {
                left: 45px;
            }
            .mp-search-field {
                padding: 14px 20px !important;
                font-size: 15px;
            }
            .mp-search-btn {
                padding: 0 20px;
                font-size: 18px;
            }
            .mp-search-results-dropdown {
                top: calc(100% + 18px);
                max-height: 500px;
            }
        }


        @media (max-width: 768px) {
            .mp-buscador-wrapper {
                width: 100%;
                margin: 5px 0;
            }
            .mp-search-form::after {
                left: 50%;
                transform: translateX(-50%);
                border-width: 8px; 
                bottom: -8px;
            }
            .mp-search-field {
                padding: 10px 15px !important;
                font-size: 13px; 
            }
            .mp-search-btn {
                padding: 0 12px;
                font-size: 16px;
            }
            .mp-search-results-dropdown {
                top: calc(100% + 12px);
                max-height: 350px; 
            }
            .mp-search-result-item img, .mp-search-cat-icon {
                width: 35px; height: 35px; margin-right: 10px; font-size: 14px;
            }
            .mp-search-result-title { font-size: 12px; }
            .mp-search-result-price { font-size: 12px; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('mp-search-input');
            const searchResults = document.getElementById('mp-search-results');
            const ajaxUrl = '<?php echo $ajax_url; ?>';
            let timeout = null;

            if(searchInput && searchResults) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(timeout);
                    const keyword = this.value.trim();

                    if (keyword.length < 3) {
                        searchResults.style.display = 'none';
                        searchResults.innerHTML = '';
                        return;
                    }

                    searchResults.style.display = 'block';
                    searchResults.innerHTML = '<div class="mp-search-loading"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';

                    timeout = setTimeout(() => {
                        const formData = new FormData();
                        formData.append('action', 'mp_live_search');
                        formData.append('keyword', keyword);

                        fetch(ajaxUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            searchResults.innerHTML = data;
                        });
                    }, 400);
                });

                document.addEventListener('click', function(e) {
                    if (!searchResults.contains(e.target) && e.target !== searchInput) {
                        searchResults.style.display = 'none';
                    }
                });
                
                searchInput.addEventListener('click', function() {
                    if (this.value.trim().length >= 3 && searchResults.innerHTML !== '') {
                        searchResults.style.display = 'block';
                    }
                });
            }
        });
    </script>
    <?php
    return ob_get_clean();
}

add_action('wp_ajax_mp_live_search', 'mp_live_search_handler');
add_action('wp_ajax_nopriv_mp_live_search', 'mp_live_search_handler');

function mp_live_search_handler() {
    $keyword = sanitize_text_field($_POST['keyword']);
    $encontrado = false; 

    $categorias = get_terms(array(
        'taxonomy'   => 'product_cat',
        'name__like' => $keyword,
        'hide_empty' => false,
        'number'     => 3 
    ));

    if (!empty($categorias) && !is_wp_error($categorias)) {
        $encontrado = true;
        echo '<div class="mp-search-section-title">Categorías</div>';
        
        foreach ($categorias as $cat) {
            ?>
            <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="mp-search-result-item">
                <div class="mp-search-cat-icon"><i class="fas fa-folder-open"></i></div>
                <div class="mp-search-result-info">
                    <span class="mp-search-result-title"><?php echo esc_html($cat->name); ?></span>
                    <span class="mp-search-result-price" style="color: #64748b; font-weight: normal;">Ver toda la categoría</span>
                </div>
            </a>
            <?php
        }
    }

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        's'              => $keyword,
        'posts_per_page' => 5 
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $encontrado = true;
        echo '<div class="mp-search-section-title">Productos</div>';
        
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            
            $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'thumbnail');
            $img_url = $image ? $image[0] : wc_placeholder_img_src();
            $price = $product->get_price_html();
            
            ?>
            <a href="<?php the_permalink(); ?>" class="mp-search-result-item">
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php the_title_attribute(); ?>">
                <div class="mp-search-result-info">
                    <span class="mp-search-result-title"><?php the_title(); ?></span>
                    <span class="mp-search-result-price"><?php echo $price; ?></span>
                </div>
            </a>
            <?php
        }
        
        ?>
        <a href="<?php echo esc_url( home_url( '/?s=' . urlencode($keyword) . '&post_type=product' ) ); ?>" class="mp-search-result-item" style="justify-content: center; background: #f8fafc; color: #1a365d; font-weight: bold;">
            Ver todos los resultados para "<?php echo esc_html($keyword); ?>" ❯
        </a>
        <?php
    }

    if (!$encontrado) {
        echo '<div class="mp-search-loading">Pucha, no encontramos productos ni categorías con ese nombre :(</div>';
    }

    wp_reset_postdata();
    wp_die();
}