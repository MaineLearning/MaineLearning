<?php
/**
 * Adds loop structures.
 *
 * @category   Genesis
 * @package    Structure
 * @subpackage Loops
 * @author     StudioPress
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://www.studiopress.com/themes/genesis
 */

add_action( 'genesis_loop', 'genesis_do_loop' );
/**
 * Attach a loop to the genesis_loop output hook so we can get
 * some front-end output. Pretty basic stuff.
 *
 * @since 1.1.0
 *
 * @uses genesis_get_option() Get theme setting value
 * @uses genesis_get_custom_field() Get custom field value
 * @uses genesis_custom_loop() Do custom loop
 * @uses genesis_standard_loop() Do standard loop
 */
function genesis_do_loop() {

	if ( is_page_template( 'page_blog.php' ) ) {
		$include = genesis_get_option( 'blog_cat' );
		$exclude = genesis_get_option( 'blog_cat_exclude' ) ? explode( ',', str_replace( ' ', '', genesis_get_option( 'blog_cat_exclude' ) ) ) : '';
		$paged   = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		/** Easter Egg */
		$query_args = wp_parse_args(
			genesis_get_custom_field( 'query_args' ),
			array(
				'cat'              => $include,
				'category__not_in' => $exclude,
				'showposts'        => genesis_get_option( 'blog_cat_num' ),
				'paged'            => $paged,
			)
		);

		genesis_custom_loop( $query_args );
	} else {
		genesis_standard_loop();
	}

}

/**
 * This is a standard loop, and is meant to be executed, without
 * modification, in most circumstances where content needs to be displayed.
 *
 * It outputs basic wrapping HTML, but uses hooks to do most of its
 * content output like title, content, post information and comments.
 *
 * The action hooks called are:
 *   genesis_before_post,
 *   genesis_before_post_title,
 *   genesis_post_title,
 *   genesis_after_post_title,
 *   genesis_before_post_content,
 *   genesis_post_content
 *   genesis_after_post_content
 *   genesis_after_post,
 *   genesis_after_endwhile,
 *   genesis_loop_else (only if no posts were found).
 *
 * @since 1.1.0
 *
 * @global integer $loop_counter Increments on each loop pass
 */
function genesis_standard_loop() {

	global $loop_counter;

	$loop_counter = 0;

	if ( have_posts() ) : while ( have_posts() ) : the_post();

	do_action( 'genesis_before_post' );
	?>
	<div <?php post_class(); ?>>

		<?php do_action( 'genesis_before_post_title' ); ?>
		<?php do_action( 'genesis_post_title' ); ?>
		<?php do_action( 'genesis_after_post_title' ); ?>

		<?php do_action( 'genesis_before_post_content' ); ?>
		<div class="entry-content">
			<?php do_action( 'genesis_post_content' ); ?>
		</div><!-- end .entry-content -->
		<?php do_action( 'genesis_after_post_content' ); ?>

	</div><!-- end .postclass -->
	<?php

	do_action( 'genesis_after_post' );
	$loop_counter++;

	endwhile; /** end of one post **/
	do_action( 'genesis_after_endwhile' );

	else : /** if no posts exist **/
	do_action( 'genesis_loop_else' );
	endif; /** end loop **/

}

/**
 * This is a custom loop function, and is meant to be executed when a
 * custom query is needed.
 *
 * It accepts arguments in query_posts style format to modify the custom
 * WP_Query object.
 *
 * It outputs basic wrapping HTML, but uses hooks to do most of its
 * content output like title, content, post information, and comments.
 *
 * The arguments can be passed in via the genesis_custom_loop_args filter.
 *
 * The action hooks called are the same as genesis_standard_loop().
 *
 * @since 1.1.0
 *
 * @uses genesis_standard_loop()
 *
 * @global WP_Query $wp_query Query object.
 * @global integer $more
 * @global integer $loop_counter Increments on each loop pass.
 *
 * @param array $args Loop configuration.
 */
function genesis_custom_loop( $args = array() ) {

	global $wp_query, $more;

	$defaults = array(); /** For forward compatibility **/
	$args     = apply_filters( 'genesis_custom_loop_args', wp_parse_args( $args, $defaults ), $args, $defaults );

	$wp_query = new WP_Query( $args );

	/** Only set $more to 0 if we're on an archive */
	$more = is_singular() ? $more : 0;

	genesis_standard_loop();

	/** Restore original query **/
	wp_reset_query();

}

/**
 * The grid loop - a specific implementation of a custom loop.
 *
 * Outputs markup compatible with a Feature + Grid style layout.
 * All normal loop hooks present, except for genesis_post_content.
 *
 * The arguments can be filtered by the genesis_grid_loop_args filter.
 *
 * @since 1.5.0
 *
 * @uses g_ent() Pass entities through filter
 * @uses genesis_custom_loop() Do custom loop
 * @uses genesis_standard_loop() Do standard loop
 * @uses genesis_reset_loop() Restores all default post loop output by rehooking all default functions
 *
 * @global array $_genesis_loop_args Associative array for grid loop configuration
 * @global string $query_string Query string
 * @param array $args Associative array for grid loop configuration
 * @return null Returns early if posts_per_page is fewer than features
 */
function genesis_grid_loop( $args = array() ) {

	/** Global vars */
	global $_genesis_loop_args, $query_string;

	/** Parse args */
	$args = apply_filters(
		'genesis_grid_loop_args',
		wp_parse_args(
			$args,
			array(
				'loop'					=> 'standard',
				'features'				=> 2,
				'features_on_all'		=> false,
				'feature_image_size'	=> 0,
				'feature_image_class'	=> 'alignleft post-image',
				'feature_content_limit'	=> 0,
				'grid_image_size'		=> 'thumbnail',
				'grid_image_class'		=> 'alignleft post-image',
				'grid_content_limit'	=> 0,
				'more'					=> g_ent( __( 'Read more&hellip;', 'genesis' ) ),
				'posts_per_page'		=> get_option( 'posts_per_page' ),
				'paged'					=> get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
			)
		)
	);

	/** Error handler */
	if ( $args['posts_per_page'] < $args['features'] ) {
		trigger_error( sprintf( __( 'You are using invalid arguments with the %s function.', 'genesis' ), __FUNCTION__ ) );
		return;
	}

	/** Potentially remove features on page 2+ */
	if ( ! $args['features_on_all'] && $args['paged'] > 1 )
		$args['features'] = 0;

	/** Set global loop args */
	$_genesis_loop_args = wp_parse_args( $args, $query_string );

	/** Remove some unnecessary stuff from the grid loop */
	remove_action( 'genesis_before_post_title', 'genesis_do_post_format_image' );
	remove_action( 'genesis_post_content', 'genesis_do_post_image' );
	remove_action( 'genesis_post_content', 'genesis_do_post_content' );

	/** Custom loop output */
	add_filter( 'post_class', 'genesis_grid_loop_post_class' );
	add_action( 'genesis_post_content', 'genesis_grid_loop_content' );
	
	/** Set query args */
	$args = $_genesis_loop_args;
	if ( isset( $args['features'] ) && is_numeric( $args['features'] ) )
		unset( $args['features'] );

	/** The loop */
	if ( 'custom' == $_genesis_loop_args['loop'] ) {
		genesis_custom_loop( $args );
	} else {
		query_posts( $args );
		genesis_standard_loop();
	}

	/** Reset loops */
	genesis_reset_loops();
	remove_filter( 'post_class', 'genesis_grid_loop_post_class' );
	remove_action( 'genesis_post_content', 'genesis_grid_loop_content' );

}

/**
 * Filters the post class array to output custom classes for the feature/grid
 * layout, based on the grid loop args and the loop counter.
 *
 * @since 1.5.0
 *
 * @global array $_genesis_loop_args Associative array for grid loop config
 * @global integer $loop_counter Increments on each loop pass
 * @param array $classes Existing post classes
 * @return array Amended post classes
 */
function genesis_grid_loop_post_class( $classes ) {

	global $_genesis_loop_args, $loop_counter;

	$grid_classes = array();

	if ( $_genesis_loop_args['features'] && $loop_counter < $_genesis_loop_args['features'] ) {
		$grid_classes[] = 'genesis-feature';
		$grid_classes[] = sprintf( 'genesis-feature-%s', $loop_counter + 1 );
		$grid_classes[] = $loop_counter&1 ? 'genesis-feature-even' : 'genesis-feature-odd';
	}
	elseif ( $_genesis_loop_args['features']&1 ) {
		$grid_classes[] = 'genesis-grid';
		$grid_classes[] = sprintf( 'genesis-grid-%s', $loop_counter - $_genesis_loop_args['features'] + 1 );
		$grid_classes[] = $loop_counter&1 ? 'genesis-grid-odd' : 'genesis-grid-even';
	}
	else {
		$grid_classes[] = 'genesis-grid';
		$grid_classes[] = sprintf( 'genesis-grid-%s', $loop_counter - $_genesis_loop_args['features'] + 1 );
		$grid_classes[] = $loop_counter&1 ? 'genesis-grid-even' : 'genesis-grid-odd';
	}

	return array_merge( $classes, apply_filters( 'genesis_grid_loop_post_class', $grid_classes ) );

}

/**
 * Outputs specially formatted content, based on the grid loop args.
 *
 * @since 1.5.0
 *
 * @uses genesis_get_image() Returns an image pulled from the media gallery
 * @uses the_content_limit() Echoes the limited content
 *
 * @global array $_genesis_loop_args  Associative array for grid loop configuration
 */
function genesis_grid_loop_content() {

	global $_genesis_loop_args;

	if ( in_array( 'genesis-feature', get_post_class() ) ) {
		if ( $_genesis_loop_args['feature_image_size'] )
			printf( '<a href="%s" title="%s">%s</a>', get_permalink(), the_title_attribute( 'echo=0' ), genesis_get_image( array( 'size' => $_genesis_loop_args['feature_image_size'], 'attr' => array( 'class' => esc_attr( $_genesis_loop_args['feature_image_class'] ) ) ) ) );

		if ( $_genesis_loop_args['feature_content_limit'] )
			the_content_limit( (int) $_genesis_loop_args['feature_content_limit'], esc_html( $_genesis_loop_args['more'] ) );
		else
			the_content( esc_html( $_genesis_loop_args['more'] ) );
	}
	else {
		if ( $_genesis_loop_args['grid_image_size'] )
			printf( '<a href="%s" title="%s">%s</a>', get_permalink(), the_title_attribute( 'echo=0' ), genesis_get_image( array( 'size' => $_genesis_loop_args['grid_image_size'], 'attr' => array( 'class' => esc_attr( $_genesis_loop_args['grid_image_class'] ) ) ) ) );

		if ( $_genesis_loop_args['grid_content_limit'] ) {
			the_content_limit( (int) $_genesis_loop_args['grid_content_limit'], esc_html( $_genesis_loop_args['more'] ) );
		} else {
			the_excerpt();
			printf( '<a href="%s" class="more-link">%s</a>', get_permalink(), esc_html( $_genesis_loop_args['more'] ) );
		}
	}

}