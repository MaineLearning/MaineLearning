<?php
/**
 * This code adapted from the Custom Field Redirect
 * plugin by Nathan Rice, http://www.nathanrice.net/plugins
 *
 * @category   Genesis
 * @package    Tools
 * @subpackage CustomFieldRedirect
 * @author     Nathan Rice
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

if ( ! function_exists( 'custom_field_redirect' ) ) {
add_action( 'template_redirect', 'custom_field_redirect' );
/**
 * Enables support for redirections via custom field values.
 *
 * Redirect a request to a post / page, if that item has a custom field
 * entry of 'redirect' and a value.
 *
 * @since 0.2.0
 *
 * @global WP_Query $wp_query Query object
 */
function custom_field_redirect() {

	global $wp_query;

	$redirect = isset( $wp_query->post->ID ) ? get_post_meta( $wp_query->post->ID, 'redirect', true ) : '';

	if ( $redirect && is_singular() ) {
		wp_redirect( esc_url_raw( $redirect ), 301 );
		exit();
	}
}
}