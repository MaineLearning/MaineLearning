<?php
$this->title( 'Amazon S3' );
		
// S3 information
$aws_accesskey = $destination['accesskey'];
$aws_secretkey = $destination['secretkey'];
$aws_bucket = $destination['bucket'];
$aws_directory = $destination['directory'];
if ( !empty( $aws_directory ) ) {
	$aws_directory = $aws_directory . '/';
}

require_once( $this->_pluginPath . '/lib/s3/s3.php' );
$s3 = new S3( $aws_accesskey, $aws_secretkey);

// Delete S3 backups
if ( !empty( $_POST['delete_file'] ) ) {
	$delete_count = 0;
	if ( !empty( $_POST['files'] ) && is_array( $_POST['files'] ) ) {
		// loop through and delete s3 files
		foreach ( $_POST['files'] as $s3file ) {
			$delete_count++;
			// Delete S3 file
			$s3->deleteObject($aws_bucket, $s3file);
		}
	}
	if ( $delete_count > 0 ) {
		$this->alert( sprintf( _n('Deleted %d file.', 'Deleted %d files.', $delete_count, 'it-l10n-backupbuddy') , $delete_count ) );
	}
}

// Copy S3 backups to the local backup files
if ( !empty( $_GET['copy_file'] ) ) {
	$this->alert( sprintf( __('The remote file is now being copied to your %s local backups', 'it-l10n-backupbuddy'), '<a href="' . $this->_selfLink . '-backup">') . '</a>.' );
	$this->log( 'Scheduling Cron for creating s3 copy.' );
	wp_schedule_single_event( time(), $this->_parent->_var . '-cron_s3_copy', array( $_GET['copy_file'], $aws_accesskey, $aws_secretkey, $aws_bucket, $aws_directory) );
	spawn_cron( time() + 150 ); // Adds > 60 seconds to get around once per minute cron running limit.
	update_option( '_transient_doing_cron', 0 ); // Prevent cron-blocking for next item.
}

echo '<h3>', __('Editing', 'it-l10n-backupbuddy'), ' ' . $destination['title'] . ' (' . $destination['type'] . ')</h3>';
?>
<div style="max-width: 950px;">
<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink . '&custom=' . $_GET['custom'] . '&destination_id=' . $_GET['destination_id'];?>">
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_file" value="<?php _e('Delete from S3', 'it-l10n-backupbuddy');?>" class="button-secondary delete" />
		</div>
	</div>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<?php
					echo '<th>', __('Backup File',   'it-l10n-backupbuddy'), '<img src="', $this->_pluginURL, '/images/sort_down.png" style="vertical-align: 0px;" title="', __('Sorted by filename', 'it-l10n-backupbuddy') ,'" /></th>',
						 '<th>', __('Last Modified', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('File Size',     'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Actions',       'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
				<?php
					echo '<th>', __('Backup File',   'it-l10n-backupbuddy'), '<img src="', $this->_pluginURL, '/images/sort_down.png" style="vertical-align: 0px;" title="', __('Sorted by filename', 'it-l10n-backupbuddy') ,'" /></th>',
						 '<th>', __('Last Modified', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('File Size',     'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Actions',       'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</tfoot>
		<tbody>
			<?php
			// List s3 backups
			$results = $s3->getBucket( $aws_bucket);
			
			if ( empty( $results ) ) {
				echo '<tr><td colspan="5" style="text-align: center;"><i>', __('You have not created any S3 backups yet.', 'it-l10n-backupbuddy'), '</i></td></tr>';
			} else {
				$file_count = 0;
				foreach ( (array) $results as $rekey => $reval ) {
					// check if file is backup
					$pos = strpos( $rekey, $aws_directory . 'backup-' );
					if ( $pos !== FALSE ) {
						$file_count++;
						?>
						<tr class="entry-row alternate">
							<th scope="row" class="check-column"><input type="checkbox" name="files[]" class="entries" value="<?php echo $rekey; ?>" /></th>
							<td>
								<?php
									$bubup = str_replace( $aws_directory . 'backup-', 'backup-', $rekey );
									echo $bubup;
								?>
							</td>
							<td style="white-space: nowrap;">
								<?php
									echo $this->_parent->format_date( $this->_parent->localize_time( $results[$rekey]['time'] ) );
									echo '<br /><span style="color: #AFAFAF;">(' . $this->_parent->time_ago( $results[$rekey]['time'] ) . ' ago)</span>';
								?>
							</td>
							<td style="white-space: nowrap;">
								<?php echo $this->_parent->format_size( $results[$rekey]['size'] ); ?>
							</td>
							<td>
								<?php echo '<a href="' . $this->_selfLink . '&custom=' . $_GET['custom'] . '&destination_id=' . $_GET['destination_id'] . '&#38;copy_file=' . $bubup . '">', __('Copy to local', 'it-l10n-backupbuddy'), '</a>'; ?>
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
			<input type="submit" name="delete_file" value="Delete from S3" class="button-secondary delete" />
		</div>
	</div>
	
	<?php $this->nonce(); ?>
</form><br />
</div>
