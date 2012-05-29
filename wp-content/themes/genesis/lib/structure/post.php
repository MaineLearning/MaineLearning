<?php
/**
 * Controls output elements in post structures.
 *
 * @category   Genesis
 * @package    Structure
 * @subpackage Post
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

/**
 * Restores all default post loop output by re-hooking all default functions.
 *
 * Useful in the event that you need to unhook something in a particular context,
 * but don't want to restore it for all subsequent loop instances.
 *
 * Calls genesis_reset_loops action after everything has been re-hooked.
 *
 * @since 1.5.0
 *
 * @global array $_genesis_loop_args Associative array for grid loop configuration
 */
function genesis_reset_loops() {

	add_action( 'genesis_before_post_title', 'genesis_do_post_format_image' );
	add_action( 'genesis_post_title', 'genesis_do_post_title' );
	add_action( 'genesis_post_content', 'genesis_do_post_image' );
	add_action( 'genesis_post_content', 'genesis_do_post_content' );
	add_action( 'genesis_loop_else', 'genesis_do_noposts' );
	add_action( 'genesis_before_post_content', 'genesis_post_info' );
	add_action( 'genesis_after_post_content', 'genesis_post_meta' );
	add_action( 'genesis_after_post', 'genesis_do_author_box_single' );
	add_action( 'genesis_after_endwhile', 'genesis_posts_nav' );

	/** Reset loop args */
	global $_genesis_loop_args;
	$_genesis_loop_args = array();

	do_action( 'genesis_reset_loops' );

}

add_filter( 'post_class', 'genesis_custom_post_class', 15 );
/**
 * Adds a custom post class based on the value stored as a custom field.
 *
 * @since 1.4.0
 *
 * @uses genesis_get_custom_field() Get custom field value
 *
 * @param array $classes Existing post classes
 * @return array Amended post classes
 */
function genesis_custom_post_class( $classes ) {

	$new_class = genesis_get_custom_field( '_genesis_custom_post_class' );

	if ( $new_class )
		$classes[] = esc_attr( sanitize_html_class( $new_class ) );

	return $classes;

}

add_action( 'genesis_before_post_title', 'genesis_do_post_format_image' );
/**
 * Adds a post format icon.
 *
 * Adds an image, corresponding to the post format, before the post title.
 *
 * @since 1.4.0
 *
 * @uses CHILD_DIR
 * @uses CHILD_URL
 *
 * @global stdClass $post Post object
 * @return null Returns early if post formats are not supported, or
 * genesis-post-format-images are not supported
 */
function genesis_do_post_format_image() {

	global $post;

	/** Do nothing if post formats aren't supported */
	if ( ! current_theme_supports( 'post-formats' ) || ! current_theme_supports( 'genesis-post-format-images' ) )
		return;

	/** Get post format */
	$post_format = get_post_format( $post );

	/** If post format is set, look for post format image */
	if ( $post_format && file_exists( sprintf( '%s/images/post-formats/%s.png', CHILD_DIR, $post_format ) ) )
		printf( '<a href="%s" title="%s" rel="bookmark"><img src="%s" class="post-format-image" alt="%s" /></a>', get_permalink(), the_title_attribute( 'echo=0' ), sprintf( '%s/images/post-formats/%s.png', CHILD_URL, $post_format ), $post_format );

	/** Else, look for the default post format image */
	elseif ( file_exists( sprintf( '%s/images/post-formats/default.png', CHILD_DIR ) ) )
		printf( '<a href="%s" title="%s" rel="bookmark"><img src="%s/images/post-formats/default.png" class="post-format-image" alt="%s" /></a>', get_permalink(), the_title_attribute( 'echo=0' ), CHILD_URL, 'post' );

}

add_action( 'genesis_post_title', 'genesis_do_post_title' );
/**
 * Echo the title of a post.
 *
 * The genesis_post_title_text filter is applied on the text of the title, while
 * the genesis_post_title_output filter is applied on the echoed markup.
 *
 * @since 1.1.0
 *
 * @return null Returns early if the length of the title string is zero
 */
function genesis_do_post_title() {

	$title = get_the_title();

	if ( strlen( $title ) == 0 )
		return;

	if ( is_singular() )
		$title = sprintf( '<h1 class="entry-title">%s</h1>', apply_filters( 'genesis_post_title_text', $title ) );
	else
		$title = sprintf( '<h2 class="entry-title"><a href="%s" title="%s" rel="bookmark">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), apply_filters( 'genesis_post_title_text', $title ) );

	echo apply_filters( 'genesis_post_title_output', $title ) . "\n";

}

add_action( 'genesis_post_content', 'genesis_do_post_image' );
/**
 * Echo the post image on archive pages.
 *
 * If this an archive page and the option is set to show thumbnail, then it
 * gets the image size as per the theme setting, wraps it in the post  permalink
 * and echoes it.
 *
 * @since 1.1.0
 *
 * @uses genesis_get_option() Get theme setting value
 * @uses genesis_get_image() Return an image pulled from the media gallery
 *
 */
function genesis_do_post_image() {

	if ( ! is_singular() && genesis_get_option( 'content_archive_thumbnail' ) ) {
		$img = genesis_get_image( array( 'format' => 'html', 'size' => genesis_get_option( 'image_size' ), 'attr' => array( 'class' => 'alignleft post-image' ) ) );
		printf( '<a href="%s" title="%s">%s</a>', get_permalink(), the_title_attribute( 'echo=0' ), $img );
	}

}

add_action( 'genesis_post_content', 'genesis_do_post_content' );
/**
 * Echo the post content.
 *
 * On single posts or pages it echoes the full content, and optionally the
 * trackback string if they are enabled. On single pages, also adds the edit
 * link after the content.
 *
 * Elsewhere it displays either the excerpt, limited content, or full content.
 *
 * Pagination links are included at the end, if needed.
 *
 * @since 1.1.0
 *
 * @uses genesis_get_option() Get theme setting value
 * @uses the_content_limit() Limited content
 */
function genesis_do_post_content() {

	if ( is_singular() ) {
		the_content();

		if ( is_single() && 'open' == get_option( 'default_ping_status' ) ) {
			echo '<!--';
			trackback_rdf();
			echo '-->' . "\n";
		}

		if ( is_page() && apply_filters( 'genesis_edit_post_link', true ) )
			edit_post_link( __( '(Edit)', 'genesis' ), '', '' );
	}
	elseif ( 'excerpts' == genesis_get_option( 'content_archive' ) ) {
		the_excerpt();
	}
	else {
		if ( genesis_get_option( 'content_archive_limit' ) )
			the_content_limit( (int) genesis_get_option( 'content_archive_limit' ), __( '[Read more...]', 'genesis' ) );
		else
			the_content( __( '[Read more...]', 'genesis' ) );
	}

	wp_link_pages( array( 'before' => '<p class="pages">' . __( 'Pages:', 'genesis' ), 'after' => '</p>' ) );

}

add_action( 'genesis_loop_else', 'genesis_do_noposts' );
/**
 * Echo filterable content when there are no posts to show.
 *
 * The applied filter is genesis_noposts_text.
 *
 * @since 1.1.0
 */
function genesis_do_noposts() {

	printf( '<p>%s</p>', apply_filters( 'genesis_noposts_text', __( 'Sorry, no posts matched your criteria.', 'genesis' ) ) );

}

add_filter( 'genesis_post_info', 'do_shortcode', 20 );
add_action( 'genesis_before_post_content', 'genesis_post_info' );
/**
 * Echo the post info (byline) under the post title.
 *
 * Doesn't do post info on pages.
 *
 * The post info makes use of several shortcodes by default, and the whole
 * output is filtered via genesis_post_info before echoing.
 *
 * @since 0.2.3
 *
 * @global stdClass $post Post object
 * @return null Returns early if on a page
 */
function genesis_post_info() {

	global $post;

	if ( is_page( $post->ID ) )
		return;

	$post_info = '[post_date] ' . __( 'By', 'genesis' ) . ' [post_author_posts_link] [post_comments] [post_edit]';
	printf( '<div class="post-info">%s</div>', apply_filters( 'genesis_post_info', $post_info ) );

}

add_filter( 'genesis_post_meta', 'do_shortcode', 20 );
add_action( 'genesis_after_post_content', 'genesis_post_meta' );
/**
 * Echo the post meta after the post content.
 *
 * Doesn't do post meta on pages.
 *
 * The post info makes use of a couple of shortcodes by default, and the whole
 * output is filtered via genesis_post_meta before echoing.
 *
 * @since 0.2.3
 *
 * @global stdClass $post Post object
 * @return null Returns early if on a page
 */
function genesis_post_meta() {

	global $post;

	if ( is_page( $post->ID ) )
		return;

	$post_meta = '[post_categories] [post_tags]';
	printf( '<div class="post-meta">%s</div>', apply_filters( 'genesis_post_meta', $post_meta ) );

}

add_action( 'genesis_after_post', 'genesis_do_author_box_single' );
/**
 * Conditionally adds the author box after single posts or pages.
 *
 * @since 1.0.0
 *
 * @uses genesis_author_box() Echo the author box
 *
 * @return null Returns early if not a single post or page
 */
function genesis_do_author_box_single() {

	if ( ! is_single() )
		return;

	if ( get_the_author_meta( 'genesis_author_box_single', get_the_author_meta( 'ID' ) ) )
		genesis_author_box( 'single' );

}

/**
 * Echos the the author box and its contents.
 *
 * The title is filterable via genesis_author_box_title, and the gravatar size
 * is filterable via genesis_author_box_gravatar_size.
 *
 * The final output is filterable via genesis_author_box, which passes many
 * variables through.
 *
 * @since 1.3.0
 *
 * @global WP_User $authordata Author (user) object
 * @param string $context Optional. Allows different author box markup for
 * different contexts, specifically 'single'. Default is empty string.
 */
function genesis_author_box( $context = '' ) {

	global $authordata;

	$authordata    = is_object( $authordata ) ? $authordata : get_userdata( get_query_var( 'author' ) );
	$gravatar_size = apply_filters( 'genesis_author_box_gravatar_size', 70, $context );
	$gravatar      = get_avatar( get_the_author_meta( 'email' ), $gravatar_size );
	$title         = apply_filters( 'genesis_author_box_title', sprintf( '<strong>%s %s</strong>', __( 'About', 'genesis' ), get_the_author() ), $context );
	$description   = wpautop( get_the_author_meta( 'description' ) );

	/** The author box markup, contextual */
	$pattern = $context == 'single' ? '<div class="author-box"><div>%s %s<br />%s</div></div><!-- end .authorbox-->' : '<div class="author-box">%s<h1>%s</h1><div>%s</div></div><!-- end .authorbox-->';

	echo apply_filters( 'genesis_author_box', sprintf( $pattern, $gravatar, $title, $description ), $context, $pattern, $gravatar, $title, $description );

}

add_action( 'genesis_after_endwhile', 'genesis_posts_nav' );
/**
 * Conditionally echoes post navigation in a format dependent on chosen setting.
 *
 * @since 0.2.3
 *
 * @uses genesis_get_option() Get theme setting value
 */
function genesis_posts_nav() {

	$nav = genesis_get_option( 'posts_nav' );

	if( 'prev-next' == $nav )
		genesis_prev_next_posts_nav();
	elseif( 'numeric' == $nav )
		genesis_numeric_posts_nav();
	else
		genesis_older_newer_posts_nav();

}

/**
 * Echoes post navigation in Older Posts / Newer Posts format.
 *
 * @since 0.2.2
 *
 * @uses g_ent() Pass entities through filter
 */
function genesis_older_newer_posts_nav() {

	$older_link = get_next_posts_link( apply_filters( 'genesis_older_link_text', g_ent( '&laquo; ' ) . __( 'Older Posts', 'genesis' ) ) );
	$newer_link = get_previous_posts_link( apply_filters( 'genesis_newer_link_text', __( 'Newer Posts', 'genesis' ) . g_ent( ' &raquo;' ) ) );

	$older = $older_link ? '<div class="alignleft">' . $older_link . '</div>' : '';
	$newer = $newer_link ? '<div class="alignright">' . $newer_link . '</div>' : '';

	$nav = '<div class="navigation">' . $older . $newer . '</div><!-- end .navigation -->';

	if ( $older || $newer )
		echo $nav;

}

/**
 * Echoes post navigation in Previous Posts / Next Posts format.
 *
 * @since 0.2.2
 *
 * @uses g_ent() Pass entities through filter
 */
function genesis_prev_next_posts_nav() {

	$prev_link = get_previous_posts_link( apply_filters( 'genesis_prev_link_text', g_ent( '&laquo; ' ) . __( 'Previous Page', 'genesis' ) ) );
	$next_link = get_next_posts_link( apply_filters( 'genesis_next_link_text', __( 'Next Page', 'genesis' ) . g_ent( ' &raquo;' ) ) );

	$prev = $prev_link ? '<div class="alignleft">' . $prev_link . '</div>' : '';
	$next = $next_link ? '<div class="alignright">' . $next_link . '</div>' : '';

	$nav = '<div class="navigation">' . $prev . $next . '</div><!-- end .navigation -->';

	if ( $prev || $next )
		echo $nav;
}

/**
 * Echoes post navigation in page numbers format (similar to WP-PageNavi).
 *
 * The links, if needed, are ordered as:
 *   previous page arrow,
 *   first page,
 *   up to two pages before current page,
 *   current page,
 *   up to two pages after the current page,
 *   last page,
 *   next page arrow.
 *
 * @since 0.2.3
 *
 * @uses g_ent() Pass entities through filter
 *
 * @global WP_Query $wp_query Query object
 * @return null Returns early if on a single post or page, or only 1 page present
 */
function genesis_numeric_posts_nav() {

	if( is_singular() )
		return;

	global $wp_query;

	/** Stop execution if there's only 1 page */
	if( $wp_query->max_num_pages <= 1 )
		return;

	$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
	$max   = intval( $wp_query->max_num_pages );

	/**	Add current page to the array */
	if ( $paged >= 1 )
		$links[] = $paged;

	/**	Add the pages around the current page to the array */
	if ( $paged >= 3 ) {
		$links[] = $paged - 1;
		$links[] = $paged - 2;
	}

	if ( ( $paged + 2 ) <= $max ) {
		$links[] = $paged + 2;
		$links[] = $paged + 1;
	}

	echo '<div class="navigation"><ul>' . "\n";

	/**	Previous Post Link */
	if ( get_previous_posts_link() )
		printf( '<li>%s</li>' . "\n", get_previous_posts_link( apply_filters( 'genesis_prev_link_text', g_ent( '&laquo; ' ) . __( 'Previous Page', 'genesis' ) ) ) );

	/**	Link to first page, plus ellipses if necessary */
	if ( ! in_array( 1, $links ) ) {
		$class = 1 == $paged ? ' class="active"' : '';

		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

		if ( ! in_array( 2, $links ) )
			echo g_ent( '<li>&hellip;</li>' );
	}

	/**	Link to current page, plus 2 pages in either direction if necessary */
	sort( $links );
	foreach ( (array) $links as $link ) {
		$class = $paged == $link ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
	}

	/**	Link to last page, plus ellipses if necessary */
	if ( ! in_array( $max, $links ) ) {
		if ( ! in_array( $max - 1, $links ) )
			echo g_ent( '<li>&hellip;</li>' ) . "\n";

		$class = $paged == $max ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
	}

	/**	Next Post Link */
	if ( get_next_posts_link() )
		printf( '<li>%s</li>' . "\n", get_next_posts_link( apply_filters( 'genesis_next_link_text', __( 'Next Page', 'genesis' ) . g_ent( ' &raquo;' ) ) ) );

	echo '</ul></div>' . "\n";

}

/**
 * Display links to previous / next post, from a single post.
 *
 * @since 1.5.1
 *
 * @return null Returns early if not a post
 */
function genesis_prev_next_post_nav() {

	if ( ! is_singular( 'post' ) )
		return;

	?>
	<div class="navigation">
		<div class="alignleft"><?php previous_post_link(); ?></div>
		<div class="alignright"><?php next_post_link(); ?></div>
	</div>
	<?php

}