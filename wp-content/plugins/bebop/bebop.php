<?php
session_start();
/*
Plugin Name: Bebop
Plugin URI: http://bebop.blogs.lincoln.ac.uk/
Description: Bebop is the name of a rapid innovation project funded by the Joint Information Systems Committee (JISC) and developed by the University of Lincoln. The project involved the utilisation of OER's from 3rd party providers such as YouTube, Vimeo, SlideShare and Flickr.
Version: 1.0.1
Authors: Dale Mckeown, David Whitehead
Author URI: http://phone.online.lincoln.ac.uk/dmckeown, http://phone.online.lincoln.ac.uk/dwhitehead
License: GNU General Public Licence - https://www.gnu.org/copyleft/gpl.html
Copyright 2012 The University of Lincoln - http://www.lincoln.ac.uk.
Credits: BuddySteam - buddystream.net
*/
// This plugin is intended for use on BuddyPress only.
// http://buddypress.org/

/****************************************************************************
// This program is distributed in the hope that it will be useful, but		*
// WITHOUT ANY WARRANTY; without even the implied warranty of				*
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.						*
****************************************************************************/

//initialise Bebop
function bebop_init() {
	
	//include files from core. (also edit in import.php)
	include_once( 'core/bebop-oauth.php' );
	include_once( 'core/bebop-tables.php' );
	include_once( 'core/bebop-filters.php' );
	include_once( 'core/bebop-extensions.php' );
	include_once( 'core/bebop-feeds.php' );
	include_once( 'core/bebop-core.php' );
	include_once( 'core/bebop-pages.php' );

	//fire cron
	add_action( 'bebop_cron', 'bebop_cron_function' ); 
	
	//Adds the schedule filter for changing the standard interval time.
	add_filter( 'cron_schedules', 'bebop_seconds_cron' );
	
	if ( ! wp_next_scheduled( 'bebop_cron' ) ) {
    	wp_schedule_event( time(), 'secs', 'bebop_cron' );
	}
}

//Code that should be fired when he plugin is activated.
function bebop_activate() {
	global $wpdb;
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		//define table sql
		$bebop_error_log = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_error_log ( 
			id int(10) NOT NULL auto_increment PRIMARY KEY, 
			timestamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			error_type varchar(40) NOT NULL,
			error_message text NOT NULL
		);';
		$bebop_general_log = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_general_log ( 
			id int(10) NOT NULL auto_increment PRIMARY KEY,
			timestamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
			type varchar(40) NOT NULL,
			message text NOT NULL
	    );';
	
		$bebop_options = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_options ( 
			timestamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,	
			option_name varchar(100) NOT NULL PRIMARY KEY,
			option_value longtext NOT NULL
		);';
		
		$bebop_user_meta = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_user_meta ( 
			id int(10) NOT NULL auto_increment PRIMARY KEY,
			user_id int(10) NOT NULL,
			meta_type varchar(255) NOT NULL,
			meta_name varchar(255) NOT NULL,
			meta_value longtext NOT NULL
		);';
		
		$bebop_oer_manager = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager ( 
			id int(10) NOT NULL auto_increment PRIMARY KEY,
			user_id int(10) NOT NULL,
			status varchar(75) NOT NULL,
			type varchar(255) NOT NULL,
			action text NOT NULL,
			content longtext NOT NULL,
			activity_stream_id bigint(20),
			secondary_item_id bigint(20),
			date_imported datetime,
			date_recorded datetime,
			hide_sitewide tinyint(1)
		);'; 
		//run queries
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $bebop_error_log );
		dbDelta( $bebop_general_log );
		dbDelta( $bebop_options );
		dbDelta( $bebop_user_meta );
		dbDelta( $bebop_oer_manager );
		
		//cleanup
		unset( $bebop_error_log );
		unset( $bebop_general_log );
		unset( $bebop_options );
		unset( $bebop_user_meta );
		unset( $bebop_oer_manager );
	}
	else {
		//BuddyPress is not installed, stop Bebop form activating and kill the script with an error message.
		include_once( 'core/bebop-tables.php' );
		bebop_tables::log_error( 'BuddyPress Error', 'BuddyPress is not active.' );
		deactivate_plugins( basename( __FILE__ ) ); // Deactivate this plugin
		wp_die( 'You cannot enable Bebop because BuddyPress is not active. Please install and activate BuddyPress before trying to activate Bebop again.' );
	}
}
//remove the tables upon deactivation
function bebop_deactivate() {
	//delete tables and clean up the activity data
	include_once( 'core/bebop-tables.php' );
	bebop_tables::drop_table( 'bp_bebop_general_log' );
	bebop_tables::drop_table( 'bp_bebop_error_log' );
	bebop_tables::drop_table( 'bp_bebop_options' );
	bebop_tables::drop_table( 'bp_bebop_user_meta' );
	bebop_tables::drop_table( 'bp_bebop_oer_manager' );
	bebop_tables::remove_activity_stream_data();
	
	//delete the cron 
	wp_clear_scheduled_hook( 'bebop_cron' );	
}

//This function sets up the time interval for the cron schedule.
function bebop_seconds_cron( $schedules ) {
	if ( bebop_tables::get_option_value( 'bebop_general_crontime' ) ) {
		$time = bebop_tables::get_option_value( 'bebop_general_crontime' );
	}
	else {
		$time = 300;
		bebop_tables::update_option( 'bebop_general_crontime', $time );
	} 
	
	$schedules['secs'] = array(
		'interval' => $time,
		'display'  => __( 'Once Weekly' ),
	); 
	return $schedules;
}

function bebop_cron_function() {	
	require_once( 'import.php' );
}

define( 'BP_BEBOP_VERSION', '1.0.1' );

//hooks into activation and deactivation of the plugin.
register_activation_hook( __FILE__, 'bebop_activate' );
//register_deactivation_hook( __FILE__, 'bebop_deactivate' );
register_uninstall_hook( __FILE__, 'bebop_deactivate' );

add_action( 'bp_init', 'bebop_init', 5 );
?>
