<?php
$this->title( 'Dropbox' );

require_once( $this->_pluginPath . '/lib/dropbuddy/dropbuddy.php' );
$dropbuddy = new pluginbuddy_dropbuddy( $this, $destination['token'] );
if ( $dropbuddy->authenticate() === true ) {
	$account_info = $dropbuddy->get_account_info();
} else {
	$account_info = false;
}

$meta_data = $dropbuddy->get_meta_data( $destination['directory'] );


/*
echo '<pre>';
print_r( $meta_data ) );
echo '</pre>';
*/

// Delete dropbox backups
if ( !empty( $_POST['delete_file'] ) ) {
	$delete_count = 0;
	if ( !empty( $_POST['files'] ) && is_array( $_POST['files'] ) ) {
		// loop through and delete dropbox files
		foreach ( $_POST['files'] as $dropboxfile ) {
			$delete_count++;
			// Delete dropbox file
			$dropbuddy->delete( $dropboxfile );
		}
	}
	if ( $delete_count > 0 ) {
		$this->alert( sprintf( _n('Deleted %d file', 'Deleted %d files', $delete_count, 'it-l10n-backupbuddy'), $delete_count) );
		$meta_data = $dropbuddy->get_meta_data( $destination['directory'] ); // Refresh listing.
	}
}

// Copy dropbox backups to the local backup files
if ( !empty( $_GET['copy_file'] ) ) {
	$this->alert( sprintf( __('The remote file is now being copied to your %s local backups', 'it-l10n-backupbuddy') , '<a href="' . $this->_selfLink . '-backup">') . 'local backups</a>.' );
	$this->log( 'Scheduling Cron for creating Dropbox copy.' );
	wp_schedule_single_event( time(), $this->_parent->_var . '-cron_dropbox_copy', array( $_GET['destination_id'], $_GET['copy_file'] ) );
	spawn_cron( time() + 150 ); // Adds > 60 seconds to get around once per minute cron running limit.
	update_option( '_transient_doing_cron', 0 ); // Prevent cron-blocking for next item.
}

echo '<h3>', __('Editing', 'it-l10n-backupbuddy'),' ' . $destination['title'] . ' (' . $destination['type'] . ')</h3>';
?>

<div style="max-width: 950px;">
<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink . '&custom=' . $_GET['custom'] . '&destination_id=' . $_GET['destination_id'];?>">
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_file" value="<?php _e('Delete from Dropbox', 'it-l10n-backupbuddy');?>" class="button-secondary delete" />
		</div>
	</div>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<?php 
					echo '<th>', __('Backup File', 'it-l10n-backupbuddy'), '<img src="', $this->_pluginURL, '/images/sort_down.png" style="vertical-align: 0px;" title="', __('Sorted by filename', 'it-l10n-backupbuddy'), '" /></th>',
						 '<th>', __('Last Modified', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('File Size', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Actions', 'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<?php
					echo '<th>', __('Backup File', 'it-l10n-backupbuddy'), '<img src="', $this->_pluginURL, '/images/sort_down.png" style="vertical-align: 0px;" title="', __('Sorted by filename', 'it-l10n-backupbuddy'), '" /></th>',
						 '<th>', __('Last Modified', 'it-l10n-backupbuddy'),'</th>',
						 '<th>', __('File Size', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Actions', 'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</tfoot>
		<tbody>
			<?php
			// List dropbox backups
			if ( empty( $meta_data['contents'] ) ) {
				echo '<tr><td colspan="5" style="text-align: center;"><i>', __('You have not created any dropbox backups yet.', 'it-l10n-backupbuddy') ,' </i></td></tr>';
			} else {
				$file_count = 0;
				foreach ( (array) $meta_data['contents'] as $file ) {
					// check if file is backup
					if ( strstr( $file['path'], 'backup-' ) ) {
						$file_count++;
						?>
						<tr class="entry-row alternate">
							<th scope="row" class="check-column"><input type="checkbox" name="files[]" class="entries" value="<?php echo $file['path']; ?>" /></th>
							<td>
								<?php
									echo str_replace( '/' . $destination['directory'] . '/', '', $file['path'] );
								?>
							</td>
							<td style="white-space: nowrap;">
								<?php
									$modified = strtotime( $file['modified'] );
									echo $this->_parent->format_date( $this->_parent->localize_time( $modified ) );
									echo '<br /><span style="color: #AFAFAF;">(' . $this->_parent->time_ago( $modified ) . ' ', __('ago', 'it-l10n-backupbuddy'), ')</span>';
								?>
							</td>
							<td style="white-space: nowrap;">
								<?php echo $this->_parent->format_size( $file['bytes'] ); ?>
							</td>
							<td>
								<?php echo '<a href="' . $this->_selfLink . '&custom=' . $_GET['custom'] . '&destination_id=' . $_GET['destination_id'] . '&#38;copy_file=' . $file['path'] . '">',__('Copy to local', 'it-l10n-backupbuddy'), '</a>'; ?>
							</td>
						</tr>
						<?php
					}
				}
			}
			?>
		</tbody>
	</table>
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_file" value="<?php _e('Delete from Dropbox', 'it-l10n-backupbuddy');?>" class="button-secondary delete" />
		</div>
	</div>
	
	<?php $this->nonce(); ?>
</form><br />
</div>
