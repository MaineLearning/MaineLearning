<?php
/**
 * This is where we put all the functions that that are
 * difficult or impossible to categorize anywhere else.
 *
 * @category Genesis
 * @package  Admin
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 **/

/**
 * Helper function to enable the author box for ALL users.
 *
 * @since 1.4.1
 *
 * @param array $args Optional. Arguments for enabling author box. Default is
 * empty array
 */
function genesis_enable_author_box( $args = array() ) {

	$args = wp_parse_args( $args, array( 'type' => 'single' ) );

	if ( 'single' === $args['type'] )
		add_filter( 'get_the_author_genesis_author_box_single', '__return_true' );
	elseif ( 'archive' === $args['type'] )
		add_filter( 'get_the_author_genesis_author_box_archive', '__return_true' );

}

/**
 * Redirects the user to an admin page, and adds query args to the URL string
 * for alerts, etc.
 *
 * @since 1.6.0
 *
 * @param string $page Menu slug
 * @param array $query_args Optional. Associative array of query string
 * arguments (key => value). Default is an empty array
 * @return null Returns early if first argument is falsy
 */
function genesis_admin_redirect( $page, $query_args = array() ) {

	if ( ! $page )
		return;

	$url = html_entity_decode( menu_page_url( $page, 0 ) );

	foreach ( (array) $query_args as $key => $value ) {
		if ( empty( $key ) && empty( $value ) ) {
			unset( $query_args[$key] );
		}
	}
	
	$url = add_query_arg( $query_args, $url ); 

	wp_redirect( esc_url_raw( $url ) );

}

/**
 * Detect plugin by constant, class or function existence.
 *
 * @since 1.6.0
 *
 * @param array $plugins Array of array for constants, classes and / or
 * functions to check for plugin existence.
 * @return boolean True if plugin exists or false if plugin constant, class or
 * function not detected.
 */
function genesis_detect_plugin( $plugins ) {

	/** Check for classes */
	if ( isset( $plugins['classes'] ) ) {
		foreach ( $plugins['classes'] as $name ) {
			if ( class_exists( $name ) )
				return true;
		}
	}

	/** Check for functions */
	if ( isset( $plugins['functions'] ) ) {
		foreach ( $plugins['functions'] as $name ) {
			if ( function_exists( $name ) )
				return true;
		}
	}

	/** Check for constants */
	if ( isset( $plugins['constants'] ) ) {
		foreach ( $plugins['constants'] as $name ) {
			if ( defined( $name ) )
				return true;
		}
	}

	/** No class, function or constant found to exist */
	return false;

}

/**
 * Helper function used to check that we're targeting a specific Genesis admin page.
 *
 * The $pagehook argument is expected to be one of 'genesis', 'seo-settings' or
 * 'genesis-import-export' although others can be accepted.
 *
 * @since 1.8.0
 *
 * @global string $page_hook Page hook for current page
 * @param string $pagehook Page hook string to check
 * @return boolean Returns true if the global $page_hook matches given $pagehook. False otherwise
 */
function genesis_is_menu_page( $pagehook = '' ) {

	global $page_hook;

	if ( isset( $page_hook ) && $page_hook == $pagehook )
		return true;

	/* May be too early for $page_hook */
	if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == $pagehook )
		return true;

	return false;

}