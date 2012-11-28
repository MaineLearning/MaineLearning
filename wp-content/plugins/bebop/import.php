<?php
/**
 * Importer for bebop
 */
set_time_limit( 60 );
ini_set( 'max_execution_time', 60 );

//load the WordPress loader
$current_path  = getcwd();
$seeking_root  = pathinfo( $current_path );
$inc_path      = str_replace( 'wp-content/plugins','',$seeking_root['dirname'] );

ini_set( 'include_path', $inc_path );
include_once( 'wp-load.php' );

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
if ( isset( $_GET['extension'] ) ) {
	$importers[] = $_GET['extension'];
}
else {
	$importers = bebop_extensions::bebop_get_active_extension_names();
}

//Check that the importers queue isn't empty, then start calling the import functions
if ( ! empty( $importers[0] ) ) {
	bebop_tables::log_general( __( 'Main Importer', 'bebop' ), __( 'Main importer service started.', 'bebop' ) ); 
	$return_array = array();
	foreach ( $importers as $extension ) {
		if ( bebop_tables::get_option_value( 'bebop_' . strtolower( $extension ) . '_provider' ) == 'on' ) {
			if ( file_exists( WP_PLUGIN_DIR . '/bebop/extensions/' . strtolower( $extension ) . '/import.php' ) ) {
				include_once( WP_PLUGIN_DIR . '/bebop/extensions/' . strtolower( $extension ) . '/import.php' );
				if ( function_exists( 'bebop_' . strtolower( $extension ) . '_import' ) ) {
					$return_array[] = call_user_func( 'bebop_' . strtolower( $extension ) . '_import', strtolower( $extension ) );
				}
				else {
					bebop_tables::log_error( __( 'Main Importer', 'bebop' ), sprintf( __( 'The function: bebop_%1$s_import does not exist.', 'bebop' ), strtolower( $extension ) ) );
				}
			}
			else {
				bebop_tables::log_error( __( 'Main Importer', 'bebop' ), sprintf( __( 'The function: %1$s/import.php does not exist.', 'bebop'), WP_PLUGIN_DIR . '/bebop/extensions/' . strtolower( $extension ) ) );
			}
		}
	}
	$log_array = array();
	foreach ( $return_array as $key => $value ) {
		if ( ! empty( $value ) ) {
			$log_array[] = $value;
		}
	}
	$log_results = implode( ', ', $log_array );
	
	if ( ! empty( $log_results ) ) {
		$message = sprintf( __( 'Main importer service completed. Imported %1$s.', 'bebop' ), $log_results );
		echo $message;
	}
	else {
		
		$message = __( 'Main importer service completed. Nothing was imported.', 'bebop' );
		echo $message;
	}
	bebop_tables::log_general( __( 'Main Importer', 'bebop' ), $message );
}