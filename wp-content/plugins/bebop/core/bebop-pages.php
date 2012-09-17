<?php
//File used to load pages

/**
* User settings
*/
if ( isset( $_get['action'] ) ) {
	if ( $_GET['action'] == 'logout' ) {
	}
	else {
		bebop_setup_user_nav();
	}
}
else {
	bebop_setup_user_nav();
}

function bebop_setup_user_nav() {
	global $bp;
	
	//Shows in the profile all the time.
	bp_core_new_nav_item(
					array(
						'name' => 'Teaching Resources',
						'slug' => 'bebop-oers/home',
						'position' => 30,
						'show_for_displayed_user' => true,
						'screen_function' => 'bebop_user_settings',
						'default_subnav_slug' => 'home',
					)
	);

	bp_core_new_subnav_item(
					array(
						'name' => 'Home',
						'slug' => 'home',
						'parent_url' => bp_displayed_user_domain() . 'bebop-oers/',
						'parent_slug' => 'bebop-oers',
						'screen_function' => 'bebop_user_settings',
						'position' => 10,
					)
	);
	//only show if current user is the owner of the profile.
	if ( bp_is_my_profile() ) {
		bp_core_new_subnav_item(
								array(
								'name' => 'Accounts',
								'slug' => 'accounts',
								'parent_url' => $bp->loggedin_user->domain . 'bebop-oers/',
								'parent_slug' => 'bebop-oers',
								'screen_function' => 'bebop_user_settings',
								'position' => 20,
								)
		);
			
		bp_core_new_subnav_item(
						array(
							'name' => 'Resource Manager',
							'slug' => 'manager',
							'parent_url' => $bp->loggedin_user->domain . 'bebop-oers/',
							'parent_slug' => 'bebop-oers',
							'screen_function' => 'bebop_user_settings',
							'position' => 20,
						)
		);
	}
}

function bebop_user_settings() {
	bebop_extensions::user_page_loader( 'default', 'settings' );
}

function bebop_user_settings_screen_content() {
	global $bp;
	include WP_PLUGIN_DIR . '/bebop/core/templates/user/bebop-user-settings.php';
}

/*
 * Admin pages
 * 
 * */

function bebop_admin_menu() {
	if ( ! is_super_admin() ) {
		return false;
	}
	add_menu_page(
					__( 'Bebop Admin', 'bebop' ),
					__( 'Bebop', 'bebop' ),
					'manage_options',
					'bebop_admin', 
					'bebop_admin_pages',
					WP_PLUGIN_URL . '/bebop/core/resources/images/bebop_icon.png'
	);
	add_submenu_page( 'bebop_admin', 'Admin Main', 'Admin Main', 'manage_options', 'bebop_admin', 'bebop_admin_pages' );
	add_submenu_page( 'bebop_admin', 'General Settings', 'General Settings', 'manage_options', 'bebop_admin_settings', 'bebop_admin_pages' );
	add_submenu_page( 'bebop_admin', 'OER Providers', 'OER Providers', 'manage_options', 'bebop_oer_providers', 'bebop_admin_pages' );
	add_submenu_page( 'bebop_admin', 'OERs', 'OERs', 'manage_options', 'bebop_oers', 'bebop_admin_pages' );
	add_submenu_page( 'bebop_admin', 'Error Log', 'Error Log', 'manage_options', 'bebop_error_log', 'bebop_admin_pages' );
	add_submenu_page( 'bebop_admin', 'General Log', 'General Log', 'manage_options', 'bebop_general_log', 'bebop_admin_pages' );
	
}

//This deals with the bebop page loaders based on the link selected.
function bebop_admin_pages() {
	if ( $_GET['page'] == 'bebop_admin' ) {
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin.php';
	}
	else if ( $_GET['page'] == 'bebop_admin_settings' ) {
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin-settings.php';
	}
	else if ( $_GET['page'] == 'bebop_oer_providers' ) {
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-oer-providers.php';
	}
	else if ( $_GET['page'] == 'bebop_oers' ) {
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-oers.php';
	}
	else if ( $_GET['page'] == 'bebop_error_log' ) {
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-error-log.php';
	}
	else if ( $_GET['page'] == 'bebop_general_log' ) {
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-general-log.php';
	}
	else {
		echo '<div class="bebop_error_box"><b>Bebop Error:</b> "' . $_GET['page'] . '" page not found. Loaded home instead.</div>';
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin.php';
	}
}  
//add_action('admin_menu', 'bebop_admin_menu');
add_action( bp_core_admin_hook(), 'bebop_admin_menu' );

function bebop_admin_stylesheets() {
	wp_register_style( 'bebop-admin-styles', plugins_url() . '/bebop/core/resources/css/admin.css' );
	wp_enqueue_style( 'bebop-admin-styles' );
}
//wp_enqueue_scripts hooks is not available?
add_action( bp_core_admin_hook(), 'bebop_admin_stylesheets' );
