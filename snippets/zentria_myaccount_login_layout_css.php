add_action( 'wp_footer', 'zentria_myaccount_layout' );
function zentria_myaccount_layout() {
    global $post;
    
    $is_account = false;
    if ( is_a( $post, 'WP_Post' ) ) {
        if ( $post->post_name === 'my-account' || $post->post_name === 'mi-cuenta' ) {
            $is_account = true;
        }
        if ( has_shortcode( $post->post_content, 'woocommerce_my_account' ) ) {
            $is_account = true;
        }
    }
    if ( ! $is_account ) return;
    
    if ( is_user_logged_in() ) {
        zentria_render_dashboard_css();
    } else {
        zentria_render_login_css();
    }
}

function zentria_render_login_css() {
    ?>
    <style>
    /* ==============================================================
       ZENTRIA · LOGIN/REGISTER · FULLSCREEN SPLIT
       ============================================================== */
    .elementor-page .elementor-widget-text-editor:has(.woocommerce) .elementor-widget-container,
    .elementor-page .elementor-element:has(> .elementor-widget-text-editor .woocommerce) {
        padding: 0 !important; margin: 0 !important; max-width: 100% !important; width: 100% !important;
    }
    body.elementor-page { overflow: hidden !important; }
    .woocommerce {
        display: flex !important; width: 100vw !important; height: 100vh !important;
        margin: 0 !important; padding: 0 !important; position: fixed !important;
        top: 0; left: 0; background: #fff; z-index: 1;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .woocommerce::before {
        content: ""; flex: 0 0 50%; align-self: stretch;
        background-image: linear-gradient(135deg, rgba(37,150,190,0.45) 0%, rgba(15,80,105,0.75) 100%), url('/wp-content/uploads/2025/03/stats-count.jpg');
        background-size: cover; background-position: center; background-color: #0f5069; position: relative;
    }
    .woocommerce::after {
        content: "Bienvenido a Mundo Planet"; position: fixed; left: 5%; bottom: 10%; width: 38%;
        color: #fff; font-size: 44px; font-weight: 700; line-height: 1.15; letter-spacing: -0.02em;
        z-index: 2; text-shadow: 0 2px 24px rgba(0,0,0,0.25); pointer-events: none;
    }
    .woocommerce .u-columns {
        flex: 0 0 50%; display: flex !important; flex-direction: column;
        justify-content: center; align-items: center; padding: 40px 8%;
        overflow-y: auto; background: #fafafa; position: relative;
    }
    .woocommerce .u-columns::before {
        content: "MUNDO PLANET"; position: absolute; top: 32px; left: 50%; transform: translateX(-50%);
        font-size: 18px; font-weight: 700; letter-spacing: 0.2em; color: #2596be;
    }
    .woocommerce > .woocommerce-notices-wrapper {
        position: absolute; top: 20px; right: 20px; z-index: 10; max-width: 380px;
    }
    .woocommerce .u-columns .col-1, .woocommerce .u-columns .col-2 {
        width: 100% !important; max-width: 400px; margin: 0 auto !important;
        float: none !important; animation: zentriaFadeIn 0.4s ease;
    }
    .woocommerce .u-columns .col-2 { display: none; }
    body.zentria-show-register .woocommerce .u-columns .col-1 { display: none !important; }
    body.zentria-show-register .woocommerce .u-columns .col-2 { display: block !important; }
    @keyframes zentriaFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .woocommerce h2 {
        font-size: 30px; font-weight: 700; color: #0f172a;
        margin-bottom: 6px; letter-spacing: -0.02em;
    }
    .zentria-subtitle { font-size: 14px; color: #64748b; margin-bottom: 28px; line-height: 1.5; }
    .woocommerce form .form-row { margin-bottom: 16px; padding: 0; display: flex; flex-direction: column; }
    .woocommerce form label {
        font-size: 13px; font-weight: 600; color: #334155;
        margin-bottom: 6px; display: block; letter-spacing: 0.01em;
    }
    .woocommerce form label .required { color: #2596be; margin-left: 2px; }
    .woocommerce input[type="text"], .woocommerce input[type="email"], .woocommerce input[type="password"] {
        width: 100% !important; border: 1.5px solid #e2e8f0 !important; border-radius: 10px !important;
        padding: 12px 14px !important; font-size: 14px !important; background: #fff !important;
        color: #0f172a !important; transition: all 0.2s ease !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
    }
    .woocommerce input:hover { border-color: #cbd5e1 !important; }
    .woocommerce input:focus {
        border-color: #2596be !important; outline: none !important;
        box-shadow: 0 0 0 4px rgba(37,150,190,0.12), 0 1px 2px rgba(0,0,0,0.04) !important;
    }
    .woocommerce button.button, .woocommerce .woocommerce-button {
        width: 100% !important; background: linear-gradient(180deg, #2596be 0%, #1f86ac 100%) !important;
        color: #fff !important; border: none !important; border-radius: 10px !important;
        padding: 14px !important; font-size: 15px !important; font-weight: 600 !important;
        cursor: pointer !important; letter-spacing: 0.01em;
        box-shadow: 0 1px 3px rgba(37,150,190,0.3), 0 1px 2px rgba(0,0,0,0.06) !important;
        transition: all 0.2s ease !important; margin-top: 8px !important;
    }
    .woocommerce button.button:hover {
        background: linear-gradient(180deg, #1f86ac 0%, #1a7090 100%) !important;
        box-shadow: 0 4px 12px rgba(37,150,190,0.35), 0 2px 4px rgba(0,0,0,0.08) !important;
        transform: translateY(-1px);
    }
    .woocommerce button.button:active { transform: translateY(0); box-shadow: 0 1px 2px rgba(37,150,190,0.3) !important; }
    .woocommerce .woocommerce-form__label-for-checkbox {
        font-size: 13px; color: #64748b; display: flex !important;
        align-items: center; gap: 8px; cursor: pointer; margin-top: 4px !important;
    }
    .woocommerce input[type="checkbox"] { width: 16px; height: 16px; accent-color: #2596be; cursor: pointer; }
    .woocommerce .lost_password { margin-top: 14px; font-size: 13px; text-align: right; }
    .woocommerce .lost_password a { color: #2596be; text-decoration: none; font-weight: 500; transition: color 0.2s; }
    .woocommerce .lost_password a:hover { color: #1a7090; }
    .zentria-toggle-auth {
        margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0;
        text-align: center; font-size: 14px; color: #64748b;
    }
    .zentria-toggle-auth button {
        background: none !important; border: none !important; color: #2596be !important;
        font-weight: 600 !important; cursor: pointer !important; padding: 0 0 0 4px !important;
        font-size: 14px !important; width: auto !important; transition: color 0.2s; box-shadow: none !important;
    }
    .zentria-toggle-auth button:hover { color: #1a7090 !important; text-decoration: underline; transform: none; }
    .woocommerce .woocommerce-privacy-policy-text { font-size: 12px; color: #94a3b8; line-height: 1.5; margin-bottom: 16px; }
    .woocommerce .woocommerce-privacy-policy-text a { color: #2596be; text-decoration: none; }
    @media (max-width: 900px) {
        body.elementor-page { overflow: auto !important; }
        .woocommerce { position: relative !important; flex-direction: column; height: auto !important; min-height: 100vh; }
        .woocommerce::before { flex: 0 0 220px; width: 100%; }
        .woocommerce::after { font-size: 28px; width: 80%; left: 8%; bottom: auto; top: 90px; position: absolute; }
        .woocommerce .u-columns { flex: 1; padding: 80px 24px 40px; overflow-y: visible; }
        .woocommerce .u-columns::before { top: 28px; }
    }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var col1 = document.querySelector('.woocommerce .u-columns .col-1');
        var col2 = document.querySelector('.woocommerce .u-columns .col-2');
        if (!col1 || !col2) return;
        var loginTitle = col1.querySelector('h2');
        if (loginTitle && !loginTitle.nextElementSibling?.classList.contains('zentria-subtitle')) {
            var sub1 = document.createElement('p');
            sub1.className = 'zentria-subtitle';
            sub1.textContent = 'Ingresa a tu cuenta para gestionar tus pedidos y datos.';
            loginTitle.insertAdjacentElement('afterend', sub1);
        }
        var regTitle = col2.querySelector('h2');
        if (regTitle && !regTitle.nextElementSibling?.classList.contains('zentria-subtitle')) {
            var sub2 = document.createElement('p');
            sub2.className = 'zentria-subtitle';
            sub2.textContent = 'Crea una cuenta nueva en pocos segundos.';
            regTitle.insertAdjacentElement('afterend', sub2);
        }
        var toLogin = document.createElement('div');
        toLogin.className = 'zentria-toggle-auth';
        toLogin.innerHTML = '¿No tienes cuenta? <button type="button">Regístrate aquí</button>';
        col1.appendChild(toLogin);
        var toRegister = document.createElement('div');
        toRegister.className = 'zentria-toggle-auth';
        toRegister.innerHTML = '¿Ya tienes cuenta? <button type="button">Inicia sesión</button>';
        col2.appendChild(toRegister);
        toLogin.querySelector('button').addEventListener('click', function () { document.body.classList.add('zentria-show-register'); });
        toRegister.querySelector('button').addEventListener('click', function () { document.body.classList.remove('zentria-show-register'); });
        if (document.querySelector('.woocommerce-error li[data-id^="reg"], form.register .woocommerce-error')) {
            document.body.classList.add('zentria-show-register');
        }
    });
    </script>
    <?php
}

function zentria_render_dashboard_css() {
    ?>
    <style>
    /* ==============================================================
       ZENTRIA · DASHBOARD (logueado)
       ============================================================== */
    .elementor-page .elementor-widget-text-editor:has(.woocommerce) .elementor-widget-container,
    .elementor-page .elementor-element:has(> .elementor-widget-text-editor .woocommerce) {
        padding: 0 !important; margin: 0 !important; max-width: 100% !important; width: 100% !important;
    }
    body.elementor-page {
        background: #f8fafc !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        padding-top: 70px;
    }

//     /* Mini-header (compensa la falta de header del Elementor Canvas) */
//     .zentria-mini-header {
//         position: fixed; top: 0; left: 0; right: 0; height: 70px;
//         background: #fff; border-bottom: 1px solid #e2e8f0;
//         display: flex; align-items: center; justify-content: space-between;
//         padding: 0 40px; z-index: 100;
//         box-shadow: 0 1px 3px rgba(0,0,0,0.03);
//     }
//     .zentria-mini-header .brand {
//         font-size: 20px; font-weight: 700; letter-spacing: 0.18em;
//         color: #2596be; text-decoration: none;
//     }
//     .zentria-mini-header .back-home {
//         color: #64748b; text-decoration: none; font-size: 14px;
//         font-weight: 500; transition: color 0.2s;
//     }
//     .zentria-mini-header .back-home:hover { color: #2596be; }

    /* Wrapper del dashboard */
    .woocommerce-account .woocommerce {
        display: flex !important; gap: 32px;
        max-width: 1100px; margin: 48px auto !important;
        padding: 0 24px; align-items: flex-start;
    }

    /* Sidebar nav */
    .woocommerce-MyAccount-navigation {
        width: 240px; flex-shrink: 0; background: #fff;
        border-radius: 14px; overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04); padding: 8px;
    }
    .woocommerce-MyAccount-navigation ul { list-style: none; padding: 0; margin: 0; }
    .woocommerce-MyAccount-navigation ul li a {
        display: block; padding: 11px 16px; color: #475569;
        text-decoration: none; font-size: 14px; font-weight: 500;
        border-radius: 10px; transition: all 0.2s ease;
    }
    .woocommerce-MyAccount-navigation ul li a br { display: none; }
    .woocommerce-MyAccount-navigation ul li a:hover { background: #f1f5f9; color: #2596be; }
    .woocommerce-MyAccount-navigation ul li.is-active a {
        background: linear-gradient(180deg, #2596be 0%, #1f86ac 100%);
        color: #fff; box-shadow: 0 1px 3px rgba(37,150,190,0.25);
    }

    /* Contenido */
    .woocommerce-MyAccount-content {
        flex: 1; background: #fff; border-radius: 14px; padding: 36px;
        border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        min-width: 0;
    }
    .woocommerce-MyAccount-content p {
        color: #475569; line-height: 1.7; margin-bottom: 16px; font-size: 15px;
    }
    .woocommerce-MyAccount-content p:first-of-type { font-size: 17px; color: #0f172a; }
    .woocommerce-MyAccount-content strong { color: #0f172a; font-weight: 600; }
    .woocommerce-MyAccount-content a {
        color: #2596be; text-decoration: none; font-weight: 500; transition: color 0.2s;
    }
    .woocommerce-MyAccount-content a:hover { color: #1a7090; text-decoration: underline; }
    .woocommerce-MyAccount-content h2,
    .woocommerce-MyAccount-content h3 {
        color: #0f172a; font-weight: 700; margin-bottom: 16px; letter-spacing: -0.01em;
    }

    /* Tablas */
    .woocommerce-account table.shop_table,
    .woocommerce-account .woocommerce-orders-table {
        width: 100%; border-collapse: separate; border-spacing: 0;
        font-size: 14px; background: #fff; border-radius: 10px;
        overflow: hidden; border: 1px solid #e2e8f0;
    }
    .woocommerce-account table th {
        background: #f8fafc; padding: 14px 16px; text-align: left;
        font-weight: 600; color: #334155; border-bottom: 1px solid #e2e8f0;
        font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .woocommerce-account table td {
        padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #475569;
    }
    .woocommerce-account table tr:last-child td { border-bottom: none; }

    /* Botones */
    .woocommerce-account .button,
    .woocommerce-account a.button,
    .woocommerce-account button.button {
        background: linear-gradient(180deg, #2596be 0%, #1f86ac 100%) !important;
        color: #fff !important; border: none !important; border-radius: 8px !important;
        padding: 10px 20px !important; font-size: 14px !important; font-weight: 600 !important;
        text-decoration: none !important; display: inline-block !important;
        transition: all 0.2s ease !important; cursor: pointer;
        box-shadow: 0 1px 3px rgba(37,150,190,0.25) !important;
    }
    .woocommerce-account .button:hover,
    .woocommerce-account a.button:hover,
    .woocommerce-account button.button:hover {
        background: linear-gradient(180deg, #1f86ac 0%, #1a7090 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37,150,190,0.3) !important;
    }

    /* Forms (editar cuenta, direcciones) */
    .woocommerce-account input[type="text"],
    .woocommerce-account input[type="email"],
    .woocommerce-account input[type="password"],
    .woocommerce-account input[type="tel"],
    .woocommerce-account select,
    .woocommerce-account textarea {
        width: 100% !important; border: 1.5px solid #e2e8f0 !important;
        border-radius: 10px !important; padding: 12px 14px !important;
        font-size: 14px !important; background: #fff !important; color: #0f172a !important;
        transition: all 0.2s !important;
    }
    .woocommerce-account input:focus,
    .woocommerce-account select:focus,
    .woocommerce-account textarea:focus {
        border-color: #2596be !important; outline: none !important;
        box-shadow: 0 0 0 4px rgba(37,150,190,0.12) !important;
    }
    .woocommerce-account form .form-row { margin-bottom: 16px; }
    .woocommerce-account form label {
        font-size: 13px; font-weight: 600; color: #334155;
        margin-bottom: 6px; display: block;
    }
    .woocommerce-account fieldset {
        border: 1px solid #e2e8f0; border-radius: 12px;
        padding: 20px 24px; margin: 24px 0;
    }
    .woocommerce-account fieldset legend {
        font-weight: 700; color: #0f172a; padding: 0 8px; font-size: 15px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .woocommerce-account .woocommerce {
            flex-direction: column; gap: 16px;
            padding: 16px; margin: 20px auto !important;
        }
        .woocommerce-MyAccount-navigation { width: 100%; }
        .woocommerce-MyAccount-content { padding: 24px 20px; }
        .zentria-mini-header { padding: 0 20px; }
        .zentria-mini-header .back-home { font-size: 13px; }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!document.querySelector('.zentria-mini-header')) {
            var header = document.createElement('div');
            header.className = 'zentria-mini-header';
            header.innerHTML = '<a href="/" class="brand">Mundo Planet</a><a href="/" class="back-home">← Volver a la tienda</a>';
            document.body.insertBefore(header, document.body.firstChild);
        }
    });
    </script>
    <?php
}