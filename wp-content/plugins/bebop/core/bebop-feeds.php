<?php
/*
 * Bebop Feeds
 */
add_action( 'bp_actions', 'bebop_feeds' );

function bebop_feeds() {
	global $bp, $wp_query, $this_bp_feed;
	if ( bp_is_activity_component() && bp_displayed_user_id() ) {
		$active_extensions = bebop_extensions::bebop_get_active_extension_names();
		$active_extensions[] = 'all_oers';
		foreach ( $active_extensions as $extension ) {
			if ( bp_current_action() == $extension ) {
				 if ( $extension == 'all_oers' ) {
				 	$this_bp_feed = $extension;
				 }
				 else if ( bebop_tables::check_option_exists( 'bebop_' . $extension . '_rss_feed' ) ) {
					if ( bebop_tables::get_option_value( 'bebop_' . $extension . '_rss_feed' ) == 'on' ) {
						$this_bp_feed = $extension;
					}
				}
			}
		}
	}
	if ( empty( $this_bp_feed ) ) {
		return false;
	}

	$wp_query->is_404 = false;
	status_header( 200 );

	include_once( 'templates/user/bebop-feed-template.php' );
	die();
}

function bebop_feed_url() {
	return bebop_get_feed_url();
}
function bebop_get_feed_url() {
	global $this_bp_feed;
	if ( ! empty( $this_bp_feed ) ) {
		return bp_displayed_user_domain() . bp_get_activity_slug() . '/' . $this_bp_feed . '/feed';
	}
	else {
		return false;
	}
}

function bebop_feed_type() {
	return bebop_get_feed_type();
}
function bebop_get_feed_type() {
	global $this_bp_feed;
	if ( ! empty( $this_bp_feed ) ) {
		$feed = str_replace( '_', ' ', $this_bp_feed );
		return ucwords( $feed );
	}
	else {
		return false;
	}
}

function bebop_feed_description() {
	return bebop_get_feed_description();
}
function bebop_get_feed_description() {
	global $this_bp_feed;
	if ( ! empty( $this_bp_feed ) ) {
		return bebop_feed_type() . ' Feed for ' . bp_get_displayed_user_fullname();
	}
	else {
		return false;
	}
}

function bebop_activity_args() {
	return bebop_get_activity_args();
}
function bebop_get_activity_args() {
	global $bp, $this_bp_feed;
	
	//get the count limit
	$action_variables = bp_action_variables();
	$standard_limit = 25;
	$max_limit = 250;
	
	if ( $action_variables[0] == 'feed' ) {
		if ( isset( $action_variables[1]) ) {
			if ( is_numeric( $action_variables[1] ) ) {
				if ( $action_variables[1] <= $max_limit ) {
					$limit = $action_variables[1];
				}
				else {
					$limit = $max_limit;
				}
			}
		}
	}
	if( ! isset( $limit ) ) {
		$limit = $standard_limit;
	}
	
	
	if ( ! empty( $this_bp_feed ) ) {
		if ( $this_bp_feed == 'all_oers' ) {
			//only want to import active feeds
			$import_feeds = array();
			$active_extensions = bebop_extensions::get_active_extension_names();
			foreach ( $active_extensions as $extension ) {
				if ( bebop_tables::check_option_exists( 'bebop_' . $extension . '_rss_feed' ) ) {
					if ( bebop_tables::get_option_value( 'bebop_' . $extension . '_rss_feed' ) == 'on' ) { 
						$import_feeds[] = $extension;
					}
				}
			}
			if ( ! empty( $import_feeds ) ) {
				if ( count( $import_feeds ) >= 2 ) {
					$query_feeds = implode( ',', $import_feeds );
				}
				else {
					$query_feeds = $import_feeds;
				}
				return 'user_id=' . bp_displayed_user_id() . '&object=bebop_oer_plugin&action=' . $query_feeds . '&max=' . $limit . '&display_comments=stream&';
			}
		}
		return 'user_id=' . bp_displayed_user_id() . '&object=bebop_oer_plugin&action=' . $this_bp_feed . '&max=' .$limit . '&display_comments=stream';
	}
	else {
		return false;
	}
}
?>