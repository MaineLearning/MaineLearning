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
<h2>RepairBuddy (BETA)</h2>
<p>Repairbuddy is a tool for diagnosing and repairing WordPress installations when things go wrong.</p>
<p>For help with RepairBuddy, please visit the <a href='http://ithemes.com/codex/page/RepairBuddy'>RepairBuddy Codex</a>.  RepairBuddy is <strong>Beta software</strong>.  Please read <a href='http://ithemes.com/codex/page/PluginBuddy#What_does_BETA_mean.3F'>our Codex entry for Beta Software</a>.</p>
<?php
$action = isset( $_GET[ 'action' ] ) ? $_GET[ 'action' ] : false;
$form_url = $this->_selfLink . '-repairbuddy';
if ( isset( $_POST[ 'enable-repair-buddy' ] ) ) {
	$errors = array();
	//Try to verify the nonce
	check_admin_referer( 'pb_save_repairbuddy_password', 'pb_nonce' );
	$pass1 = $_POST[ 'pass1' ];
	$pass2 = $_POST[ 'pass2' ];
	if ( $pass1 != $pass2 ) {
		$errors[] = __( 'Passwords do not match.', 'it-l10n-backupbuddy' );
	} elseif ( empty( $pass1 ) || empty( $pass2 ) ) {
		$errors[] = __( 'Passwords may not be empty.', 'it-l10n-backupbuddy' );
	}
	if ( count( $errors ) > 0 ) {
		foreach ( $errors as $error ) {
			?>
			<div class='error'><p><strong><?php echo esc_html( $error ); ?></strong</p></div>
			<?php
		}
	} else {
		$pass1 = sanitize_text_field( $pass1 );
		$this->_options[ 'repairbuddy_password' ] = $pass1;
		$this->_parent->save();
		?>
		<div class='updated'><p><strong><?php esc_html_e( 'Password has been saved.', 'it-l10n-backupbuddy' ); ?></strong</p></div>
		<?php
	}
	
} //end isset action
$saved_password = $this->_options[ 'repairbuddy_password' ];
if ( empty( $saved_password ) ) {
	include_once( 'repairbuddy/view_passwords.php' );
} else {
	include_once( 'repairbuddy/view_download.php' );
}
?>