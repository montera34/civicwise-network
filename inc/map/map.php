<?php
add_action( 'wp_enqueue_scripts', 'cmap_enqueue_scripts' );
function cmap_enqueue_scripts() {

	if ( is_admin() )
		return;

	if ( is_page(HCANARIAS_P_NETWORK) ) {
		wp_enqueue_script( 'isotope', get_stylesheet_directory_uri() . '/inc/map-canarias/js/isotope.pkgd.min.js', array('jquery'), null, true );
	}
	wp_enqueue_script( 'd3', get_stylesheet_directory_uri() . '/inc/map-canarias/js/d3.v5.min.js', array(), null, true );
	wp_enqueue_script( 'topojson', get_stylesheet_directory_uri() . '/inc/map-canarias/js/topojson.v3.min.js', array('d3'), null, true );
	wp_enqueue_script( 'cmap', get_stylesheet_directory_uri() . '/inc/map-canarias/js/map.js', array('d3','topojson'), null, true );

}

add_action( 'wp_footer', 'cmap_enqueue_styles' );
function cmap_enqueue_styles() {

	wp_enqueue_style( 'map', get_stylesheet_directory_uri() . '/inc/map-canarias/css/map.css', array(), null );

}

?>
