<?php
session_start();
/*
Plugin Name: Bebop
Plugin URI: http://bebop.blogs.lincoln.ac.uk/
Description: Bebop provides your BuddyPress users with the ability to import and share content from a wide range of online content providers, such as Facebook, Twitter, Vimeo, Youtube and Flickr. Bebop was originally developed as a method of sharing Open Educational Resources, but has since been opened up to the wider community.
Version: 1.3.1
Text Domain: bebop
Author: Dale Mckeown
Author URI: http://www.dalemckeown.co.uk
License: GNU General Public Licence - https://www.gnu.org/copyleft/gpl.html
Copyright 2013 The University of Lincoln - http://www.lincoln.ac.uk.
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
	
	load_plugin_textdomain( 'bebop' , false, basename( dirname( __FILE__ ) ) . '/languages' );
	
	//include files from core. (also edit in import.php/secondary_import.php)
	include_once( 'core/bebop-oauth.php' );
	include_once( 'core/bebop-tables.php' );
	include_once( 'core/bebop-filters.php' );
	include_once( 'core/bebop-extensions.php' );
	include_once( 'core/bebop-feeds.php' );
	include_once( 'core/bebop-pages.php' );
	
	if ( current_user_can( 'manage_options' ) && is_admin() ) {
		include_once( 'core/bebop-core-admin.php' );
	}
	include_once( 'core/bebop-core.php' );
	
	//Plugin activation/deactivation hooks do not fire on plugin update, so check the current DB version and update as necessary.
	$db_version = bebop_tables::get_option_value( 'bebop_db_version' );
	if ( empty( $db_version ) || $db_version != '1.3.1' ) {
		include_once( 'core/bebop-activate.php' );
	}
	

	//fire crons
	add_action( 'bebop_main_import_cron', 'bebop_main_import_function' );
	add_action( 'bebop_secondary_import_cron', 'bebop_secondary_import_function' );
	
	//Adds the schedule filter for changing the standard interval time.
	add_filter( 'cron_schedules', 'bebop_main_cron_schedule' );
	add_filter( 'cron_schedules', 'bebop_secondary_cron_schedule' );
	
	//main cron
	if ( ! wp_next_scheduled( 'bebop_main_import_cron' ) ) {
		wp_schedule_event( time(), 'bebop_main_cron_time', 'bebop_main_import_cron' );
	}
	
	//secondary cron
	if ( ! wp_next_scheduled( 'bebop_secondary_import_cron' ) ) {
		wp_schedule_event( time(), 'bebop_secondary_cron_time', 'bebop_secondary_import_cron' );
	}
	
	//hook in any 3rd party extensions to this hook.
	do_action( 'bebop_loaded' );
	
	//load all the extensions.
	bebop_extensions::bebop_load_extensions();
}

//Code that should be fired when he plugin is activated.
function bebop_activate() {
	global $wpdb;
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	include_once( 'core/bebop-tables.php' );
	if ( is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		include_once( 'core/bebop-activate.php' );
	}
	else {
		//BuddyPress is not installed, stop Bebop form activating and kill the script with an error message.
		deactivate_plugins( basename( __FILE__ ) ); // Deactivate this plugin
		wp_die( _e( 'You cannot enable Bebop because BuddyPress is not active. Please install and activate BuddyPress before trying to activate Bebop again.', 'bebop') );
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
	bebop_tables::drop_table( 'bp_bebop_first_imports' );
	bebop_tables::remove_activity_stream_data();
	
	//delete the scheduled crons
	wp_clear_scheduled_hook( 'bebop_main_import_cron' );
	wp_clear_scheduled_hook( 'bebop_secondary_import_cron' );
}

//This function sets up the time interval for the cron schedule.
function bebop_main_cron_schedule( $schedules ) {
	
	$crontime = bebop_tables::get_option_value( 'bebop_general_crontime' );
	
	if ( is_numeric( $crontime ) ) {
		$time = $crontime;
	}
	else {
		$time = 600;
		bebop_tables::update_option( 'bebop_general_crontime', $time );
	} 
	
	$schedules['bebop_main_cron_time'] = array(
		'interval' => $time,
		'display'  => __( 'main_cron' ),
	); 
	return $schedules;
}

function bebop_secondary_cron_schedule( $schedules ) {

	$schedules['bebop_secondary_cron_time'] = array(
		'interval' => 10,
		'display'  => __( 'secondary_cron' ),
	);
	return $schedules;
}

function bebop_main_import_function() {	
	require_once( 'import.php' );
}

function bebop_secondary_import_function() {	
	require_once( 'secondary_import.php' );
}

define( 'BP_BEBOP_VERSION', '1.3.1' );

//hooks into activation and deactivation of the plugin.
register_activation_hook( __FILE__, 'bebop_activate' );
//register_deactivation_hook( __FILE__, 'bebop_deactivate' );
register_uninstall_hook( __FILE__, 'bebop_deactivate' );

add_action( 'bp_init', 'bebop_init', 5 );
?>
