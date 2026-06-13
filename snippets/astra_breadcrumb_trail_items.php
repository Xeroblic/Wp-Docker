add_filter( 'woocommerce_get_breadcrumb', 'mp_arreglar_migas_woo', 20, 2 );
function mp_arreglar_migas_woo( $crumbs, $breadcrumb ) {
    $shop_title = get_the_title( wc_get_page_id( 'shop' ) );
    
    foreach ( $crumbs as $key => $crumb ) {
        if ( $crumb[0] === $shop_title || strtolower($crumb[0]) === 'tienda' || strtolower($crumb[0]) === 'shop' || strtolower($crumb[0]) === 'productos' ) {
            $crumbs[$key][0] = 'Categorías';
            $crumbs[$key][1] = home_url( '/categorias/' );
        }
    }
    
    if ( is_product() && count($crumbs) > 0 ) {
        array_pop( $crumbs );
        
        $ultimo = count($crumbs) - 1;
        if ( isset($crumbs[$ultimo]) ) {
            $crumbs[$ultimo][1] = ''; 
        }
    }
    
    return $crumbs;
}

add_filter( 'astra_breadcrumb_trail_items', 'mp_arreglar_migas_astra', 50 );
function mp_arreglar_migas_astra( $items ) {
    foreach ( $items as $key => $item ) {
        if ( strpos( $item, 'Tienda' ) !== false || strpos( $item, 'Shop' ) !== false || strpos( $item, 'Productos' ) !== false ) {
            $items[$key] = '<a href="' . home_url( '/categorias/' ) . '"><span itemprop="name">Categorías</span></a>';
        }
    }
    
    if ( is_product() && count($items) > 0 ) {
        array_pop( $items ); 
        
        $ultimo = count($items) - 1;
        if ( isset($items[$ultimo]) ) {
            $items[$ultimo] = strip_tags( $items[$ultimo] );
        }
    }
    
    return $items;
}