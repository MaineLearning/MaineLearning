<?php
/* This file contains all the actions required for Auto Group Join Plugin to work after Buddypress has been loaded. */

/**********
* Inititalize the admin panel by adding it to the 'admin_menu' action.
* - Does verify if function exists.
* - Returns if $autoGroupJoin object is not instantiated.
**********/
if (!function_exists("auto_group_join_admin_panel")) {
	function auto_group_join_admin_panel() {
		global $autoGroupJoin;
		if (!isset($autoGroupJoin)) {
			return;
		}
		
		// Add AutoGroupJoin's Admin Page to BuddyPress's Admin Page Menu
		add_submenu_page( 'bp-general-settings', __( 'Auto Group Join', 'bpAutoGroupJoin' ), __( 'Auto Group Join', 'bpAutoGroupJoin' ), 'manage_options', 'bp-auto-group-join', array(&$autoGroupJoin, 'display_admin_page') );
		
	}
	
	// Add AutoGroupJoin Admin Menu to Wordpress Action
	// * Correction submitted by Chestnut to resolve multi-site issues. Thank you.
	add_action(is_multisite() ? 'network_admin_menu' : 'admin_menu', 'auto_group_join_admin_panel');	
}


/**********
* Check for Auto Group Join required database fields.
* - If not present, add required fields.
* - Requires $wpdb, $current_user and $bp global variables.
**********/
if ( !function_exists('auto_group_join_check_installed') ) {
	function auto_group_join_check_installed() {	
		global $wpdb, $bp, $current_user;
	
		// Ensure current user has Administration Level Capabilities
		if ( current_user_can('manage_options') ) {
			
			// Check to see if our buddypress groups table contains auto-join column, if not add it.
			if ( !$wpdb->get_var("SHOW COLUMNS FROM {$bp->groups->table_name} LIKE 'auto_join'")  ) {
				$mysql = "ALTER TABLE {$bp->groups->table_name} ADD `auto_join` BINARY NOT NULL DEFAULT '0'";
				$wpdb->query($mysql);
			}
			
			// Check to see if our wordpress users table contains auto_join_complete column, if not add it.
			if ( !$wpdb->get_var("SHOW COLUMNS FROM {$wpdb->users} LIKE 'auto_join_complete'")  ) {
				$mysql = "ALTER TABLE {$wpdb->users} ADD `auto_join_complete` BINARY NOT NULL DEFAULT '0'";
				$wpdb->query($mysql);
			}
		}
	}

	// Add Auto Group Join Check Installed to Admin Menu
	// Adding it to admin menu, rather than plugin activation, because activation does not fire when auto-updating.
	// Also, want to ensure buddypress has completed loading for access to $bp.
	// * Correction submitted by Chestnut to resolve multi-site issues.
	add_action(is_multisite() ? 'network_admin_menu' : 'admin_menu', 'auto_group_join_check_installed');
}
	
	
/***********
* groups_are_active function
* - Returns true if BuddyPress Groups are active otherwise returns false.
* - Updated as of version 2.2.0 - thanks go to Jeremy Edmiston for the suggestion.
***********/
function groups_are_active() {
	$active_components = get_site_option('bp-active-components');
	if ( isset( $active_components['groups']) ) {
		return true;
	} else {
		return false;
	}
}


/**********
* update_auto_join_status function
* 
* Used to auto join new users after they register.
* - Requires $wpdb, $bp global variables.
* - Takes $user_id as an attribute.
**********/
if (!function_exists('update_auto_join_status')) {
	function update_auto_join_status($user_id) {
		global $wpdb, $bp;
		
		// get list of groups to auto-join.
		$group_list = $wpdb->get_results("SELECT * FROM {$bp->groups->table_name} WHERE auto_join = 1");
		foreach ($group_list as $auto_join_group) {
			groups_accept_invite( $user_id, $auto_join_group->id );
		}
		$wpdb->query("UPDATE {$wpdb->users} SET auto_join_complete = 1 WHERE ID = {$user_id}");
	}
	
	add_action( 'user_register', 'update_auto_join_status');
}
?>
