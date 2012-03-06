<?php
$this->admin_scripts();

// Used for drag & drop / collapsing boxes.
wp_enqueue_style('dashboard');
wp_print_styles('dashboard');
wp_enqueue_script('dashboard');
wp_print_scripts('dashboard');
?>
<div class="wrap">
<?php
// Show warning if loopback connections are not available.
if ( ( $loopback_response = $this->_parent->loopback_test() ) !== true ) {
	if ( defined( 'ALTERNATE_WP_CRON' ) && ( ALTERNATE_WP_CRON == true ) ) {
		$this->alert( __('Running in Alternate WordPress Cron mode. HTTP Loopback Connections are not enabled on this server but you have overridden this in the wp-config.php file (this is a good thing).', 'it-l10n-backupbuddy') . ' <a href="http://ithemes.com/codex/page/BackupBuddy:_Frequent_Support_Issues#HTTP_Loopback_Connections_Disabled" target="_new">' . __('Additional Information Here', 'it-l10n-backupbuddy') . '</a>.' );
	} else {
		$this->alert( __('HTTP Loopback Connections are not enabled on this server. You may encounter stalled or significantly delayed backups.', 'it-l10n-backupbuddy') . ' <a href="http://ithemes.com/codex/page/BackupBuddy:_Frequent_Support_Issues#HTTP_Loopback_Connections_Disabled" target="_new">' . __('Click for instructions on how to resolve this issue.', 'it-l10n-backupbuddy') . '</a>', true );
	}
}


// Scan for WordPress in subdirectories.
$wordpress_locations = $this->_parent->get_wordpress_locations();
if ( count( $wordpress_locations ) > 0 ) {
	$this->alert( __( 'WordPress may have been detected in one or more subdirectories. Backing up multiple instances of WordPress may result in server timeouts due to increased backup time. Detected locations:', 'it-l10n-backupbuddy' ) . '<br>' . implode( ',', $wordpress_locations ) );
}


// Make backup directory.
$backup_dir_exists = $this->_parent->create_backup_directory();

require_once( $this->_pluginPath . '/lib/zipbuddy/zipbuddy.php' );
$this->_zipbuddy = new pluginbuddy_zipbuddy( $this->_options['backup_directory'] );
if ( !in_array( 'exec', $this->_zipbuddy->_zip_methods ) ) {
	$this->alert(   __('Your server does not support command line ZIP. Backups will be performed in compatibility mode.', 'it-l10n-backupbuddy') 
				    . '<br>' 
				    . __('Directory/file exclusion is not available in this mode so even existing backups will be backed up.', 'it-l10n-backupbuddy') 
				    . '<br>' 
				    . __('You may encounter stalled or significantly delayed backups.', 'it-l10n-backupbuddy') 
				    .  '<a href="http://ithemes.com/codex/page/BackupBuddy:_Frequent_Support_Issues#Compatibility_Mode" target="_new">' 
				    . __('Click for instructions on how to resolve this issue.', 'it-l10n-backupbuddy') 
				    . '</a>'
				  , true 
				  );
}


if ( isset( $_GET['run_backup'] ) ) {
	if ( $backup_dir_exists === false ) {
		$this->alert( __( 'Backing up is unavailable due to permissions issues. Please address any reported errors.', 'it-l10n-backupbuddy' ) );
	} else {
		if ( $this->_options['backup_mode'] == '2' ) {
			require_once( 'view_backup-run_backup-perform.php' );
		} else {
			require_once( 'view_backup-run_backup-perform_classic.php' );
		}
	} // end $backup_dir_exists check.
} else {
	require_once( 'view_backup-run_backup-home.php' );
}
?>
</div>

<br /><br />
