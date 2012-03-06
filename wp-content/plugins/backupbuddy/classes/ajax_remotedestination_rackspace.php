<?php
if ( !empty( $_GET['callback_data'] ) ) {
	$callback_data = $_GET['callback_data'];
} else {
	$callback_data = '';
}?>

<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>?action=pb_backupbuddy_remotedestination&callback_data=<?php if ( !empty( $_GET['callback_data'] ) ) { echo $_GET['callback_data']; } ?>#pb_backupbuddy_tab_rackspace">
	<div class="tablenav">
		<div class="alignleft actions">
			<input type="submit" name="delete_destinations" value="<?php _e('Delete', 'it-l10n-backupbuddy'); ?>" class="button-secondary delete" />
		</div>
	</div>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th scope="col" class="check-column"><!-- input type="checkbox" class="check-all-entries" --></th>
				<?php 
					echo '<th>', __('Name',      'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Username',  'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Container', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Limit',     'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th scope="col" class="check-column"><!-- input type="checkbox" class="check-all-entries" --></th>
				<?php 
					echo '<th>', __('Name',      'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Username',  'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Container', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Limit',     'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$file_count = 0;
			foreach ( $this->_options['remote_destinations'] as $destination_id => $destination ) {
				if ( $destination['type'] != 'rackspace' ) {
					continue;
				}
				
				$destination = array_merge( $this->_parent->_rackspacedefaults, $destination );
				?>
				<tr class="entry-row alternate">
					<th scope="row" class="check-column"><input type="checkbox" name="destinations[]" class="entries" value="<?php echo $destination_id; ?>" /></th>
					<td>
						<?php echo $destination['title']; ?><br />
							<a href="<?php echo $destination_id; ?>" alt="<?php echo $destination['title'] . ' (Rackspace)'; ?>" class="pb_backupbuddy_selectdestination"><?php _e('Select this destination', 'it-l10n-backupbuddy');?></a> |
							<a href="<?php echo admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&edit=' . htmlentities( $destination_id ); ?>&callback_data=<?php if ( !empty( $_GET['callback_data'] ) ) { echo $_GET['callback_data']; } ?>&type=rackspace#pb_backupbuddy_tab_rackspace"><?php _e('Edit Settings', 'it-l10n-backupbuddy');?></a>
					</td>
					<td style="white-space: nowrap;">
						<?php echo $destination['username']; ?>
					</td>
					<td style="white-space: nowrap;">
						<?php echo $destination['container']; ?>
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
			<input type="submit" name="delete_destinations" value="<?php _e('Delete', 'it-l10n-backupbuddy');?>" class="button-secondary delete" />
		</div>
	</div>
	
	<?php $this->nonce(); ?>
</form>

<br />

<?php
if ( isset( $_GET['edit'] ) && ( $_GET['edit'] != '' ) && ( $_GET['type'] == 'rackspace' ) ) {
	$options = array_merge( $this->_parent->_rackspacedefaults, (array)$this->_options['remote_destinations'][$_GET['edit']] );
	
	echo '<h3>', __('Edit Destination', 'it-l10n-backupbuddy'), '</h3>';
	echo '<form method="post" action="' . admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&edit=' . htmlentities( $edit_id ) . '&callback_data=' . $callback_data . '&type=rackspace#pb_backupbuddy_tab_rackspace">';
	echo '	<input type="hidden" name="savepoint" value="remote_destinations#' . $_GET['edit'] . '" />';
} else {
	$options = $this->_parent->_rackspacedefaults;
	
	echo '<h3>', __('Add New Destination', 'it-l10n-backupbuddy') . ' ' . $this->video( 'lfTs_GtAp1I', __('Add a new Rackspace destination', 'it-l10n-backupbuddy'), false ) . '</h3>';
	echo '<form method="post" action="' . admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination&callback_data=' . $callback_data . '&type=rackspace#pb_backupbuddy_tab_rackspace">';
}
?>
	<input type="hidden" name="#type" value="rackspace" />
	<table class="form-table">
		<tr>
			<td><label for="title"><?php _e('Destination Name', 'it-l10n-backupbuddy'); $this->tip( __('Name of the new destination to create. This is for your convenience only.', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#title" id="title" size="45" maxlength="45" value="<?php echo $options['title']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="username"><?php _e('Username', 'it-l10n-backupbuddy'); $this->tip( __('[Example: badger] - Your Rackspace Cloudfiles username.', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#username" id="username" size="45" maxlength="45" value="<?php echo $options['username']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="api_key"><?php _e('API Key', 'it-l10n-backupbuddy'); $this->tip( __('[Example: 9032jk09jkdspo9sd32jds9swd039dwe] - Log in to your Rackspace Cloudfiles Account and navigate to Your Account: API Access', 'it-l10n-backupbuddy') ); echo ' '; $this->video( 'lfTs_GtAp1I#14s', __('Get your Rackspace Cloudfiles API key', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="password" name="#api_key" id="api_key" size="45" maxlength="45" value="<?php echo $options['api_key']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="container"><?php _e('Container', 'it-l10n-backupbuddy'); $this->tip( __('[Example: wordpress_backups] - This container will NOT be created for you automatically if it does not already exist. Please create it first.', 'it-l10n-backupbuddy') ); echo ' '; $this->video( 'lfTs_GtAp1I#26', __('Create a container from the Rackspace Cloudfiles panel', 'it-l10n-backupbuddy') ); ?></label></td>
			<td><input type="text" name="#container" id="container" size="45" maxlength="45" value="<?php echo $options['container']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="archive_limit"><?php _e('Archive Limit', 'it-l10n-backupbuddy'); $this->tip( __('[Example: 5] - Enter 0 for no limit. This is the maximum number of archives to be stored in this specific destination. If this limit is met the oldest backups will be deleted.', 'it-l10n-backupbuddy') ); echo ' '; ?></label></td>
			<td><input type="text" name="#archive_limit" id="archive_limit" size="45" maxlength="6" value="<?php echo $options['archive_limit']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="server"><?php _e( 'Cloud Network', 'it-l10n-backupbuddy' ); ?></label></td>
			<td>
				<select name="#server">
					<option value="https://auth.api.rackspacecloud.com" <?php if ( $options['server'] == 'https://auth.api.rackspacecloud.com' ) { echo 'selected'; } ?>>USA</option>
					<option value="https://lon.auth.api.rackspacecloud.com" <?php if ( $options['server'] == 'https://lon.auth.api.rackspacecloud.com' ) { echo 'selected'; } ?>>UK</option>
				</server>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<input value="<?php _e('Test these settings', 'it-l10n-backupbuddy');?>" class="button-secondary pb_backupbuddy_remotetest" id="pb_backupbuddy_remotetest_rackspace" type="submit" alt="<?php echo admin_url('admin-ajax.php').'?action=pb_backupbuddy_remotetest&service=rackspace'; ?>" />
			</td>
		</tr>
	</table>
	
	<?php
	if ( isset( $_GET['edit'] ) && ( $_GET['edit'] != '' ) && ( $_GET['type'] == 'rackspace' ) ) {
		echo '<p class="submit"><input type="submit" name="edit_destination" value="',__('Save Changes', 'it-l10n-backupbuddy'),'" class="button-primary" /></p>';
	} else {
		echo '<p class="submit"><input type="submit" name="add_destination" value="+ ',__('Add Destination', 'it-l10n-backupbuddy'),'" class="button-primary" /></p>';
	}
	
	$this->nonce();
	?>
</form>

