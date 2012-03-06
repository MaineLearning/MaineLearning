<?php
if ( !empty( $_GET['callback_data'] ) ) {
	$callback_data = $_GET['callback_data'];
} else {
	$callback_data = '';
}?>

<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>?action=pb_backupbuddy_remotedestination&callback_data=<?php if ( !empty( $_GET['callback_data'] ) ) { echo $_GET['callback_data']; } ?>#pb_backupbuddy_tab_s3">
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_destinations" value="<?php _e('Delete', 'it-l10n-backupbuddy');?>" class="button-secondary delete" />
		</div>
	</div>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th scope="col" class="check-column"><!-- input type="checkbox" class="check-all-entries" --></th>
				<?php
					echo '<th>', __('Name', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('AWS Keys', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Bucket', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Directory', 'it-l10n-backupbuddy'), '</th>',
				    	 '<th>', __('SSL', 'it-l10n-backupbuddy'), '</th>',
				    	 '<th>', __('Limit', 'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th scope="col" class="check-column"><!-- input type="checkbox" class="check-all-entries" --></th>
				<?php
					echo '<th>', __('Name', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('AWS Keys', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Bucket', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Directory', 'it-l10n-backupbuddy'), '</th>',
				    	 '<th>', __('SSL', 'it-l10n-backupbuddy'), '</th>',
				    	 '<th>', __('Limit', 'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$file_count = 0;
			foreach ( $this->_options['remote_destinations'] as $destination_id => $destination ) {
				if ( $destination['type'] != 's3' ) {
					continue;
				}
				
				$destination = array_merge( $this->_parent->_s3defaults, $destination );
				?>
				<tr class="entry-row alternate">
					<th scope="row" class="check-column"><input type="checkbox" name="destinations[]" class="entries" value="<?php echo $destination_id; ?>" /></th>
					<td>
						<?php echo $destination['title']; ?><br />
							<a href="<?php echo $destination_id; ?>" alt="<?php echo $destination['title'] . ' (Amazon S3)'; ?>" class="pb_backupbuddy_selectdestination"><?php _e('Select this destination', 'it-l10n-backupbuddy');?></a> |
							<a href="<?php echo admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&edit=' . htmlentities( $destination_id ); ?>&callback_data=<?php if ( !empty( $_GET['callback_data'] ) ) { echo $_GET['callback_data']; } ?>&type=s3#pb_backupbuddy_tab_s3"><?php _e('Edit Settings', 'it-l10n-backupbuddy');?></a>
					</td>
					<td style="white-space: nowrap;">
						<?php echo $destination['accesskey']; ?>
					</td>
					<td style="white-space: nowrap;">
						<?php echo $destination['bucket']; ?>
					</td>
					<td>
						<?php echo $destination['directory']; ?>
					</td>
					<td>
						<?php echo $destination['ssl']; ?>
					</td>
					<td>
						<?php
							echo $destination['archive_limit'];
						?>
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_destinations" value="<?php _e('Delete', 'it-l10n-backupbuddy'); ?>" class="button-secondary delete" />
		</div>
	</div>
	
	<?php $this->nonce(); ?>
</form>

<br />

<?php
if ( isset( $_GET['edit'] ) && ( $_GET['edit'] != '' ) && ( $_GET['type'] == 's3' ) ) {
	$options = array_merge( $this->_parent->_s3defaults, (array)$this->_options['remote_destinations'][$_GET['edit']] );
	
	echo '<h3>', __('Edit Destination', 'it-l10n-backupbuddy'), '</h3>';
	echo '<form method="post" action="' . admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&edit=' . htmlentities( $edit_id ) . '&callback_data=' . $callback_data . '&type=s3#pb_backupbuddy_tab_s3">';
	echo '	<input type="hidden" name="savepoint" value="remote_destinations#' . $_GET['edit'] . '" />';
} else {
	$options = $this->_parent->_s3defaults;
	
	echo '<h3>', __('Add New Destination ', 'it-l10n-backupbuddy') . $this->video( 'njT1ExMgUrk', __('Add a new Amazon S3 destination', 'it-l10n-backupbuddy'), false ) . '</h3>';
	echo '<form method="post" action="' . admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&callback_data=' . $callback_data . '&type=s3#pb_backupbuddy_tab_s3">';
}
?>
	<input type="hidden" name="#type" value="s3" />
	<table class="form-table">
		<tr>
			<td><label for="title"><?php _e('Destination Name', 'it-l10n-backupbuddy'); $this->tip( __('Name of the new destination to create. This is for your convenience only.', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#title" id="title" size="45" maxlength="45" value="<?php echo $options['title']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="accesskey"><?php _e('AWS Access Key', 'it-l10n-backupbuddy'); $this->tip( __('[Example: BSEGHGSDEUOXSQOPGSBE] - Log in to your Amazon S3 AWS Account and navigate to Account: Access Credentials: Security Credentials.', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#accesskey" id="accesskey" size="45" maxlength="45" value="<?php echo $options['accesskey']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="secretkey"><?php _e('AWS Secret Key', 'it-l10n-backupbuddy'); $this->tip( __('[Example: GHOIDDWE56SDSAZXMOPR] - Log in to your Amazon S3 AWS Account and navigate to Account: Access Credentials: Security Credentials.', 'it-l10n-backupbuddy') ); echo ' '; $this->video( 'Tp_VkLoBEpw', __('Find your Amazon S3 key', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="password" name="#secretkey" id="secretkey" size="45" maxlength="45" value="<?php echo $options['secretkey']; ?>" /> <a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?ie=UTF8&action=access-key" target="_blank" title="<?php _e('Opens a new tab where you can get your Amazon S3 key', 'it-l10n-backupbuddy'); ?>"><small><?php _e('Get Key', 'it-l10n-backupbuddy');?></small></a></td>
		</tr>
		<tr>
			<td><label for="bucket"><?php _e('Bucket Name', 'it-l10n-backupbuddy'); $this->tip( __('[Example: wordpress_backups] - This bucket will be created for you automatically if it does not already exist.', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#bucket" id="bucket" size="45" maxlength="45" value="<?php echo $options['bucket']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="directory"><?php _e('Directory (optional)', 'it-l10n-backupbuddy'); $this->tip( __('[Example: backupbuddy] - Directory name to place the backup within.', 'it-l10n-backupbuddy') ); echo ' '; $this->video( 'njT1ExMgUrk#20', __('Create an Amazon S3 bucket', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#directory" id="directory" size="45" maxlength="250" value="<?php echo $options['directory']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="archive_limit"><?php _e('Archive Limit', 'it-l10n-backupbuddy'); $this->tip( __('[Example: 5] - Enter 0 for no limit. This is the maximum number of archives to be stored in this specific destination. If this limit is met the oldest backups will be deleted.', 'it-l10n-backupbuddy') ); echo ' '; ?></label></td>
			<td><input type="text" name="#archive_limit" id="archive_limit" size="45" maxlength="6" value="<?php echo $options['archive_limit']; ?>" /></td>
		</tr>
		<tr>
			<td valign="top"><label><?php _e('Use SSL Encryption', 'it-l10n-backupbuddy'); $this->tip( __('[Default: enabled] - When enabled, all transfers will be encrypted with SSL encryption. Please note that encryption introduces overhead and may slow down the transfer. If Amazon S3 sends are failing try disabling this feature to speed up the process.  Note that 32-bit servers cannot encrypt transfers of 2GB or larger with SSL, causing large file transfers to fail.', 'it-l10n-backupbuddy') ); ?></label></td>
			<td>
				<input type="hidden" name="#ssl" value="0" />
				<input type="checkbox" name="#ssl" id="ssl" value="1" <?php if ( $options['ssl'] == '1' ) { echo 'checked'; } ?> /> <label for="high_security"><?php _e('Enable high security mode', 'it-l10n-backupbuddy'); ?></label>
			</td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td>
				<input value="<?php _e('Test these settings', 'it-l10n-backupbuddy'); ?>" class="button-secondary pb_backupbuddy_remotetest" id="pb_backupbuddy_remotetest_s3" type="submit" alt="<?php echo admin_url('admin-ajax.php').'?action=pb_backupbuddy_remotetest&service=s3'; ?>" />
			</td>
		</tr>
		
	</table>
	
	<?php
	if ( isset( $_GET['edit'] ) && ( $_GET['edit'] != '' ) && ( $_GET['type'] == 's3' ) ) {
		echo '<p class="submit"><input type="submit" name="edit_destination" value="', __('Save Changes', 'it-l10n-backupbuddy'), '" class="button-primary" /></p>';
	} else {
		echo '<p class="submit"><input type="submit" name="add_destination" value="+ ', __('Add Destination', 'it-l10n-backupbuddy'), '" class="button-primary" /></p>';
	}
	
	$this->nonce();
	?>
</form>

