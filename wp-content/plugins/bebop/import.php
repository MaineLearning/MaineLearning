<?php
/**
 * Importer for bebop
 */
set_time_limit( 10000 );
ini_set( 'max_execution_time', 10000 );

//load the WordPress loader
$currentPpath = getcwd();
$seekingRoot  = pathinfo($currentPpath);
$incPath      = str_replace('wp-content/plugins','',$seekingRoot['dirname']);

ini_set('include_path', $incPath);
include_once('wp-load.php');

//include files from core.
include_once( 'core/bebop-data.php' );
include_once( 'core/bebop-oauth.php' );
include_once( 'core/bebop-tables.php' );
include_once( 'core/bebop-filters.php' );
include_once( 'core/bebop-pages.php' );
include_once( 'core/bebop-extensions.php' );

//Main content file
include_once( 'core/bebop-core.php' );

//if import a specific OER.
if ( isset( $_GET['provider'] ) ) {
	$importers[] = $_GET['provider'];
}
else {
	$active_extensions = bebop_extensions::get_active_extension_names();
	//save importers to database
	bebop_tables::update_option( 'bebop_importers', implode( ',', $active_extensions ) );
	
	//check if there is a import queue, if empty reset
	if ( ! bebop_tables::get_option_value( 'bebop_importers_queue' ) ) {
		bebop_tables::update_option( 'bebop_importers_queue', implode( ',', $active_extensions ) );
	}
	$importers = bebop_tables::get_option_value( 'bebop_importers_queue' );
	$importers = explode( ',', $importers );
}

//Check that the importers queue isn't empty, then start calling the import functions
if ( ! empty( $importers[0] ) ) {
	bebop_tables::log_general( 'Importer', 'Importer service started.' ); 
	$return_array = array();
	foreach ( $importers as $extension ) {
		if ( bebop_tables::get_option_value( 'bebop_' . strtolower( $extension ) . '_provider' ) == 'on' ) {
			if ( file_exists( WP_PLUGIN_DIR . '/bebop/extensions/' . strtolower( $extension ) . '/import.php' ) ) {
			
				include_once( WP_PLUGIN_DIR . '/bebop/extensions/' . strtolower( $extension ) . '/import.php' );
				if ( function_exists( 'bebop_' . strtolower( $extension ) . '_import' ) ) {
					//call the import function, and pass in the extension name.
					$return_array[] = call_user_func( 'bebop_' . strtolower( $extension ) . '_import', strtolower( $extension ) );
				}
				else {
					bebop_tables::log_error( 'Importer', 'The function: bebop_' . strtolower( $extension ) . '_import does not exist.' );
				}
			}
			else {
				bebop_tables::log_error( 'Importer', 'The file: ' . WP_PLUGIN_DIR . '/bebop/extensions/' . strtolower( $extension ) . '/import.php does not exist.' );
			}
		}
	}
	
	
	$log_results = implode( ', ', $return_array );
	bebop_tables::log_general( 'Importer', 'Importer service completed. Imported ' . $log_results . '.' );
}