<?php
// Used for drag & drop / collapsing boxes.
wp_enqueue_style('dashboard');
wp_print_styles('dashboard');
wp_enqueue_script('dashboard');
wp_print_scripts('dashboard');

wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );
// Handles resizing thickbox.
if ( !wp_script_is( 'media-upload' ) ) {
	wp_enqueue_script( 'media-upload' );
	wp_print_scripts( 'media-upload' );
}
wp_enqueue_script( 'backupbuddy-ms-export', $this->_parent->_pluginURL . '/js/ms.js', array( 'jquery' ) );
wp_print_scripts( 'backupbuddy-ms-export' );



?>
<style type="text/css">
#status-bar-container {
	display: none;
	width: 500px;
	height: 12px;
	margin-top: 20px;
	background-color: white;
	border: 1px solid #000;
	-moz-border-radius: 5px;
	border-radius: 5px;
}
#status-bar {
	height: 12px;
	width: 0px;
	float: left;
	overflow: visible;
	background-color: #5b962f;
}
.status {
	display: none;
	margin: 5px 0 15px;
	background-color: #FFFFE0;
	border: 1px solid #E6DB55;
	padding: 0 0.6em;
	-moz-border-radius: 3px;
	border-radius: 3px;
}
.status p {
	margin: 0.5em 0;
	padding: 2px;
	
}
</style>
<div class='wrap'>
<h2>Duplicate a site (BETA)</h2>
<p>For BackupBuddy Multisite documentation, please visit the <a href='http://ithemes.com/codex/page/BackupBuddy_Multisite'>BackupBuddy Multisite Codex</a>.</p>
<?php
//Let's do some sanity checks to make sure a valid nonce is used or a $_POST variable is set - If not, we're on the first step and the user must select plugins
global $current_blog, $current_site;
$action = isset( $_GET[ 'action' ] ) ? $_GET[ 'action' ] : false;
$form_url = $this->_selfLink . '-msduplicate';
$zip_id = 0;
if ( isset( $_POST[ 'action' ] ) ) {
	//Try to verify the nonce
	
	
} //end isset action
?>
<div class='wrap'>
<?php
switch( $action ) {
	case 'step2':
		require( $this->_parent->_pluginPath . '/classes/' . 'msduplicate_steps/step2.php' );
		break;
	default:
		require( $this->_parent->_pluginPath . '/classes/' . 'msduplicate_steps/step1.php' );
		break;
} //end switch
?>
</div><!--/.wrap-->