<?php
/*
Plugin Name: External Group Blogs
Plugin URI: http://wordpress.org/extend/plugins/external-group-blogs/
Description: Allow group creators to supply external blog RSS feeds that will attach future posts on blogs to a group.
Version: 1.2.1
Requires at least: WordPress 2.9.1 / BuddyPress 1.2
Tested up to: WordPress 2.9.1 / BuddyPress 1.2
License: GNU/GPL 2
Author: Andy Peatling
Author URI: http://buddypress.org/developers/apeatling/
*/

/* Only load the plugin functions if BuddyPress is loaded and initialized. */
function bp_groupblogs_init() {
	require( dirname( __FILE__ ) . '/bp-groups-externalblogs.php' );
}
add_action( 'bp_init', 'bp_groupblogs_init' );

/* On activation register the cron to refresh external blog posts. */
function bp_groupblogs_activate() {
	wp_schedule_event( time(), 'hourly', 'bp_groupblogs_cron' );
}
register_activation_hook( __FILE__, 'bp_groupblogs_activate' );

/* On deacativation, clear the cron. */
function bp_groupblogs_deactivate() {
	wp_clear_scheduled_hook( 'bp_groupblogs_cron' );

	/* Remove all external blog activity */
	if ( function_exists( 'bp_activity_delete' ) )
		bp_activity_delete( array( 'type' => 'exb' ) );
}
register_deactivation_hook( __FILE__, 'bp_groupblogs_deactivate' );

?>