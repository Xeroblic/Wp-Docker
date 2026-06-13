add_shortcode('menu_dinamico_categorias', 'generar_menu_mundo_planet_unificado');

function generar_menu_mundo_planet_unificado() {
    $categorias_padre = get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => false, 
    ));

    if (empty($categorias_padre) || is_wp_error($categorias_padre)) {
        return 'No hay categorías creadas aún.';
    }

    $iconos = array(
        'computadores' => 'fas fa-laptop',
        'smartphones'  => 'fas fa-mobile-alt',
        'gamer'        => 'fas fa-gamepad',
        'audio'        => 'fas fa-headphones',
        'componentes'  => 'fas fa-microchip',
        'indoor-plants'=> 'fas fa-leaf',
        'conica'       => 'fas fa-seedling', 
    );

    ob_start();
    ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        .ast-builder-html-element, .ast-header-html-inner { overflow: visible !important; }
        .pcf-dropdown-wrapper { position: relative; display: inline-block; font-family: 'Montserrat', Arial, sans-serif; }
        .pcf-dropdown-btn { 
			background-color: transparent; 
			color: #ffffff; 
			padding: 10px 15px; 
			font-size: 16px; 
			font-weight: 600; 
			border-radius: 5px; 
			cursor: pointer; 
			display: flex; 
			align-items: center; 
			gap: 8px; 
			transition: background 0.3s; 
			border: none; 
			white-space: nowrap;
			outline: none !important;
			box-shadow: none !important;
		}
        .pcf-dropdown-btn:hover,
        .pcf-dropdown-btn:focus,
        .pcf-dropdown-btn:active { 
            background-color: rgba(255, 255, 255, 0.2) !important; 
            color: #ffffff !important;
            outline: none !important;
            box-shadow: none !important;
        }
        
        .pcf-mega-container { display: none; position: absolute; top: 110%; left: 0; width: 850px; max-width: 90vw; min-height: 450px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.15); text-align: left; z-index: 99999; }
        .pcf-mega-container.show-mega { display: flex !important; }
        .pcf-mega-sidebar { width: 300px; background: #ffffff; border-right: 2px solid #e5e7eb; margin: 0 !important; padding: 20px 0 !important; list-style: none !important; }
        .pcf-mega-sidebar li { margin: 0 !important; padding: 0 !important; }
        .pcf-mega-sidebar li a { display: flex; align-items: center; padding: 12px 25px; color: #333333; text-decoration: none; font-size: 15px; font-weight: 600; transition: all 0.2s ease; cursor: pointer; position: relative; }
        .pcf-mega-sidebar li a i.cat-icon { width: 24px; text-align: center; margin-right: 12px; font-size: 18px; color: #64748b; transition: color 0.2s ease; }
        .pcf-mega-sidebar li a svg.arrow-icon { width: 16px; height: 16px; color: #a0aec0; margin-left: auto; }
        .pcf-mega-sidebar li a:hover, .pcf-mega-sidebar li.active a { background-color: #f4fcf7; color: #23c16b; border-left: 4px solid #23c16b; padding-left: 21px; }
        .pcf-mega-sidebar li a:hover i.cat-icon, .pcf-mega-sidebar li.active a i.cat-icon, .pcf-mega-sidebar li.active a svg.arrow-icon { color: #23c16b; }
        .pcf-mega-content-area { flex-grow: 1; background: #ffffff; position: relative; }
        .pcf-mega-panel { display: none; padding: 40px; width: 100%; height: 100%; animation: fadeInPcf 0.3s ease; }
        .pcf-mega-panel.active { display: block; }
        @keyframes fadeInPcf { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }
        .pcf-mega-panel-header { font-size: 16px; font-weight: 700; color: #111; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #23c16b; display: inline-block; width: 100%; }
        .pcf-mega-panel-header a { color: inherit; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .pcf-mega-panel-header a span { color: #666; font-weight: 400; font-size: 14px; }
        .pcf-mega-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
        .pcf-mega-col h4 { font-size: 15px; font-weight: 800; color: #1a365d; margin-bottom: 15px; margin-top: 0; }
        .pcf-mega-col h4 a { color: inherit; text-decoration: none; }
        .pcf-mega-col h4 a:hover { text-decoration: underline; }
        .pcf-mega-col ul { list-style: none !important; margin: 0 !important; padding: 0 !important; }
        .pcf-mega-col ul li { margin-bottom: 10px !important; padding: 0 !important; }
        .pcf-mega-col ul li a { color: #4a5568; font-size: 14px; font-weight: 400; text-decoration: none; transition: color 0.2s; }
        .pcf-mega-col ul li a:hover { color: #23c16b; font-weight: 600; }

        :root { --nx-accent: #23c16b; --nx-text-dark: #1e293b; --nx-text-muted: #64748b; --nx-border: #f1f5f9; --nx-bg: #ffffff; }
        .nx-mobile-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); z-index: 999998; opacity: 0; transition: opacity 0.3s ease; }
        .nx-mobile-backdrop.show { display: block; opacity: 1; }
        .nx-slider-wrapper { position: fixed; top: 0; left: 0; width: 320px; max-width: 85vw; height: 100vh; background: var(--nx-bg); overflow-x: hidden; overflow-y: auto; font-family: 'Montserrat', Arial, sans-serif; box-shadow: 5px 0 25px rgba(0,0,0,0.2); z-index: 999999; transform: translateX(-100%); transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
        .nx-slider-wrapper.show-mobile { transform: translateX(0); }
        .nx-mobile-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #1a365d; color: #fff; font-size: 16px; font-weight: 700; }
        .nx-mobile-close { background: transparent; border: none; color: #fff; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0; }
        .nx-panel { width: 100%; background: var(--nx-bg); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); min-height: calc(100vh - 65px); }
        .nx-panel-main { position: relative; z-index: 1; }
        .nx-panel-main.slide-out { transform: translateX(-100%); }
        .nx-panel-sub { position: absolute; top: 0; left: 0; width: 100%; background: var(--nx-bg); transform: translateX(100%); z-index: 2; visibility: hidden; }
        .nx-panel-sub.active { transform: translateX(0); visibility: visible; }
        .nx-main-list { list-style: none; padding: 0; margin: 0; }
        .nx-main-list li { border-bottom: 1px solid var(--nx-border); display: flex; align-items: center; justify-content: space-between; }
        .nx-main-list a { flex-grow: 1; padding: 16px 20px; color: var(--nx-text-dark); text-decoration: none; font-weight: 500; font-size: 15px; }
        button.nx-next-btn { background: transparent !important; border: none !important; padding: 16px 20px !important; cursor: pointer !important; color: #94a3b8 !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-right: 5px !important; box-shadow: none !important; }
        .nx-panel-header { padding: 20px; border-bottom: 2px solid var(--nx-accent); margin-bottom: 10px; background: #f8fafc; }
        .nx-back-btn { background: transparent; border: none; padding: 0; cursor: pointer; color: var(--nx-text-dark); font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 10px; }
        .nx-sub-content { padding: 10px 20px; }
        .nx-sub-group { margin-bottom: 25px; }
        .nx-sub-title { display: flex; justify-content: space-between; align-items: center; color: var(--nx-text-dark); font-weight: 700; font-size: 14px; text-decoration: none; margin-bottom: 12px; }
        .nx-sub-list { list-style: none; padding: 0 0 0 15px; margin: 0; }
        .nx-sub-list a { display: block; color: var(--nx-text-muted); font-size: 14px; text-decoration: none; padding: 8px 0; }

        @media (max-width: 992px) {
            .pcf-dropdown-btn span { display: none; }
            .pcf-dropdown-btn { padding: 10px; } 
            .pcf-mega-container { display: none !important; }
        }
        @media (min-width: 993px) {
            .nx-slider-wrapper, .nx-mobile-backdrop { display: none !important; }
        }
    </style>

    <div class="mp-master-menu-module">
        <div class="pcf-dropdown-wrapper">
            <button class="pcf-dropdown-btn js-dropdown-trigger">
                <i class="fas fa-bars"></i> <span>Categorías</span>
            </button>

            <div class="pcf-mega-container js-mega-menu">
                <ul class="pcf-mega-sidebar">
                    <?php 
                    $first = true;
                    foreach ($categorias_padre as $cat) : 
                        $active_class = $first ? 'active' : ''; 
                        $slug = $cat->slug;
                        $icono_clase = isset($iconos[$slug]) ? $iconos[$slug] : 'fas fa-box';
                    ?>
                        <li class="<?php echo $active_class; ?>" data-target="mega-panel-<?php echo $cat->term_id; ?>">
                            <a href="<?php echo get_term_link($cat); ?>">
                                <i class="<?php echo esc_attr($icono_clase); ?> cat-icon"></i> 
                                <?php echo esc_html($cat->name); ?>
                                <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                            </a>
                        </li>
                    <?php $first = false; endforeach; ?>
                </ul>

                <div class="pcf-mega-content-area">
                    <?php 
                    $first_panel = true;
                    foreach ($categorias_padre as $cat) : 
                        $panel_class = $first_panel ? 'active' : '';
                        $subcategorias = get_terms(array('taxonomy' => 'product_cat', 'parent' => $cat->term_id, 'hide_empty' => false));
                    ?>
                        <div class="pcf-mega-panel <?php echo $panel_class; ?>" data-panel="mega-panel-<?php echo $cat->term_id; ?>">
                            <div class="pcf-mega-panel-header">
                                <a href="<?php echo get_term_link($cat); ?>"><?php echo esc_html($cat->name); ?> <span>Ver categoría completa ❯</span></a>
                            </div>
                            
                            <?php if (!empty($subcategorias) && !is_wp_error($subcategorias)) : ?>
                                <div class="pcf-mega-grid">
                                    <?php foreach ($subcategorias as $subcat) : 
                                        $nietos = get_terms(array('taxonomy' => 'product_cat', 'parent' => $subcat->term_id, 'hide_empty' => false));
                                    ?>
                                        <div class="pcf-mega-col">
                                            <h4><a href="<?php echo get_term_link($subcat); ?>"><?php echo esc_html($subcat->name); ?></a></h4>
                                            <?php if (!empty($nietos) && !is_wp_error($nietos)) : ?>
                                                <ul>
                                                    <?php foreach ($nietos as $nieto) : ?>
                                                        <li><a href="<?php echo get_term_link($nieto); ?>"><?php echo esc_html($nieto->name); ?></a></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php $first_panel = false; endforeach; ?>
                </div>
            </div>
        </div>

        <div class="nx-mobile-backdrop js-mobile-backdrop"></div>
        <div class="nx-slider-wrapper js-mobile-menu">
            <div class="nx-mobile-header">
                <span>Menú Principal</span>
                <button class="nx-mobile-close js-mobile-close">&times;</button>
            </div>
            <div class="nx-panel nx-panel-main js-main-panel">
                <ul class="nx-main-list">
                    <?php foreach ($categorias_padre as $cat) : 
                        $subcategorias_test = get_terms(array('taxonomy' => 'product_cat', 'parent' => $cat->term_id, 'hide_empty' => false));
                        $has_children = !empty($subcategorias_test) && !is_wp_error($subcategorias_test);
                    ?>
                        <li>
                            <a href="<?php echo get_term_link($cat); ?>"><?php echo esc_html($cat->name); ?></a>
                            <?php if ($has_children) : ?>
                                <button class="nx-next-btn js-next-btn" data-target="mob-panel-<?php echo $cat->term_id; ?>">
                                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php foreach ($categorias_padre as $cat) : 
                $subcategorias = get_terms(array('taxonomy' => 'product_cat', 'parent' => $cat->term_id, 'hide_empty' => false));
                if (empty($subcategorias) || is_wp_error($subcategorias)) continue;
            ?>
                <div class="nx-panel nx-panel-sub js-sub-panel" data-panel="mob-panel-<?php echo $cat->term_id; ?>">
                    <div class="nx-panel-header">
                        <button class="nx-back-btn js-back-btn">
                            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M15 18l-6-6 6-6"/></svg>
                            <?php echo esc_html($cat->name); ?>
                        </button>
                    </div>
                    <div class="nx-sub-content">
                        <?php foreach ($subcategorias as $subcat) : 
                            $nietos = get_terms(array('taxonomy' => 'product_cat', 'parent' => $subcat->term_id, 'hide_empty' => false));
                        ?>
                            <div class="nx-sub-group">
                                <a href="<?php echo get_term_link($subcat); ?>" class="nx-sub-title">
                                    <?php echo esc_html($subcat->name); ?>
                                    <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none"><path d="M9 18l6-6-6-6"/></svg>
                                </a>
                                <?php if (!empty($nietos) && !is_wp_error($nietos)) : ?>
                                    <ul class="nx-sub-list">
                                        <?php foreach ($nietos as $nieto) : ?>
                                            <li><a href="<?php echo get_term_link($nieto); ?>"><?php echo esc_html($nieto->name); ?></a></li>
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.mpMenuScriptsLoaded) return;
            window.mpMenuScriptsLoaded = true;

            document.addEventListener('click', function(e) {
                const triggerBtn = e.target.closest('.js-dropdown-trigger');
                
                if (triggerBtn) {
                    e.preventDefault();
                    e.stopPropagation(); 
                    
                    const module = triggerBtn.closest('.mp-master-menu-module');
                    if(!module) return;

                    const megaMenuDesktop = module.querySelector('.js-mega-menu');
                    const menuMobile = module.querySelector('.js-mobile-menu');
                    const mobileBackdrop = module.querySelector('.js-mobile-backdrop');

                    if(window.innerWidth > 992) {
                        if(megaMenuDesktop) {
                            document.querySelectorAll('.js-mega-menu').forEach(m => {
                                if(m !== megaMenuDesktop) m.classList.remove('show-mega');
                            });
                            megaMenuDesktop.classList.toggle('show-mega');
                        }
                    } else {
                        if(menuMobile) menuMobile.classList.add('show-mobile');
                        if(mobileBackdrop) mobileBackdrop.classList.add('show');
                        document.body.style.overflow = 'hidden'; 
                    }
                    return; 
                }

                if (window.innerWidth > 992) {
                    document.querySelectorAll('.js-mega-menu.show-mega').forEach(function(mega) {
                        const module = mega.closest('.mp-master-menu-module');
                        const btn = module ? module.querySelector('.js-dropdown-trigger') : null;
                        if (!mega.contains(e.target) && (!btn || !btn.contains(e.target))) {
                            mega.classList.remove('show-mega');
                        }
                    });
                } else {
                    const closeBtn = e.target.closest('.js-mobile-close');
                    const isBackdrop = e.target.classList.contains('js-mobile-backdrop');
                    if (closeBtn || isBackdrop) {
                        document.querySelectorAll('.js-mobile-menu').forEach(m => m.classList.remove('show-mobile'));
                        document.querySelectorAll('.js-mobile-backdrop').forEach(b => b.classList.remove('show'));
                        document.body.style.overflow = '';
                    }
                }
            });

            document.addEventListener('mouseover', function(e) {
                const li = e.target.closest('.pcf-mega-sidebar li');
                if(!li || li.classList.contains('active')) return; 

                const megaContainer = li.closest('.pcf-mega-container');
                if(!megaContainer) return;
                
                megaContainer.querySelectorAll('.pcf-mega-sidebar li').forEach(el => el.classList.remove('active'));
                megaContainer.querySelectorAll('.pcf-mega-panel').forEach(el => el.classList.remove('active'));
                
                li.classList.add('active');
                const targetPanel = megaContainer.querySelector(`[data-panel="${li.getAttribute('data-target')}"]`);
                if (targetPanel) targetPanel.classList.add('active');
            });

            document.addEventListener('click', function(e) {
                const nextBtn = e.target.closest('.js-next-btn');
                const backBtn = e.target.closest('.js-back-btn');

                if (nextBtn) {
                    e.preventDefault();
                    const module = nextBtn.closest('.nx-slider-wrapper');
                    const mainPanel = module.querySelector('.js-main-panel');
                    const targetPanel = module.querySelector(`[data-panel="${nextBtn.getAttribute('data-target')}"]`);
                    if (mainPanel && targetPanel) { mainPanel.classList.add('slide-out'); targetPanel.classList.add('active'); }
                }

                if (backBtn) {
                    e.preventDefault();
                    const module = backBtn.closest('.nx-slider-wrapper');
                    const currentPanel = backBtn.closest('.js-sub-panel');
                    const mainPanel = module.querySelector('.js-main-panel');
                    if (currentPanel && mainPanel) { currentPanel.classList.remove('active'); mainPanel.classList.remove('slide-out'); }
                }
            });
        });
    </script>

    <?php
    return ob_get_clean();
}