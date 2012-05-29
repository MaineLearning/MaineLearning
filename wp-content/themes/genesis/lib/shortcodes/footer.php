<?php
/**
 * Defines shortcodes functions, primarily intended to be used in the site footer.
 *
 * <code>[footer_something]</code>
 * <code>[footer_something before="<em>" after="</em>" foo="bar"]</code>
 *
 * @category Genesis
 * @package  Shortcodes
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

add_shortcode( 'footer_backtotop', 'genesis_footer_backtotop_shortcode' );
/**
 * Produces the "Return to Top" link.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is empty string),
 *   href (link url, default is fragment identifier '#wrap'),
 *   nofollow (boolean for whether to make the link include the rel="nofollow"
 *     attribute. Default is true),
 *   text (Link text, default is 'Return to top of page').
 *
 * Output passes through 'genesis_footer_backtotop_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_footer_backtotop_shortcode( $atts ) {

	$defaults = array(
		'after'    => '',
		'before'   => '',
		'href'     => '#wrap',
		'nofollow' => true,
		'text'     => __( 'Return to top of page', 'genesis' ),
	);
	$atts = shortcode_atts( $defaults, $atts );

	$nofollow = $atts['nofollow'] ? 'rel="nofollow"' : '';

	$output = sprintf( '%s<a href="%s" %s>%s</a>%s', $atts['before'], esc_url( $atts['href'] ), $nofollow, $atts['text'], $atts['after'] );

	return apply_filters( 'genesis_footer_backtotop_shortcode', $output, $atts );

}

add_shortcode( 'footer_copyright', 'genesis_footer_copyright_shortcode' );
/**
 * Adds the visual copyright notice.
 *
 * Supported shortcode attributes are:
 *   after (output after notice, default is empty string),
 *   before (output before notice, default is empty string),
 *   copyright (copyright notice, default is copyright character like (c) ),
 *   first(year copyright first applies, default is empty string).
 *
 * If the 'first' attribute is not empty, and not equal to the current year, then
 * output will be formatted as first-current year (e.g. 1998-2020).
 * Otherwise, output is just given as the current year.
 *
 * Output passes through 'genesis_footer_copyright_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_footer_copyright_shortcode( $atts ) {

	$defaults = array(
		'after'     => '',
		'before'    => '',
		'copyright' => g_ent( '&copy;' ),
		'first'     => '',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = $atts['before'] . $atts['copyright'] . ' ';

	if ( '' != $atts['first'] && date( 'Y' ) != $atts['first'] )
		$output .= $atts['first'] . g_ent( '&ndash;' );

	$output .= date( 'Y' ) . $atts['after'];

	return apply_filters( 'genesis_footer_copyright_shortcode', $output, $atts );

}

add_shortcode( 'footer_childtheme_link', 'genesis_footer_childtheme_link_shortcode' );
/**
 * Adds the link to the child theme, if the details are defined.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is a string with a middot character).
 *
 * Output passes through 'genesis_footer_childtheme_link_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string|null Returns early on failure, otherwise returns shortcode output
 */
function genesis_footer_childtheme_link_shortcode( $atts ) {

	if ( ! is_child_theme() || ! defined( 'CHILD_THEME_NAME' ) || ! defined( 'CHILD_THEME_URL' ) )
		return;

	$defaults = array(
		'after'  => '',
		'before' => g_ent( '&middot; ' ),
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '%s<a href="%s" title="%s">%s</a>%s', $atts['before'], esc_url( CHILD_THEME_URL ), esc_attr( CHILD_THEME_NAME ), esc_html( CHILD_THEME_NAME ), $atts['after'] );

	return apply_filters( 'genesis_footer_childtheme_link_shortcode', $output, $atts );

}

add_shortcode( 'footer_genesis_link', 'genesis_footer_genesis_link_shortcode' );
/**
 * Adds link to the Genesis page on the StudioPress website.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is empty string).
 *
 * Output passes through 'genesis_footer_genesis_link_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_footer_genesis_link_shortcode( $atts ) {

	$defaults = array(
		'after'  => '',
		'before' => '',
		'url'    => 'http://www.studiopress.com/themes/genesis',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = $atts['before'] . '<a href="' . esc_url( $atts['url'] ) . '" title="Genesis Framework">Genesis Framework</a>' . $atts['after'];

	return apply_filters( 'genesis_footer_genesis_link_shortcode', $output, $atts );

}

add_shortcode( 'footer_studiopress_link', 'genesis_footer_studiopress_link_shortcode' );
/**
 * Adds link to the StudioPress home page.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is 'by ').
 *
 * Output passes through 'genesis_footer_studiopress_link_shortcode' filter before returning.
 *
 * @since 1.2.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_footer_studiopress_link_shortcode( $atts ) {

	$defaults = array(
		'after'  => '',
		'before' => __( 'by ', 'genesis' ),
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = $atts['before'] . '<a href="http://www.studiopress.com/">StudioPress</a>' . $atts['after'];

	return apply_filters( 'genesis_footer_studiopress_link_shortcode', $output, $atts );

}

add_shortcode( 'footer_wordpress_link', 'genesis_footer_wordpress_link_shortcode' );
/**
 * Adds link to WordPress - http://wordpress.org/ .
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is empty string).
 *
 * Output passes through 'genesis_footer_wordpress_link_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_footer_wordpress_link_shortcode( $atts ) {

	$defaults = array(
		'after'  => '',
		'before' => '',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '%s<a href="%s" title="%s">%s</a>%s', $atts['before'], 'http://wordpress.org/', 'WordPress', 'WordPress', $atts['after'] );

	return apply_filters( 'genesis_footer_wordpress_link_shortcode', $output, $atts );

}

add_shortcode( 'footer_loginout', 'genesis_footer_loginout_shortcode' );
/**
 * Adds admin login / logout link.
 *
 * Support shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is empty string),
 *   redirect (path to redirect to on login, default is empty string).
 *
 * Output passes through 'genesis_footer_loginout_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_footer_loginout_shortcode( $atts ) {

	$defaults = array(
		'after'    => '',
		'before'   => '',
		'redirect' => '',
	);
	$atts = shortcode_atts( $defaults, $atts );

	if ( ! is_user_logged_in() )
		$link = '<a href="' . esc_url( wp_login_url( $atts['redirect'] ) ) . '">' . __( 'Log in', 'genesis' ) . '</a>';
	else
		$link = '<a href="' . esc_url( wp_logout_url( $atts['redirect'] ) ) . '">' . __( 'Log out', 'genesis' ) . '</a>';

	$output = $atts['before'] . apply_filters( 'loginout', $link ) . $atts['after'];

	return apply_filters( 'genesis_footer_loginout_shortcode', $output, $atts );

}