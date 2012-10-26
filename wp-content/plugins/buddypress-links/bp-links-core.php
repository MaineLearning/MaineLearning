<?php

require ( BP_LINKS_PLUGIN_DIR . '/bp-links-embed.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-classes.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-ajax.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-templatetags.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-widgets.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-filters.php' );
require ( BP_LINKS_PLUGIN_DIR . '/bp-links-dtheme.php' );

function bp_links_add_cron_schedules() {
	return array(
		'5_min' => array( 'interval' => 5*60, 'display' => sprintf( __( 'Every %1$d minutes', 'buddypress-links' ), 5 ) ),
		'10_min' => array( 'interval' => 10*60, 'display' => sprintf( __( 'Every %1$d minutes', 'buddypress-links' ), 10 ) ),
		'15_min' => array( 'interval' => 15*60, 'display' => sprintf( __( 'Every %1$d minutes', 'buddypress-links' ), 15 ) ),
		'20_min' => array( 'interval' => 20*60, 'display' => sprintf( __( 'Every %1$d minutes', 'buddypress-links' ), 20 ) ),
		'30_min' => array( 'interval' => 30*60, 'display' => sprintf( __( 'Every %1$d minutes', 'buddypress-links' ), 30 ) )
	);
}
add_filter( 'cron_schedules', 'bp_links_add_cron_schedules' );

/**
 * Return the links unique component id
 * 
 * @return string
 */
function bp_links_id()
{
	global $bp;
	return $bp->links->id;
}

/**
 * Return the links component slug
 *
 * @return string
 */
function bp_links_slug()
{
	global $bp;
	return $bp->links->slug;
}

/**
 * Return the links component root slug
 *
 * @return string
 */
function bp_links_root_slug()
{
	global $bp;
	return $bp->links->root_slug;
}

/**
 * Returns true if links is the current component
 * 
 * @return boolean
 * @return type
 */
function bp_is_links_component()
{
	global $bp;

	return (
		bp_current_component() == bp_links_id() ||
		bp_current_component() == bp_links_slug()
	);
}

/**
 * Get links settings
 *
 * @return mixed
 */
function bp_links_get_settings()
{
	return BP_Links_Settings::get_settings();
}

/**
 * Init links settings
 */
function bp_links_init_settings()
{
	// default values
	$settings = bp_links_get_settings();

	// list avatar size
	if ( !defined( 'BP_LINKS_LIST_AVATAR_SIZE' ) )
		define( 'BP_LINKS_LIST_AVATAR_SIZE', (integer) $settings['buddypress_links_global_avsize'] );

	// list item url to local?
	if ( !defined( 'BP_LINKS_LIST_ITEM_URL_LOCAL' ) )
		define( 'BP_LINKS_LIST_ITEM_URL_LOCAL', (boolean) $settings['buddypress_links_global_linklocal'] );

	// max url characters
	if ( !defined( 'BP_LINKS_MAX_CHARACTERS_URL' ) )
		define( 'BP_LINKS_MAX_CHARACTERS_URL', (integer) $settings['buddypress_links_content_maxurl'] );

	// max name characters
	if ( !defined( 'BP_LINKS_MAX_CHARACTERS_NAME' ) )
		define( 'BP_LINKS_MAX_CHARACTERS_NAME', (integer) $settings['buddypress_links_content_maxname'] );

	// max desc characters
	if ( !defined( 'BP_LINKS_MAX_CHARACTERS_DESCRIPTION' ) )
		define( 'BP_LINKS_MAX_CHARACTERS_DESCRIPTION', (integer) $settings['buddypress_links_content_maxdesc'] );

	// desc required?
	if ( !defined( 'BP_LINKS_IS_REQUIRED_DESCRIPTION' ) )
		define( 'BP_LINKS_IS_REQUIRED_DESCRIPTION', (boolean) $settings['buddypress_links_content_reqdesc'] );

	// create category input
	if ( !defined( 'BP_LINKS_CREATE_CATEGORY_SELECT' ) )
		define( 'BP_LINKS_CREATE_CATEGORY_SELECT', (boolean) $settings['buddypress_links_content_catselect'] );

	// vote changing
	if ( !defined( 'BP_LINKS_VOTE_ALLOW_CHANGE' ) )
		define( 'BP_LINKS_VOTE_ALLOW_CHANGE', (boolean) $settings['buddypress_links_voting_change'] );

	// voting activity
	if ( !defined( 'BP_LINKS_VOTE_RECORD_ACTIVITY' ) )
		define( 'BP_LINKS_VOTE_RECORD_ACTIVITY', (boolean) $settings['buddypress_links_voting_activity'] );
	
	// profile nav position
	if ( !defined( 'BP_LINKS_NAV_POSITION' ) )
		define( 'BP_LINKS_NAV_POSITION', (integer) $settings['buddypress_links_profile_navpos'] );

	// profile activity nav position
	if ( !defined( 'BP_LINKS_ACTNAV_POSITION' ) )
		define( 'BP_LINKS_ACTNAV_POSITION', (integer) $settings['buddypress_links_profile_actnavpos'] );

	// activity history
	if ( !defined( 'BP_LINKS_PERSONAL_ACTIVITY_HISTORY' ) )
		define( 'BP_LINKS_PERSONAL_ACTIVITY_HISTORY', (integer) $settings['buddypress_links_profile_acthist'] );

	do_action( 'bp_links_init_settings' );
}
add_action( 'bp_links_init', 'bp_links_init_settings', 1 );

/**
 * Filter located template from bp_core_load_template
 *
 * @see bp_core_load_template()
 * @param string $located_template
 * @param array $template_names
 * @return string
 */
function bp_links_filter_template( $located_template, $template_names ) {
	global $bp;
	
	// template already located, skip
	if ( !empty( $located_template ) )
		return $located_template;

	// only filter for our component
	if ( bp_is_links_component() ) {
		return bp_links_locate_theme_template( $template_names );
	}

	return '';
}
add_filter( 'bp_located_template', 'bp_links_filter_template', 10, 2 );

/**
 * Define the active theme and stylesheet paths
 */
function bp_links_setup_theme() {
	
	if ( !defined( 'BP_LINKS_THEME' ) ) {

		if ( false != BP_LINKS_CUSTOM_THEME ) {
			define( 'BP_LINKS_THEME', BP_LINKS_CUSTOM_THEME );
		} else {
			switch ( basename( TEMPLATEPATH ) ) {
				case 'bp-classic':
					die('Only the BuddyPress 1.2 and higher default theme is supported!');
				case 'bp-default':
				default:
					define( 'BP_LINKS_THEME', BP_LINKS_DEFAULT_THEME );
			}
		}

		define( 'BP_LINKS_THEME_DIR', BP_LINKS_THEMES_DIR . '/' . BP_LINKS_THEME );
		define( 'BP_LINKS_THEME_DIR_INC', BP_LINKS_THEME_DIR . '/_inc' );

		define( 'BP_LINKS_THEME_URL', BP_LINKS_THEMES_URL . '/' . BP_LINKS_THEME );
		define( 'BP_LINKS_THEME_URL_INC', BP_LINKS_THEME_URL . '/_inc' );
	}
}
add_action( 'bp_init', 'bp_links_setup_theme' );

/**
 * Check if template exists in style path, then check custom plugin location
 *
 * @param array $template_names
 * @param boolean $load Auto load template if set to true
 * @param boolean $require_once
 * @return string
 */
function bp_links_locate_theme_template( $template_names, $load = false, $require_once = true ) {

	bp_links_setup_theme();
	
	if ( !is_array( $template_names ) )
		return '';
	
	$located = '';
	foreach($template_names as $template_name) {

		// split template name at the slashes
		$paths = split( '/', $template_name );

		// only filter templates names that match our unique starting path
		if ( !empty( $paths[0] ) && BP_LINKS_THEME == $paths[0] ) {

			$style_path = STYLESHEETPATH . '/' . $template_name;
			$plugin_path = BP_LINKS_THEMES_DIR . '/' . $template_name;

			if ( file_exists( $style_path )) {
				$located = $style_path;
				break;
			} else if ( file_exists( $plugin_path ) ) {
				$located = $plugin_path;
				break;
			}
		}
	}

	if ($load && '' != $located)
		load_template($located, $require_once);

	return $located;
}

/**
 * Auto-prepend theme name and call standard locate template function
 *
 * @param array $template_names
 * @param boolean $load Auto load template if set to true
 * @return string
 */
function bp_links_locate_template( $template_names, $load = false, $require_once = true ) {

	bp_links_setup_theme();
	
	if ( !is_array( $template_names ) )
		return '';

	$ret_arr = array();

	foreach( $template_names as $template_name ) {
		$ret_arr[] = BP_LINKS_THEME . '/' . $template_name;
	}

	return bp_links_locate_theme_template( $ret_arr, $load, $require_once );
}

/**
 * Use this only inside of screen functions, etc
 *
 * @param string $template
 */
function bp_links_load_template( $template ) {
	bp_links_setup_theme();
	bp_core_load_template( BP_LINKS_THEME . '/' . $template );
}

function bp_links_load_textdomain() {

	// try to get locale
	$locale = apply_filters( 'bp_links_load_textdomain_get_locale', get_locale() );

	// if we found a locale, try to load .mo file
	if ( !empty( $locale ) ) {
		// default .mo file path
		$mofile_default = sprintf( '%s/languages/%s-%s.mo', BP_LINKS_PLUGIN_DIR, BP_LINKS_PLUGIN_TEXTDOMAIN, $locale );
		// final filtered file path
		$mofile = apply_filters( 'bp_links_load_textdomain_mofile', $mofile_default );
		// make sure file exists, and load it
		if ( file_exists( $mofile ) ) {
			load_textdomain( BP_LINKS_PLUGIN_TEXTDOMAIN, $mofile );
		}
	}

	do_action( 'bp_links_load_textdomain' );
}
add_action ( 'bp_setup_nav', 'bp_links_load_textdomain', 1 );
add_action ( 'bp_init', 'bp_links_load_textdomain', 2 );

function bp_links_setup_admin()
{
	// load admin file
	require ( BP_LINKS_PLUGIN_DIR . '/bp-links-admin.php' );

	// set up cron for popularity recalc
	if ( !wp_next_scheduled( 'bp_links_cron_popularity' ) ) {
		wp_schedule_event( time(), '15_min', 'bp_links_cron_popularity' );
	}

	do_action( 'bp_links_setup_admin' );
}
add_action( 'admin_menu', 'bp_links_setup_admin' );

function bp_links_setup_nav() {
	global $bp;

	if ( $link_id = BP_Links_Link::link_exists($bp->current_action) ) {

		/* This is a single link page. */
		$bp->is_single_item = true;
		$bp->links->current_link = &new BP_Links_Link( $link_id );

		/* Using "item" not "link" for generic support in other components. */
		if ( current_user_can( 'manage_links' ) ) {
			$bp->is_item_admin = 1;
		} else {
			$bp->is_item_admin = ( $bp->loggedin_user->id == $bp->links->current_link->user_id ) ? true : false;
		}
	}

	/* Add 'Links' to the main navigation */
	$nav_item_name = sprintf(
		apply_filters(
			'bp_links_nav_item_name',
			__( 'Links', 'buddypress-links' ) . '  <span>%d</span>'
		),
		bp_links_total_links_for_user()
	);

	bp_core_new_nav_item( array(
		'name' => $nav_item_name,
		'slug' => bp_links_slug(),
		'position' => BP_LINKS_NAV_POSITION,
		'screen_function' => 'bp_links_screen_personal_links',
		'default_subnav_slug' => 'my-links',
		'item_css_id' => bp_links_id()
	));

	$links_link = $bp->loggedin_user->domain . bp_links_slug() . '/';

	/* Add the subnav items to the links nav item */
	$subnav_name_mylinks =
		apply_filters(
			'bp_links_subnav_item_name_mylinks',
			__( 'My Links', 'buddypress-links' )
		);

	bp_core_new_subnav_item( array(
		'name' => $subnav_name_mylinks,
		'slug' => 'my-links',
		'parent_url' => $links_link,
		'parent_slug' => bp_links_slug(),
		'screen_function' => 'bp_links_screen_personal_links',
		'position' => 10,
		'item_css_id' => 'links-my-links'
	));

	if ( bp_is_links_component() ) {

		if ( bp_is_my_profile() && !$bp->is_single_item ) {

			$bp->bp_options_title = __( 'My Links', 'buddypress-links' );

			$subnav_name_create = apply_filters(
				'bp_links_subnav_item_name_create',
				__( 'Create', 'buddypress-links'
			));

			bp_core_new_subnav_item( array(
				'name' => $subnav_name_create,
				'slug' => 'create',
				'parent_url' => $links_link,
				'parent_slug' => bp_links_slug(),
				'screen_function' => 'bp_links_screen_personal_links',
				'position' => 20,
				'item_css_id' => 'links-create'
			));

		} else if ( !bp_is_my_profile() && !$bp->is_single_item ) {

			$bp->bp_options_avatar = bp_core_fetch_avatar( array( 'item_id' => $bp->displayed_user->id, 'type' => 'thumb' ) );
			$bp->bp_options_title = $bp->displayed_user->fullname;

		} else if ( $bp->is_single_item ) {
			// We are viewing a single link, so set up the
			// link navigation menu using the $bp->links->current_link global.
			
			/* When in a single link, the first action is bumped down one because of the
			   link name, so we need to adjust this and set the link name to current_item. */
			$bp->current_item = bp_current_action();
			$bp->current_action = bp_action_variable( 0 );
			array_shift( $bp->action_variables );

			$bp->bp_options_title = $bp->links->current_link->name;

			$bp->bp_options_avatar = bp_links_fetch_avatar( array( 'type' => 'thumb' ), $bp->links->current_link );
			
			$link_link = $bp->root_domain . '/' . bp_links_root_slug() . '/' . $bp->links->current_link->slug . '/';

			/* New HIDDEN nav item */
			$nav_name_single = apply_filters(
				'bp_links_nav_item_name_single',
				__( 'Links', 'buddypress-links' )
			);
			bp_core_new_nav_item( array(
				'name' => $nav_name_single,
				'slug' => $bp->links->current_link->slug,
				'position' => -1,
				'screen_function' => 'bp_links_screen_link_home',
				'default_subnav_slug' => 'home',
				'item_css_id' => bp_links_id()
			));

			/* Add the "Home" subnav item, as this will always be present */
			$subnav_name_home = apply_filters(
				'bp_links_subnav_item_name_home',
				__( 'Home', 'buddypress-links' )
			);
			bp_core_new_subnav_item( array(
				'name' => $subnav_name_home,
				'slug' => 'home',
				'parent_url' => $link_link,
				'parent_slug' => $bp->links->current_link->slug,
				'screen_function' => 'bp_links_screen_link_home',
				'position' => 10,
				'item_css_id' => 'link-home'
			));

			/* If the user is a link mod or more, then show the link admin nav item */
			if ( $bp->is_item_admin ) {
				$subnav_name_admin = apply_filters(
					'bp_links_subnav_item_name_admin',
					__( 'Admin', 'buddypress-links' )
				);
				bp_core_new_subnav_item( array(
					'name' => $subnav_name_admin,
					'slug' => 'admin',
					'parent_url' => $link_link,
					'parent_slug' => $bp->links->current_link->slug,
					'screen_function' => 'bp_links_screen_link_admin',
					'position' => 20,
					'user_has_access' => $bp->is_item_admin,
					'item_css_id' => 'link-admin'
				));
			}

		}
	}

	do_action( 'bp_links_setup_nav', $bp->is_item_admin );
}
add_action( 'bp_setup_nav', 'bp_links_setup_nav' );

function bp_links_setup_admin_bar() {
	global $bp, $wp_admin_bar;

	// user must be logged in
	if ( false == is_user_logged_in() ) {
		// abort
		return;
	}

	// base link
	$links_link = $bp->loggedin_user->domain . bp_links_slug() . '/';

	// add top level
	$wp_admin_bar->add_menu( array(
		'parent' => $bp->my_account_menu_id,
		'id'     => 'my-account-' . $bp->links->id,
		'title'  => __( 'Links', 'buddypress-links' ),
		'href'   => trailingslashit( $links_link )
	));

	// add list sub
	$wp_admin_bar->add_menu( array(
		'parent' => 'my-account-' . $bp->links->id,
		'id'     => 'my-account-' . $bp->links->id . '-list',
		'title'  => __( 'List', 'buddypress-links' ),
		'href'   => bp_get_loggedin_user_link() . $bp->links->slug
	));

	// add create sub
	$wp_admin_bar->add_menu( array(
		'parent' => 'my-account-' . $bp->links->id,
		'id'     => 'my-account-' . $bp->links->id . '-create',
		'title'  => __( 'Create', 'buddypress-links' ),
		'href'   => bp_get_loggedin_user_link() . $bp->links->slug . '/create'
	));
	
}
add_action( 'bp_setup_admin_bar', 'bp_links_setup_admin_bar' );

function bp_links_setup_activity_nav() {
	global $bp;

	$user_domain = ( !empty( $bp->displayed_user->domain ) ) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;
	$activity_link = $user_domain . $bp->activity->slug . '/';

	bp_core_new_subnav_item( array(
		'name' => __( 'Links', 'buddypress-links' ),
		'slug' => bp_links_slug(),
		'parent_url' => $activity_link,
		'parent_slug' => $bp->activity->slug,
		'screen_function' => 'bp_links_screen_personal_links_activity',
		'position' => BP_LINKS_ACTNAV_POSITION,
		'item_css_id' => 'activity-links' )
	);
}
add_action( 'bp_activity_setup_nav', 'bp_links_setup_activity_nav' );

function bp_links_directory_links_setup() {
	global $bp;

	if ( bp_is_current_component( 'links') && !bp_current_action() && !bp_current_item() ) {
		bp_update_is_directory( true, 'links' );
		
		do_action( 'bp_links_directory_links_setup' );
		bp_links_load_template( 'index' );
	}
}
add_action( 'bp_screens', 'bp_links_directory_links_setup', 2 );

function bp_links_setup_adminbar_menu() {
	global $bp;

	if ( !$bp->links->current_link )
		return false;

	/* Don't show this menu to non site admins or if you're viewing your own profile */
	if ( !is_super_admin() )
		return false;
	?>
	<li id="bp-adminbar-adminoptions-menu">
		<a href=""><?php _e( 'Admin Options', 'buddypress-links' ) ?></a>

		<ul>
			<li><a class="confirm" href="<?php echo wp_nonce_url( bp_get_link_permalink( $bp->links->current_link ) . '/admin/delete-link/', 'bp_links_delete_link' ) ?>&amp;delete-link-button=1&amp;delete-link-understand=1"><?php _e( 'Delete Link', 'buddypress-links' ) ?></a></li>

			<?php do_action( 'bp_links_adminbar_menu_items' ) ?>
		</ul>
	</li>
	<?php
}
add_action( 'bp_adminbar_menus', 'bp_links_setup_adminbar_menu', 20 );

function bp_links_adminbar_random_menu_setup() {
	global $bp;
	echo sprintf( '<li><a href="%s/%s/?random-link">%s</a></li>', $bp->root_domain, bp_links_root_slug(), __( 'Random Link', 'buddypress-links' ) );
}
add_action( 'bp_adminbar_random_menu', 'bp_links_adminbar_random_menu_setup' );

function bp_links_add_meta() {
	global $bp;

	if ( $bp->is_single_item ) {
		printf(
			'<meta name="description" content="%s" />' . PHP_EOL,
			apply_filters( 'bp_links_add_meta_description_single_item', $bp->links->current_link->description )
		);
	}
}
add_action( 'wp_head', 'bp_links_add_meta' );


/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */

//
// Profile Pages
//

/**
 * Load profile Links page outer (plugins) template
 */
function bp_links_screen_personal_links() {
	global $bp;

	if ( !bp_is_links_component() )
		return false;
	
	// format for deleting notifications if we ever add any
	//bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, bp_links_slug(), 'link_example_notification' );

	do_action( 'bp_links_screen_personal_links' );

	bp_core_load_template( apply_filters( 'bp_links_template_personal_links', 'members/single/plugins' ) );
}

/**
 * Load profile Links page content template
 */
function bp_links_screen_personal_links_template_content() {
	global $bp;

	if ( !bp_is_links_component() )
		return false;

	do_action( 'bp_links_screen_personal_links_template_content' );

	switch ( $bp->current_action ) {
		default:
		case 'my-links':
			do_action( 'bp_links_screen_personal_links_template_content' );
			bp_links_locate_template( array( 'members/single/links-list.php' ), true );
			break;
		case 'create':
			do_action( 'bp_links_screen_personal_links_creation_template_content' );
			bp_links_locate_template( array( 'members/single/links-create.php' ), true );
			break;
	}
}
add_action( 'bp_template_content', 'bp_links_screen_personal_links_template_content' );

/**
 * Load profile Links personal activity page
 */
function bp_links_screen_personal_links_activity() {
	global $bp;

	if ( !is_super_admin() )
		$bp->is_item_admin = false;

	do_action( 'bp_links_screen_personal_links_activity' );
	bp_core_load_template( apply_filters( 'bp_links_template_activity_personal_links', 'members/single/home' ) );
}

/**
 * Load Link home page
 */
function bp_links_screen_link_home() {
	global $bp;

	if ( $bp->is_single_item ) {

		// format for deleting notifications if we ever add any
		//if ( isset($_GET['new']) ) {
		//	bp_core_delete_notifications_for_user_by_type( $bp->loggedin_user->id, bp_links_slug(), 'link_example_notification' );
		//}

		do_action( 'bp_links_screen_link_home' );

		bp_links_load_template( 'single/home' );
	}
}

/**
 * Intercept Link home page admin empty action and redirect
 */
function bp_links_screen_link_admin() {
	global $bp;

	if ( !bp_is_current_component( 'links' ) || 'admin' != $bp->current_action )
		return false;

	if ( !empty( $bp->action_variables[0] ) )
		return false;
	
	bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) . '/admin/edit-details' );
}

/**
 * Load Link home page edit details template, handle form if submitted
 */
function bp_links_screen_link_admin_edit_details() {
	global $bp;

	if ( !$bp->is_item_admin && !$bp->is_item_mod ) {
		return false;
	}

	if ( 'edit-details' != bp_links_admin_current_action_variable() ) {
		return false;
	}

	// If the edit form has been submitted, save the edited details
	if ( isset( $_POST['save'] ) ) {
		
		/* Check the nonce first. */
		if ( !check_admin_referer( 'bp_link_details_form_save' ) )
			return false;

		// validate the data fields
		$data_valid = bp_links_validate_create_form_input();

		if ( !empty( $data_valid ) ) {

			// try to update the link
			$link =
				bp_links_manage_link(
					array(
						'link_id' => $bp->links->current_link->id,
						'category_id' => $data_valid['link-category'],
						'url' => $data_valid['link-url'],
						'name' => $data_valid['link-name'],
						'description' => $data_valid['link-desc'],
						'status' => $data_valid['link-status'],
						'embed_data' => $data_valid['link-url-embed-data'],
						'embed_thidx' => $data_valid['link-url-embed-thidx'],
						'group_id' => $data_valid['link-group-id']
					)
				);

			if ( $link instanceof BP_Links_Link ) {
				$bp->links->current_link = $link;
				do_action( 'bp_links_link_details_edited', $bp->links->current_link->id );
				bp_core_add_message( __( 'Link details were successfully updated.', 'buddypress-links' ) );

				if ( $_POST['link-avatar-option'] == 1 ) {
					bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) . '/admin/link-avatar' );
				} else {
					bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) . '/admin/edit-details' );
				}

			} else {
				bp_core_add_message( sprintf( '%s %s', __( 'There was an error updating link details.', 'buddypress-links' ), __( 'Please try again.', 'buddypress-links' ) ), 'error' );
			}
		}
	}

	do_action( 'bp_links_screen_link_admin_edit_details', $bp->links->current_link->id );

	bp_links_load_template( 'single/home' );

}
add_action( 'bp_screens', 'bp_links_screen_link_admin_edit_details' );

/**
 * Load Link home page edit avatar template, handle form if submitted
 */
function bp_links_screen_link_admin_avatar() {
	global $bp;

	if ( !$bp->is_item_admin || 'link-avatar' != bp_links_admin_current_action_variable() ) {
		return false;
	}

	// If the link admin has deleted the admin avatar
	if ( 'delete' == $bp->action_variables[1] ) {

		/* Check the nonce */
		check_admin_referer( 'bp_link_avatar_delete' );

		if ( bp_core_delete_existing_avatar( array( 'item_id' => $bp->links->current_link->id, 'object' => 'link', 'avatar_dir' => 'link-avatars' ) ) ) {
			bp_core_add_message( __( 'Your avatar was deleted successfully!', 'buddypress-links' ) );
		} else {
			bp_core_add_message( sprintf( '%s %s', __( 'There was a problem deleting that avatar', 'buddypress-links' ), __( 'Please try again.', 'buddypress-links' ) ), 'error' );
		}
	}

	$bp->avatar_admin->step = 'upload-image';

	if ( isset( $_POST['avatar-crop-submit'] ) ) {

		// Check the nonce
		check_admin_referer( 'bp_avatar_cropstore' );

		// received crop coords, crop the image and save a full/thumb version
		if ( bp_core_avatar_handle_crop( array( 'object' => 'link', 'avatar_dir' => 'link-avatars', 'item_id' => $bp->links->current_link->id, 'original_file' => $_POST['image_src'], 'crop_x' => $_POST['x'], 'crop_y' => $_POST['y'], 'crop_w' => $_POST['w'], 'crop_h' => $_POST['h'] ) ) ) {
			bp_links_embed_handle_crop( $bp->links->current_link );
			bp_core_add_message( __( 'The link avatar was uploaded successfully!', 'buddypress-links' ) );
		} else {
			bp_core_add_message( sprintf( '%s %s', __( 'There was an error saving link avatar.', 'buddypress-links' ), __( 'Please try again.', 'buddypress-links' ) ), 'error' );
		}
		
	} elseif ( isset( $_POST['upload'] ) || isset( $_POST['embed-submit'] ) ) {

		// Check the nonce
		check_admin_referer( 'bp_avatar_upload' );

		// handle image uploading
		if ( !empty( $_POST['embed-submit'] ) && bp_links_embed_handle_upload( $bp->links->current_link, $_POST['embed-html'] ) ) {
			
			// we are good to crop
			$bp->avatar_admin->step = 'crop-image';

			// Make sure we include the jQuery jCrop file for image cropping
			add_action( 'wp_enqueue_scripts', 'bp_core_add_jquery_cropper' );

		} elseif ( isset( $_POST['upload'] ) && !empty( $_FILES ) ) {

			// Pass the file to the avatar upload handler
			if ( bp_core_avatar_handle_upload( $_FILES, 'bp_links_avatar_upload_dir' ) ) {

				// we are good to crop
				$bp->avatar_admin->step = 'crop-image';

				// Make sure we include the jQuery jCrop file for image cropping
				add_action( 'wp_enqueue_scripts', 'bp_core_add_jquery_cropper' );
			}
		}
	}

	do_action( 'bp_links_screen_link_admin_avatar', $bp->links->current_link->id );

	bp_links_load_template( 'single/home' );
}
add_action( 'bp_screens', 'bp_links_screen_link_admin_avatar' );

/**
 * Load Link home page delete link template, handle form if submitted
 */
function bp_links_screen_link_admin_delete_link() {
	global $bp;

	if ( !$bp->is_item_admin && !is_super_admin() ) {
		return false;
	}

	if ( 'delete-link' != bp_links_admin_current_action_variable() ) {
		return false;
	}

	if ( isset( $_REQUEST['delete-link-button'] ) && !empty( $_REQUEST['delete-link-understand'] ) ) {

		/* Check the nonce first. */
		if ( !check_admin_referer( 'bp_links_delete_link' ) ) {
			return false;
		}

		// Link admin has deleted the link, now do it.
		if ( bp_links_delete_link( $bp->links->current_link->id ) ) {
			do_action( 'bp_links_link_deleted', $bp->links->current_link->id );
			bp_core_add_message( __( 'The link was deleted successfully', 'buddypress-links' ) );
			bp_core_redirect( $bp->loggedin_user->domain . bp_links_slug() . '/' );
		} else {
			bp_core_add_message( __( 'There was an error deleting the link, please try again.', 'buddypress-links' ), 'error' );
		}

		bp_core_redirect( $bp->loggedin_user->domain . bp_links_slug() );
	}

	do_action( 'bp_links_screen_link_admin_delete_link', $bp->links->current_link->id );

	bp_links_load_template( 'single/home' );
}
add_action( 'bp_screens', 'bp_links_screen_link_admin_delete_link' );

/**
 * Validate new/udpate link form data and format errors
 */
function bp_links_validate_create_form_input() {
	
	$message_required = __( 'Please fill in all of the required fields', 'buddypress-links' );

	// init return data array
	$return_data = array();

	// link category
	if ( !empty( $_POST['link-category'] ) ) {
		$bp_new_link_category = stripslashes( $_POST['link-category'] );
		$return_data['link-category'] = $bp_new_link_category;
	} else {
		bp_core_add_message( $message_required, 'error' );
		return false;
	}

	// link url
	if ( !empty( $_POST['link-url'] ) ) {

		$bp_new_link_url = trim( stripslashes( $_POST['link-url'] ) );

		if ( strlen( $bp_new_link_url ) > BP_LINKS_MAX_CHARACTERS_URL ) {
			bp_core_add_message( sprintf( __( 'Link URL must be %1$d characters or less, please make corrections and re-submit.', 'buddypress-links' ), BP_LINKS_MAX_CHARACTERS_URL ), 'error' );
			return false;
		} elseif ( bp_links_is_url_valid( $bp_new_link_url ) !== true ) {
			bp_core_add_message( __( 'The URL you entered is not valid.', 'buddypress-links' ), 'error' );
			return false;
		}

		$return_data['link-url'] = $bp_new_link_url;

	} else {
		bp_core_add_message( $message_required, 'error' );
		return false;
	}

	// link name
	if ( !empty( $_POST['link-name'] ) ) {

		$bp_new_link_name = trim( stripslashes( $_POST['link-name'] ) );

		if ( ( function_exists('mb_strlen') && mb_strlen( $bp_new_link_name ) > BP_LINKS_MAX_CHARACTERS_NAME ) || strlen( $bp_new_link_name ) > BP_LINKS_MAX_CHARACTERS_NAME ) {
			bp_core_add_message( sprintf( __( 'Link Name must be %1$d characters or less, please make corrections and re-submit.', 'buddypress-links' ), BP_LINKS_MAX_CHARACTERS_NAME ), 'error' );
			return false;
		}

		$return_data['link-name'] = $bp_new_link_name;

	} else {
		bp_core_add_message( $message_required, 'error' );
		return false;
	}

	// link description
	if ( !empty( $_POST['link-desc'] ) ) {

		$bp_new_link_description = trim( stripslashes( $_POST['link-desc'] ) );

		if ( ( function_exists('mb_strlen') && mb_strlen( $bp_new_link_description ) > BP_LINKS_MAX_CHARACTERS_DESCRIPTION ) || strlen( $bp_new_link_description ) > BP_LINKS_MAX_CHARACTERS_DESCRIPTION ) {
			bp_core_add_message( sprintf( __( 'Link Description must be %1$d characters or less, please make corrections and re-submit.', 'buddypress-links' ), BP_LINKS_MAX_CHARACTERS_DESCRIPTION ), 'error' );
			return false;
		}

		$return_data['link-desc'] = $bp_new_link_description;

	} elseif ( true == BP_LINKS_IS_REQUIRED_DESCRIPTION ) {
		bp_core_add_message( $message_required, 'error' );
		return false;
	}

	// link status
	if ( bp_links_is_valid_status( $_POST['link-status'] ) ) {
		$return_data['link-status'] = (integer) $_POST['link-status'];
	} else {
		$return_data['link-status'] = null;
	}

	// link url embed service (optional)
	if ( !empty( $_POST['link-url-embed-data'] ) ) {
		$return_data['link-url-embed-data'] = trim( $_POST['link-url-embed-data'] );
	} else {
		$return_data['link-url-embed-data'] = null;
	}

	// link url embed service selected image index (optional)
	if ( isset( $_POST['link-url-embed-thidx'] ) ) {
		$return_data['link-url-embed-thidx'] = trim( $_POST['link-url-embed-thidx'] );
	} else {
		$return_data['link-url-embed-thidx'] = null;
	}

	// link group association (optional)
	if ( isset( $_POST['link-group-id'] ) ) {
		$return_data['link-group-id'] = (integer) $_POST['link-group-id'];
	} else {
		$return_data['link-group-id'] = null;
	}

	return $return_data;
}

/********************************************************************************
 * Action Functions
 *
 * Action functions are exactly the same as screen functions, however they do not
 * have a template screen associated with them. Usually they will send the user
 * back to the default screen after execution.
 */

function bp_links_action_create_link() {
	global $bp;

	// get component that is creating link
	$component_id = bp_links_create_component_id();

	// must have a component (string)
	if ( false === $component_id ) {
		return false;
	}
	
	// User must be logged in to create links
	if ( !is_user_logged_in() )
		return false;
	
	// If the save, upload or embed button is clicked, lets try to save
	if ( isset( $_POST['save'] ) ) {

		// Check the nonce
		check_admin_referer( 'bp_link_details_form_save' );

		// validate the data fields, redirects on error
		$data_valid = bp_links_validate_create_form_input();

		if ( !empty( $data_valid ) ) {

			// try to create the link
			$bp->links->current_link =
				bp_links_manage_link(
					array(
						'category_id' => $data_valid['link-category'],
						'url' => $data_valid['link-url'],
						'name' => $data_valid['link-name'],
						'description' => $data_valid['link-desc'],
						'status' => $data_valid['link-status'],
						'embed_data' => $data_valid['link-url-embed-data'],
						'embed_thidx' => $data_valid['link-url-embed-thidx'],
						'group_id' => $data_valid['link-group-id']
					)
				);

			if ( bp_links_current_link_exists() ) {

				bp_links_update_linkmeta( $bp->links->current_link->id, 'last_activity', time() );

				bp_links_record_activity( array(
					'item_id' => $bp->links->current_link->cloud_id,
					'action' => apply_filters( 'bp_links_activity_created_link', sprintf( __( '%1$s created the link %2$s', 'buddypress-links'), bp_core_get_userlink( $bp->loggedin_user->id ), '<a href="' . bp_get_link_permalink( $bp->links->current_link ) . '">' . attribute_escape( $bp->links->current_link->name ) . '</a>' ) ),
					'content' => apply_filters( 'bp_links_activity_created_link_content', bp_get_link_description_excerpt( $bp->links->current_link ) ),
					'primary_link' => apply_filters( 'bp_links_activity_created_link_primary_link', bp_get_link_permalink( $bp->links->current_link ) ),
					'type' => BP_LINKS_ACTIVITY_ACTION_CREATE
				) );

				do_action( 'bp_links_create_complete', $bp->links->current_link->id );

				if ( $_POST['link-avatar-option'] == 1 ) {
					bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) . '/admin/link-avatar' );
				} else {
					bp_core_redirect( bp_get_link_permalink( $bp->links->current_link ) );
				}

			} else {
				bp_core_add_message( sprintf( '%s %s', __( 'There was an error saving link details.', 'buddypress-links' ), __( 'Please try again.', 'buddypress-links' ) ), 'error' );
			}
		}
	}

	// only load the template for native links component.
	// the group extension *should* load the correct template for us if applicable
	if ( bp_links_id() == $component_id ) {
		if ( bp_displayed_user_id() ) {
			bp_links_screen_personal_links();
		} else {
			bp_links_load_template( apply_filters( 'bp_links_template_create_link', 'create' ) );
		}
	}
}
add_action( 'bp_screens', 'bp_links_action_create_link', 1 );

function bp_links_action_redirect_to_random_link() {
	global $bp, $wpdb;

	if ( bp_is_links_component() && isset( $_GET['random-link'] ) ) {
	
		$link = bp_links_get_random();

		bp_core_redirect( $bp->root_domain . '/' . bp_links_root_slug() . '/' . $link['links'][0]->slug );
	}
}
add_action( 'bp_screens', 'bp_links_action_redirect_to_random_link' );

function bp_links_action_link_feed() {
	global $bp, $wp_query;

	if ( !bp_is_links_component() || !$bp->links->current_link || $bp->current_action != 'feed' )
		return false;

	$wp_query->is_404 = false;
	status_header( 200 );

	if ( !bp_links_is_link_visibile( $bp->links->current_link ) ) {
		return false;
	}

	include_once( 'feeds/bp-links-link-feed.php' );
	die;
}
add_action( 'bp_init', 'bp_links_action_link_feed', 6 );

function bp_links_action_personal_links_feed() {
	global $bp, $wp_query;

	if ( !bp_is_activity_component() || !$bp->displayed_user->id || $bp->current_action != 'my-links' || $bp->action_variables[0] != 'feed' )
		return false;

	$wp_query->is_404 = false;
	status_header( 200 );

	include_once( 'feeds/bp-links-mylinks-feed.php' );
	die;
}
add_action( 'bp_init', 'bp_links_action_personal_links_feed', 6 );

function bp_links_action_directory_feed() {
	global $bp, $wp_query;

	if ( !bp_is_links_component() || $bp->current_action != 'feed' )
		return false;

	$wp_query->is_404 = false;
	status_header( 200 );

	include_once( 'feeds/bp-links-feed.php' );
	die();
}
add_action( 'bp_init', 'bp_links_action_directory_feed', 6 );

/********************************************************************************
 * Activity & Notification Functions
 *
 * These functions handle the recording, deleting and formatting of activity and
 * notifications for the user and for this specific component.
 */

function bp_links_register_activity_actions() {
	global $bp;

	if ( !function_exists( 'bp_activity_set_action' ) )
		return false;

	bp_activity_set_action( bp_links_id(), BP_LINKS_ACTIVITY_ACTION_CREATE, __( 'Created a link', 'buddypress-links' ) );
	bp_activity_set_action( bp_links_id(), BP_LINKS_ACTIVITY_ACTION_VOTE, __( 'Voted on a link', 'buddypress-links' ) );
	bp_activity_set_action( bp_links_id(), BP_LINKS_ACTIVITY_ACTION_COMMENT, __( 'Commented on a link', 'buddypress-links' ) );

	do_action( 'bp_links_register_activity_actions' );
}
add_action( 'bp_register_activity_actions', 'bp_links_register_activity_actions' );

function bp_links_record_activity( $args = '' ) {
	global $bp;

	if ( !function_exists( 'bp_activity_add' ) )
		return false;

	/* If the link is not public, hide the activity sitewide. */
	if ( BP_Links_Link::STATUS_PUBLIC == $bp->links->current_link->status )
		$hide_sitewide = false;
	else
		$hide_sitewide = true;

	$defaults = array(
		'id' => false,
		'user_id' => $bp->loggedin_user->id,
		'action' => '',
		'content' => '',
		'primary_link' => '',
		'component' => bp_links_id(),
		'type' => false,
		'item_id' => false,
		'secondary_item_id' => false,
		'recorded_time' => gmdate( "Y-m-d H:i:s" ),
		'hide_sitewide' => $hide_sitewide
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	return bp_activity_add( array( 'id' => $id, 'user_id' => $user_id, 'action' => $action, 'content' => $content, 'primary_link' => $primary_link, 'component' => $component, 'type' => $type, 'item_id' => $item_id, 'secondary_item_id' => $secondary_item_id, 'recorded_time' => $recorded_time, 'hide_sitewide' => $hide_sitewide ) );
}

function bp_links_update_last_activity( $link_id ) {
	bp_links_update_linkmeta( $link_id, 'last_activity', time() );
}
add_action( 'bp_links_created_link', 'bp_links_update_last_activity' );

function bp_links_format_notifications( $action, $item_id, $secondary_item_id, $total_items ) {
	global $bp;
	
	switch ( $action ) {
		case 'link_example_notification':
			$link_id = $secondary_item_id;
			$requesting_user_id = $item_id;

			$link = new BP_Links_Link( $link_id, false, false );
			
			$link_link = bp_get_link_permalink( $link );

			if ( (int)$total_items > 1 ) {
				return apply_filters( 'bp_links_multiple_example_notification', '<a href="' . $link_link . '/admin/example-slug/" title="' . __( 'Link Example Event', 'buddypress-links' ) . '">' . sprintf( __('%d number of example events happened for the link "%s"', 'buddypress-links' ), (int)$total_items, $link->name ) . '</a>', $link_link, $total_items, $link->name );
			} else {
				$user_fullname = bp_core_get_user_displayname( $requesting_user_id );
				return apply_filters( 'bp_links_single_example_notification', '<a href="' . $link_link . '/admin/example-slug/" title="' . $user_fullname .' did something">' . sprintf( __('%1$s triggered a notification for the link "%2$s"', 'buddypress-links' ), $user_fullname, $link->name ) . '</a>', $link_link, $user_fullname, $link->name );
			}	
		break;
	}

	do_action( 'bp_links_format_notifications', $action, $item_id, $secondary_item_id, $total_items );
	
	return false;
}


/********************************************************************************
 * Business Functions
 *
 * Business functions are where all the magic happens in BuddyPress. They will
 * handle the actual saving or manipulation of information. Usually they will
 * hand off to a database class for data access, then return
 * true or false on success or failure.
 */

/*** Link Creation, Editing & Deletion *****************************************/

function bp_links_manage_link( $args = '' ) {
	global $bp;

	// init possible args
	$link_id = null;
	$user_id = null;
	$category_id = null;
	$url = null;
	$target = null;
	$rel = null;
	$name = null;
	$description = null;
	$status = null;
	$embed_data = null;
	$embed_thidx = null;

	extract( $args );

	if ( $link_id ) {
		$link = new BP_Links_Link( $link_id );
	} else {
		$link = new BP_Links_Link();
	}
	
	if ( $user_id ) {
		$link->user_id = $user_id;
	} else {
		if ( empty( $link->id ) ) {
			$link->user_id = $bp->loggedin_user->id;
		}
	}
	
	if ( empty( $link->id ) ) {
		$link->slug = bp_links_check_slug( sanitize_title( $name ) );
	}

	if ( bp_links_is_valid_status( $status ) ) {
		$link->status = $status;
	}

	$link->category_id = $category_id;
	$link->url = $url;
	$link->target = $target;
	$link->rel = $rel;
	$link->name = $name;
	$link->description = $description;
	
	if ( !empty( $embed_data ) ) {
		try {
			// load service
			$service = BP_Links_Embed::LoadService( $embed_data );
			// try to attach embed service to link
			if ( $service instanceof BP_Links_Embed_Service ) {
				// handle selectable image
				if ( $service instanceof BP_Links_Embed_Has_Selectable_Image ) {
					if ( isset( $embed_thidx ) ) {
						$service->image_set_selected( $embed_thidx );
					}
				}
				// attach and enable service
				$link->embed_attach( $service );
				$link->embed_status_set_enabled();
			}
		} catch ( BP_Links_Embed_Exception $e ) {
			// epic failure
			return false;
		}
	}

	if ( $link->save() ) {

		// successful save event
		do_action( 'bp_links_manage_link_save_success', $link, $args );

		// all done
		return $link;

	} else {

		// unsuccessful save event
		do_action( 'bp_links_manage_link_save_failure', $link, $args );

	}
	
	return false;
}

function bp_links_delete_link( $link_id ) {
	global $bp;
	
	// Check the user is the link admin.
	if ( !$bp->is_item_admin && !is_super_admin())
		return false;
	
	// Get the link object
	$link = new BP_Links_Link( $link_id );
	
	if ( !$link->delete() )
		return false;

	/* Delete all link activity from activity streams */
	if ( function_exists( 'bp_activity_delete_by_item_id' ) ) {
		bp_activity_delete_by_item_id( array( 'item_id' => $link->cloud_id, 'component_name' => bp_links_id() ) );
	}	
 
	// Remove all notifications for any user belonging to this link
	bp_core_delete_all_notifications_by_type( $link_id, bp_links_id() );
	
	do_action( 'bp_links_delete_link', $link_id );
	
	return true;
}

function bp_links_create_component_id() {

	$component_id = false;

	// check current component
	switch ( bp_current_component() ) {
		// Are we at domain.org/links/create ???
		case bp_links_id():
		case bp_links_slug():
			if ( 'create' == bp_current_action() ) {
				$component_id = bp_links_id();
			}
			break;
	}

	return apply_filters( 'bp_links_create_component_id', $component_id );
}

function bp_links_admin_current_action_variable() {
	global $bp;

	if ( bp_is_links_component() && 'admin' == $bp->current_action ) {
		return $bp->action_variables[0];
	} else {
		return false;
	}
}

function bp_links_is_link_admin_page() {
	global $bp;

	if ( $bp->is_single_item && bp_links_admin_current_action_variable() )
		return true;

	return false;
}

function bp_links_is_link_visibile( $link_id_or_obj, $user_id = null ) {
	global $bp;

	// owners and site admins can always see the link
	if ( $bp->is_item_admin ) {
		return true;
	}

	if ( $link_id_or_obj instanceof BP_Links_Link ) {
		$link = $link_id_or_obj;
	} else {
		$link = new BP_Links_Link( $link_id_or_obj );
	}

	if ( empty( $user_id ) && is_user_logged_in() ) {
		$user_id = $bp->loggedin_user->id;
	}

	// who else can see this link?
	// check friendship last because of DB hit
	switch ( $link->status ) {
		case BP_Links_Link::STATUS_PUBLIC:
			return true;
		case BP_Links_Link::STATUS_HIDDEN:
			return false;
		case BP_Links_Link::STATUS_FRIENDS:
			return ( $user_id && bp_links_is_friends_enabled() ) ? friends_check_friendship( $user_id, $link->user_id ) : false;
		default:
			return false;
	}
}

function bp_links_is_valid_status( $status ) {
	return BP_Links_Link::is_valid_status( $status );
}

function bp_links_check_slug( $slug ) {
	global $bp;

	if ( 'wp' == substr( $slug, 0, 2 ) )
		$slug = substr( $slug, 2, strlen( $slug ) - 2 );
			
	if ( in_array( $slug, (array)$bp->links->forbidden_names ) ) {
		$slug = $slug . '-' . rand();
	}
	
	if ( BP_Links_Link::check_slug( $slug ) ) {
		do {
			$slug = $slug . '-' . rand();
		}
		while ( BP_Links_Link::check_slug( $slug ) );
	}
	
	return $slug;
}

function bp_links_get_slug( $link_id ) {
	$link = new BP_Links_Link( $link_id, false, false );
	return $link->slug;
}

function bp_links_get_last_updated() {
	return apply_filters( 'bp_links_get_last_updated', BP_Links_Link::get_last_updated() );
}

function bp_links_current_link() {
	global $bp;

	return $bp->links->current_link;
}

function bp_links_current_link_exists() {
	global $bp;

	return ( $bp->links->current_link instanceof BP_Links_Link );
}

function bp_links_current_link_embed_enabled() {
	global $bp;

	if ( bp_links_current_link_exists() ) {
		return ( $bp->links->current_link->embed_status_enabled() );
	} else {
		return false;
	}
}

function bp_links_current_link_embed_service() {
	global $bp;

	if ( bp_links_current_link_embed_enabled() ) {
		return $bp->links->current_link->embed();
	} else {
		return false;
	}
}

/*** General Link Functions ***************************************************/

function bp_links_is_url_valid( $url ) {
	return ( apply_filters( 'bp_links_is_url_valid', ( preg_match('/^https?:\/\/(([a-z0-9-]+\.)+[a-z]{2,6}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(:[0-9]+)?(\/?|\/\S+)$/iu', $url ) === 1 ) ? true : false, $url ) );
}

function bp_links_check_link_exists( $link_id ) {
	return BP_Links_Link::link_exists( $link_id );
}

/*** Link Fetching, Filtering & Searching  *************************************/

function bp_links_get_all( $args ) {
	return BP_Links_Link::get_all( $args );
}

function bp_links_get_active( $args ) {
	return BP_Links_Link::get_active( $args );
}

function bp_links_get_newest( $args ) {
	return BP_Links_Link::get_newest( $args );
}

function bp_links_get_search( $args ) {
	return BP_Links_Link::get_search( $args );
}

function bp_links_get_popular( $args ) {
	return BP_Links_Link::get_popular( $args );
}

function bp_links_get_most_votes( $args ) {
	return BP_Links_Link::get_most_votes( $args );
}

function bp_links_get_high_votes( $args ) {
	return BP_Links_Link::get_high_votes( $args );
}

function bp_links_get_random() {
	return BP_Links_Link::get_random( array( 'per_page' => 1, 'page' => 1 ) );
}

function bp_links_total_links() {
	return BP_Links_Link::get_total_link_count();
}

function bp_links_total_links_for_user( $user_id = false ) {
	global $bp;
	
	if ( !$user_id )
		$user_id = ( $bp->displayed_user->id ) ? $bp->displayed_user->id : $bp->loggedin_user->id;

	return BP_Links_Link::get_total_link_count_for_user( $user_id );
}

function bp_links_recent_activity_item_ids_for_user( $user_id = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = ( $bp->displayed_user->id ) ? $bp->displayed_user->id : $bp->loggedin_user->id;

	return BP_Links_Link::get_activity_recent_ids_for_user( $user_id );
}

/*** Link Avatars *************************************************************/

function bp_links_default_avatar_uri() {
	return apply_filters( 'bp_links_default_avatar_uri', BP_LINKS_THEME_URL_INC . '/images/default_avatar.png' );
}

function bp_links_check_avatar( $item_id ) {

	$params = array(
		'item_id' => $item_id,
		'object' => 'link',
		'avatar_dir' => 'link-avatars',
		'no_grav' => true
	);

	$avatar = bp_core_fetch_avatar( $params );

	return ( empty( $avatar ) ) ? false : true;
}

function bp_links_fetch_avatar( $args = '', $link = false ) {

	$defaults = array(
		'item_id' => false,
		'type' => 'full',
		'width' => false,
		'height' => false,
		'class' => 'avatar',
		'css_id' => false,
		'alt' => __( 'Link Avatar', 'buddypress-links' )
	);

	$params = wp_parse_args( $args, $defaults );

	// hard code these options to prevent tampering
	// DO NOT try to use a gravatar, ever!
	$params['object'] = 'link';
	$params['avatar_dir'] = 'link-avatars';
	$params['no_grav'] = true;

	// try to grab avatar file
	$avatar = bp_core_fetch_avatar( $params );

	if ( !empty( $avatar ) ) {

		// found an avatar file, return html for it
		return $avatar;
		
	} else {
		
		extract( $params, EXTR_SKIP );

		$avatar_url = null;

		// check if we can use thumb from embedded content
		if ( !empty( $link ) && $link->embed_status_enabled() ) {

			$image_thumb_url = $link->embed()->image_thumb_url();

			if ( !empty( $image_thumb_url ) ) {
				
				// append class avatar-embed
				$class .= ' avatar-embed';

				// check for additional avatar class
				if ( $link->embed()->avatar_class() ) {
					$class .= ' ' . $link->embed()->avatar_class();
				}

				// when avatar type is 'full', check for avatar size limits and special class
				if ( 'full' == $type ) {
					// get large thumb url from service object
					$avatar_url = $link->embed()->image_large_thumb_url();

					// check for custom width and height
					if ( $link->embed()->avatar_max_width() && $link->embed()->avatar_max_height() ) {
						$width = $link->embed()->avatar_max_width();
						$height = $link->embed()->avatar_max_height();
					}
				} else {
					// get standard thumb url from service object
					$avatar_url = $link->embed()->image_thumb_url();
				}
			}
		}

		// have an avatar file yet?
		if ( empty( $avatar_url ) ) {

			// no avatar file found, use the default image
			$avatar_url = bp_links_default_avatar_uri();
			
			// default width/height
			if ( empty( $width ) )
				$width = ( 'thumb' == $type ) ? BP_AVATAR_THUMB_WIDTH : BP_AVATAR_FULL_WIDTH;
			if ( empty( $height ) )
				$height = ( 'thumb' == $type ) ? BP_AVATAR_THUMB_HEIGHT : BP_AVATAR_FULL_HEIGHT;
				
		}

		if ( !$css_id )
			$css_id = $object . '-' . $item_id . '-avatar';

		if ( $width )
			$attr_width = " width='{$width}'";

		if ( $height )
			$attr_height = " height='{$height}'";
	
		return apply_filters( 'bp_links_fetch_avatar_not_found', sprintf( '<img src="%s" alt="%s" id="%s" class="%s"%s%s />', $avatar_url, $alt, $css_id, $class, $attr_width, $attr_height ), $args );
	}
}

function bp_links_fetch_avatar_no_default( $string, $params  )
{
	if ( $params['object'] == 'link' && $params['no_grav'] && strpos( $string, 'mystery-man' ) ) {
		return false;
	} else {
		return $string;
	}
}
add_action( 'bp_core_fetch_avatar', 'bp_links_fetch_avatar_no_default', 10, 2 );

function bp_links_avatar_upload_dir( $link_id = false ) {
	global $bp;

	if ( !$link_id )
		$link_id = $bp->links->current_link->id;

	$subdir = '/link-avatars/' . $link_id;
	$path = bp_core_avatar_upload_path() . $subdir;
	$url = str_replace( bp_core_avatar_upload_path(), bp_core_avatar_url(), $path );

	if ( !file_exists( $path ) )
		@wp_mkdir_p( $path );

	return apply_filters( 'bp_links_avatar_upload_dir', array( 'path' => $path, 'url' => $url, 'subdir' => $subdir, 'basedir' => $path, 'baseurl' => $url, 'error' => false ) );
}

/*** Link Activity Posting **************************************************/

function bp_links_post_update( $args = '' ) {
	global $bp;

	$defaults = array(
		'type' => BP_LINKS_ACTIVITY_ACTION_COMMENT,
		'content' => false,
		'user_id' => $bp->loggedin_user->id,
		'link_id' => $bp->links->current_link->id
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( empty($content) || empty($user_id) || empty($link_id) )
		return false;

	$bp->links->current_link = new BP_Links_Link( $link_id );

	/* Record this in activity streams */
	$activity_action = sprintf( __( '%s posted a comment on the link %s:', 'buddypress-links'), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_link_permalink( $bp->links->current_link ) . '">' . attribute_escape( $bp->links->current_link->name ) . '</a>' );

	$activity_id = bp_links_record_activity( array(
		'user_id' => $user_id,
		'action' => apply_filters( 'bp_links_activity_new_update_action', $activity_action ),
		'content' => apply_filters( 'bp_links_activity_new_update_content', $content ),
		'type' => $type,
		'item_id' => $bp->links->current_link->cloud_id
	) );

 	/* Require the notifications code so email notifications can be set on the 'bp_activity_posted_update' action. */
	require_once( BP_LINKS_PLUGIN_DIR . '/bp-links-notifications.php' );

	do_action( 'bp_links_posted_update', $content, $user_id, $link_id, $activity_id );

	return $activity_id;
}

/*** Link Voting ***/

/**
 * Cast a user vote for a link
 * 
 * Returns a BP_Links_Link object on successful vote
 * in case you need immediate access to the link data
 *
 * @see BP_Links_Link
 * @param integer $link_id
 * @param string $up_or_down "up" or "down"
 * @return BP_Links_Link|false
 */
function bp_links_cast_vote( $link_id, $up_or_down ) {
	global $bp;

	$bp->links->current_link = new BP_Links_Link( $link_id, true );

	if ( false === bp_links_is_link_visibile( $bp->links->current_link ) ) {
		return false;
	}

	$vote = $bp->links->current_link->vote();

	if ( !$vote instanceof BP_Links_Vote ) {
		return false;
	}

	// determine if member has voted for this link already
	$is_first_vote = ( is_numeric( $vote->vote ) ) ? false : true;

	// the default behavior is to allow members to change their vote,
	// which can be overriden with the configuration constant you see passed
	// to the filter below. use this filter to override the `configured` behavior
	// for special circumstances. you must return a boolean value!
	$allow_change = (boolean) apply_filters( 'bp_links_cast_vote_allow_change', (boolean) BP_LINKS_VOTE_ALLOW_CHANGE, $vote );

	// member can vote if its first time, or they are allowed to change vote
	if ( $is_first_vote || $allow_change ) {
		
		// the default behavior is to record vote activity.
		// this can be overriden with the configuration constant you see below.
		if ( (boolean) BP_LINKS_VOTE_RECORD_ACTIVITY === true ) {
			// the default behavior is to only record activity if this is their
			// original vote. use the filter below to override this behavior.
			// you must return a boolean value!
			$record_activity = (boolean) apply_filters( 'bp_links_cast_vote_record_activity', $is_first_vote, $vote );
		} else {
			// do not record activity per configuration constant
			$record_activity = false;
		}

		switch ( $up_or_down ) {
			case 'up':
				$vote->up();
				break;
			case 'down':
				$vote->down();
				break;
			default:
				return false;
		}

		if ( true === $bp->links->current_link->save() ) {

			if ( $record_activity ) {

				// translate up or down string
				$up_or_down_translated = ( 'up' == $up_or_down ) ? __( 'up', 'buddypress-links') : __( 'down', 'buddypress-links');

				// record the activity
				$activity_action = sprintf( __( '%1$s voted %2$s the link %3$s', 'buddypress-links'), bp_core_get_userlink( $bp->loggedin_user->id ), $up_or_down_translated, '<a href="' . bp_get_link_permalink( $bp->links->current_link ) . '">' . attribute_escape( $bp->links->current_link->name ) . '</a>' );

				bp_links_record_activity( array(
					'action' => apply_filters( 'bp_links_activity_voted', $activity_action ),
					'primary_link' => apply_filters( 'bp_links_activity_voted_primary_link', bp_get_link_permalink( $bp->links->current_link ) ),
					'type' => BP_LINKS_ACTIVITY_ACTION_VOTE,
					'item_id' => $bp->links->current_link->cloud_id
				) );

			}

			do_action( 'bp_links_cast_vote_success', $bp->links->current_link->id );

			// return the link object
			return $bp->links->current_link;
		} else {
			return false;
		}
	} else {
		// member not allowed change vote
		// this is not an error, so return true!
		return true;
	}
}

function bp_links_recalculate_popularity_for_all() {
	return BP_Links_Link::popularity_recalculate_all();
}
add_action( 'bp_links_cron_popularity', 'bp_links_recalculate_popularity_for_all', 1 );


/*** Link Meta Function ****************************************************/

function bp_links_delete_linkmeta( $link_id, $meta_key = false, $meta_value = false ) {
	global $wpdb, $bp;
	
	if ( !is_numeric( $link_id ) )
		return false;
		
	$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

	if ( is_array($meta_value) || is_object($meta_value) )
		$meta_value = serialize($meta_value);

	$meta_value = trim( $meta_value );

	if ( !$meta_key ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d", $link_id ) );
	} else if ( $meta_value ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d AND meta_key = %s AND meta_value = %s", $link_id, $meta_key, $meta_value ) );
	} else {
		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d AND meta_key = %s", $link_id, $meta_key ) );
	}
	
	wp_cache_delete( 'bp_links_linkmeta_' . $link_id . '_' . $meta_key, 'bp' );

	return true;
}

function bp_links_get_linkmeta( $link_id, $meta_key = '') {
	global $wpdb, $bp;
	
	$link_id = (int) $link_id;

	if ( !$link_id )
		return false;

	if ( !empty($meta_key) ) {
		$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

		if ( !$metas = wp_cache_get( 'bp_links_linkmeta_' . $link_id . '_' . $meta_key, 'bp' ) ) {
			$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d AND meta_key = %s", $link_id, $meta_key) );
			wp_cache_set( 'bp_links_linkmeta_' . $link_id . '_' . $meta_key, $metas, 'bp' );
		}
	} else {
		$metas = $wpdb->get_col( $wpdb->prepare("SELECT meta_value FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d", $link_id) );
	}

	if ( empty($metas) ) {
		if ( empty($meta_key) )
			return array();
		else
			return '';
	}

	if ( is_array( $metas ) )
		$metas = array_map('maybe_unserialize', $metas);

	if ( 1 == count($metas) )
		return $metas[0];
	else
		return $metas;
}

function bp_links_update_linkmeta( $link_id, $meta_key, $meta_value ) {
	global $wpdb, $bp;
	
	if ( !is_numeric( $link_id ) )
		return false;

	$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

	if ( is_string($meta_value) )
		$meta_value = stripslashes($wpdb->escape($meta_value));

	$meta_value = maybe_serialize($meta_value);

	if (empty($meta_value)) {
		return bp_links_delete_linkmeta( $link_id, $meta_key );
	}

	$cur = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $bp->links->table_name_linkmeta . " WHERE link_id = %d AND meta_key = %s", $link_id, $meta_key ) );

	if ( !$cur ) {
		$wpdb->query( $wpdb->prepare( "INSERT INTO " . $bp->links->table_name_linkmeta . " ( link_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $link_id, $meta_key, $meta_value ) );
	} else if ( $cur->meta_value != $meta_value ) {
		$wpdb->query( $wpdb->prepare( "UPDATE " . $bp->links->table_name_linkmeta . " SET meta_value = %s WHERE link_id = %d AND meta_key = %s", $meta_value, $link_id, $meta_key ) );
	} else {
		return false;
	}

	/* Update the cached object and recache */
	wp_cache_set( 'bp_links_linkmeta_' . $link_id . '_' . $meta_key, $meta_value, 'bp' );

	return true;
}

/*** Link Cleanup Functions ****************************************************/

/**
 * Reset embed fields if avatar is deleted
 *
 * @param array $args
 * @return boolean
 */
function bp_links_delete_existing_avatar( $args ) {
	if ( 'link' == $args['object'] ) {
		$link = new BP_Links_Link( $args['item_id'] );
		if ( $link->embed_status_enabled() && $link->embed()->avatar_only() === true ) {
			return $link->embed_remove(true);
		}
	}
	return true;
}
add_action( 'bp_core_delete_existing_avatar', 'bp_links_delete_existing_avatar' );

function bp_links_remove_data_for_user( $user_id ) {
	// remove all links for deleted user
	BP_Links_Link::delete_all_for_user($user_id);

	do_action( 'bp_links_remove_data_for_user', $user_id );
}
add_action( 'wpmu_delete_user', 'bp_links_remove_data_for_user', 1 );
add_action( 'delete_user', 'bp_links_remove_data_for_user', 1 );
add_action( 'make_spam_user', 'bp_links_remove_data_for_user', 1 );

function bp_links_clear_link_object_cache( $link_id ) {
	wp_cache_delete( 'bp_links_link_nouserdata_' . $link_id, 'bp' );
	wp_cache_delete( 'bp_links_link_' . $link_id, 'bp' );
}

// List actions to clear object caches on
add_action( 'bp_links_link_deleted', 'bp_links_clear_link_object_cache' );
add_action( 'bp_links_settings_updated', 'bp_links_clear_link_object_cache' );
add_action( 'bp_links_details_updated', 'bp_links_clear_link_object_cache' );
add_action( 'bp_links_link_avatar_updated', 'bp_links_clear_link_object_cache' );
add_action( 'bp_links_cast_vote_success', 'bp_links_clear_link_object_cache' );

// List actions to clear super cached pages on, if super cache is installed
add_action( 'bp_links_details_updated', 'bp_core_clear_cache' );
add_action( 'bp_links_settings_updated', 'bp_core_clear_cache' );
add_action( 'bp_links_create_complete', 'bp_core_clear_cache' );
add_action( 'bp_links_created_link', 'bp_core_clear_cache' );
add_action( 'bp_links_link_avatar_updated', 'bp_core_clear_cache' );
add_action( 'bp_links_cast_vote_success', 'bp_core_clear_cache' );

// this action should always be called after all other code
do_action( 'bp_links_core_includes' );

?>
