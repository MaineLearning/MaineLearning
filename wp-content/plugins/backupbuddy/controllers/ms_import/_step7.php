<?php
global $wpdb, $current_site, $current_blog;
$blog_id = isset( $_POST[ 'blog_id' ] ) ? absint( $_POST[ 'blog_id' ] ) : 0;
//switch_to_blog( $blog_id );
$new_db_prefix = $wpdb->get_blog_prefix( $blog_id );

echo $this->status_box( 'Migrating users . . .' );
echo '<div id="pb_importbuddy_working" style="width: 793px;"><center><img src="' . $this->_pluginURL . '/images/working.gif" title="Working... Please wait as this may take a moment..."></center></div>';
flush();

$this->status( 'message', 'NOTE: If a user you are attempting to import already exists in the network then they will NOT be migrated. This may result in orphaned posts.' );

// Delete BackupBuddy options for imported site.
$this->status( 'details', 'Clearing importing BackupBuddy options.' );
$sql = "DELETE from {$new_db_prefix}options WHERE option_name='pluginbuddy_backupbuddy' LIMIT 1";
$wpdb->query( $wpdb->prepare( $sql ) );

// Clear out all BackupBuddy cron jobs.
$this->status( 'details', 'Clearing importing BackupBuddy scheduled crons.' );

// Clear out any cron hooks related to BackupBuddy for imported site.
$wipe_cron_hooks = array( 
						$this->_parent->_var . '-cron_final_cleanup',
						$this->_parent->_var . '-cron_process_backup',
						$this->_parent->_var . '-cron_dropbox_copy',
						$this->_parent->_var . '-cron_ftp_copy',
						$this->_parent->_var . '-cron_rackspace_copy',
						$this->_parent->_var . '-cron_s3_copy',
						'pb_backupbuddy-cron_scheduled_backup', // Backward compat to BB 1.x.
						'pb_backupbuddy-cron_remotesend',
					);
$sql = "SELECT option_value FROM `{$new_db_prefix}options` WHERE option_name='cron' LIMIT 1";
$crons = $wpdb->get_var( $wpdb->prepare( $sql ) );
$crons = unserialize( $crons );
if ( $crons != '' ) {
	foreach ( $crons as $timestamp => $cron ) {
		foreach ( $wipe_cron_hooks as $wipe_cron_hook ) {
			if ( isset( $cron[ $wipe_cron_hook ] ) ) {
				unset( $crons[ $timestamp ] ); // Remove this BB hook from the cron system.
			}
		}
	}
	$cron = serialize( $cron );
	$sql = "UPDATE `{$new_db_prefix}options` SET option_value='{$cron}' WHERE option_name='cron' LIMIT 1";
	$wpdb->query( $wpdb->prepare( $sql ) );
}


// Migrate users.
$sql = "select * from `{$new_db_prefix}users`";
$users = $wpdb->get_results( $wpdb->prepare( $sql ) ); // Users to import.
if ( !is_array( $users ) ) {
	?>
	<strong>No users can be found</strong><br />
	<?php
	return;
}
if ( version_compare( get_bloginfo( 'version' ), '3.1', '<' ) ) {
	require_once(ABSPATH . WPINC . '/registration.php');
}

$this->status( 'message', 'This may take a moment . . .' );

$user_count = 0;
$users_skipped = 0;
foreach ( $users as $user ) { // For each source user to migrate.
	$user_count++;
	
	$old_user_id = $user->ID;
	$old_user_pass = $user->user_pass;
	$sql = "select ID from {$wpdb->users} where user_login = '{$user->user_login}' or user_email = '{$user->user_email}'"; // Get user if they already exist on network.
	$user_id = $wpdb->get_var( $wpdb->prepare( $sql ) ); // We will see if user already exists; 
	if ( !$user_id ) { // User does NOT already exist in network.
		$new_destination_user_args = array();
		foreach ( $user as $key => $user_param ) { // Loop through all user parameters.
			$new_destination_user_args[ $key ] = $user_param;
		}
		// TODO: is Ronald's following line wrong?
		$user_role = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( "select meta_value from {$wpdb->prefix}usermeta where meta_key = '{$wpdb->prefix}capabilities' and user_id = {$old_user_id}" )  ) );
		$new_user_role = '';
		if ( is_array( $user_role ) ) {
			foreach ( $user_role as $key => $value ) {
				$new_user_role = $key;
			}
		}
		
		unset( $new_destination_user_args[ 'ID' ] );
		$user_id = wp_insert_user( $new_destination_user_args ); // Create new user into network.
		add_user_to_blog( $blog_id, $user_id, $new_user_role ); // Add this user to the destination blog.
		$wpdb->update( $wpdb->users, array( 'user_pass' => $old_user_pass ), array( 'ID' => $user_id ) ); // Keep password the same.
	
	} else { // User already exists.
		$users_skipped++;
	}
	
	// Update user IDs with the user's new migrated ID.
	$wpdb->update( $wpdb->posts, array( 'post_author' => absint( $user_id ) ), array( 'post_author' => $old_user_id ), array( '%d' ) ); 
	$wpdb->update( $wpdb->comments, array( 'user_id' => absint( $user_id ) ), array( 'user_id' => $old_user_id ), array( '%d' ) );
} //end foreach

$this->status( 'message', 'Migrated ' . $user_count . ' users. ' . $users_skipped . ' users were skipped due to collision.' );
if ( $users_skipped > 0 ) {
	$this->status( 'message', 'NOTE: Some users could not be imported as the username already existed in the network. Any posts attributed to them will no longer show them as the author.' );
}


// Drop the imported sites temporary users tables since they are now merged into the network site.
$drop_tables = array(
	'users',
	'usermeta',
);
foreach ( $drop_tables as $table ) {
	$table = $new_db_prefix . $table;
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $table );
}

$this->status( 'details', 'Dropped temporary user tables.' );


$this->status( 'message', 'Users migrated.' );
echo '<script type="text/javascript">jQuery("#pb_importbuddy_working").hide();</script>';
flush();


//Output form interface
global $current_site;
	$errors = false;	
	$blog = $domain = $path = '';
	$form_url = add_query_arg( array(
		'step' => '8',
		'action' => 'step8'
	) , $this->_parent->_selfLink . '-msimport' );
?>
<form method="post" action="<?php echo esc_url( $form_url ); ?>">
<?php wp_nonce_field( 'bbms-migration', 'pb_bbms_migrate' ); ?>
<input type='hidden' name='backup_file' value='<?php echo esc_attr( $_POST[ 'backup_file' ] ); ?>' />
<input type='hidden' name='blog_id' value='<?php echo esc_attr( absint( $_POST[ 'blog_id' ] ) ); ?>' />
<input type='hidden' name='blog_path' value='<?php echo esc_attr( $_POST[ 'blog_path' ] ); ?>' />
<input type='hidden' name='global_options' value='<?php echo base64_encode( serialize( $this->advanced_options ) ); ?>' />

<h3>Last Step: Final Cleanup</h3>

<label for="delete_backup" style="width: auto; font-size: 12px;"><input type="checkbox" name="delete_backup" id="delete_backup" value="1" checked> Delete backup zip archive</label>
<br>		
<label for="delete_temp" style="width: auto; font-size: 12px;"><input type="checkbox" name="delete_temp" id="delete_temp" value="1" checked> Delete temporary import files</label>


<?php submit_button( __('Next') . ' &raquo;', 'primary', 'add-site' ); ?>
</form>