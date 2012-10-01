<?php
//File used to load pages
/**
* User settings
*/
if ( isset( $_get['action'] ) ) {
	if ( $_GET['action'] == 'logout' ) {
	}
	else {
		add_action( 'bp_setup_nav', 'bebop_setup_user_nav', 20 );
	}
}
else {
	add_action( 'bp_setup_nav', 'bebop_setup_user_nav', 20 );
}

function bebop_setup_user_nav() {
	global $bp;
	
	$should_users_verify_content = bebop_tables::get_option_value( 'bebop_content_user_verification' );
	
	//Shows in the profile all the time.
	bp_core_new_nav_item(
					array(
						'name' => __( 'Resources', 'bebop' ),
						'slug' => 'bebop',
						'position' => 30,
						'show_for_displayed_user' => true,
						'screen_function' => 'bebop_user_settings',
						'default_subnav_slug' => 'content',
					)
	);

	bp_core_new_subnav_item(
					array(
						'name' =>  __( 'Content', 'bebop' ),
						'slug' => 'content',
						'parent_url' => bp_displayed_user_domain() . 'bebop/',
						'parent_slug' => 'bebop',
						'screen_function' => 'bebop_user_settings',
						'position' => 10,
					)
	);
	//only show if current user is the owner of the profile.
	if ( bp_is_my_profile() ) {
		
		if ( $should_users_verify_content != 'no' ) {
			bp_core_new_subnav_item(
							array(
								'name' => __( 'Content Manager', 'bebop' ),
								'slug' => 'manager',
								'parent_url' => $bp->loggedin_user->domain . 'bebop/',
								'parent_slug' => 'bebop',
								'screen_function' => 'bebop_user_settings',
								'position' => 20,
							)
			);
		}
		
		bp_core_new_subnav_item(
								array(
								'name' => __( 'Accounts', 'bebop' ),
								'slug' => 'accounts',
								'parent_url' => $bp->loggedin_user->domain . 'bebop/',
								'parent_slug' => 'bebop',
								'screen_function' => 'bebop_user_settings',
								'position' => 20,
								)
		);
	}
}

function bebop_user_settings() {
	bebop_extensions::bebop_user_page_loader( 'default', 'settings' );
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
					WP_PLUGIN_URL . '/bebop/core/resources/images/bebop_icon.png',
					'101.191'
	);
	add_submenu_page( 'bebop_admin', 'Admin Main', 'Admin Main', 'manage_options', 'bebop_admin', 'bebop_admin_pages' );
	add_submenu_page( 'bebop_admin', 'General Settings', 'General Settings', 'manage_options', 'bebop_admin_settings', 'bebop_admin_pages' );
	add_submenu_page( 'bebop_admin', 'Content Providers', 'Content Providers', 'manage_options', 'bebop_providers', 'bebop_admin_pages' );
	add_submenu_page( 'bebop_admin', 'Content', 'Content', 'manage_options', 'bebop_content', 'bebop_admin_pages' );
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
	else if ( $_GET['page'] == 'bebop_providers' ) {
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-content-providers.php';
	}
	else if ( $_GET['page'] == 'bebop_content' ) {
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-content.php';
	}
	else if ( $_GET['page'] == 'bebop_error_log' ) {
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-error-log.php';
	}
	else if ( $_GET['page'] == 'bebop_general_log' ) {
		include WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-general-log.php';
	}
}
add_action( bp_core_admin_hook(), 'bebop_admin_menu' );

function bebop_admin_stylesheets() {
	wp_register_style( 'bebop-admin-styles', plugins_url() . '/bebop/core/resources/css/admin.css' );
	wp_enqueue_style( 'bebop-admin-styles' );
}
add_action( bp_core_admin_hook(), 'bebop_admin_stylesheets' );
