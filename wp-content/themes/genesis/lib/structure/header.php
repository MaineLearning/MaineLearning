<?php
/**
 * Adds header structures.
 *
 * @category   Genesis
 * @package    Structure
 * @subpackage Header
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

add_action( 'genesis_doctype', 'genesis_do_doctype' );
/**
 * Echo the doctype and opening markup.
 *
 * If you are going to replace the doctype with a custom one, you must remember
 * to include the opening <html> and <head> elements too, along with the proper
 * properties.
 *
 * It would be beneficial to also include the <meta> tag for Content Type.
 *
 * The default doctype is XHTML v1.0 Transitional.
 *
 * @since 1.3.0
 */
function genesis_do_doctype() {

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes( 'xhtml' ); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<?php

}

add_action( 'get_header', 'genesis_doc_head_control' );
/**
 * Remove unnecessary code that WordPress puts in the <head>
 *
 * @since 1.3.0
 *
 * @uses genesis_get_option() Get theme setting value
 * @uses genesis_get_seo_option() Get SEO setting value
 */
function genesis_doc_head_control() {

	remove_action( 'wp_head', 'wp_generator' );

	if ( ! genesis_get_seo_option( 'head_adjacent_posts_rel_link' ) )
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

	if ( ! genesis_get_seo_option( 'head_wlwmanifest_link' ) )
		remove_action( 'wp_head', 'wlwmanifest_link' );

	if ( ! genesis_get_seo_option( 'head_shortlink' ) )
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );

	if ( is_single() && ! genesis_get_option( 'comments_posts' ) )
		remove_action( 'wp_head', 'feed_links_extra', 3 );

	if ( is_page() && ! genesis_get_option( 'comments_pages' ) )
		remove_action( 'wp_head', 'feed_links_extra', 3 );

}

add_action( 'genesis_site_title', 'genesis_seo_site_title' );
/**
 * Echo the site title into the header.
 *
 * Depending on the SEO option set by the user, this will either be wrapped in
 * h1 or p tags.
 *
 * Applies the genesis_seo_title filter before echoing.
 *
 * @since 1.1.0
 *
 * @uses genesis_get_seo_option() Get SEO setting value
 */
function genesis_seo_site_title() {

	/** Set what goes inside the wrapping tags */
	$inside = sprintf( '<a href="%s" title="%s">%s</a>', trailingslashit( home_url() ), esc_attr( get_bloginfo( 'name' ) ), get_bloginfo( 'name' ) );

	/** Determine which wrapping tags to use */
	$wrap = is_home() && 'title' == genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : 'p';

	/** A little fallback, in case an SEO plugin is active */
	$wrap = is_home() && ! genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : $wrap;

	/** Build the Title */
	$title = sprintf( '<%s id="title">%s</%s>', $wrap, $inside, $wrap );

	/** Echo (filtered) */
	echo apply_filters( 'genesis_seo_title', $title, $inside, $wrap );

}

add_action( 'genesis_site_description', 'genesis_seo_site_description' );
/**
 * Echo the site description into the header.
 *
 * Depending on the SEO option set by the user, this will either be wrapped in
 * h1 or p tags.
 *
 * Applies the genesis_seo_description filter before echoing.
 *
 * @since 1.1.0
 *
 * @uses genesis_get_seo_option() Get SEO setting value
 */
function genesis_seo_site_description() {

	/** Set what goes inside the wrapping tags */
	$inside = esc_html( get_bloginfo( 'description' ) );

	/** Determine which wrapping tags to use */
	$wrap = is_home() && 'description' == genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : 'p';

	/* Build the description */
	$description = $inside ? sprintf( '<%s id="description">%s</%s>', $wrap, $inside, $wrap ) : '';

	/** Echo (filtered) */
	echo apply_filters( 'genesis_seo_description', $description, $inside, $wrap );

}

add_filter( 'wp_title', 'genesis_doctitle_wrap', 20 );
/**
 * Wraps the doctitle in 'title' tags.
 *
 * @since 1.3.0
 *
 * @param string $title Document title
 * @return string Plain text or HTML markup
 */
function genesis_doctitle_wrap( $title ) {

	return is_feed() || is_admin() ? $title : sprintf( "<title>%s</title>\n", $title );

}

add_action( 'genesis_title', 'wp_title' );
add_filter( 'wp_title', 'genesis_default_title', 10, 3 );
/**
 * Return filtered post title.
 *
 * This function does 3 things:
 * 1. Pulls the values for $sep and $seplocation, uses defaults if necessary
 * 2. Determines if the site title should be appended
 * 3. Allows the user to set a custom title on a per-page/post basis
 *
 * @since 0.1.3
 *
 * @uses genesis_get_seo_option() Get SEO setting value
 * @uses genesis_get_custom_field() Get custom field value
 *
 * @global WP_Query $wp_query
 * @param string $title Existing page title
 * @param string $sep Separator character(s). Default is '-' if not set
 * @param string $seplocation Separator location - "left" or "right". Default is "right" if not set
 * @return string Page title
 */
function genesis_default_title( $title, $sep, $seplocation ) {

	global $wp_query;

	if ( is_feed() )
		return trim( $title );

	$sep = genesis_get_seo_option( 'doctitle_sep' ) ? genesis_get_seo_option( 'doctitle_sep' ) : '–';
	$seplocation = genesis_get_seo_option( 'doctitle_seplocation' ) ? genesis_get_seo_option( 'doctitle_seplocation' ) : 'right';

	/**	If viewing the home page */
	if ( is_front_page() ) {
		/** Determine the doctitle */
		$title = genesis_get_seo_option( 'home_doctitle' ) ? genesis_get_seo_option( 'home_doctitle' ) : get_bloginfo( 'name' );

		/** Append site description, if necessary */
		$title = genesis_get_seo_option( 'append_description_home' ) ? $title . " $sep " . get_bloginfo( 'description' ) : $title;
	}

	/**	if viewing a post / page / attachment */
	if ( is_singular() ) {
		/**	The User Defined Title (Genesis) */
		if ( genesis_get_custom_field( '_genesis_title' ) )
			$title = genesis_get_custom_field( '_genesis_title' );
		/**	All-in-One SEO Pack Title (latest, vestigial) */
		elseif ( genesis_get_custom_field( '_aioseop_title' ) )
			$title = genesis_get_custom_field( '_aioseop_title' );
		/**	Headspace Title (vestigial) */
		elseif ( genesis_get_custom_field( '_headspace_page_title' ) )
			$title = genesis_get_custom_field( '_headspace_page_title' );
		/**	Thesis Title (vestigial) */
		elseif ( genesis_get_custom_field( 'thesis_title' ) )
			$title = genesis_get_custom_field( 'thesis_title' );
		/**	SEO Title Tag (vestigial) */
		elseif ( genesis_get_custom_field( 'title_tag' ) )
			$title = genesis_get_custom_field( 'title_tag' );
		/**	All-in-One SEO Pack Title (old, vestigial) */
		elseif ( genesis_get_custom_field( 'title' ) )
			$title = genesis_get_custom_field( 'title' );
	}

	if ( is_category() ) {
		//$term = get_term( get_query_var('cat'), 'category' );
		$term  = $wp_query->get_queried_object();
		$title = ! empty( $term->meta['doctitle'] ) ? $term->meta['doctitle'] : $title;
	}

	if ( is_tag() ) {
		//$term = get_term( get_query_var('tag_id'), 'post_tag' );
		$term  = $wp_query->get_queried_object();
		$title = ! empty( $term->meta['doctitle'] ) ? $term->meta['doctitle'] : $title;
	}

	if ( is_tax() ) {
		$term  = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
		$title = ! empty( $term->meta['doctitle'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->meta['doctitle'] ) ) : $title;
	}

	if ( is_author() ) {
		$user_title = get_the_author_meta( 'doctitle', (int) get_query_var( 'author' ) );
		$title      = $user_title ? $user_title : $title;
	}

	/**	If we don't want site name appended, or if we're on the home page */
	if ( ! genesis_get_seo_option( 'append_site_title' ) || is_front_page() )
		return esc_html( trim( $title ) );

	/** Else append the site name */
	$title = 'right' == $seplocation ? $title . " $sep " . get_bloginfo( 'name' ) : get_bloginfo( 'name' ) . " $sep " . $title;
	return esc_html( trim( $title ) );

}

add_action( 'genesis_meta', 'genesis_seo_meta_description' );
/**
 * Generates the meta description based on contextual criteria.
 *
 * Outputs nothing if description isn't present.
 *
 * @since 1.2.0
 *
 * @uses genesis_get_seo_option() Get SEO setting value
 * @uses genesis_get_custom_field() Get custom field value
 *
 * @global WP_Query $wp_query Query object
 * @global stdClass $post Post object
 */
function genesis_seo_meta_description() {

	global $wp_query, $post;

	$description = '';

	/** If we're on the home page */
	if ( is_front_page() )
		$description = genesis_get_seo_option( 'home_description' ) ? genesis_get_seo_option( 'home_description' ) : get_bloginfo( 'description' );

	/** If we're on a single post / page / attachment */
	if ( is_singular() ) {
		/** Description is set via custom field */
		if ( genesis_get_custom_field( '_genesis_description' ) )
			$description = genesis_get_custom_field( '_genesis_description' );
		/** All-in-One SEO Pack (latest, vestigial) */
		elseif ( genesis_get_custom_field( '_aioseop_description' ) )
			$description = genesis_get_custom_field( '_aioseop_description' );
		/** Headspace2 (vestigial) */
		elseif ( genesis_get_custom_field( '_headspace_description' ) )
			$description = genesis_get_custom_field( '_headspace_description' );
		/** Thesis (vestigial) */
		elseif ( genesis_get_custom_field( 'thesis_description' ) )
			$description = genesis_get_custom_field( 'thesis_description' );
		/** All-in-One SEO Pack (old, vestigial) */
		elseif ( genesis_get_custom_field( 'description' ) )
			$description = genesis_get_custom_field( 'description' );
	}

	if ( is_category() ) {
		//$term = get_term( get_query_var('cat'), 'category' );
		$term = $wp_query->get_queried_object();
		$description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : '';
	}

	if ( is_tag() ) {
		//$term = get_term( get_query_var('tag_id'), 'post_tag' );
		$term = $wp_query->get_queried_object();
		$description = ! empty( $term->meta['description'] ) ? $term->meta['description'] : '';
	}

	if ( is_tax() ) {
		$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
		$description = ! empty( $term->meta['description'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->meta['description'] ) ) : '';
	}

	if ( is_author() ) {
		$user_description = get_the_author_meta( 'meta_description', (int) get_query_var( 'author' ) );
		$description = $user_description ? $user_description : '';
	}

	/** Add the description if one exists */
	if ( $description )
		echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";

}

add_action( 'genesis_meta', 'genesis_seo_meta_keywords' );
/**
 * This function generates the meta keywords based on contextual criteria.
 *
 * Outputs nothing if keywords aren't present.
 *
 * @since 1.2.0
 *
 * @uses genesis_get_seo_option() Get SEO setting value
 * @uses genesis_get_custom_field() Get custom field value
 *
 * @global WP_Query $wp_query Query object
 * @global stdClass $post Post object
 */
function genesis_seo_meta_keywords() {

	global $wp_query, $post;

	$keywords = '';

	/** If we're on the home page */
	if ( is_front_page() )
		$keywords = genesis_get_seo_option( 'home_keywords' );

	/** If we're on a single post / page / attachment */
	if ( is_singular() ) {
		/** Keywords are set via custom field */
		if ( genesis_get_custom_field( '_genesis_keywords' ) )
			$keywords = genesis_get_custom_field( '_genesis_keywords' );
		/** All-in-One SEO Pack (latest, vestigial) */
		elseif ( genesis_get_custom_field( '_aioseop_keywords' ) )
			$keywords = genesis_get_custom_field( '_aioseop_keywords' );
		/** Thesis (vestigial) */
		elseif ( genesis_get_custom_field( 'thesis_keywords' ) )
			$keywords = genesis_get_custom_field( 'thesis_keywords' );
		/** All-in-One SEO Pack (old, vestigial) */
		elseif ( genesis_get_custom_field( 'keywords' ) )
			$keywords = genesis_get_custom_field( 'keywords' );
	}

	if ( is_category() ) {
		$term     = $wp_query->get_queried_object();
		$keywords = ! empty( $term->meta['keywords'] ) ? $term->meta['keywords'] : '';
	}

	if ( is_tag() ) {
		$term     = $wp_query->get_queried_object();
		$keywords = ! empty( $term->meta['keywords'] ) ? $term->meta['keywords'] : '';
	}

	if ( is_tax() ) {
		$term     = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
		$keywords = ! empty( $term->meta['keywords'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->meta['keywords'] ) ) : '';
	}

	if ( is_author() ) {
		$user_keywords = get_the_author_meta( 'meta_keywords', (int) get_query_var( 'author' ) );
		$keywords = $user_keywords ? $user_keywords : '';
	}

	/** Add the keywords if they exist */
	if ( $keywords )
		echo '<meta name="keywords" content="' . esc_attr( $keywords ) . '" />' . "\n";

}

add_action( 'genesis_meta', 'genesis_robots_meta' );
/**
 * This function generates the index / follow / noodp / noydir / noarchive code
 * in the document <head>.
 *
 * @since 0.1.3
 *
 * @uses genesis_get_seo_option() Get SEO setting value
 * @uses genesis_get_custom_field() Get custom field value
 *
 * @global WP_Query $wp_query Query object
 * @global stdClass $post Post object
 * @return null Returns early if blog is not public
 */
function genesis_robots_meta() {

	global $wp_query, $post;

	/*
	 * If the blog is private, then following logic is unnecessary as WP will
	 * insert noindex and nofollow
	 */
	if ( 0 == get_option( 'blog_public' ) )
		return;

	/** Defaults */
	$meta = array(
		'noindex'   => '',
		'nofollow'  => '',
		'noarchive' => genesis_get_seo_option( 'noarchive' ) ? 'noarchive' : '',
		'noodp'     => genesis_get_seo_option( 'noodp' ) ? 'noodp' : '',
		'noydir'    => genesis_get_seo_option( 'noydir' ) ? 'noydir' : '',
	);

	/** Check home page SEO settings, set noindex, nofollow and noarchive */
	if ( is_front_page() ) {
		$meta['noindex']   = genesis_get_seo_option( 'home_noindex' ) ? 'noindex' : $meta['noindex'];
		$meta['nofollow']  = genesis_get_seo_option( 'home_nofollow' ) ? 'nofollow' : $meta['nofollow'];
		$meta['noarchive'] = genesis_get_seo_option( 'home_noarchive' ) ? 'noarchive' : $meta['noarchive'];
	}

	if ( is_category() ) {
		$term = $wp_query->get_queried_object();

		$meta['noindex']   = $term->meta['noindex'] ? 'noindex' : $meta['noindex'];
		$meta['nofollow']  = $term->meta['nofollow'] ? 'nofollow' : $meta['nofollow'];
		$meta['noarchive'] = $term->meta['noarchive'] ? 'noarchive' : $meta['noarchive'];

		$meta['noindex']   = genesis_get_seo_option( 'noindex_cat_archive' ) ? 'noindex' : $meta['noindex'];
		$meta['noarchive'] = genesis_get_seo_option( 'noarchive_cat_archive' ) ? 'noarchive' : $meta['noarchive'];

		/**	noindex paged archives, if canonical archives is off */
		$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		$meta['noindex'] = $paged > 1 && ! genesis_get_seo_option( 'canonical_archives' ) ? 'noindex' : $meta['noindex'];
	}

	if ( is_tag() ) {
		$term = $wp_query->get_queried_object();

		$meta['noindex']   = $term->meta['noindex'] ? 'noindex' : $meta['noindex'];
		$meta['nofollow']  = $term->meta['nofollow'] ? 'nofollow' : $meta['nofollow'];
		$meta['noarchive'] = $term->meta['noarchive'] ? 'noarchive' : $meta['noarchive'];

		$meta['noindex']   = genesis_get_seo_option( 'noindex_tag_archive' ) ? 'noindex' : $meta['noindex'];
		$meta['noarchive'] = genesis_get_seo_option( 'noarchive_tag_archive' ) ? 'noarchive' : $meta['noarchive'];

		/**	noindex paged archives, if canonical archives is off */
		$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		$meta['noindex'] = $paged > 1 && ! genesis_get_seo_option( 'canonical_archives' ) ? 'noindex' : $meta['noindex'];
	}

	if ( is_tax() ) {
		$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

		$meta['noindex']   = $term->meta['noindex'] ? 'noindex' : $meta['noindex'];
		$meta['nofollow']  = $term->meta['nofollow'] ? 'nofollow' : $meta['nofollow'];
		$meta['noarchive'] = $term->meta['noarchive'] ? 'noarchive' : $meta['noarchive'];

		/** noindex paged archives, if canonical archives is off */
		$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		$meta['noindex'] = $paged > 1 && ! genesis_get_seo_option( 'canonical_archives' ) ? 'noindex' : $meta['noindex'];
	}

	if ( is_author() ) {
		$meta['noindex']   = get_the_author_meta( 'noindex', (int) get_query_var( 'author' ) ) ? 'noindex' : $meta['noindex'];
		$meta['nofollow']  = get_the_author_meta( 'nofollow', (int) get_query_var( 'author' ) ) ? 'nofollow' : $meta['nofollow'];
		$meta['noarchive'] = get_the_author_meta( 'noarchive', (int) get_query_var( 'author' ) ) ? 'noarchive' : $meta['noarchive'];

		$meta['noindex']   = genesis_get_seo_option( 'noindex_author_archive' ) ? 'noindex' : $meta['noindex'];
		$meta['noarchive'] = genesis_get_seo_option( 'noarchive_author_archive' ) ? 'noarchive' : $meta['noarchive'];

		/**	noindex paged archives, if canonical archives is off */
		$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		$meta['noindex'] = $paged > 1 && ! genesis_get_seo_option( 'canonical_archives' ) ? 'noindex' : $meta['noindex'];
	}

	if ( is_date() ) {
		$meta['noindex']   = genesis_get_seo_option( 'noindex_date_archive' ) ? 'noindex' : $meta['noindex'];
		$meta['noarchive'] = genesis_get_seo_option( 'noarchive_date_archive' ) ? 'noarchive' : $meta['noarchive'];
	}

	if ( is_search() ) {
		$meta['noindex']   = genesis_get_seo_option( 'noindex_search_archive' ) ? 'noindex' : $meta['noindex'];
		$meta['noarchive'] = genesis_get_seo_option( 'noarchive_search_archive' ) ? 'noarchive' : $meta['noarchive'];
	}

	if ( is_singular() ) {
		$meta['noindex']   = genesis_get_custom_field( '_genesis_noindex' ) ? 'noindex' : $meta['noindex'];
		$meta['nofollow']  = genesis_get_custom_field( '_genesis_nofollow' ) ? 'nofollow' : $meta['nofollow'];
		$meta['noarchive'] = genesis_get_custom_field( '_genesis_noarchive' ) ? 'noarchive' : $meta['noarchive'];
	}

	/** Strip empty array items */
	$meta = array_filter( $meta );

	/** Add meta if any exist */
	if ( $meta )
		printf( '<meta name="robots" content="%s" />' . "\n", implode( ',', $meta ) );

}

add_action( 'genesis_meta', 'genesis_show_theme_info_in_head' );
/**
 * Show Parent and Child information in the document head if specified by the user.
 *
 * This can be helpful for diagnosing problems with the theme, because you can
 * easily determine if anything is out of date, needs to be updated.
 *
 * @since 1.0.0
 *
 * @uses PARENT_THEME_NAME
 * @uses PARENT_THEME_VERSION
 * @uses CHILD_DIR
 * @uses genesis_get_option() Get theme setting value
 *
 * @return null Returns early if show_info setting is off
 */
function genesis_show_theme_info_in_head() {

	if ( ! genesis_get_option( 'show_info' ) )
		return;

	/** Show Parent Info */
	echo "\n" . '<!-- Theme Information -->' . "\n";
	echo '<meta name="wp_template" content="' . esc_attr( PARENT_THEME_NAME ) . ' ' . esc_attr( PARENT_THEME_VERSION ) . '" />' . "\n";

	/** If there is no child theme, don't continue */
	if ( ! is_child_theme() )
		return;

	global $wp_version;

	/** Show Child Info */
	$child_info = version_compare( $wp_version, '3.4', '>=' ) ? wp_get_theme() : get_theme_data( CHILD_DIR . '/style.css' );
	echo '<meta name="wp_theme" content="' . esc_attr( $child_info['Name'] ) . ' ' . esc_attr( $child_info['Version'] ) . '" />' . "\n";

}

add_action( 'wp_head', 'genesis_do_meta_pingback' );
/**
 * Adds the pingback meta tag to the head so that other sites can know how to
 * send a pingback to our site.
 *
 * @since 1.3.0
 */
function genesis_do_meta_pingback() {

	if ( 'open' == get_option( 'default_ping_status' ) )
		echo '<link rel="pingback" href="' . get_bloginfo( 'pingback_url' ) . '" />' . "\n";

}

add_action( 'wp_head', 'genesis_canonical', 5 );
/**
 * Echo custom canonical link tag.
 *
 * Remove the default WordPress canonical tag, and use our custom
 * one. Gives us more flexibility and effectiveness.
 *
 * @since 0.1.3
 *
 * @uses genesis_get_seo_option() Get SEO setting value
 * @uses genesis_get_custom_field() Get custom field value
 *
 * @global WP_Query $wp_query
 * @return null Returns null on failure to determine queried object
 */
function genesis_canonical() {

	/** Remove the WordPress canonical */
	remove_action( 'wp_head', 'rel_canonical' );

	global $wp_query;

	$canonical = '';

	if ( is_front_page() )
		$canonical = trailingslashit( home_url() );

	if ( is_singular() ) {
		if ( ! $id = $wp_query->get_queried_object_id() )
			return;

		$cf = genesis_get_custom_field( '_genesis_canonical_uri' );

		$canonical = $cf ? $cf : get_permalink( $id );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		if ( !$id = $wp_query->get_queried_object_id() )
			return;

		$taxonomy = $wp_query->queried_object->taxonomy;

		$canonical = genesis_get_seo_option( 'canonical_archives' ) ? get_term_link( (int) $id, $taxonomy ) : 0;
	}

	if ( is_author() ) {
		if ( ! $id = $wp_query->get_queried_object_id() )
			return;

		$canonical = genesis_get_seo_option( 'canonical_archives' ) ? get_author_posts_url( $id ) : 0;
	}

	if ( $canonical )
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( apply_filters( 'genesis_canonical', $canonical ) ) );

}

add_action( 'genesis_meta', 'genesis_load_favicon' );
/**
 * Echo favicon link if one is found.
 *
 * Falls back to Genesis theme favicon.
 *
 * URL to favicon is filtered via genesis_favicon_url before being echoed.
 *
 * @since 0.2.2
 *
 * @uses CHILD_DIR
 * @uses CHILD_URL
 * @uses PARENT_URL
 */
function genesis_load_favicon() {

	/** Allow child theme to short-circuit this function */
	$pre = apply_filters( 'genesis_pre_load_favicon', false );

	if ( $pre !== false )
		$favicon = $pre;
	elseif ( file_exists( CHILD_DIR . '/images/favicon.ico' ) )
		$favicon = CHILD_URL . '/images/favicon.ico';
	elseif ( file_exists( CHILD_DIR . '/images/favicon.gif' ) )
		$favicon = CHILD_URL . '/images/favicon.gif';
	elseif ( file_exists( CHILD_DIR . '/images/favicon.png' ) )
		$favicon = CHILD_URL . '/images/favicon.png';
	elseif ( file_exists( CHILD_DIR . '/images/favicon.jpg' ) )
		$favicon = CHILD_URL . '/images/favicon.jpg';
	else
		$favicon = PARENT_URL . '/images/favicon.ico';

	$favicon = apply_filters( 'genesis_favicon_url', $favicon );

	if ( $favicon )
		echo '<link rel="Shortcut Icon" href="' . esc_url( $favicon ) . '" type="image/x-icon" />' . "\n";

}

add_filter( 'genesis_header_scripts', 'do_shortcode' );
add_action( 'wp_head', 'genesis_header_scripts' );
/**
 * Echo header scripts in to wp_head().
 *
 * Allows shortcodes.
 *
 * Applies genesis_header_scripts on value stored in header_scripts setting.
 *
 * Also echoes scripts from the post's custom field.
 *
 * @since 0.2.3
 *
 * @uses genesis_get_option() Get theme setting value
 * @uses genesis_get_custom_field() Echo custom field value
 */
function genesis_header_scripts() {

	echo apply_filters( 'genesis_header_scripts', genesis_get_option( 'header_scripts' ) );

	/** If singular, echo scripts from custom field */
	if ( is_singular() )
		genesis_custom_field( '_genesis_scripts' );

}

add_action( 'after_setup_theme', 'genesis_custom_header' );
/**
 * Activate the custom header feature.
 *
 * It gets arguments passed through add_theme_support(), defines the constants,
 * and calls add_custom_image_header().
 *
 * @since 1.6.0
 *
 * @return null Returns early if custom header not supported in the theme
 */
function genesis_custom_header() {

	$custom_header = get_theme_support( 'genesis-custom-header' );
	$wp_custom_header = get_theme_support( 'custom-header' );

	/** If not active (Genesis of WP custom header), do nothing */
	if ( ! $custom_header && ! $wp_custom_header )
		return;

	/** If not active, do nothing */
	if ( ! $custom_header )
		return;

	/** Blog title option is obsolete when custom header is active */
	add_filter( 'genesis_pre_get_option_blog_title', '__return_empty_array' );

	/** If WP custom header is active, no need to continue */
	if ( $wp_custom_header )
		return;

	/** Cast, if necessary */
	$custom_header = isset( $custom_header[0] ) && is_array( $custom_header[0] ) ? $custom_header[0] : array();

	/** Merge defaults with passed arguments */
	$args = wp_parse_args(
		$custom_header,
		apply_filters(
			'genesis_custom_header_defaults',
			array(
			'width'                 => 960,
			'height'                => 80,
			'textcolor'             => '333333',
			'no_header_text'        => false,
			'header_image'          => '%s/images/header.png',
			'header_callback'       => 'genesis_custom_header_style',
			'admin_header_callback' => 'genesis_custom_header_admin_style',
			)
		)
	);

	/** If running WordPress 3.4 or greater, use new WP custom header */
	global $wp_version;
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {

		/** Push $args into theme support array */
		add_theme_support( 'custom-header', array(
			'default-image'       => sprintf( $args['header_image'], get_stylesheet_directory_uri() ),
			'header-text'         => $args['no_header_text'] ? false : true,
			'default-text-color'  => $args['textcolor'],
			'width'               => $args['width'],
			'height'              => $args['height'],
			'random-default'      => false,
			'wp-head-callback'    => $args['header_callback'],
			'admin-head-callback' => $args['admin_header_callback'],
		) );

		/** Return, so nothing below gets executed */
		return;

	}

	/** Define all the constants */
	if ( ! defined( 'HEADER_IMAGE_WIDTH' ) && is_numeric( $args['width'] ) )
		define( 'HEADER_IMAGE_WIDTH', $args['width'] );

	if ( ! defined( 'HEADER_IMAGE_HEIGHT' ) && is_numeric( $args['height'] ) )
		define( 'HEADER_IMAGE_HEIGHT', $args['height'] );

	if ( ! defined( 'HEADER_TEXTCOLOR' ) && $args['textcolor'] )
		define( 'HEADER_TEXTCOLOR', $args['textcolor'] );

	if ( ! defined( 'NO_HEADER_TEXT' ) && $args['no_header_text'] )
		define( 'NO_HEADER_TEXT', $args['no_header_text'] );

	if ( ! defined( 'HEADER_IMAGE' ) && $args['header_image'] )
		define( 'HEADER_IMAGE', sprintf( $args['header_image'], CHILD_URL ) );

	/** Activate Custom Header */
	add_custom_image_header( $args['header_callback'], $args['admin_header_callback'] );

}

/**
 * Custom header callback.
 *
 * It outputs special CSS to the document head, modifying the look of the header
 * based on user input.
 *
 * @since 1.6.0
 */
function genesis_custom_header_style() {

	/** If no options set, don't waste the output. Do nothing. */
	if ( HEADER_TEXTCOLOR == get_header_textcolor() && HEADER_IMAGE == get_header_image() )
		return;

	/** Header image CSS */
	$output = sprintf( '#header { background: url(%s) no-repeat; }', esc_url( get_header_image() ) );

	/** Header text color CSS, if showing text */
	if ( 'blank' != get_header_textcolor() )
		$output .= sprintf( '#title a, #title a:hover, #description { color: #%s; }', esc_html( get_header_textcolor() ) );

	printf( '<style type="text/css">%s</style>', $output );

}

/**
 * Custom header admin callback.
 *
 * It outputs special CSS to the admin document head, modifying the look of the
 * header area based on user input.
 *
 * Will probably need to be overridden in the child theme with a custom callback.
 *
 * @since 1.6.0
 */
function genesis_custom_header_admin_style() {

	$headimg = sprintf( '.appearance_page_custom-header #headimg { background: url(%s) no-repeat; min-height: %spx; }', get_header_image(), HEADER_IMAGE_HEIGHT );
	$h1      = sprintf( '#headimg h1, #headimg h1 a { color: #%s; font-size: 24px; font-weight: normal; line-height: 30px; margin: 20px 0 0; text-decoration: none; }', esc_html( get_header_textcolor() ) );
	$desc    = sprintf( '#headimg #desc { color: #%s; font-size: 12px; font-style: italic; line-height: 1; margin: 0; }', esc_html( get_header_textcolor() ) );

	printf( '<style type="text/css">%1$s %2$s %3$s</style>', $headimg, $h1, $desc );

}

add_action( 'genesis_header', 'genesis_header_markup_open', 5 );
/**
 * Echo the opening structural markup for the header.
 *
 * @since 1.2.0
 *
 * @uses genesis_structural_wrap() Maybe add opening .wrap div tag
 */
function genesis_header_markup_open() {

	echo '<div id="header">';
	genesis_structural_wrap( 'header' );

}

add_action( 'genesis_header', 'genesis_header_markup_close', 15 );
/**
 * Echo the opening structural markup for the header.
 *
 * @since 1.2.0
 *
 * @uses genesis_structural_wrap() Maybe add closing .wrap div tag
 */
function genesis_header_markup_close() {

	genesis_structural_wrap( 'header', 'close' );
	echo '</div><!--end #header-->';

}

add_action( 'genesis_header', 'genesis_do_header' );
/**
 * Echo the default header, including the #title-area div,
 * along with #title and #description, as well as the .widget-area.
 *
 * Calls the genesis_site_title, genesis_site_description and
 * genesis_header_right actions.
 *
 * @since 1.0.2
 */
function genesis_do_header() {

	echo '<div id="title-area">';
	do_action( 'genesis_site_title' );
	do_action( 'genesis_site_description' );
	echo '</div><!-- end #title-area -->';

	if ( is_active_sidebar( 'header-right' ) || has_action( 'genesis_header_right' ) ) {
		echo '<div class="widget-area">';
		do_action( 'genesis_header_right' );
		dynamic_sidebar( 'header-right' );
		echo '</div><!-- end .widget-area -->';
	}

}