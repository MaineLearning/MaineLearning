<?php

add_action( 'bp_init', 'bp_group_hierarchy_load_translations');

/** This function appears to work when loaded at bp_loaded, but more research is needed */
add_action( 'bp_loaded', 'bp_group_hierarchy_init' );

add_action( 'bp_loaded', 'bp_group_hierarchy_load_components' );
add_action( 'bp_setup_globals', 'bp_group_hierarchy_setup_globals' );
add_action( 'bp_groups_delete_group', 'bp_group_hierarchy_rescue_child_groups' );

/**
 * Set up global variables
 */
function bp_group_hierarchy_setup_globals() {
	global $bp, $wpdb;

	/* For internal identification */
	$bp->group_hierarchy = new stdClass();
	$bp->group_hierarchy->id = 'group_hierarchy';
	$bp->group_hierarchy->table_name = $wpdb->base_prefix . 'bp_group_hierarchy';
	$bp->group_hierarchy->slug = BP_GROUP_HIERARCHY_SLUG;
	
	/* Register this in the active components array */
	$bp->active_components[$bp->group_hierarchy->slug] = $bp->group_hierarchy->id;
	
	do_action( 'bp_group_hierarchy_globals_loaded' );
}

/**
 * Activate group extension
 */
function bp_group_hierarchy_init() {
	
	/** Enable logging with WP Debug Logger */
	$GLOBALS['wp_log_plugins'][] = 'bp_group_hierarchy';
	
	/** Ensure BP is loaded before loading admin portion */
	require ( dirname( __FILE__ ) . '/bp-group-hierarchy-admin.php' );
	require ( dirname( __FILE__ ) . '/extension.php' );
	
}

/**
 * Add hook for intercepting requests before they're routed by normal BP processes
 */
function bp_group_hierarchy_load_components() {

	require ( dirname( __FILE__ ) . '/bp-group-hierarchy-classes.php' );
	require ( dirname( __FILE__ ) . '/bp-group-hierarchy-template.php' );

	if( is_admin() && ! strpos( admin_url('admin-ajax.php'), $_SERVER['REQUEST_URI'] ) ) return;
	
	do_action( 'bp_group_hierarchy_components_loaded' );
}

function bp_group_hierarchy_load_translations() {
	/** load localization files if present */
	if( file_exists( dirname( __FILE__ ) . '/languages/' . dirname(plugin_basename(__FILE__)) . '-' . get_locale() . '.mo' ) ) {
		return load_plugin_textdomain( 'bp-group-hierarchy', false, dirname(plugin_basename(__FILE__)) . '/languages' );
	} else if ( file_exists( dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' ) ) {
		_doing_it_wrong( 'load_textdomain', 'Please rename your translation files to use the ' . dirname(plugin_basename(__FILE__)) . '-' . get_locale() . '.mo' . ' format', '1.2.7' );
		return load_textdomain( 'bp-group-hierarchy', dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' );
	}
	return false;
}

/**
 * Before deleting a group, move all its child groups to its immediate parent.
 */
function bp_group_hierarchy_rescue_child_groups( &$parent_group ) {

	$parent_group_id = $parent_group->id;

	if($child_groups = BP_Groups_Hierarchy::has_children( $parent_group_id )) {
		
		$group = new BP_Groups_Hierarchy($parent_group_id);
		if($group) {
			$new_parent_group_id = $group->parent_id;
		} else {
			$new_parent_group_id = 0;
		}
		
		foreach($child_groups as $group_id) {
			$child_group = new BP_Groups_Hierarchy($group_id);
			$child_group->parent_id = $new_parent_group_id;
			$child_group->save();
		}
	}
}

?>