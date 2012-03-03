<?php
if ( !defined( 'PB_DB_LOADED' ) ) {
	global $pluginbuddy_repairbuddy;
	$pluginbuddy_repairbuddy->output_status( 'Could not connect to the database.  Please make sure RepairBuddy is placed at the root of your WordPress install and verify your wp-config.php database credentials.', true );
} else {
	require( 'view_tools-database.php' );
}
?>