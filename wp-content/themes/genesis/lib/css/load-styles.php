<?php
/**
 * Controls the adding of stylesheets.
 *
 * @category Genesis
 * @package  Scripts-Styles
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link     http://www.studiopress.com/themes/genesis
 */

add_action( 'genesis_meta', 'genesis_load_stylesheet' );
/**
 * Echo reference to the style sheet.
 *
 * If a child theme is active, it loads the child theme's stylesheet,
 * otherwise, it loads the Genesis stylesheet.
 *
 * @since 0.2.2
 */
function genesis_load_stylesheet() {

	add_action( 'wp_enqueue_scripts', 'genesis_enqueue_main_stylesheet', 5 );

}

/**
 * Enqueue main stylesheet.
 *
 * Properly enqueue the main stylesheet.
 *
 * @since 1.9.0
 */
function genesis_enqueue_main_stylesheet() {

	$version = defined( 'CHILD_THEME_VERSION' ) && CHILD_THEME_VERSION ? CHILD_THEME_VERSION : PARENT_THEME_VERSION;
	$handle  = defined( 'CHILD_THEME_NAME' ) && CHILD_THEME_NAME ? sanitize_title_with_dashes( CHILD_THEME_NAME ) : 'child-theme';

	wp_enqueue_style( $handle, get_stylesheet_uri(), false, $version ); 

}

add_action( 'admin_print_styles', 'genesis_load_admin_styles' );
/**
 * Enqueue Genesis admin styles.
 *
 * @category Genesis
 * @package Scripts-Styles
 *
 * @since 0.2.3
 */
function genesis_load_admin_styles() {

	wp_enqueue_style( 'genesis_admin_css', GENESIS_CSS_URL . '/admin.css', array(), PARENT_THEME_VERSION );

}