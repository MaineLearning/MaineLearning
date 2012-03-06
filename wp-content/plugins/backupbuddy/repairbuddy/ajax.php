<?php
/*
	1.  To load WordPress, pass a GET or POST variable of load_wp
	2.  You must pass the a POST vaiable of password or a GET variable of "v" with the passed token
	3.  You must have already registered your action and pass a GET or POST variable of action
*/
define('PB_DOING_AJAX', true);

if ( ! isset( $_REQUEST['action'] ) )
	die('-1');
$pb_path = dirname( dirname( __FILE__ ) );
//Get the action variable
$ajax_action = isset( $_GET[ 'action' ] ) ? $_GET[ 'action' ] : false;
if ( !$ajax_action ) {
	$ajax_action = isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : false;
	if ( !$ajax_action ) die( '-1' );
}


require_once( $pb_path . '/repairbuddy.php' );

if ( !pb_has_access() ) {
	die( 'Access Denied' );
}
pb_do_action( 'pb_ajax_' . $ajax_action );
die( 0 );
?>