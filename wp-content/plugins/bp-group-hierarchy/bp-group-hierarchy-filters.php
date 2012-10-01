<?php

add_filter( 'bp_optional_components', 'bp_group_hierarchy_overload_groups' );
add_filter( 'bp_current_action', 'group_hierarchy_override_current_action' );
add_filter( 'bp_has_groups', 'bp_group_hierarchy_override_template', 10, 2 );
add_filter( 'bp_get_group_permalink', 'bp_group_hierarchy_fixup_permalink' );
add_filter( 'bp_forums_get_forum_topics', 'bp_group_hierarchy_fixup_forum_paths', 10, 2 );
add_filter( 'bp_has_topic_posts', 'bp_group_hierarchy_fixup_forum_links', 10, 2 );

/**
 * Catch requests for the groups component and find the requested group
 */
function group_hierarchy_override_current_action( $current_action ) {
	global $bp;
	
	do_action( 'bp_group_hierarchy_route_requests' );

	/** Only process once - hopefully this won't have any side effects */
	remove_action( 'bp_current_action', 'group_hierarchy_override_current_action' );
	
	/** Abort processing on dashboard pages and when not in groups component */
	if( is_admin() && ! strpos( admin_url('admin-ajax.php'), $_SERVER['REQUEST_URI'] ) ) {
		return $current_action;
	}
	
	if( ! bp_is_groups_component() ) {
		return $current_action;
	}
	
	$groups_slug = bp_get_groups_root_slug();

	bp_group_hierarchy_debug('Routing requests for BP 1.5');
	bp_group_hierarchy_debug('Current component: ' . $bp->current_component);
	bp_group_hierarchy_debug('Current action: ' . $current_action);
	bp_group_hierarchy_debug('Groups slug: ' . $groups_slug);
	bp_group_hierarchy_debug('Are we on a user profile page?: ' . ( empty($bp->displayed_user->id) ? 'N' : 'Y' ));

	if($current_action == '')	return $current_action;
	
	if( ! empty($bp->displayed_user->id) || in_array($current_action, apply_filters( 'groups_forbidden_names', array( 'my-groups', 'create', 'invites', 'send-invites', 'forum', 'delete', 'add', 'admin', 'request-membership', 'members', 'settings', 'avatar', $groups_slug, '' ) ) ) ) {
		bp_group_hierarchy_debug('Not rewriting current action.');
		return $current_action;
	}
	
	$action_vars = $bp->action_variables;

	$group = new BP_Groups_Hierarchy( $current_action );

	if( ! $group->id && ( ! isset( $bp->current_item ) || ! $bp->current_item ) ) {
		$current_action = '';
		bp_group_hierarchy_debug('Redirecting to groups root.');
		bp_core_redirect( $bp->root_domain . '/' . $groups_slug . '/');
	}

	if( $group->has_children() ) {
		$parent_id = $group->id;
		foreach($bp->action_variables as $action_var) {
			$subgroup_id = BP_Groups_Hierarchy::check_slug($action_var, $parent_id);
			if($subgroup_id) {
				$action_var = array_shift($action_vars);
				$current_action .= '/' . $action_var;
				$parent_id = $subgroup_id;
			} else {
				// once we find something that isn't a group, we're done
				break;
			}
		}
	}

	bp_group_hierarchy_debug('Action changed to: ' . $current_action);

	$bp->action_variables = $action_vars;
	$bp->current_action = $current_action;
	
	return $current_action;
}


/**
 *	Override group retrieval for global $groups_template,
 *	replacing every BP_Groups_Group with a BP_Groups_Hierarchy object
 *  @return int|bool number of matching groups or FALSE if none
 */
function bp_group_hierarchy_override_template($has_groups) {
	
	global $bp, $groups_template;

	if(!$has_groups)	return false;
	
	$groups_hierarchy_template = new BP_Groups_Hierarchy_Template();

	bp_group_hierarchy_copy_vars(
		$groups_template,
		$groups_hierarchy_template, 
		array(
			'group',
			'group_count',
			'groups',
			'single_group',
			'total_group_count',
			'pag_links',
			'pag_num',
			'pag_page'
		)
	);

	$groups_hierarchy_template->synchronize();

	foreach($groups_hierarchy_template->groups as $key => $group) {
		if(isset($group->id)) {
			$groups_hierarchy_template->groups[$key] = new BP_Groups_Hierarchy($group->id);
		}
	}
	$groups_template = $groups_hierarchy_template;
	
	return $has_groups;
}


/**
 * Fix forum topic permalinks for subgroups
 */
function bp_group_hierarchy_fixup_forum_paths( $topics ) {
	
	// replace each simple slug with its full path
	if(is_array($topics)) {
		foreach($topics as $key => $topic) {
	
			$group_id = BP_Groups_Group::group_exists($topic->object_slug);
			if($group_id) {
				$topics[$key]->object_slug = BP_Groups_Hierarchy::get_path( $group_id );
			}
		}
	}
	return $topics;
	
}

/**
 * Fix forum topic action links (Edit, Delete, Close, Sticky, etc.)
 */
function bp_group_hierarchy_fixup_forum_links( $has_topics ) {
	global $forum_template;
	
	$group_id = BP_Groups_Group::group_exists( $forum_template->topic->object_slug );
	$forum_template->topic->object_slug = BP_Groups_Hierarchy::get_path( $group_id );
	
	return $has_topics;
	
}

/**
 * Override the group slug in permalinks with a group's full path
 */
function bp_group_hierarchy_fixup_permalink( $permalink ) {
	
	global $bp;
	
	$group_slug = substr( $permalink, strlen( $bp->root_domain . '/' . bp_get_groups_root_slug() . '/' ), -1 );
	
	if(strpos($group_slug,'/'))	return $permalink;
	
	$group_id = BP_Groups_Group::get_id_from_slug( $group_slug );
	
	if( !is_null($group_id) ) {
		$group_path = BP_Groups_Hierarchy::get_path( $group_id );
		return str_replace( '/' . $group_slug . '/', '/' . $group_path . '/', $permalink );
	}
	return $permalink;
	
}


/**
 * Load the normal BP_Groups_Component, then quickly replace it with the derived class and prevent re-loading
 * This loads the Groups component out of order, but testing has revealed no issues
 */
function bp_group_hierarchy_overload_groups( $components ) {
	
	if( is_admin() && ! strpos( admin_url('admin-ajax.php'), $_SERVER['REQUEST_URI'] ) )	return $components;
	
	global $bp;

	$components = array_flip( $components );

	if( array_key_exists( 'groups', $components ) ) {

		include_once( BP_PLUGIN_DIR . '/bp-groups/bp-groups-loader.php' );

		// BP 1.6
		if( has_action( 'bp_setup_components') ) {
			
			remove_action( 'bp_setup_components', 'bp_setup_groups', 6);
			add_action( 'bp_setup_components', 'bp_setup_groups_hierarchy', 6);
	
			include_once dirname(__FILE__) . '/bp-group-hierarchy-loader.php';
			
		} else {

			include_once dirname(__FILE__) . '/bp-group-hierarchy-loader.php';

			/** Remove these actions while the $bp->groups reference is correct */
			remove_action( 'bp_setup_globals', array( $bp->groups, 'setup_globals' ));
			remove_action( 'bp_setup_nav', array( $bp->groups, 'setup_nav' ));
			remove_action( 'bp_setup_title', array( $bp->groups, 'setup_title' ));
			
			bp_setup_groups_hierarchy();
			
		}
		
	}

	unset($components['groups']);
	$components = array_flip( $components );
	
	return $components;
	
}
?>