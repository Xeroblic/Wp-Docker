add_shortcode('menu_dinamico_categorias_mobile', 'generar_menu_ecommerce_slider_limpio');

function generar_menu_ecommerce_slider_limpio() {
    $categorias_padre = get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => false,
    ));

    if (empty($categorias_padre) || is_wp_error($categorias_padre)) {
        return 'No hay categorías creadas aún.';
    }

    $num_paneles = 1;
    foreach ($categorias_padre as $cat) {
        $subs = get_terms(array('taxonomy' => 'product_cat', 'parent' => $cat->term_id, 'hide_empty' => false));
        if (!empty($subs) && !is_wp_error($subs)) $num_paneles++;
    }

    $track_width  = $num_paneles * 100;
    $panel_width  = round(100 / $num_paneles, 4);
    $uid = 'zntr' . substr(md5(uniqid('', true)), 0, 8);

    ob_start();
    ?>

    <style>
        /* RESET BLINDADO */
        #<?php echo $uid; ?> ul,
        #<?php echo $uid; ?> ol,
        #<?php echo $uid; ?> li {
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #<?php echo $uid; ?> a {
            text-decoration: none !important;
        }

        #<?php echo $uid; ?> *,
        #<?php echo $uid; ?> *::before,
        #<?php echo $uid; ?> *::after {
            box-sizing: border-box !important;
        }

        /* VARIABLES */
        #<?php echo $uid; ?> {
            --zntr-accent:     #6D96F7; 
            --zntr-text-dark:  #1e293b;
            --zntr-text-muted: #64748b;
            --zntr-border:     #f1f5f9;
            --zntr-bg:         #ffffff;
            --zntr-transition: 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            --zntr-panel-w:    <?php echo $panel_width; ?>%;

            display: block !important;
            width: 100% !important;
            font-family: 'Montserrat', Arial, sans-serif !important;
        }

        /* WRAPPER (La ventana principal) */
        #<?php echo $uid; ?> .zntrmenu-slider-wrapper {
            display: block !important;
            width: 100% !important;
            min-height: 350px !important;
            overflow: hidden !important;
            background: var(--zntr-bg) !important;
            position: relative !important;
        }

        /* TRACK (El tren de paneles) */
        #<?php echo $uid; ?> .zntrmenu-track {
            display: flex !important;
            flex-wrap: nowrap !important;
            width: <?php echo $track_width; ?>% !important;
            transition: transform var(--zntr-transition) !important;
            align-items: flex-start !important;
            transform: translateX(0%) !important;
        }

        /* PANELES */
        #<?php echo $uid; ?> .zntrmenu-panel {
            display: block !important;
            width: var(--zntr-panel-w) !important;
            flex-shrink: 0 !important;
            background: var(--zntr-bg) !important;
            min-width: 0 !important;
        }

        /* LISTA PRINCIPAL */
        #<?php echo $uid; ?> .zntrmenu-main-list {
            display: block !important;
            width: 100% !important;
        }

        #<?php echo $uid; ?> .zntrmenu-main-list > li {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            border-bottom: 1px solid var(--zntr-border) !important;
            background: var(--zntr-bg) !important;
            box-shadow: none !important;
            width: 100% !important;
        }

        #<?php echo $uid; ?> .zntrmenu-main-list > li > a {
            flex-grow: 1 !important;
            display: block !important;
            padding: 16px 20px !important;
            color: var(--zntr-text-dark) !important;
            font-weight: 500 !important;
            font-size: 15px !important;
            background: transparent !important;
            transition: color 0.2s ease !important;
            border: none !important;
        }

        #<?php echo $uid; ?> .zntrmenu-main-list > li > a:hover {
            color: var(--zntr-accent) !important;
        }

        /* BOTONES NEXT */
        #<?php echo $uid; ?> .zntrmenu-next-btn {
            flex-shrink: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 44px !important;
            height: 44px !important;
            margin-right: 8px !important;
            cursor: pointer !important;
            color: var(--zntr-accent) !important;
            background: #f4f7fc !important; 
            border: none !important;
            border-radius: 6px !important;
            transition: all 0.2s ease !important;
            position: relative;
            z-index: 10;
        }

        #<?php echo $uid; ?> .zntrmenu-next-btn:hover {
            background: var(--zntr-accent) !important;
            color: #ffffff !important;
        }

        /* EVITAR QUE EL SVG INTERCEPTE EL CLIC */
        #<?php echo $uid; ?> .zntrmenu-next-btn svg,
        #<?php echo $uid; ?> .zntrmenu-back-btn svg {
            display: block !important;
            flex-shrink: 0 !important;
            pointer-events: none !important; 
        }

        /* HEADER SUB-PANEL (El Botón de Volver) */
        #<?php echo $uid; ?> .zntrmenu-panel-header {
            display: block !important;
            padding: 15px 20px !important;
            background: #f8fafc !important; 
            border-bottom: 2px solid var(--zntr-accent) !important; 
        }

        #<?php echo $uid; ?> .zntrmenu-back-btn {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            cursor: pointer !important;
            color: var(--zntr-text-dark) !important;
            font-weight: 700 !important;
            font-size: 16px !important;
            text-decoration: none !important;
            background: transparent !important;
            border: none !important;
            transition: color 0.2s ease !important;
            padding: 0 !important;
            width: 100%;
            text-align: left;
        }

        #<?php echo $uid; ?> .zntrmenu-back-btn:hover {
            color: var(--zntr-accent) !important;
        }

        /* CONTENIDO SUB-PANEL */
        #<?php echo $uid; ?> .zntrmenu-sub-content {
            display: block !important;
            padding: 20px !important;
            background: var(--zntr-bg) !important;
        }

        #<?php echo $uid; ?> .zntrmenu-sub-group {
            display: block !important;
            margin: 0 0 25px 0 !important;
        }

        #<?php echo $uid; ?> .zntrmenu-sub-title {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 12px !important;
            color: var(--zntr-text-dark) !important;
            font-weight: 700 !important;
            font-size: 15px !important;
            background: transparent !important;
            transition: color 0.2s ease !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        #<?php echo $uid; ?> .zntrmenu-sub-title svg {
            flex-shrink: 0 !important;
            color: #cbd5e1 !important;
        }

        #<?php echo $uid; ?> .zntrmenu-sub-title:hover {
            color: var(--zntr-accent) !important;
        }

        #<?php echo $uid; ?> .zntrmenu-sub-list > li {
            display: block !important;
            background: var(--zntr-bg) !important;
        }

        #<?php echo $uid; ?> .zntrmenu-sub-list > li > a {
            display: block !important;
            padding: 10px 0 10px 16px !important;
            color: var(--zntr-text-muted) !important;
            font-size: 14px !important;
            transition: color 0.2s ease, padding-left 0.2s ease !important;
            border-left: 2px solid transparent !important;
        }

        #<?php echo $uid; ?> .zntrmenu-sub-list > li > a:hover {
            color: var(--zntr-accent) !important;
            padding-left: 20px !important;
            border-left: 2px solid var(--zntr-accent) !important;
        }
    </style>

    <?php
    $panel_index_map = array();
    $panel_idx = 1;
    foreach ($categorias_padre as $cat) {
        $subs_check = get_terms(array('taxonomy' => 'product_cat', 'parent' => $cat->term_id, 'hide_empty' => false));
        if (!empty($subs_check) && !is_wp_error($subs_check)) {
            $panel_index_map[$cat->term_id] = $panel_idx;
            $panel_idx++;
        }
    }
    ?>

    <div id="<?php echo $uid; ?>">
        <div class="zntrmenu-slider-wrapper">
            <div class="zntrmenu-track" data-panel-count="<?php echo $num_paneles; ?>">

                <div class="zntrmenu-panel">
                    <ul class="zntrmenu-main-list">
                        <?php foreach ($categorias_padre as $cat) :
                            $has_children = isset($panel_index_map[$cat->term_id]);
                        ?>
                            <li>
                                <a href="<?php echo esc_url(get_term_link($cat)); ?>">
                                    <?php echo esc_html($cat->name); ?>
                                </a>
                                <?php if ($has_children) : ?>
                                    <button
                                        class="zntrmenu-next-btn"
                                        data-panel-index="<?php echo $panel_index_map[$cat->term_id]; ?>"
                                        aria-label="Ver subcategorías"
                                    >
                                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
                                            <path d="M9 18l6-6-6-6"/>
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php foreach ($categorias_padre as $cat) :
                    if (!isset($panel_index_map[$cat->term_id])) continue;
                    $subcategorias = get_terms(array(
                        'taxonomy'   => 'product_cat',
                        'parent'     => $cat->term_id,
                        'hide_empty' => false,
                    ));
                ?>
                    <div class="zntrmenu-panel">
                        <div class="zntrmenu-panel-header">
                            <button class="zntrmenu-back-btn" aria-label="Volver">
                                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2.5" fill="none">
                                    <path d="M15 18l-6-6 6-6"/>
                                </svg>
                                Menú Principal
                            </button>
                        </div>
                        <div class="zntrmenu-sub-content">
                            <?php foreach ($subcategorias as $subcat) :
                                $nietos = get_terms(array(
                                    'taxonomy'   => 'product_cat',
                                    'parent'     => $subcat->term_id,
                                    'hide_empty' => false,
                                ));
                            ?>
                                <div class="zntrmenu-sub-group">
                                    <a href="<?php echo esc_url(get_term_link($subcat)); ?>" class="zntrmenu-sub-title">
                                        <?php echo esc_html($subcat->name); ?>
                                        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none">
                                            <path d="M9 18l6-6-6-6"/>
                                        </svg>
                                    </a>
                                    
                                    <?php if (!empty($nietos) && !is_wp_error($nietos)) : ?>
                                        <ul class="zntrmenu-sub-list">
                                            <?php foreach ($nietos as $nieto) : ?>
                                                <li>
                                                    <a href="<?php echo esc_url(get_term_link($nieto)); ?>">
                                                        <?php echo esc_html($nieto->name); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>

    <img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" onload="if(!window.zntrMenuInit){window.zntrMenuInit=true;document.addEventListener('click',function(e){var t=e.target,n=t.closest?t.closest('.zntrmenu-next-btn'):null,b=t.closest?t.closest('.zntrmenu-back-btn'):null;if(n){e.preventDefault();e.stopPropagation();var r=n.closest('.zntrmenu-track');if(r){var c=parseFloat(r.getAttribute('data-panel-count'))||1,i=parseInt(n.getAttribute('data-panel-index'),10)||0;r.style.transform='translateX(-'+(i*(100/c))+'%)'}}if(b){e.preventDefault();e.stopPropagation();var r=b.closest('.zntrmenu-track');if(r)r.style.transform='translateX(0%)'}},true);}" style="display:none;" />

    <?php
    return ob_get_clean();
}