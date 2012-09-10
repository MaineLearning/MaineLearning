<?php
/************************************
 * Utility and replacement functions
 ***********************************/

function bp_group_hierarchy_copy_vars($from, &$to, $attribs) {
	foreach($attribs as $var) {
		if(isset($from->$var)) {
			$to->$var = $from->$var;
		}
	}
}

/**
 * Catch requests for groups by parent and use BP_Groups_Hierarchy::get_by_parent to handle
 */
function bp_group_hierarchy_get_by_hierarchy($args) {

	$defaults = array(
		'type' => 'active', // active, newest, alphabetical, random, popular, most-forum-topics or most-forum-posts
		'user_id' => false, // Pass a user_id to limit to only groups that this user is a member of
		'search_terms' => false, // Limit to groups that match these search terms

		'per_page' => 20, // The number of results to return per page
		'page' => 1, // The page to return if limiting per page
		'parent_id' => 0, //
		'populate_extras' => true, // Fetch meta such as is_banned and is_member
	);
	
	$params = wp_parse_args( $args, $defaults );
	
	extract( $params, EXTR_SKIP );
	
	if(isset($parent_id)) {
		$groups = BP_Groups_Hierarchy::get_by_parent( $parent_id, $type, $per_page, $page, $user_id, $search_terms, $populate_extras );
	}
	return $groups;
}

/**
 * Function for creating groups with parents programmatically
 * @param array Args same as groups_create_group, but accepts a 'parent_id' param
 */
function groups_hierarchy_create_group( $args = '' ) {
	if( $group_id = groups_create_group( $args ) ) {
		if( isset( $args['parent_id'] ) ) {
			$group = new BP_Group_Hierarchy( $group_id );
			$group->parent_id = (int)$args['parent_id'];
			$group->save();
		}
		return $group_id;
	}
	return false;
}

/** Alias for bp_get_groups_root_slug originally for BP 1.2 compat */
function bp_get_groups_hierarchy_root_slug() {
	_deprecated_function( __FUNCTION__, '1.3.2', 'bp_get_groups_root_slug' );
	return bp_get_groups_root_slug();

}

?>