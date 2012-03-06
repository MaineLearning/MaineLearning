<?php
if ( !empty( $_GET['callback_data'] ) ) {
	$callback_data = $_GET['callback_data'];
} else {
	$callback_data = '';
}?>

<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>?action=pb_backupbuddy_remotedestination&callback_data=<?php if ( !empty( $_GET['callback_data'] ) ) { echo $_GET['callback_data']; } ?>">
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_destinations" value="<?php _e('Delete', 'it-l10n-backupbuddy');?>" class="button-secondary delete" />
		</div>
	</div>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th scope="col" class="check-column"><!-- input type="checkbox" class="check-all-entries" --></th>
				<th><?php _e('Name', 'it-l10n-backupbuddy');?></th>
				<th><?php _e('Email', 'it-l10n-backupbuddy');?></th>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th scope="col" class="check-column"><!-- input type="checkbox" class="check-all-entries" --></th>
				<th><?php _e('Name', 'it-l10n-backupbuddy');?></th>
				<th><?php _e('Email', 'it-l10n-backupbuddy');?></th>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$file_count = 0;
			foreach ( $this->_options['remote_destinations'] as $destination_id => $destination ) {
				if ( $destination['type'] != 'email' ) {
					continue;
				}
				
				$destination = array_merge( $this->_parent->_emaildefaults, $destination );
				
				?>
				<tr class="entry-row alternate">
					<th scope="row" class="check-column"><input type="checkbox" name="destinations[]" class="entries" value="<?php echo $destination_id; ?>" /></th>
					<td>
						<?php echo $destination['title']; ?><br />
							<a href="<?php echo $destination_id; ?>" alt="<?php echo $destination['title'] . ' (' . __('Email', 'it-l10n-backupbuddy') . ')'; ?>" class="pb_backupbuddy_selectdestination"><?php _e('Select this destination', 'it-l10n-backupbuddy');?></a> |
							<a href="<?php echo admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&edit=' . htmlentities( $destination_id ); ?>&callback_data=<?php if ( !empty( $_GET['callback_data'] ) ) { echo $_GET['callback_data']; } ?>&type=email#pb_backupbuddy_tab_email"><?php _e('Edit Settings', 'it-l10n-backupbuddy');?></a>
					</td>
					<td style="white-space: nowrap;">
						<?php echo $destination['email']; ?>
					</td>
				</tr>
				<?php
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

<p>
	<?php $this->alert( __( 'Important: Large files typically cannot be sent by email. If your backups are larger than approximately 10MB you will likely encounter failures in sending. This is a limitation of email and most servers.', 'it-l10n-backupbuddy' ) ); ?>
</p>

<?php
if ( isset( $_GET['edit'] ) && ( $_GET['edit'] != '' ) && ( $_GET['type'] == 'email' ) ) {
	$options = array_merge( $this->_parent->_emaildefaults, (array)$this->_options['remote_destinations'][$_GET['edit']] );
	
	echo '<h3>', __('Edit Destination', 'it-l10n-backupbuddy'),'</h3>';
	echo '<form method="post" action="' . admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&edit=' . htmlentities( $edit_id ) . '&callback_data=' . $callback_data . '&type=email#pb_backupbuddy_tab_email">';
	echo '	<input type="hidden" name="savepoint" value="remote_destinations#' . $_GET['edit'] . '" />';
} else {
	$options = $this->_parent->_emaildefaults;
	
	echo '<h3>', __('Add New Destination', 'it-l10n-backupbuddy') . ' ' . $this->video( 'Tp_VkLoBEpw', __('Add a new Email destination', 'it-l10n-backupbuddy'), false ) . '</h3>';
	echo '<form method="post" action="' . admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&callback_data=' . $callback_data . '&type=email#pb_backupbuddy_tab_email">';
}
?>
	<input type="hidden" name="#type" value="email" />
	<table class="form-table">
		<tr>
			<td><label for="title"><?php _e('Destination Name', 'it-l10n-backupbuddy'); $this->tip( __('Name of the new destination to create. This is for your convenience only.', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#title" id="title" size="45" maxlength="45" value="<?php echo $options['title']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="email"><?php _e('Email', 'it-l10n-backupbuddy'); $this->tip( __('[Example: your@email.com] - Email address for this destination.', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#email" id="email" size="45" maxlength="255" value="<?php echo $options['email']; ?>" /></td>
		</tr>
	</table>
	
	<?php
	if ( isset( $_GET['edit'] ) && ( $_GET['edit'] != '' ) && ( $_GET['type'] == 'email' ) ) {
		echo '<p class="submit"><input type="submit" name="edit_destination" value="', __('Save Changes', 'it-l10n-backupbuddy'),'" class="button-primary" /></p>';
	} else {
		echo '<p class="submit"><input type="submit" name="add_destination" value="+ ', __('Add Destination', 'it-l10n-backupbuddy'), '" class="button-primary" /></p>';
	}
	
	$this->nonce();
	?>
</form>
