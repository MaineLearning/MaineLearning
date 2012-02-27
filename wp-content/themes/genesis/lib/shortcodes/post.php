<?php
/**
 * Defines shortcodes functions, primarily intended to be used in the post-info
 * and post-meta sections.
 *
 * <code>[post_something]</code>
 * <code>[post_something before="<em>" after="</em>" foo="bar"]</code>
 *
 * @category Genesis
 * @package  Shortcodes
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

add_shortcode( 'post_date', 'genesis_post_date_shortcode' );
/**
 * Produces the date of post publication.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is empty string),
 *   format (date format, default is value in date_format option field),
 *   label (text following 'before' output, but before date).
 *
 * Output passes through 'genesis_post_date_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_post_date_shortcode( $atts ) {

	$defaults = array(
		'after'  => '',
		'before' => '',
		'format' => get_option( 'date_format' ),
		'label'  => '',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$display = ( 'relative' == $atts['format'] ) ? genesis_human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'genesis' ) : get_the_time( $atts['format'] );

	$output = sprintf( '<span class="date published time" title="%5$s">%1$s%3$s%4$s%2$s</span> ', $atts['before'], $atts['after'], $atts['label'], $display, get_the_time( 'c' ) );

	return apply_filters( 'genesis_post_date_shortcode', $output, $atts );

}

add_shortcode( 'post_time', 'genesis_post_time_shortcode' );
/**
 * Produces the time of post publication.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is empty string),
 *   format (date format, default is value in date_format option field),
 *   label (text following 'before' output, but before date).
 *
 * Output passes through 'genesis_post_time_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_post_time_shortcode( $atts ) {

	$defaults = array(
		'after'  => '',
		'before' => '',
		'format' => get_option( 'time_format' ),
		'label'  => '',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '<span class="published time" title="%5$s">%1$s%3$s%4$s%2$s</span> ', $atts['before'], $atts['after'], $atts['label'], get_the_time( $atts['format'] ), get_the_time( 'Y-m-d\TH:i:sO' ) );

	return apply_filters( 'genesis_post_time_shortcode', $output, $atts );

}

add_shortcode( 'post_author', 'genesis_post_author_shortcode' );
/**
 * Produces the author of the post (unlinked display name).
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is empty string).
 *
 * Output passes through 'genesis_post_author_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_post_author_shortcode( $atts ) {

	$defaults = array(
		'after'  => '',
		'before' => '',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$output = sprintf( '<span class="author vcard">%2$s<span class="fn">%1$s</span>%3$s</span>', esc_html( get_the_author() ), $atts['before'], $atts['after'] );

	return apply_filters( 'genesis_post_author_shortcode', $output, $atts );

}

add_shortcode( 'post_author_link', 'genesis_post_author_link_shortcode' );
/**
 * Produces the author of the post (link to author URL).
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is empty string).
 *
 * Output passes through 'genesis_post_author_link_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_post_author_link_shortcode( $atts ) {

	$defaults = array(
		'after'    => '',
		'before'   => '',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$author = get_the_author();

	/** If there's a URL, build the link */
	if ( get_the_author_meta( 'url' ) )
		$author = '<a href="' . get_the_author_meta( 'url' ) . '" title="' . esc_attr( sprintf( __( 'Visit %s&#8217;s website', 'genesis' ), $author ) ) . '" rel="author external">' . $author . '</a>';

	$output = sprintf( '<span class="author vcard">%2$s<span class="fn">%1$s</span>%3$s</span>', $author, $atts['before'], $atts['after'] );

	return apply_filters( 'genesis_post_author_link_shortcode', $output, $atts );

}

add_shortcode( 'post_author_posts_link', 'genesis_post_author_posts_link_shortcode' );
/**
 * Produces the author of the post (link to author archive).
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is empty string).
 *
 * Output passes through 'genesis_post_author_posts_link_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_post_author_posts_link_shortcode( $atts ) {

	$defaults = array(
		'after'  => '',
		'before' => '',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$author = sprintf( '<a href="%s" class="fn n" title="%s" rel="author">%s</a>', get_author_posts_url( get_the_author_meta( 'ID' ) ), get_the_author(), get_the_author() );

	$output = sprintf( '<span class="author vcard">%2$s<span class="fn">%1$s</span>%3$s</span>', $author, $atts['before'], $atts['after'] );

	return apply_filters( 'genesis_post_author_posts_link_shortcode', $output, $atts );

}

add_shortcode( 'post_comments', 'genesis_post_comments_shortcode' );
/**
 * Produces the link to the current post comments.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is empty string),
 *   hide_if_off (hide link if comments are off, default is 'enabled' (true)),
 *   more (text when there is more than 1 comment, use % character as placeholder
 *     for actual number, default is '% Comments')
 *   one (text when there is exactly one comment, default is '1 Comment'),
 *   zero (text when there are no comments, default is 'Leave a Comment').
 *
 * Output passes through 'genesis_post_comments_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_post_comments_shortcode( $atts ) {

	$defaults = array(
		'after'       => '',
		'before'      => '',
		'hide_if_off' => 'enabled',
		'more'        => __( '% Comments', 'genesis' ),
		'one'         => __( '1 Comment', 'genesis' ),
		'zero'        => __( 'Leave a Comment', 'genesis' ),
	);
	$atts = shortcode_atts( $defaults, $atts );

	if ( ( ! genesis_get_option( 'comments_posts' ) || ! comments_open() ) && 'enabled' === $atts['hide_if_off'] )
		return;

	// Darn you, WordPress!
	ob_start();
	comments_number( $atts['zero'], $atts['one'], $atts['more'] );
	$comments = ob_get_clean();

	$comments = sprintf( '<a href="%s">%s</a>', get_comments_link(), $comments );

	$output = sprintf( '<span class="post-comments">%2$s%1$s%3$s</span>', $comments, $atts['before'], $atts['after'] );

	return apply_filters( 'genesis_post_comments_shortcode', $output, $atts );

}

add_shortcode( 'post_tags', 'genesis_post_tags_shortcode' );
/**
 * Produces the tag links list.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is 'Tagged With: '),
 *   sep (separator string between tags, default is ', ').
 *
 * Output passes through 'genesis_post_tags_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_post_tags_shortcode( $atts ) {

	$defaults = array(
		'after'  => '',
		'before' => __( 'Tagged With: ', 'genesis' ),
		'sep'    => ', ',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$tags = get_the_tag_list( $atts['before'], trim( $atts['sep'] ) . ' ', $atts['after'] );

	if ( ! $tags ) return;

	$output = sprintf( '<span class="tags">%s</span> ', $tags );

	return apply_filters( 'genesis_post_tags_shortcode', $output, $atts );

}

add_shortcode( 'post_categories', 'genesis_post_categories_shortcode' );
/**
 * Produces the category links list.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is 'Tagged With: '),
 *   sep (separator string between tags, default is ', ').
 *
 * Output passes through 'genesis_post_categories_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_post_categories_shortcode( $atts ) {

	$defaults = array(
		'sep'    => ', ',
		'before' => __( 'Filed Under: ', 'genesis' ),
		'after'  => '',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$cats = get_the_category_list( trim( $atts['sep'] ) . ' ' );

	$output = sprintf( '<span class="categories">%2$s%1$s%3$s</span> ', $cats, $atts['before'], $atts['after'] );

	return apply_filters( 'genesis_post_categories_shortcode', $output, $atts );

}

add_shortcode( 'post_terms', 'genesis_post_terms_shortcode' );
/**
 * Produces the linked post taxonomy terms list.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is 'Tagged With: '),
 *   sep (separator string between tags, default is ', '),
 *    taxonomy (name of the taxonomy, default is 'category').
 *
 * Output passes through 'genesis_post_terms_shortcode' filter before returning.
 *
 * @since 1.6.0
 *
 * @global stdClass $post Post object
 *
 * @param array $atts Shortcode attributes
 * @return string|boolean Shortcode output or false on failure to retrieve terms
 */
function genesis_post_terms_shortcode( $atts ) {

	global $post;

	$defaults = array(
			'after'    => '',
			'before'   => __( 'Filed Under: ', 'genesis' ),
			'sep'      => ', ',
			'taxonomy' => 'category',
	);
	$atts = shortcode_atts( $defaults, $atts );

	$terms = get_the_term_list( $post->ID, $atts['taxonomy'], $atts['before'], trim( $atts['sep'] ) . ' ', $atts['after'] );

	if ( is_wp_error( $terms ) )
			return false;

	if ( empty( $terms ) )
			return false;

	$output = '<span class="terms">' . $terms . '</span>';

	return apply_filters( 'genesis_post_terms_shortcode', $output, $terms, $atts );

}

add_shortcode( 'post_edit', 'genesis_post_edit_shortcode' );
/**
 * Produces the edit post link for logged in users.
 *
 * Supported shortcode attributes are:
 *   after (output after link, default is empty string),
 *   before (output before link, default is 'Tagged With: '),
 *   link (link text, default is '(Edit)').
 *
 * Output passes through 'genesis_post_edit_shortcode' filter before returning.
 *
 * @since 1.1.0
 *
 * @param array $atts Shortcode attributes
 * @return string Shortcode output
 */
function genesis_post_edit_shortcode( $atts ) {
	
	if ( ! apply_filters( 'genesis_edit_post_link', true ) )
		return;

	$defaults = array(
		'after'  => '',
		'before' => '',
		'link'   => __( '(Edit)', 'genesis' ),
	);
	$atts = shortcode_atts( $defaults, $atts );

	/** Darn you, WordPress! */
	ob_start();
	edit_post_link( $atts['link'], $atts['before'], $atts['after'] );
	$edit = ob_get_clean();

	$output = $edit;

	return apply_filters( 'genesis_post_edit_shortcode', $output, $atts );

}