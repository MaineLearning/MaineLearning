<?php
/**
 * Controls output elements on archive pages.
 *
 * @category   Genesis
 * @package    Structure
 * @subpackage Archives
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

add_action( 'genesis_before_loop', 'genesis_do_taxonomy_title_description', 15 );
/**
 * Add custom headline and / or description to category / tag / taxonomy archive pages.
 *
 * If the page is not a category, tag or taxonomy term archive, or we're not on
 * the first page, or there's no term, or no term meta set, then nothing extra
 * is displayed.
 *
 * If there's a title to display, it is marked up as a level 1 heading.
 * If there's a description to display, it runs through wpautop() before being
 * added to a div.
 *
 * @since 1.3.0
 *
 * @global WP_Query $wp_query
 * @return null Returns null if not the correct achive page, not page 1, or no
 * term meta is set.
 */
function genesis_do_taxonomy_title_description() {

	global $wp_query;

	if ( ! is_category() && ! is_tag() && ! is_tax() )
		return;

	if ( get_query_var( 'paged' ) >= 2 )
		return;

	$term = is_tax() ? get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ) : $wp_query->get_queried_object();

	if ( ! $term || ! isset( $term->meta ) )
		return;

	$headline = $intro_text = '';

	if ( $term->meta['headline'] )
		$headline = sprintf( '<h1>%s</h1>', $term->meta['headline'] );
	if ( $term->meta['intro_text'] )
		$intro_text = wpautop( $term->meta['intro_text'] );

	if ( $headline || $intro_text )
		printf( '<div class="taxonomy-description">%s</div>', $headline . $intro_text );

}

add_action( 'genesis_before_loop', 'genesis_do_author_title_description', 15 );
/**
 * Add custom headline and description to author archive pages.
 *
 * If we're not on an author archive page, or not on page 1, then nothing extra
 * is displayed.
 *
 * If there's a custom headline to display, it is marked up as a level 1 heading.
 * If there's a description (intro text) to display, it is run through wpautop()
 * before being added to a div.
 *
 * @since 1.4.0
 *
 * @return null Returns null if not author archive or not page 1.
 */
function genesis_do_author_title_description() {

	if ( ! is_author() )
		return;

	if ( get_query_var( 'paged' ) >= 2 )
		return;

	$headline   = get_the_author_meta( 'headline', (int) get_query_var( 'author' ) );
	$intro_text = get_the_author_meta( 'intro_text', (int) get_query_var( 'author' ) );

	$headline   = $headline ? sprintf( '<h1>%s</h1>', esc_html( $headline ) ) : '';
	$intro_text = $intro_text ? wpautop( wp_kses( $intro_text, genesis_formatting_allowedtags() ) ) : '';

	if ( $headline || $intro_text )
		printf( '<div class="author-description">%s</div>', $headline . $intro_text );

}

add_action( 'genesis_before_loop', 'genesis_do_author_box_archive', 15 );
/**
 * Add author box to the top of author archive.
 *
 * If the headline and description are set to display the author box appears
 * underneath them.
 *
 * @since 1.4.0
 *
 * @uses genesis_author_box() Echo the author box and its contents
 * @see genesis_do_author_title_and_description
 *
 * @returns Returns null if not author archive or not page 1.
 */
function genesis_do_author_box_archive() {

	if ( ! is_author() || get_query_var( 'paged' ) >= 2 )
		return;

	if ( get_the_author_meta( 'genesis_author_box_archive', get_query_var( 'author' ) ) )
		genesis_author_box( 'archive' );

}