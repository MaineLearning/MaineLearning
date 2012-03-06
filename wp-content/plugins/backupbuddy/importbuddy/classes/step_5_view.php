<?php
$this->set_greedy_script_limits();




echo $this->status_box( 'Migrating database content with ImportBuddy ' . $this->_version . '...' );
echo '<div id="pb_importbuddy_working"><img src="?ezimg=working.gif" title="Working... Please wait as this may take a moment..."></div>';

$result = $api->migrate_database();

echo '<script type="text/javascript">jQuery("#pb_importbuddy_working").hide();</script>';

if ( true === $result ) {
	if ( true === $api->migrate_wp_config() ) {
		$this->status( 'message', 'Import complete!' );
		echo '<br><br><b>Import complete for the site: </b><a href="' . $this->_options['siteurl'] . '" target="_new">' . $this->_options['siteurl'] . '</a><br><br>';
		echo '<img src="?ezimg=bullet_error.png" style="float: left;"><div style="margin-left: 20px;">Verify site functionality then delete the backup ZIP file and importbuddy.php from your site root (the next step will attempt to do this for you). Leaving these files is a security risk. Leaving the zip file and then subsequently running a BackupBuddy backup will result in excessively large backups as this zip file will be included.</div>';
		
		echo '<form action="?step=6" method=post>';
		echo '<input type="hidden" name="options" value="' . htmlspecialchars( serialize( $this->_options ) ) . '" />';
		?>
		
		<br>
		<h3>Last step: File Cleanup</h3>
		<table><tr><td>
			<label for="delete_backup" style="width: auto; font-size: 12px;"><input type="checkbox" name="delete_backup" id="delete_backup" value="1" checked> Delete backup zip archive</label>
			<br>		
			<label for="delete_temp" style="width: auto; font-size: 12px;"><input type="checkbox" name="delete_temp" id="delete_temp" value="1" checked> Delete temporary import files</label>
		</td><td>
			<label for="delete_importbuddy" style="width: auto; font-size: 12px;"><input type="checkbox" name="delete_importbuddy" id="delete_importbuddy" value="1" checked> Delete importbuddy.php script</label>
			<br>
			<label for="delete_importbuddylog" style="width: auto; font-size: 12px;"><input type="checkbox" name="delete_importbuddylog" id="delete_importbuddylog" value="1" checked> Delete importbuddy.txt log file</label>
		</td></tr></table>
		
		<?php
		echo '<br><p style="text-align: center;"><input type="submit" name="submit" class="button" value="Clean up & remove temporary files &raquo" /></p>';
		echo '</form>';
	} else {
		$this->alert( 'Error: Unable to update wp-config.php file.', 'Verify write permissions for the wp-config.php file then refresh this page.' );
	}
} else {
	$this->alert( 'Error: Unable to migrate database content.', 'Something went wrong with the database migration portion of the restore process.' );
}
?>