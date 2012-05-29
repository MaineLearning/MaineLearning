<?php
/**
 * Register default sidebars.
 *
 * @category Genesis
 * @package  Widget-Areas
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/**
 * Expedites the widget area registration process by taking common things,
 * before / after_widget, before / after_title, and doing them automatically.
 *
 * See the WP function register_sidebar() for the list of supports $args keys.
 *
 * A typical usage is:
 * <code>
 * genesis_register_sidebar(
 *     'id'          => 'my-sidebar',
 *     'name'        => __( 'My Sidebar', 'my-theme-text-domain' ),
 *     'description' => __( 'A description of the intended purpose or location', 'my-theme-text-domain' ),
 * );
 * </code>
 *
 * @since 1.0.1
 *
 * @param string|array $args Name, ID, description and other widget area arguments
 * @return string The sidebar ID that was added.
 */
function genesis_register_sidebar( $args ) {

	$defaults = (array) apply_filters(
		'genesis_register_sidebar_defaults',
		array(
			'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-wrap">',
			'after_widget'  => "</div></div>\n",
			'before_title'  => '<h4 class="widgettitle">',
			'after_title'   => "</h4>\n",
		)
	);

	$args = wp_parse_args( $args, $defaults );

	return register_sidebar( $args );
}


add_action( 'genesis_setup', 'genesis_register_default_widget_areas' );
/**
 * Registers the default Genesis widget areas.
 *
 * @since 1.6.0
 *
 * @uses genesis_register_sidebar() Register widget areas
 */
function genesis_register_default_widget_areas() {

	genesis_register_sidebar(
		array(
			'id'          => 'header-right',
			'name'        => is_rtl() ? __( 'Header Left', 'genesis' ) : __( 'Header Right', 'genesis' ),
			'description' => __( 'This is the widget area in the header.', 'genesis' ),
		)
	);

	genesis_register_sidebar(
		array(
			'id'          => 'sidebar',
			'name'        => __( 'Primary Sidebar', 'genesis' ),
			'description' => __( 'This is the primary sidebar if you are using a two or three column site layout option.', 'genesis' ),
		)
	);

	genesis_register_sidebar(
		array(
			'id'          => 'sidebar-alt',
			'name'        => __( 'Secondary Sidebar', 'genesis' ),
			'description' => __( 'This is the secondary sidebar if you are using a three column site layout option.', 'genesis' ),
		)
	);

}

add_action( 'after_setup_theme', 'genesis_register_footer_widget_areas' );
/**
 * Registers footer widget areas based on the number of widget areas the user
 * wishes to create with add_theme_support().
 *
 * @since 1.6.0
 *
 * @uses genesis_register_sidebar() Register footer widget areas
 *
 * @return null Returns early if there's no theme support.
 */
function genesis_register_footer_widget_areas() {

	$footer_widgets = get_theme_support( 'genesis-footer-widgets' );

	if ( ! $footer_widgets || ! isset( $footer_widgets[0] ) || ! is_numeric( $footer_widgets[0] ) )
		return;

	$footer_widgets = (int) $footer_widgets[0];

	$counter = 1;

	while ( $counter <= $footer_widgets ) {
		genesis_register_sidebar(
			array(
				'id'          => sprintf( 'footer-%d', $counter ),
				'name'        => sprintf( __( 'Footer %d', 'genesis' ), $counter ),
				'description' => sprintf( __( 'Footer %d widget area.', 'genesis' ), $counter ),
			)
		);

		$counter++;
	}

}


/**
 * Conditionally displays a sidebar, wrapped in a div by default.
 *
 * The $args array accepts the following keys:
 *  - before (markup to be displayed before the widget area output),
 *  - after (markup to be displayed after the widget area output),
 *  - default (fallback text if the sidebar is not found, or has no widgets, default is an empty string),
 *  - show_inactive (flag to show inactive sidebars, default is false),
 *  - before_sidebar_hook (hook that fires before the widget area output),
 *  - after_sidebar_hook (hook that fires after the widget area output).
 *
 * Returns false early if the sidebar is not active and the show_inactive argument is false.
 *
 * @since 1.8.0
 *
 * @param string $id Sidebar ID,as per when it was registered
 * @param array $args Arguments
 * @return boolean False if $args['show_inactive'] set to false and sidebar is not currently being used. True otherwise.
 */
function genesis_widget_area( $id, $args = array() ) {

	if ( ! $id )
		return false;

	$args = wp_parse_args(
		$args,
		array(
			'before'              => '<div class="widget-area">',
			'after'               => '</div>',
			'default'             => '',
			'show_inactive'       => 0,
			'before_sidebar_hook' => 'genesis_before_' . $id . '_widget_area',
			'after_sidebar_hook'  => 'genesis_after_' . $id . '_widget_area',
		)
	);

	if ( ! is_active_sidebar( $id ) && ! $args['show_inactive'] )
		return false;

	/** Opening markup */
	echo $args['before'];

	/** Before hook */
	if ( $args['before_sidebar_hook'] )
			do_action( $args['before_sidebar_hook'] );

	if ( ! dynamic_sidebar( $id ) )
		echo $args['default'];

	/** After hook */
	if( $args['after_sidebar_hook'] )
			do_action( $args['after_sidebar_hook'] );

	/** Closing markup */
	echo $args['after'];

	return true;

}