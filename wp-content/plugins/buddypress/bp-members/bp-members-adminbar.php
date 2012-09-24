<?php

/**
 * BuddyPress Members Toolbar
 *
 * Handles the member functions related to the WordPress Toolbar
 *
 * @package BuddyPress
 * @subpackage MembersAdminBar
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add the "My Account" menu and all submenus.
 *
 * @since BuddyPress (1.6)
 * @todo Deprecate WP 3.2 Toolbar compatibility when we drop 3.2 support
 */
function bp_members_admin_bar_my_account_menu() {
	global $bp, $wp_admin_bar;

	// Bail if this is an ajax request
	if ( defined( 'DOING_AJAX' ) )
		return;

	// Logged in user
	if ( is_user_logged_in() ) {

		// Stored in the global so we can add menus easily later on
		$bp->my_account_menu_id = 'my-account-buddypress';

		// Create the main 'My Account' menu
		$wp_admin_bar->add_menu( array(
			'id'     => $bp->my_account_menu_id,
			'group'  => true,
			'title'  => __( 'Edit My Profile', 'buddypress' ),
			'href'   => bp_loggedin_user_domain(),
			'meta'   => array(
			'class'  => 'ab-sub-secondary'
		) ) );

	// Show login and sign-up links
	} elseif ( !empty( $wp_admin_bar ) ) {

		add_filter ( 'show_admin_bar', '__return_true' );

		// Create the main 'My Account' menu
		$wp_admin_bar->add_menu( array(
			'id'    => 'bp-login',
			'title' => __( 'Log in', 'buddypress' ),
			'href'  => wp_login_url()
		) );

		// Sign up
		if ( bp_get_signup_allowed() ) {
			$wp_admin_bar->add_menu( array(
				'id'    => 'bp-register',
				'title' => __( 'Register', 'buddypress' ),
				'href'  => bp_get_signup_page()
			) );
		}
	}
}
add_action( 'bp_setup_admin_bar', 'bp_members_admin_bar_my_account_menu', 4 );

/**
 * Adds the User Admin top-level menu to user pages
 *
 * @package BuddyPress
 * @since BuddyPress (1.5)
 */
function bp_members_admin_bar_user_admin_menu() {
	global $bp, $wp_admin_bar;

	// Only show if viewing a user
	if ( !bp_is_user() )
		return false;

	// Don't show this menu to non site admins or if you're viewing your own profile
	if ( !current_user_can( 'edit_users' ) || bp_is_my_profile() )
		return false;

	// Unique ID for the 'My Account' menu
	$bp->user_admin_menu_id = 'user-admin';

	// Add the top-level User Admin button
	$wp_admin_bar->add_menu( array(
		'id'    => $bp->user_admin_menu_id,
		'title' => __( 'Edit Member', 'buddypress' ),
		'href'  => bp_displayed_user_domain()
	) );

	// User Admin > Edit this user's profile
	$wp_admin_bar->add_menu( array(
		'parent' => $bp->user_admin_menu_id,
		'id'     => $bp->user_admin_menu_id . '-edit-profile',
		'title'  => __( "Edit Profile", 'buddypress' ),
		'href'   => bp_get_members_component_link( 'profile', 'edit' )
	) );

	// User Admin > Edit this user's avatar
	$wp_admin_bar->add_menu( array(
		'parent' => $bp->user_admin_menu_id,
		'id'     => $bp->user_admin_menu_id . '-change-avatar',
		'title'  => __( "Edit Avatar", 'buddypress' ),
		'href'   => bp_get_members_component_link( 'profile', 'change-avatar' )
	) );

	// User Admin > Spam/unspam
	$wp_admin_bar->add_menu( array(
		'parent' => $bp->user_admin_menu_id,
		'id'     => $bp->user_admin_menu_id . '-user-capabilities',
		'title'  => __( 'User Capabilities', 'buddypress' ),
		'href'   => bp_displayed_user_domain() . 'settings/capabilities/'
	) );

	// User Admin > Delete Account
	$wp_admin_bar->add_menu( array(
		'parent' => $bp->user_admin_menu_id,
		'id'     => $bp->user_admin_menu_id . '-delete-user',
		'title'  => __( 'Delete Account', 'buddypress' ),
		'href'   => bp_displayed_user_domain() . 'settings/delete-account/'
	) );
}
add_action( 'admin_bar_menu', 'bp_members_admin_bar_user_admin_menu', 99 );

/**
 * Build the "Notifications" dropdown
 *
 * @package Buddypress
 * @since BuddyPress (1.5)
 */
function bp_members_admin_bar_notifications_menu() {
	global $wp_admin_bar;

	if ( !is_user_logged_in() )
		return false;

	$notifications = bp_core_get_notifications_for_user( bp_loggedin_user_id(), 'object' );
	$count         = !empty( $notifications ) ? count( $notifications ) : 0;
	$alert_class   = (int) $count > 0 ? 'pending-count alert' : 'count no-alert';
	$menu_title    = '<span id="ab-pending-notifications" class="' . $alert_class . '">' . $count . '</span>';

	// Add the top-level Notifications button
	$wp_admin_bar->add_menu( array(
		'parent'    => 'top-secondary',
		'id'        => 'bp-notifications',
		'title'     => $menu_title,
		'href'      => bp_loggedin_user_domain(),
	) );

	if ( !empty( $notifications ) ) {
		foreach ( (array) $notifications as $notification ) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'bp-notifications',
				'id'     => 'notification-' . $notification->id,
				'title'  => $notification->content,
				'href'   => $notification->href
			) );
		}
	} else {
		$wp_admin_bar->add_menu( array(
			'parent' => 'bp-notifications',
			'id'     => 'no-notifications',
			'title'  => __( 'No new notifications', 'buddypress' ),
			'href'   => bp_loggedin_user_domain()
		) );
	}

	return;
}
add_action( 'admin_bar_menu', 'bp_members_admin_bar_notifications_menu', 90 );

/**
 * Remove rogue WP core edit menu when viewing a single user
 *
 * @since BuddyPress (1.6)
 */
function bp_members_remove_edit_page_menu() {
	if ( bp_is_user() ) {
		remove_action( 'admin_bar_menu', 'wp_admin_bar_edit_menu', 80 );
	}
}
add_action( 'bp_init', 'bp_members_remove_edit_page_menu', 99 );

?>
