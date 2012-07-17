<?php

/**
 * Modifies the "action" string of external blog email notifications by adding quotation marks
 * around post titles, blog titles, and group names
 */
function mainelearning_external_blog_notification_action( $action, $activity ) {
	if ( 'exb' != $activity->type ) {
		return $action;
	}

	$action = preg_replace( '/external post (.*?) from the blog (.*?) in the group (.*?)$/', 'external post "$1" from the blog "$2" in the group "$3"', $action );

	return $action;
}
add_filter( 'bp_ass_activity_notification_action', 'mainelearning_external_blog_notification_action', 10, 2 );

/**
 * Modifies the "content" string of external blog email notifications by removing the 'View
 * external post' string from the end
 */
function mainelearning_external_blog_notification_content( $content, $activity ) {
	if ( 'exb' != $activity->type ) {
		return $content;
	}

	$content = str_replace( 'View external post', '', $content );

	return $content;
}
add_filter( 'bp_ass_activity_notification_content', 'mainelearning_external_blog_notification_content', 10, 2 );

/**
 * Don't load buddybar css in the Dashboard
 */
function mainelearning_dont_load_bp_css_in_dashboard() {
	if ( is_admin() || is_network_admin() ) {
		remove_action( 'bp_init', 'bp_core_load_buddybar_css' );
	}
}
add_action( 'bp_init', 'mainelearning_dont_load_bp_css_in_dashboard', 1 );

?>