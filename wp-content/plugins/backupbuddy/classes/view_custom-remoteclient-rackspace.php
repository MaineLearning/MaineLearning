<?php
$this->title( 'Rackspace Cloudfiles' );

echo '<h3>Editing ' . $destination['title'] . ' (' . $destination['type'] . ')</h3>';
	
	$destination = array_merge( $this->_parent->_rackspacedefaults, $destination ); // load defaults
	
	// Rackspace information
	$rs_username = $destination['username'];
	$rs_api_key = $destination['api_key'];
	$rs_container = $destination['container'];
	$rs_server = $destination['server'];
	/*
	if ( isset( $destination['server'] ) ) {
		$rs_server = $destination['server'];
	} else {
		$rs_server = 'https://auth.api.rackspacecloud.com';
	}
	$rs_path = ''; //$destination['path'];
	*/
	
	require_once($this->_parent->_pluginPath . '/lib/rackspace/cloudfiles.php');
	$auth = new CF_Authentication( $rs_username, $rs_api_key );
	$auth->authenticate();
	$conn = new CF_Connection( $auth );
	
	// Set container
	$container = @$conn->get_container($rs_container);
	
	// Delete Rackspace backups
	if ( !empty( $_POST['delete_file'] ) ) {
		$delete_count = 0;
		if ( !empty( $_POST['files'] ) && is_array( $_POST['files'] ) ) {	
			// loop through and delete Rackspace files
			foreach ( $_POST['files'] as $rsfile ) {
				$delete_count++;
				// Delete Rackspace file
				$container->delete_object($rsfile);
			}
		}
		if ( $delete_count > 0 ) {
			$this->alert( sprintf( _n('Deleted %d file', 'Deleted %d files', $delete_count, 'it-l10n-backupbuddy'), $delete_count) );
		}
	}
	
	// Copy Rackspace backup to the local backup files
	if ( !empty( $_GET['copy_file'] ) ) {
		$this->alert( sprintf( _x('The remote file is now being copied to your %1$slocal backups%2$s', '%1$s and %1$s are open and close <a> tags', 'it-l10n-backupbuddy'), '<a href="' . $this->_selfLink . '-backup">', '</a>.' ) );
		$this->log( 'Scheduling Cron for creating Rackspace copy.' );
		wp_schedule_single_event( time(), $this->_parent->_var . '-cron_rackspace_copy', array( $_GET['copy_file'], $rs_username, $rs_api_key, $rs_container, $rs_path, $rs_server ) );
		spawn_cron( time() + 150 ); // Adds > 60 seconds to get around once per minute cron running limit.
		update_option( '_transient_doing_cron', 0 ); // Prevent cron-blocking for next item.
	}
	
	// List objects in container
	if ( $rs_path != '' ) {
		$results = $container->get_objects( 0, NULL, 'backup-', $rs_path );
	} else {
		$results = $container->get_objects( 0, NULL, 'backup-');
	}

?>
<div style="max-width: 950px;">
	<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink . '&custom=' . $_GET['custom'] . '&destination_id=' . $_GET['destination_id'];?>">
		<div class="tablenav">
			<div class="alignleft actions">
				<input type="submit" name="delete_file" value="Delete from Rackspace" class="button-secondary delete" />
			</div>
		</div>
		<table class="widefat">
			<thead>
				<tr class="thead">
					<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th>Backup File <img src="<?php echo $this->_pluginURL; ?>/images/sort_down.png" style="vertical-align: 0px;" title="Sorted by filename" /></th>
					<th>Last Modified</th>
					<th>File Size</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tfoot>
				<tr class="thead">
					<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
					<th>Backup File <img src="<?php echo $this->_pluginURL; ?>/images/sort_down.png" style="vertical-align: 0px;" title="Sorted by filename" /></th>
					<th>Last Modified</th>
					<th>File Size</th>
					<th>Actions</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				
				if ( empty( $results ) ) {
					echo '<tr><td colspan="5" style="text-align: center;"><i>You have not created any Rackspace backups yet.</i></td></tr>';
				} else {
					$file_count = 0;
					foreach ( (array) $results as $backup ) {
						$file_count++;
						?>
						<tr class="entry-row alternate">
							<th scope="row" class="check-column"><input type="checkbox" name="files[]" class="entries" value="<?php echo $backup->name; ?>" /></th>
							<td><?php echo $backup->name; ?></td>
							<td style="white-space: nowrap;">
								<?php
									echo $this->_parent->format_date( $this->_parent->localize_time( strtotime($backup->last_modified) ) );
									echo '<br /><span style="color: #AFAFAF;">(' . $this->_parent->time_ago( strtotime($backup->last_modified) ) . ' ago)</span>';
								?>
							</td>
							<td style="white-space: nowrap;">
								<?php echo $this->_parent->format_size( $backup->content_length ); ?>
							</td>
							<td>
								<?php echo '<a href="' . $this->_selfLink . '&custom=' . $_GET['custom'] . '&destination_id=' . $_GET['destination_id'] . '&#38;copy_file=' . $backup->name . '">Copy to local</a>'; ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="alignleft actions">
				<input type="submit" name="delete_file" value="Delete from Rackspace" class="button-secondary delete" />
			</div>
		</div>
		
		<?php $this->nonce(); ?>
	</form><br />
</div>
