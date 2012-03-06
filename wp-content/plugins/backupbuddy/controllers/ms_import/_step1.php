<?php
//STEP 1
$form_url = add_query_arg( array( 'action' => 'step2' ) , $this->_selfLink . '-msimport' );
global $current_site;
?>

<?php _e( 'Multisite import allows you to import a site from a BackupBuddy backup archive as a new site within this Multisite network with a new URL.  Please upload your BackupBuddy backup archive into the root of your site before proceeding.', 'it-l10n-backupbuddy' ) ?><br><br>

<form method="post" action="<?php echo esc_url( $form_url ); ?>">
<?php wp_nonce_field( 'bbms-migration', 'pb_bbms_migrate' ); ?>
<table class="form-table">
	<tr class="form-field form-required">
		<th scope="row">Backup file to import</th>
		<td>
		<?php
		$files = glob( ABSPATH . '/backup*.zip' );
		if ( !is_array( $files ) || empty( $files ) ) {
			$files = array();
		}
		foreach( $files as $i => $file ) {
			$file = basename( $file );
			?>
			<input style="width: auto;" type='radio' id='backup_<?php echo esc_attr( $i ); ?>' value='<?php echo esc_attr( $file );?>'
			<?php
			if ( count( $files ) == 1 ) {
				echo ' CHECKED';
			}
			?> name='backup_file' />&nbsp;&nbsp;<label for='backup_<?php echo esc_attr( $i ); ?>'><?php echo esc_html( $file ); ?></label><br />
			<?php
		}
		if ( count( $files ) == 0 ) {
			_e( 'No BackupBuddy backups found in the root directory of the site. Please upload your backup and refresh to continue.', 'it-l10n-backupbuddy' );
		}
		?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e( 'New site address', 'it-l10n-backupbuddy' ) ?></th>
		<td>
		<?php if ( is_subdomain_install() ) { ?>
			<span>	 </span><input name="blog[domain]" type="text" class="regular-text" title="<?php _e( 'Domain', 'it-l10n-backupbuddy' ) ?>" style="width: 25em;">
		<?php } else {
			echo 'http://' . $current_blog->domain . $current_blog->path ?><input name="blog[domain]" class="regular-text" type="text" title="<?php _e( 'Domain', 'it-l10n-backupbuddy' ) ?>" style="width: 25em;">
		<?php }
		
		echo '<p class="description">' . __( 'Only the characters a-z and 0-9 recommended.', 'it-l10n-backupbuddy' ) . '</p>';
		?>		
		<p class='description'><?php esc_html_e( 'If the site already exists and is mapped into a different domain, simply use the domain name (e.g., jubyo.com)', 'it-l10n-backupbuddy' ); ?></p>
		
		<br>
		
		<?php // These advanced options will be available via $this->advanced_options from within the msimport class on each page. Passed along in the submitted form per step. ?>
		<span class="toggle button-secondary" id="advanced">Advanced Configuration Options</span>
			<div id="toggle-advanced" class="toggled" style="margin-top: 12px; width: 600px;">
				<b>WARNING:</b> Improper use of Advanced Options could result in data loss.<br><br>
				<input type="hidden" name="advanced_options[skip_files]" value="false">
				<input type="checkbox" name="advanced_options[skip_files]" value="true"> Skip zip file extraction. <?php $this->_parent->tip( 'Checking this box will prevent extraction/unzipping of the backup ZIP file.  You will need to manually extract it either on your local computer then upload it or use a server-based tool such as cPanel to extract it. This feature is useful if the extraction step is unable to complete for some reason.' ); ?><br>
				
				<input type="hidden" name="advanced_options[ignore_sql_errors]" value="false">
				<input type="checkbox" name="advanced_options[ignore_sql_errors]" value="true" /> Ignore SQL errors & hide them. <br>
				
				<input type="hidden" name="advanced_options[skip_database_import]" value="false">
				<input type="checkbox" name="advanced_options[skip_database_import]" value="true" /> Skip import of database. <br>
				
				<input type="hidden" name="advanced_options[skip_database_migration]" value="false">
				<input type="checkbox" name="advanced_options[skip_database_migration]" value="true" /> Skip migration of database. <br>
				
				<input type="hidden" name="advanced_options[force_compatibility_medium]" value="false">
				<input type="checkbox" name="advanced_options[force_compatibility_medium]" value="true" /> Force medium speed compatibility mode (ZipArchive). <br>
				
				<input type="hidden" name="advanced_options[force_compatibility_slow]" value="false">
				<input type="checkbox" name="advanced_options[force_compatibility_slow]" value="true" /> Force slow speed compatibility mode (PCLZip). <br>
				
				<br>
				PHP Maximum Execution Time: <input type="text" name="advanced_options[max_execution_time]" value="<?php echo $this->detected_max_execution_time; ?>" size="5"> seconds. <?php $this->_parent->tip( 'The maximum allowed PHP runtime. If your database import step is timing out then lowering this value will instruct the script to limit each `chunk` to allow it to finish within this time period.' ); ?>
			</div>
		</td>
	</tr>
</table>
<?php submit_button( __('Next Step') . ' &raquo;', 'primary', 'add-site' ); ?>
</form>