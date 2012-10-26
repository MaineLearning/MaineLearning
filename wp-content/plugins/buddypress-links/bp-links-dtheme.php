<?php
/* 
 * Display functions that are specific to the bp-links-default theme
 */

function bp_links_is_default_theme() {
	return ( BP_LINKS_THEME == BP_LINKS_DEFAULT_THEME );
}

function bp_links_dtheme_enqueue_styles() {
	wp_enqueue_style( 'bp-links-default', BP_LINKS_THEME_URL . '/_inc/css/default.css' );
	wp_enqueue_style( 'bp-links-forms', BP_LINKS_THEME_URL . '/_inc/css/forms.css' );
	wp_enqueue_style( 'colorbox', BP_LINKS_THEME_URL . '/_inc/css/colorbox/colorbox.css' );
}

function bp_links_dtheme_enqueue_scripts( $forms = false ) {
	// load global ajax scripts
	wp_enqueue_script( 'bp-links-ajax', BP_LINKS_THEME_URL_INC . '/global.js', array('jquery') );
	// load color box JS
	wp_enqueue_script( 'colorbox', BP_LINKS_THEME_URL_INC . '/jquery.colorbox-min.js', array('jquery') );
	// load create forms ajax scripts if necessary
	if ( $forms || bp_links_is_link_admin_page() ) {
		wp_enqueue_script( 'bp-links-ajax-forms', BP_LINKS_THEME_URL_INC . '/forms.js', array('jquery') );
	}
}

//
// Template Actions / Filters
//

function bp_links_dtheme_add_css() {
	global $bp;
	
	if ( !bp_links_is_default_theme() )
		return false;
	
	if ( bp_is_links_component() ) {
		bp_links_dtheme_enqueue_styles();
	}

	do_action( 'bp_links_dtheme_add_css' );
}
add_action( 'wp_enqueue_scripts', 'bp_links_dtheme_add_css' );

function bp_links_dtheme_add_js() {
	global $bp;

	if ( !bp_links_is_default_theme() )
		return false;

	// leaving this debug code here on purpose
	//var_dump( bp_current_component(), bp_current_action(), $bp->action_variables );

	if ( bp_is_links_component() ) {
		bp_links_dtheme_enqueue_scripts( ( $bp->current_action == 'create' ) );
	}

	do_action( 'bp_links_dtheme_add_js' );
}
add_action( 'wp_enqueue_scripts', 'bp_links_dtheme_add_js' );

function bp_links_dtheme_activity_type_tabs_setup() {
	global $bp;

	if ( !bp_links_is_default_theme() )
		return false;

	if ( is_user_logged_in() && bp_links_total_links_for_user( bp_loggedin_user_id() ) ) {
		echo sprintf(
			'<li id="activity-links"><a href="%s" title="%s">%s</a></li>',
			bp_loggedin_user_domain() . BP_ACTIVITY_SLUG . '/' . bp_links_slug() . '/',
			__( 'The activity of links I created.', 'buddypress-links' ),
			__( 'My Links', 'buddypress-links' ) .
			sprintf( ' <span>%s</span>', bp_links_total_links_for_user( bp_loggedin_user_id() ) )
		);
	}
}
add_action( 'bp_before_activity_type_tab_mentions', 'bp_links_dtheme_activity_type_tabs_setup' );

function bp_links_dtheme_activity_filter_options_setup() {
	global $bp;

	if ( !bp_links_is_default_theme() )
		return false;

	echo sprintf( '<option value="%s">%s</option>', BP_LINKS_ACTIVITY_ACTION_CREATE, __( 'Show Link Created', 'buddypress-links' ) );
	echo sprintf( '<option value="%s">%s</option>', BP_LINKS_ACTIVITY_ACTION_COMMENT, __( 'Show Link Comments', 'buddypress-links' ) );
	echo sprintf( '<option value="%s">%s</option>', BP_LINKS_ACTIVITY_ACTION_VOTE, __( 'Show Link Votes', 'buddypress-links' ) );
}
add_action( 'bp_activity_filter_options', 'bp_links_dtheme_activity_filter_options_setup' );
add_action( 'bp_link_activity_filter_options', 'bp_links_dtheme_activity_filter_options_setup' );

function bp_links_dtheme_screen_notification_settings() {

	if ( !bp_links_is_default_theme() )
		return false;
	
	echo bp_links_notification_settings();
}
add_action( 'bp_notification_settings', 'bp_links_dtheme_screen_notification_settings' );

//
// Template Tags
//

function bp_links_dtheme_search_form() {
	global $bp; ?>
	<form action="" method="get" id="search-links-form">
		<label><input type="text" name="s" id="links_search" value="<?php if ( isset( $_GET['s'] ) ) { echo attribute_escape( $_GET['s'] ); } else { _e( 'Search anything...', 'buddypress' ); } ?>"  onfocus="if (this.value == '<?php _e( 'Search anything...', 'buddypress' ) ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e( 'Search anything...', 'buddypress' ) ?>';}" /></label>
		<input type="submit" id="links_search_submit" name="links_search_submit" value="<?php _e( 'Search', 'buddypress-links' ) ?>" />
	</form> <?php
}

function bp_links_dtheme_creation_tabs() {
	global $bp;

	$href = sprintf( '%s/%s/create/', $bp->root_domain, bp_links_root_slug() ); ?>

	<li class="current"><a href="<?php echo $href ?>"><?php _e( 'Create', 'buddypress-links' ) ?></a></li>
	<li><a href="<?php echo $href ?>"><?php _e( 'Start Over', 'buddypress-links' ) ?></a></li> <?php
	do_action( 'bp_links_dtheme_creation_tabs' );
}

function bp_links_dtheme_link_order_options_list() { ?>
	<li id="links-order-select" class="last filter">
		<?php _e( 'Order By:', 'buddypress' ) ?>
		<select id="links-order-by">
			<?php bp_links_link_order_options() ?>
			<?php do_action( 'bp_links_dtheme_link_order_options_list' ) ?>
		</select>
	</li> <?php
}

function bp_links_dtheme_link_category_filter_options_list() { ?>
		<li id="links-category-select" class="last">
			<?php _e( 'Category:', 'buddypress-links' ) ?>
			<select id="links-category-filter">
				<option value="-1"><?php _e( 'All', 'buddypress' ) ?></option>
				<?php bp_links_category_select_options( bp_links_dtheme_selected_category() ) ?>
				<?php do_action( 'bp_links_category_filter_options' ) ?>
			</select>
		</li> <?php
}

//
// AJAX Actions and Filters
//

/**
 * Links Directory Hook
 */
function bp_links_dtheme_template_loader() {
	bp_links_locate_template( array( 'links-loop.php' ), true );
	die();
}
add_action( 'wp_ajax_links_filter', 'bp_links_dtheme_template_loader' );
add_action( 'wp_ajax_nopriv_links_filter', 'bp_links_dtheme_template_loader' );

/**
 * Augment profile Links page sub-navigation
 */
function bp_links_dtheme_personal_links_subnav( $html ) {
	$html .= bp_links_dtheme_link_order_options_list();
	$html .= bp_links_dtheme_link_category_filter_options_list();

	return $html;
}
add_filter( 'bp_get_options_nav_links-my-links', 'bp_links_dtheme_personal_links_subnav' );

/**
 * Helper function to return selected category cookie
 */
function bp_links_dtheme_selected_category() {
	if ( isset( $_COOKIE['bp-links-extras'] ) && preg_match('/^category-\d+$/', $_COOKIE['bp-links-extras'] ) ) {
		$parts = split( '-', $_COOKIE['bp-links-extras'] );
		if ( $parts[1] > 0 ) {
			return $parts[1];
		}
	}

	return null;
}

/**
 * Filter all AJAX bp_filter_request() calls for the category filter
 *
 * @param string $query_string
 * @param string $object
 * @return string
 */
function bp_links_dtheme_category_filter( $query_string, $object ) {
	global $bp;

	$filter_enabled = ( bp_is_links_component() || 'links' == $object );

	$filter_enabled = apply_filters( 'bp_links_dtheme_category_filter_enabled', $filter_enabled );

	if ( true === $filter_enabled ) {

		$selected_category = bp_links_dtheme_selected_category();

		if ( !empty( $selected_category ) ) {
			$args = array();
			parse_str( $query_string, $args );
			$args['category_id'] = $selected_category;
			return http_build_query( $args );
		}

	}

	return $query_string;
}
add_filter( 'bp_dtheme_ajax_querystring', 'bp_links_dtheme_category_filter', 1, 2 );

/**
 * Filter all AJAX bp_filter_request() calls to user ids to profile page calls
 *
 * @param string $query_string
 * @param string $object
 * @param string $filter
 * @param string $scope
 * @return string
 */
function bp_links_dtheme_directory_filter( $query_string, $object, $filter, $scope ) {
	global $bp;

	// look for links component
	if ( bp_is_links_component() ) {
		// must be my links action or scope
		if ( 'mylinks' == $scope || 'my-links' == $bp->current_action ) {
			
			$args = array();
			parse_str( $query_string, $args );

			// inject user id
			$args['user_id'] = ( bp_is_user() ) ? $bp->displayed_user->id : $bp->loggedin_user->id;

			return http_build_query( $args );
		}
	}

	return $query_string;
}
add_filter( 'bp_dtheme_ajax_querystring', 'bp_links_dtheme_directory_filter', 1, 4 );

/**
 * Filter all AJAX bp_activity_request() calls for the 'activity' object with the 'links' scope
 *
 * @param string $query_string
 * @param string $object
 * @param string $filter
 * @param string $scope
 * @param integer $page
 * @param string $search_terms
 * @param string $extras
 * @return string
 */
function bp_links_dtheme_activity_filter( $query_string, $object, $filter, $scope, $page, $search_terms, $extras )
{
	global $bp;

	$do_filter = false;

	// only filter activity.
	if ( bp_links_is_activity_enabled() && $bp->activity->id == $object ) {

		if ( bp_is_user() ) {
			// handle filtering for profile pages
			// this nav does not use AJAX so don't rely on $scope
			if ( bp_is_activity_component() && bp_links_slug() == $bp->current_action ) {
				$do_filter = 'user';
			}
		} else {
			// handle filtering for all non-profile, non-links pages
			if ( !bp_current_component() || bp_is_activity_component() ) {
				// filter under 'activity' component with 'links' scope
				if ( bp_links_id() == $scope ) {
					$do_filter = 'user';
				}
			} elseif ( bp_is_links_component() ) {
				// filter 'links' component home pages
				if ( $bp->is_single_item ) {
					$do_filter = 'default';
				}
			}
		}
	}

	if ( $do_filter ) {
		
		// parse query string
		$args = array();
		parse_str( $query_string, $args );

		switch ( $do_filter ) {
			case 'user':
				// override with links object
				$args['object'] = bp_links_id();
				// user_id must be empty to show OTHER user's actions for this user's links
				$args['user_id'] = false;
				// get recent link cloud ids for this user
				$recent_ids = bp_links_recent_activity_item_ids_for_user();
				// if there is activity, send the ids
				if ( count( $recent_ids ) )
					$args['primary_id'] = join( ',', $recent_ids );
				break;
			case 'default':
				// override with links object
				$args['object'] = bp_links_id();
				// set primary id to current link id if applicable
				if ( $bp->links->current_link ) {
					$args['primary_id'] = $bp->links->current_link->cloud_id;
				}
				break;
		}

		// return modified query string
		return http_build_query( $args );
	}

	// no filtering
	return $query_string;
}
add_filter( 'bp_dtheme_ajax_querystring', 'bp_links_dtheme_activity_filter', 1, 7 );

/**
 * Return "my links" feed URL on activity home page
 *
 * @param string $feed_url
 * @param string $scope
 * @return string
 */
function bp_links_dtheme_activity_feed_url( $feed_url, $scope ) {
	global $bp;

	if ( !bp_links_is_activity_enabled() || empty( $scope ) || $scope != bp_links_id() )
		return $feed_url;

	return $bp->loggedin_user->domain . BP_ACTIVITY_SLUG . '/my-links/feed/';
}
add_filter( 'bp_dtheme_activity_feed_url', 'bp_links_dtheme_activity_feed_url', 11, 2 );

/**
 * Handle creating a custom update to a Link
 *
 * @param string $object
 * @param integer $item_id
 * @param string $content
 * @return integer|false Activity id that was created
 */
function bp_links_dtheme_activity_custom_update( $object, $item_id, $content ) {
	// if object is links, try a custom update
	if ( 'links' == $object ) {
		return bp_links_post_update( array( 'type' => BP_LINKS_ACTIVITY_ACTION_COMMENT, 'link_id' => $item_id, 'content' => $content ) );
	}
}
add_filter( 'bp_activity_custom_update', 'bp_links_dtheme_activity_custom_update', 10, 3 );

?>
