<?php
/**
 * Register default Genesis layouts.
 *
 * @category Genesis
 * @package  Layout
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

add_action( 'genesis_init', 'genesis_create_initial_layouts', 0 );
/**
 * Registers Genesis default layouts.
 *
 * Genesis comes with 6 layouts registered by default. These are:
 * - content-sidebar (default)
 * - sidebar-content
 * - content-sidebar-sidebar
 * - sidebar-sidebar-content
 * - sidebar-content-sidebar
 * - full-width-content
 *
 * @since 1.4.0
 *
 * @uses genesis_register_layout() Register a layout in Genesis
 * @uses GENESIS_ADMIN_IMAGES_URL URL path to admin images
 */
function genesis_create_initial_layouts() {

	/** Common path to default layout images */
	$url = GENESIS_ADMIN_IMAGES_URL . '/layouts/';

	genesis_register_layout(
		'content-sidebar',
		array(
			'label'   => __( 'Content-Sidebar', 'genesis' ),
			'img'     => $url . 'cs.gif',
			'default' => true,
		)
	);

	genesis_register_layout(
		'sidebar-content',
		array(
			'label' => __( 'Sidebar-Content', 'genesis' ),
			'img'   => $url . 'sc.gif',
		)
	);

	genesis_register_layout(
		'content-sidebar-sidebar',
		array(
			'label' => __( 'Content-Sidebar-Sidebar', 'genesis' ),
			'img'   => $url . 'css.gif',
		)
	);

	genesis_register_layout(
		'sidebar-sidebar-content',
		array(
			'label' => __( 'Sidebar-Sidebar-Content', 'genesis' ),
			'img'   => $url . 'ssc.gif',
		)
	);

	genesis_register_layout(
		'sidebar-content-sidebar',
		array(
			'label' => __( 'Sidebar-Content-Sidebar', 'genesis' ),
			'img'   => $url . 'scs.gif',
		)
	);

	genesis_register_layout(
		'full-width-content',
		array(
			'label' => __( 'Full Width Content', 'genesis' ),
			'img'   => $url . 'c.gif',
		)
	);

}

/**
 * Registers new layouts in Genesis.
 *
 * Modifies the global $_genesis_layouts variable.
 *
 * The support $args keys are:
 *   label (Internationalized name of the layout),
 *   img   (URL path to layout image),
 *   type  (Layout type).
 *
 * Although the 'default' key is also supported, the correct way to change the
 * default is via the genesis_set_default_layout() function to ensure only one
 * layout is set as the default at one time.
 *
 * @since 1.4.0
 *
 * @uses GENESIS_ADMIN_IMAGE_URL
 *
 * @see genesis_set_default_layout()
 *
 * @global array $_genesis_layouts Holds all layouts data
 * @param string $id ID of layout
 * @param array $args Layout data
 * @return boolean|array Returns false if ID is missing or is already set.
 * Returns merged $args otherwise
 */
function genesis_register_layout( $id = '', $args = array() ) {

	global $_genesis_layouts;

	if ( ! is_array( $_genesis_layouts ) )
		$_genesis_layouts = array();

	/** Don't allow empty $id, or double registrations */
	if ( ! $id || isset( $_genesis_layouts[$id] ) )
		return false;

	$defaults = array(
		'label' => __( 'No Label Selected', 'genesis' ),
		'img'   => GENESIS_ADMIN_IMAGES_URL . '/layouts/none.gif',
		'type'  => 'site',
	);

	$args = wp_parse_args( $args, $defaults );

	$_genesis_layouts[$id] = $args;

	return $args;

}

/**
 * Set a default layout.
 *
 * Allows a user to identify a layout as being the default layout on a new
 * install, as well as serve as the fallback layout.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layouts data
 * @param string $id ID of layout to set as default
 * @return boolean|string Returns false if ID is empty or layout is not
 * registered. Returns ID otherwise
 */
function genesis_set_default_layout( $id = '' ) {

	global $_genesis_layouts;

	if ( ! is_array( $_genesis_layouts ) )
		$_genesis_layouts = array();

	/** Don't allow empty $id, or unregistered layouts */
	if ( ! $id || ! isset( $_genesis_layouts[$id] ) )
		return false;

	/** Remove default flag for all other layouts */
	foreach ( (array) $_genesis_layouts as $key => $value ) {
		if ( isset( $_genesis_layouts[$key]['default'] ) )
			unset( $_genesis_layouts[$key]['default'] );
	}

	$_genesis_layouts[$id]['default'] = true;

	return $id;

}

/**
 * Unregister a layout in Genesis.
 *
 * Modifies the global $_genesis_layouts variable.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layout data
 * @param string $id ID of the layout to unregister
 * @return boolean Returns false if ID is empty, or layout is not registered
 */
function genesis_unregister_layout( $id = '' ) {

	global $_genesis_layouts;

	if ( ! $id || ! isset( $_genesis_layouts[$id] ) )
		return false;

	unset( $_genesis_layouts[$id] );

	return true;

}

/**
 * Returns all registered Genesis layouts.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layout data.
 * @param string $type Layout type to return. Leave empty to return all types.
 * @return array Registered layouts.
 */
function genesis_get_layouts( $type = '' ) {

	global $_genesis_layouts;

	/** If no layouts exists, return empty array */
	if ( ! is_array( $_genesis_layouts ) ) {
		$_genesis_layouts = array();
		return $_genesis_layouts;
	}

	/** Return all layouts, if no type specified */
	if ( '' == $type )
		return $_genesis_layouts;

	$layouts = array();

	/** Cycle through looking for layouts of $type */
	foreach ( (array) $_genesis_layouts as $id => $data ) {
		if ( $data['type'] == $type )
			$layouts[$id] = $data;
	}

	return $layouts;

}

/**
 * Returns the data from a single layout, specified by the $id passed to it.
 *
 * @since 1.4.0
 *
 * @uses genesis_get_layouts()
 *
 * @param string $id ID of the layout to return data for
 * @return null|array Returns null if ID is not set, or layout is not registered.
 * Returns array of layout data otherwise, with 'label' and 'image' (and possibly
 * 'default') sub-keys.
 */
function genesis_get_layout( $id ) {

	$layouts = genesis_get_layouts();

	if ( ! $id || ! isset( $layouts[$id] ) )
		return;

	return $layouts[$id];

}

/**
 * Returns the layout that is set to default.
 *
 * @since 1.4.0
 *
 * @global array $_genesis_layouts Holds all layout data
 * @return string Returns ID of the layout, or 'nolayout'
 */
function genesis_get_default_layout() {

	global $_genesis_layouts;

	$default = 'nolayout';

	foreach ( (array) $_genesis_layouts as $key => $value ) {
		if ( isset( $value['default'] ) && $value['default'] ) {
			$default = $key;
			break;
		}
	}

	return $default;

}

/**
 * Returns the site layout for different contexts.
 *
 * Checks both the custom field and the theme option to find the user-selected
 * site layout, and returns it.
 *
 * Value is passed through genesis_site_layout filter just before returning.
 *
 * @since 0.2.2
 *
 * @uses genesis_get_custom_field() Get per-post layout value
 * @uses genesis_get_option() Get theme setting layout value
 * @uses genesis_get_default_layout() Get default from registered layouts
 *
 * @global WP_Query $wp_query
 * @return string
 */
function genesis_site_layout() {

	/** Reset the query, so we always get the right layout */
	wp_reset_query();

	/** If viewing a singular page or post */
	if ( is_singular() ) {
		$custom_field = genesis_get_custom_field( '_genesis_layout' );
		$site_layout  = $custom_field ? $custom_field : genesis_get_option( 'site_layout' );
	}

	/** If viewing a taxonomy archive */
	elseif ( is_category() || is_tag() || is_tax() ) {
		global $wp_query;

		$term = $wp_query->get_queried_object();

		$site_layout = $term && isset( $term->meta['layout'] ) && $term->meta['layout'] ? $term->meta['layout'] : genesis_get_option( 'site_layout' );
	}

	/** If viewing an author archive */
	elseif ( is_author() ) {
		$site_layout = get_the_author_meta( 'layout', (int) get_query_var( 'author' ) ) ? get_the_author_meta( 'layout', (int) get_query_var( 'author' ) ) : genesis_get_option( 'site_layout' );
	}

	/** Else pull the theme option */
	else {
		$site_layout = genesis_get_option( 'site_layout' );
	}

	/** Use default layout as a fallback, if necessary */
	if ( ! genesis_get_layout( $site_layout ) )
		$site_layout = genesis_get_default_layout();

	return esc_attr( apply_filters( 'genesis_site_layout', $site_layout ) );

}

/**
 * Helper function that outputs the form elements necessary to select a layout.
 *
 * You must manually wrap this in an HTML element with the class of
 * 'genesis-layout-selector' in order for the CSS and Javascript to apply properly.
 *
 * Supported $args keys are:
 *   name (default is ''),
 *   selected (default is ''),
 *   echo (default is true).
 *
 * The Genesis admin script is enqueued to ensure the layout selector behaviour
 * (amending label class to add border on selected layout) works.
 *
 * @since 1.7.0
 *
 * @uses genesis_get_layouts() Get all registered layouts
 *
 * @param array $args Optional. Function arguments. Default is empty array
 * @return string HTML markup of labels, images and radio inputs for layout selector
 */
function genesis_layout_selector( $args = array() ) {

	/** Enqueue the Javascript */
	genesis_load_admin_js();

	/** Merge defaults with user args */
	$args = wp_parse_args(
		$args,
		array(
			'name'     => '',
			'selected' => '',
			'type'     => '',
			'echo'     => true,
		)
	);

	$output = '';

	foreach ( genesis_get_layouts( $args['type'] ) as $id => $data ) {
		$class = $id == $args['selected'] ? ' selected' : '';

		$output .= sprintf(
			'<label title="%1$s" class="box%2$s"><img src="%3$s" alt="%1$s" /><br /> <input type="radio" name="%4$s" id="%5$s" value="%5$s" %6$s /></label>',
            esc_attr( $data['label'] ),
			esc_attr( $class ),
			esc_url( $data['img'] ),
			esc_attr( $args['name'] ),
			esc_attr( $id ),
			checked( $id, $args['selected'], false )
		);
	}

	/** Echo or return output */
	if ( $args['echo'] )
		echo $output;
	else
		return $output;

}

/**
 * Potentially echo or return a structural wrap div.
 *
 * A checks is made to see if the $context is in the 'genesis-structural-wraps'
 * theme support data. If so, then the $output may be echoed or returned.
 *
 * @since 1.6.0
 *
 * @param string $context The location ID
 * @param string $output Optional. The markup to include. Can also be 'open'
 * (default) or 'closed' to use pre-determined markup for consistency
 * @param boolean $echo Optional. Whether to echo or return. Default is true (echo)
 * @return string
 */
function genesis_structural_wrap( $context = '', $output = 'open', $echo = true ) {

	$genesis_structural_wraps = get_theme_support( 'genesis-structural-wraps' );

	if ( ! in_array( $context, (array) $genesis_structural_wraps[0] ) )
		return '';

	switch ( $output ) {
		case 'open':
			$output = '<div class="wrap">';
			break;
		case 'close':
			$output = '</div><!-- end .wrap -->';
			break;
	}

	if ( $echo )
		echo $output;
	else
		return $output;

}

/**
 * Helper function for returning layout key 'content-sidebar'.
 *
 * @since 1.7.0
 *
 * @return string 'content-sidebar'
 */
function __genesis_return_content_sidebar() {

	return 'content-sidebar';

}

/**
 * Helper function for returning layout key 'sidebar-content'.
 *
 * @since 1.7.0
 *
 * @return string 'sidebar-content'
 */
function __genesis_return_sidebar_content() {

	return 'sidebar-content';

}

/**
 * Helper function for returning layout key 'content-sidebar-sidebar'.
 *
 * @since 1.7.0
 *
 * @return string 'content-sidebar-sidebar'
 */
function __genesis_return_content_sidebar_sidebar() {

	return 'content-sidebar-sidebar';

}

/**
 * Helper function for returning layout key 'sidebar-sidebar-content'.
 *
 * @since 1.7.0
 *
 * @return string 'sidebar-sidebar-content'
 */
function __genesis_return_sidebar_sidebar_content() {

	return 'sidebar-sidebar-content';

}

/**
 * Helper function for returning layout key 'sidebar-content-sidebar'.
 *
 * @since 1.7.0
 *
 * @return string 'sidebar-content-sidebar'
 */
function __genesis_return_sidebar_content_sidebar() {

	return 'sidebar-content-sidebar';

}

/**
 * Helper function for returning layout key 'full-width-content'.
 *
 * @since 1.7.0
 *
 * @return string 'full-width-content'
 */
function __genesis_return_full_width_content() {

	return 'full-width-content';

}