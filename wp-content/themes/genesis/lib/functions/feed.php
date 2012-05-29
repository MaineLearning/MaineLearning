<?php
/**
 * Feed-related functions.
 *
 * @category   Genesis
 * @package    Feeds
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

add_filter( 'feed_link', 'genesis_feed_links_filter', 10, 2 );
/**
 * Filter the feed URI if the user has input a custom feed URI.
 *
 * Applied in the get_feed_link() WordPress function.
 *
 * @since 1.3.0
 *
 * @uses genesis_get_option()
 * @uses esc_url()
 *
 * @param string $output From the get_feed_link() WordPress function.
 * @param string $feed Optional. Defaults to default feed. Feed type (rss2, rss, sdf, atom).
 * @return string Amended feed URL
 */
function genesis_feed_links_filter( $output, $feed ) {

	$feed_uri = genesis_get_option( 'feed_uri' );
	$comments_feed_uri = genesis_get_option( 'comments_feed_uri' );

	if ( $feed_uri && ! strpos( $output, 'comments' ) && ( '' == $feed || 'rss2' == $feed || 'rss' == $feed || 'rdf' == $feed || 'atom' == $feed ) ) {
		$output = esc_url( $feed_uri );
	}

	if ( $comments_feed_uri && strpos( $output, 'comments' ) ) {
		$output = esc_url( $comments_feed_uri );
	}

	return $output;

}

add_action( 'template_redirect', 'genesis_feed_redirect' );
/**
 * Redirects the browser to the custom feed URI.
 *
 * Exits PHP after redirect.
 *
 * @since 1.3.0
 *
 * @uses genesis_get_option() Get feed-related options from database
 *
 * @return null Returns null or nothing on failure. Exits on success.
 */
function genesis_feed_redirect() {

	if ( ! is_feed() || preg_match( '/feedburner|feedvalidator/i', $_SERVER['HTTP_USER_AGENT'] ) )
		return;

	// Don't redirect if viewing archive, search, or post comments feed
	if ( is_archive() || is_search() || is_singular() )
		return;

	$feed_uri = genesis_get_option( 'feed_uri' );
	$comments_feed_uri = genesis_get_option( 'comments_feed_uri' );

	if ( $feed_uri && ! is_comment_feed() && genesis_get_option( 'redirect_feed' ) ) {
		wp_redirect( $feed_uri, 302 );
		exit;
	}

	if ( $comments_feed_uri && is_comment_feed() && genesis_get_option( 'redirect_comments_feed' ) ) {
		wp_redirect( $comments_feed_uri, 302 );
		exit;
	}

}