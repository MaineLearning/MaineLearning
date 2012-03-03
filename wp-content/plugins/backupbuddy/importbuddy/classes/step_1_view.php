Select the BackupBuddy backup file you would like to import or migrate.
Throughout the restore process you may hover over question marks
 <?php $this->tip( 'This is an example help tip. Hover over these for additional help.' ); ?> 
for additional help. 

For support see the <a href="http://ithemes.com/codex/page/BackupBuddy" target="_blank">Knowledge Base</a>
or <a href="http://pluginbuddy.com/support/" target="_blank">Support Forum</a>.

<br><br>

<?php
$api->upload();

echo '<br><br>';
?>

<div id="pluginbuddy-tabs">
	<ul>
		<li><a href="#pluginbuddy-tabs-server"><span>Server</span></a></li>
		<li><a href="#pluginbuddy-tabs-upload"><span>Upload</span></a></li>
	</ul>
	<div class="tabs-borderwrap" id="editbox" style="border-bottom: 1px solid #DFDFDF;">
		
		<div id="pluginbuddy-tabs-upload">
			<div class="tabs-item">
				<?php
				if ( $this->_options['password'] == '#PASSWORD#' ) {
					echo self::UPLOAD_ACCESS_DENIED;
				} else {
				?>
				<form enctype="multipart/form-data" action="?step=1" method="POST">
					<input type="hidden" name="upload" value="local">
					<input type="hidden" name="options" value="<?php echo htmlspecialchars( serialize( $this->_options ) ); ?>'" />
					Choose backup to upload: <input name="file" type="file" />&nbsp;
					<input type="submit" value="Upload" class="toggle button-secondary">
				</form>
				<?php
				}
				?>
			</div>
		</div>
		
		<div id="pluginbuddy-tabs-server">
			<div class="tabs-item">
				<?php
				$backup_archives = $api->get_archives_list();
				if ( empty( $backup_archives ) ) { // No backups found.
					$this->alert( 'ERROR: Unable to find any BackupBuddy backup files.',
						'Upload the backup zip archive into the same directory as this file,
						keeping the original file name. Ex: backup-your_com-2011_07_19-g1d1jpvd4e.zip<br><br>
						If you manually extracted, upload the backup file, select it, then select <i>Advanced
						Troubleshooting Options</i> & click <i>Skip Zip Extraction</i>.' );
				} else { // Found one or more backups.
					?>
						<form action="?step=2" method="post">
							<input type="hidden" name="options" value="<?php echo htmlspecialchars( serialize( $this->_options ) ); ?>'" />
					<?php
					echo ezimg::genImageTag( 'bullet_go.png' );
					echo '&nbsp;<select name="file" style="max-width: 590px;">';
					foreach( $backup_archives as $backup_archive ) {
						echo '<option value="' . $backup_archive . '">' . $backup_archive . '</option>';
					}
					echo '</select>';
					echo $this->tip( 'Select the backup file you would like to restore data from. This must be a valid BackupBuddy backup archive with its original filename. Remember to delete importbuddy.php and this backup file from your server after migration.', '', true );
				}
				?>
			</div>
		</div>
	</div>
</div>

<div style="margin-left: 20px; margin-top: 12px;">
	<span class="toggle button-secondary" id="serverinfo">Server Information</span> <span class="toggle button-secondary" id="advanced">Advanced Configuration Options</span>
	<div id="toggle-advanced" class="toggled" style="margin-top: 12px;">
		<?php
		//$this->alert( 'WARNING: These are advanced configuration options.', 'Use caution as improper use could result in data loss or other difficulties.' );
		?>
		<b>WARNING:</b> Improper use of Advanced Options could result in data loss.<br>
		&nbsp;&nbsp;&nbsp;&nbsp;Leave as is unless you understand what these settings do.
		<br><br>
		<input type="checkbox" name="wipe_database" onclick="
			if ( !confirm( 'WARNING! WARNING! WARNING! WARNING! WARNING! \n\nThis will clear any existing WordPress installation or other content in this database. This could result in loss of posts, comments, pages, settings, and other software data loss. Verify you are using the exact database settings you want to be using. PluginBuddy & all related persons hold no responsibility for any loss of data caused by using this option. \n\n Are you sure you want to do this and potentially wipe existing data? \n\n WARNING! WARNING! WARNING! WARNING! WARNING!' ) ) {
				return false;
			}
		" /> Wipe database on import. Use with caution. <?php $this->tip( 'WARNING: Checking this box will have this script clear ALL existing data from your database prior to import, including non-WordPress data. This is useful if you are restoring over an existing site or for repaired a failed migration. Use caution when using this option.' ); ?><br>
		<input type="checkbox" name="ignore_sql_errors" /> Ignore SQL errors & hide them. <br>
		<input type="checkbox" name="skip_files" /> Skip zip file extraction. <?php $this->tip( 'Checking this box will prevent extraction/unzipping of the backup ZIP file.  You will need to manually extract it either on your local computer then upload it or use a server-based tool such as cPanel to extract it. This feature is useful if the extraction step is unable to complete for some reason.' ); ?><br>
		<input type="checkbox" name="skip_database_import" /> Skip import of database. <br>
		<input type="checkbox" name="skip_database_migration" /> Skip migration of database. <br>
		<input type="checkbox" name="skip_htaccess" /> Skip migration of .htaccess file. <br>
		<!-- TODO: <input type="checkbox" name="merge_databases" /> Ignore existing WordPress data & merge database.<?php $this->tip( 'This may result in data conflicts, lost database data, or incomplete restores.', 'WARNING' ); ?></a><br> -->
		<input type="checkbox" name="force_compatibility_medium" /> Force medium speed compatibility mode (ZipArchive). <br>
		<input type="checkbox" name="force_compatibility_slow" /> Force slow speed compatibility mode (PCLZip). <br>
		<?php //<input type="checkbox" name="force_high_security"> Force high security on a normal security backup<br> ?>
		<input type="checkbox" name="show_php_warnings" /> Show detailed PHP warnings. <br>
		<br>
		PHP Maximum Execution Time: <input type="text" name="max_execution_time" value="<?php echo $this->detected_max_execution_time; ?>" size="5"> seconds. <?php $this->tip( 'The maximum allowed PHP runtime. If your database import step is timing out then lowering this value will instruct the script to limit each `chunk` to allow it to finish within this time period.' ); ?>
		<br>
		Error Logging to importbuddy.txt: <select name="log_level">
			<option value="0">None</option>
			<option value="1" selected>Errors Only (default)</option>
			<option value="2">Errors & Warnings</option>
			<option value="3">Everything (debug mode)</option>
		</select> <?php $this->tip( 'Errors and other debugging information will be written to importbuddy.txt in the same directory as importbuddy.php.  This is useful for debugging any problems encountered during import.  Support may request this file to aid in tracking down any problems or bugs.' ); ?>
	</div>
	<?php
	echo '<div id="toggle-serverinfo" class="toggled" style="margin-top: 12px;">';
	require_once( 'view_tools-server.php' );
	echo '</div>';
	?>
</div>
<br>
<?php
echo '<br>';

// Warn of existing WordPress installation. Potential conflict.
$api->wordpress_exists();
echo '<br>';

// If one or more backup files was found then provide a button to continue.
if ( !empty( $backup_archives ) ) {
	echo '<p style="text-align: center;"><input type="submit" name="submit" value="Next Step &raquo;" class="button"></p>';
} else {
	echo '<p><i>Upload a backup file to continue.</i></p>';
}
echo '</form>';
?>