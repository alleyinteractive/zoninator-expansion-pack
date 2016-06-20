<?php
/**
 * Plugin Name: Zoninator Expansion Pack
 * Version: 0.1-alpha
 * Description: Add-ons for Zoninator
 * Author: Matthew Boynes
 * Author URI: https://alleyinteractive.com
 * Plugin URI: https://github.com/alleyinteractive/zoninator-expansion-pack
 * Text Domain: zoninator-expansion-pack
 * Domain Path: /languages
 * @package Zoninator_Expansion_Pack
 */

define( 'ZONINATOR_EP_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

require_once( __DIR__ . '/lib/trait-singleton.php' );

/**
 * Autoload Zoninator_EP classes.
 *
 * @param  string $cls Class name.
 */
function zoninator_ep_autoload( $cls ) {
	$cls = ltrim( $cls, '\\' );
	if ( strpos( $cls, 'Zoninator_EP\\' ) !== 0 ) {
		return;
	}
	require_once( __DIR__ . '/lib/class-' . strtolower( str_replace( [ 'Zoninator_EP\\', '_' ], [ '', '-' ], $cls ) ) . '.php' );
}
spl_autoload_register( 'zoninator_ep_autoload' );

// Hooks and filters that will trigger autoloading
add_action( 'after_setup_theme', function() {
	\Zoninator_EP\Term_Binding::instance();

	if ( apply_filters( 'zoninator_ep_enable_ui', true ) ) {
		add_filter( 'zoninator_search_results_post', array( '\\Zoninator_EP\\UI', 'ajax_search_results' ), 10, 2 );
		add_filter( 'zoninator_zone_post_columns', array( '\\Zoninator_EP\\UI', 'columns' ) );
	}

	if ( apply_filters( 'zoninator_ep_enable_search_filters', true ) ) {
		\Zoninator_EP\Search_Filters::instance();
	}
} );

/**
 * Load assets.
 */
function zoninator_ep_assets() {
	wp_enqueue_style( 'zoninator-ep-css', ZONINATOR_EP_URL . 'static/css/zoninator-ep.min.css', [], '1.0' );
	wp_enqueue_script( 'zoninator-ep-js', ZONINATOR_EP_URL . 'static/js/zoninator-ep.min.js', [ 'jquery', 'underscore' ], '1.0', true );
}
add_action( 'admin_print_styles-toplevel_page_zoninator', 'zoninator_ep_assets' );
