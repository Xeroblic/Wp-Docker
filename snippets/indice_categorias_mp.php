add_shortcode('indice_categorias_mp', 'generar_pagina_todas_categorias_v2');

function generar_pagina_todas_categorias_v2() {
    $categorias_padre = get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => false, 
    ));

    if (empty($categorias_padre) || is_wp_error($categorias_padre)) {
        return '<p>No hay categorías creadas aún.</p>';
    }

    $iconos = array(
        'computadores' => 'fas fa-laptop',
        'smartphones'  => 'fas fa-mobile-alt',
        'gamer'        => 'fas fa-gamepad',
        'audio'        => 'fas fa-headphones',
        'componentes'  => 'fas fa-microchip',
        'indoor-plants'=> 'fas fa-leaf',
        'conica'       => 'fas fa-seedling', 
        'sin-categoria'=> 'fas fa-folder-open',
    );

    ob_start();
    ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        .mp-dir-full-page {
            background-color: #f8fafc;
            padding: 40px 20px;
            font-family: 'Montserrat', sans-serif;
            border-radius: 10px;
        }

        .mp-dir-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }

        .mp-dir-header {
            background-color: #4683b2; 
            color: #ffffff;
            padding: 35px 45px;
            border-radius: 8px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 15px rgba(70, 131, 178, 0.2);
        }
        
        .mp-dir-header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 800;
            color: #ffffff !important;
            letter-spacing: -0.5px;
        }
        
        .mp-dir-header .icon-box {
            background: rgba(255, 255, 255, 0.2);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 22px;
        }

        .mp-dir-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            align-items: start;
        }

        .mp-dir-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            height: 100%;
        }
        
        .mp-dir-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.07);
            border-color: #4683b2;
        }

        .mp-dir-box-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .mp-dir-box-title h3 {
            margin: 0;
            font-size: 19px;
            font-weight: 700;
            color: #1a365d;
        }
        
        .mp-dir-box-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .mp-dir-box-title a:hover {
            color: #4683b2;
        }

        .mp-dir-box-icon {
            color: #23c16b;
            font-size: 20px;
            background: #f0fdf4;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .mp-dir-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .mp-dir-list li {
            margin-bottom: 14px;
        }
        
        .mp-dir-list a {
            color: #64748b;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .mp-dir-list a::before {
            content: "";
            width: 6px;
            height: 6px;
            background-color: #cbd5e1;
            border-radius: 50%;
            display: inline-block;
            margin-right: 12px;
            transition: all 0.2s;
        }

        .mp-dir-list a:hover {
            color: #1a365d;
            padding-left: 8px;
        }
        
        .mp-dir-list a:hover::before {
            background-color: #23c16b;
            transform: scale(1.5);
        }

        .mp-dir-empty-msg {
            font-size: 13px;
            color: #94a3b8;
            font-style: italic;
        }

        @media (max-width: 1024px) {
            .mp-dir-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .mp-dir-grid { grid-template-columns: 1fr; }
            .mp-dir-header { padding: 25px; border-radius: 0; margin: -40px -20px 30px -20px; }
            .mp-dir-header h1 { font-size: 24px; }
        }
    </style>

    <div class="mp-dir-full-page">
        <div class="mp-dir-wrapper">
            
            <div class="mp-dir-header">
                <div class="icon-box"><i class="fas fa-th-large"></i></div>
                <h1>Todas las categorías</h1>
            </div>

            <div class="mp-dir-grid">
                <?php foreach ($categorias_padre as $cat) : 
                    $slug = $cat->slug;
                    $icono_clase = isset($iconos[$slug]) ? $iconos[$slug] : 'fas fa-folder';
                    
                    $subcategorias = get_terms(array(
                        'taxonomy'   => 'product_cat',
                        'parent'     => $cat->term_id,
                        'hide_empty' => false,
                    ));
                ?>
                    <div class="mp-dir-box">
                        <div class="mp-dir-box-title">
                            <h3><a href="<?php echo get_term_link($cat); ?>"><?php echo esc_html($cat->name); ?></a></h3>
                            <div class="mp-dir-box-icon"><i class="<?php echo esc_attr($icono_clase); ?>"></i></div>
                        </div>
                        
                        <?php if (!empty($subcategorias) && !is_wp_error($subcategorias)) : ?>
                            <ul class="mp-dir-list">
                                <?php foreach ($subcategorias as $subcat) : ?>
                                    <li><a href="<?php echo get_term_link($subcat); ?>"><?php echo esc_html($subcat->name); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="mp-dir-empty-msg">Explora los productos de esta sección.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}