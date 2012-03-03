<?php
if ( !empty( $_GET['callback_data'] ) ) {
	$callback_data = $_GET['callback_data'];
} else {
	$callback_data = '';
}

require_once( $this->_pluginPath . '/lib/dropbuddy/dropbuddy.php' );
?>


<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('.pb_dropbox_authorize').click(function(e) {
			jQuery('.pb_dropbox_authorize').hide();
			jQuery('#pb_dropbox_authorize').slideDown();
		});
	});
</script>

<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>?action=pb_backupbuddy_remotedestination&callback_data=<?php if ( !empty( $_GET['callback_data'] ) ) { echo $_GET['callback_data']; } ?>#pb_backupbuddy_tab_dropbox">
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_destinations" value="Delete" class="button-secondary delete" />
		</div>
	</div>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th scope="col" class="check-column"><!-- input type="checkbox" class="check-all-entries" --></th>
				<?php
					echo
						'<th>', __('Name', 'it-l10n-backupbuddy'), '</th>',
						'<th>', __('Owner', 'it-l10n-backupbuddy'), '</th>',
						'<th>', __('Directory', 'it-l10n-backupbuddy'), '</th>',
						'<th>', __('Usage', 'it-l10n-backupbuddy'), '</th>',
						'<th>', __('Limit', 'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th scope="col" class="check-column"><!-- input type="checkbox" class="check-all-entries" --></th>
				<?php
					echo
						'<th>', __('Name', 'it-l10n-backupbuddy'), '</th>',
						'<th>', __('Owner', 'it-l10n-backupbuddy'), '</th>',
						'<th>', __('Directory', 'it-l10n-backupbuddy'), '</th>',
						'<th>', __('Usage', 'it-l10n-backupbuddy'), '</th>',
						'<th>', __('Limit', 'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</tfoot>
		<tbody>
			<?php
			
			$file_count = 0;
			foreach ( $this->_options['remote_destinations'] as $destination_id => $destination ) {
				if ( $destination['type'] != 'dropbox' ) {
					continue;
				}
				
				$destination = array_merge( $this->_parent->_dropboxdefaults, $destination );
				
				$dropbuddy = new pluginbuddy_dropbuddy( $this, $destination['token'] );
				if ( $dropbuddy->authenticate() === true ) {
					$account_info = $dropbuddy->get_account_info();
				} else {
					$account_info = false;
				}
				?>
				<tr class="entry-row alternate">
					<th scope="row" class="check-column"><input type="checkbox" name="destinations[]" class="entries" value="<?php echo $destination_id; ?>" /></th>
					<td>
						<?php echo $destination['title']; ?><br />
							<a href="<?php echo $destination_id; ?>" alt="<?php echo $destination['title'] . ' (' . __('Dropbox', 'it-l10n-backupbuddy') . ')'; ?>" class="pb_backupbuddy_selectdestination"><?php _e('Select this destination', 'it-l10n-backupbuddy');?></a> |
							<a href="<?php echo admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&edit=' . htmlentities( $destination_id ); ?>&callback_data=<?php if ( !empty( $_GET['callback_data'] ) ) { echo $_GET['callback_data']; } ?>&type=dropbox#pb_backupbuddy_tab_dropbox"><?php _e('Edit Settings', 'it-l10n-backupbuddy');?></a>
					</td>
					<td style="white-space: nowrap;">
						<?php
							if ( $account_info === false ) {
								echo __('Access Denied', 'it-l10n-backupbuddy');
							} else {
								echo $account_info['display_name'] . '<br>';
								echo '<a href="' . $account_info['referral_link'] . '" target="_new">' . __('Referral Link', 'it-l10n-backupbuddy') . '</a>';
							}
						?>
					</td>
					<td style="white-space: nowrap;">
						<?php
							echo $destination['directory'];
						?>
					</td>
					<td style="white-space: nowrap;">
						<?php
							if ( $account_info === false ) {
								echo __('Access Denied', 'it-l10n-backupbuddy');
							} else {
								echo $this->_parent->format_size( $account_info['quota_info']['normal'] ) . ' / ' . $this->_parent->format_size( $account_info['quota_info']['quota'] ) . ' (' . round( ( $account_info['quota_info']['normal'] / $account_info['quota_info']['quota'] ) * 100, 2 ) . '%)';
							}
						?>
					</td>
					<td>
						<?php
							echo $destination['archive_limit'];
						?>
					</td>
				</tr>
				<?php
				unset( $dropbuddy );
				unset( $account_info );
			}
			?>
		</tbody>
	</table>
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_destinations" value="<?php _e('Delete', 'it-l10n-backupbuddy');?>" class="button-secondary delete" />
		</div>
	</div>
	
	<?php $this->nonce(); ?>
</form>








<br>
<?php
// CALCULATE MEMORY. **********************************************
$this_val = ini_get( 'memory_limit' );
if ( preg_match( '/(\d+)(\w*)/', $this_val, $matches ) ) {
	$this_val = $matches[1];
	$unit = $matches[2];

	if ( 'g' == strtolower( $unit ) ) {
		// Convert GB to MB.
		$this_val = $this_val = $this_val * 1024;
	}
} else {
	$limit = 0;
}

$memory_usage = memory_get_peak_usage() / 1048576;
$memory_limit = $this_val;
$memory_free = $this_val - $memory_usage;
$memory_hypothesis = $memory_free - 2 - ( $memory_free * .10 ); // Free memory minus 2MB minus a 10% free memory wiggle room. Underestimate.

$this->alert( sprintf( __( 'Note: Backup files must be fully loaded into memory to transfer to Dropbox. BackupBuddy estimates you will be able to transfer backups up to %s with your current memory limit of %s (BackupBuddy will attempt to increase this during transfer if possible). This is a limitation of PHP\'s oAuth implementation which Dropbox requires. Increasing the PHP memory limit or reducing backup file size are currently the only workarounds.', 'it-l10n-backupbuddy' ), round( $memory_hypothesis, 0 ) . ' MB', $memory_limit . ' MB' ) );
// END MEMORY CALCULATIONS. **********************************************
?>
<br>










<a href="http://dropbox.com" target="_new" title="<?php _e('BackupBuddy works with Dropbox.com', 'it-l10n-backupbuddy');?>"><img src="<?php echo $this->_pluginURL; ?>/images/dropbox_white.png" style="float: right;" /></a>

<?php


$next_index = end( array_keys( $this->_options['remote_destinations'] ) ) + 1;
if ( empty( $next_index ) ) {
	$next_index = 0;
}

/*
echo '<pre>';
print_r( $this->_options['dropboxtemptoken'] );
echo '</pre>';
*/

$dropbuddy = new pluginbuddy_dropbuddy( $this, $this->_options['dropboxtemptoken'] ); //  $this, $this->_parent->_options['dropbox_token'] 

$dropbox_connected = false;
if ( empty( $this->_options['dropboxtemptoken'] ) ) {
	//echo '<div class="pb_dropbox_authorize"><a href="' . $dropbuddy->get_authorize_url() . '" class="button-primary" target="_new">Connect to Dropbox & Authorize</a></div>';
} else {
	//echo 'Existing token found. Trying to use it!';
	if ( $dropbuddy->authenticate() === true ) {
		$dropbox_connected = true;
		//echo 'Authorized & connected to Dropbox!<br><br>';
		
		$account_info = $dropbuddy->get_account_info();
		//echo '<div class="pb_dropbox_authorize"><a href="' . $dropbuddy->get_authorize_url() . '" class="button-primary" target="_new">Re-Authorize with Dropbox</a></div>';
	} else {
		//echo 'Access Denied. Did you authenticate via the URL?<br><br>';
		if ( isset( $_GET['dropbox_auth'] ) && ( $_GET['dropbox_auth'] == 'true' ) ) {
			// do nothing
		} else {
			echo '<br><div class="pb_dropbox_authorize"><a href="' . $dropbuddy->get_authorize_url() . '" class="button-primary" target="_new">' . __('Connect to Dropbox & Authorize', 'it-l10n-backupbuddy') . '</a></div>';
		}
	}
}
echo '<div id="pb_dropbox_authorize" style="display: none;"><a href="' . admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&callback_data=' . $callback_data . '&t=' . time() . '&dropbox_auth=true#pb_backupbuddy_tab_dropbox" class="button-primary">' . __("Yes, I've Authorized BackupBuddy with Dropbox", 'it-l10n-backupbuddy') . '</a></div>';
echo '<br><br>';

$hide_add = false;
if ( isset( $_GET['edit'] ) && ( $_GET['edit'] != '' ) && ( $_GET['type'] == 'dropbox' ) ) {
	$options = array_merge( $this->_parent->_dropboxdefaults, (array)$this->_options['remote_destinations'][$_GET['edit']] );
	
	echo '<h3>' . __('Edit Destination', 'it-l10n-backupbuddy') . '</h3>';
	echo '<form method="post" action="' . admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&edit=' . htmlentities( $edit_id ) . '&callback_data=' . $callback_data . '&type=dropbox#pb_backupbuddy_tab_dropbox">';
	echo '	<input type="hidden" name="savepoint" value="remote_destinations#' . $_GET['edit'] . '" />';
} else {
	if ( $dropbox_connected === true ) {
		$options = $this->_parent->_dropboxdefaults;
		
		echo '<h3>' . __('Add New Destination', 'it-l10n-backupbuddy') . ' ' . $this->video( '', 'Add a new Dropbox destination', false ) . '</h3>';
		echo '<form method="post" action="' . admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&callback_data=' . $callback_data . '&type=dropbox#pb_backupbuddy_tab_dropbox">';
	} else {
		$hide_add = true;
	}
}

if ( $hide_add !== true ) {
	if ( !isset( $account_info ) ) {
		$dropbuddy = new pluginbuddy_dropbuddy( $this, $this->_options['remote_destinations'][$_GET['edit']]['token'] );
		if ( $dropbuddy->authenticate() === true ) {
			$dropbox_connected = true;
			$account_info = $dropbuddy->get_account_info();
		} else {
			echo __('Dropbox Access Denied', 'it-l10n-backupbuddy');
		}
	}
?>
	<input type="hidden" name="#type" value="dropbox" />
	<table class="form-table">
		<tr>
			<td><label><?php _e('Dropbox Owner', 'it-l10n-backupbuddy');?></label></td>
			<td><?php echo $account_info['display_name'] . ' (UID: ' . $account_info['uid'] . ') [<a href="' . $account_info['referral_link'] . '" target="_new">' . __('Referral Link', 'it-l10n-backupbuddy') .'</a>]'; ?></td>
		</tr>
		<tr>
			<td><label><?php _e('Email', 'it-l10n-backupbuddy');?></label></td>
			<td><?php echo $account_info['email']; ?></td>
		</tr>
		<tr>
			<td><label><?php _e('Quota Usage', 'it-l10n-backupbuddy');?></label></td>
			<td><?php echo $this->_parent->format_size( $account_info['quota_info']['normal'] ) . ' / ' . $this->_parent->format_size( $account_info['quota_info']['quota'] ) . ' (' . round( ( $account_info['quota_info']['normal'] / $account_info['quota_info']['quota'] ) * 100, 2 ); ?>%)</td>
		</tr>
	
	
		<tr>
			<td><label for="title"><?php _e('Destination Name', 'it-l10n-backupbuddy'); $this->tip( __('Name of the new destination to create. This is for your convenience only.', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#title" id="title" size="45" maxlength="45" value="<?php echo $options['title']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="directory"><?php _e('Directory (optional)', 'it-l10n-backupbuddy'); $this->tip( __('[Example: backupbuddy] - Directory name to place the backup within.', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#directory" id="directory" size="45" maxlength="250" value="<?php echo $options['directory']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="archive_limit"><?php _e('Archive Limit', 'it-l10n-backupbuddy'); $this->tip( __('[Example: 5] - Enter 0 for no limit. This is the maximum number of archives to be stored in this specific destination. If this limit is met the oldest backups will be deleted.', 'it-l10n-backupbuddy') ); echo ' '; ?></label></td>
			<td><input type="text" name="#archive_limit" id="archive_limit" size="45" maxlength="6" value="<?php echo $options['archive_limit']; ?>" /></td>
		</tr>
		
	</table>
	
	<?php
	if ( isset( $_GET['edit'] ) && ( $_GET['edit'] != '' ) && ( $_GET['type'] == 'dropbox' ) ) {
		echo '<p class="submit"><input type="submit" name="edit_destination" value="', __('Save Changes', 'it-l10n-backupbuddy'), '" class="button-primary" /></p>';
	} else {
		echo '<p class="submit">';
		echo '	<input type="submit" name="add_destination" value="+ ', __('Add Destination', 'it-l10n-backupbuddy'), '" class="button-primary" />';
		echo '	&nbsp;&nbsp;&nbsp;<a href="' . admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&clear_dropboxtemptoken=true&callback_data=' . $callback_data . '#pb_backupbuddy_tab_dropbox" class="button-secondary">', __('Re-authenticate Dropbox', 'it-l10n-backupbuddy'), '</a>';
		echo '</p>';
	}
	
	$this->nonce();
	?>
</form>
<?php } // end if ( $hide_add !== true ) { ?>

