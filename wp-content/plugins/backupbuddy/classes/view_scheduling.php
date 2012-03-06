<?php
$date_format_example = 'mm/dd/yyyy hh:mm [am/pm]'; // Example date format for displaying to user.

$this->admin_scripts();

wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );

if ( !empty( $_POST['add_schedule'] ) ) {
	if ( defined( 'PB_DEMO_MODE' ) ) {
		$this->alert( 'Access denied in demo mode.', true );
	} else {
		$error = false;
		
		$_POST['#last_run'] = 0;
		$_POST['#first_run'] = $this->_parent->unlocalize_time( strtotime( $_POST['#first_run'] ) );
		if ( ( $_POST['#first_run'] == 0 ) || ( $_POST['#first_run'] == 18000 ) ) {
			$this->alert( sprintf(__('Invalid time format. Please use the specified format / example %s', 'it-l10n-backupbuddy') , $date_format_example) );
			$error = true;
		}
		if ( $_POST['#title'] == '' ) {
			$this->alert( __('You must enter a schedule name.', 'it-l10n-backupbuddy') );
			$error = true;
		}
		if ( $error === false ) {
			$next_index = $this->_options['next_schedule_index']; // v2.1.3: $next_index = end( array_keys( $this->_options['schedules'] ) ) + 1;
			$this->_options['next_schedule_index']++; // This change will be saved in savesettings function below.
			$this->savesettings( 'schedules#' . $next_index );
			wp_schedule_event( $_POST['#first_run'], $_POST['#interval'], 'pb_backupbuddy-cron_scheduled_backup', array( $next_index ) );
		}
	}
} elseif ( !empty( $_POST['edit_schedule'] ) ) {
	if ( defined( 'PB_DEMO_MODE' ) ) {
		$this->alert( 'Access denied in demo mode.', true );
	} else {
		$_POST['#last_run'] = '0';
		
		$error = false;
		$_POST['#first_run'] = $this->_parent->unlocalize_time( strtotime( $_POST['#first_run'] ) );
		if ( $_POST['#first_run'] == 0 ) {
			$this->alert( sprintf( __('Invalid time format. Please use the specified format / example %s', 'it-l10n-backupbuddy') , $date_format_example) );
			$error = true;
		}
		if ( $_POST['#title'] == '' ) {
			$this->alert( __('You must enter a schedule name.', 'it-l10n-backupbuddy') );
			$error = true;
		}
		if ( $error === false ) {
			$next_scheduled_time = wp_next_scheduled( 'pb_backupbuddy-cron_scheduled_backup', array( (int)$_GET['edit'] ) );
			wp_unschedule_event( $next_scheduled_time, 'pb_backupbuddy-cron_scheduled_backup', array( (int)$_GET['edit'] ) ); // Remove old schedule. $this->_options['schedules'][$_GET['edit']]['first_run']
			$this->savesettings( $_POST['savepoint'] );
			wp_schedule_event( $_POST['#first_run'], $_POST['#interval'], 'pb_backupbuddy-cron_scheduled_backup', array( (int)$_GET['edit'] ) ); // Add new schedule.
		}
	}
}

if ( isset( $_POST['delete_schedules'] ) ) {
	if ( ! empty( $_POST['schedules'] ) && is_array( $_POST['schedules'] ) ) {
		$deleted_groups = '';
		
		foreach ( (array) $_POST['schedules'] as $id ) {
			$deleted_groups .= ' "' . stripslashes( $this->_options['schedules'][$id]['title'] ) . '",';
			$next_scheduled_time = wp_next_scheduled( 'pb_backupbuddy-cron_scheduled_backup', array( (int)$id ) );
			wp_unschedule_event( $next_scheduled_time, 'pb_backupbuddy-cron_scheduled_backup', array( (int)$id ) ); // Remove old schedule. $this->_options['schedules'][$id]['first_run']
			unset( $this->_options['schedules'][$id] );
		}
		
		$this->_parent->save();
		$this->alert( __('Deleted schedule(s)', 'it-l10n-backupbuddy') . ' ' . trim( $deleted_groups, ',' ) . '.' );
	}
}

?>

<script type="text/javascript">
	function pb_backupbuddy_selectdestination( destination_id, destination_title, callback_data ) {
		jQuery( '#pb_backupbuddy_remotedestinations_list' ).append( '<li id="pb_remotedestination_' + destination_id + '">' + destination_title + ' <img class="pb_remotedestionation_delete" src="<?php echo $this->_pluginURL; ?>/images/bullet_delete.png" style="vertical-align: -3px; cursor: pointer;" title="<?php _e( 'Remove remote destination from this schedule.', 'it-l10n-backupbuddy' ); ?>" /></li>' + "\n" );
		jQuery( '#pb_backupbuddy_deleteafter' ).slideDown();
	}
	
	
	jQuery(document).ready(function() {
		/* Generate the remote destination list upon submission. */
		jQuery('#pb_backupbuddy_schedulesave').submit(function(e) {
			remote_destinations = '';
			jQuery( '#pb_backupbuddy_remotedestinations_list' ).children('li').each(function () {
				remote_destinations = jQuery(this).attr( 'id' ).substr( 21 ) + '|' + remote_destinations ;
			});
			jQuery( '#pb_backupbuddy_remotedestinations' ).val( remote_destinations );
		});
		
		
		/* Allow deleting of remote destinations from the list. */
		jQuery('.pb_remotedestionation_delete').live( 'click', function(e) {
			jQuery( '#pb_remotedestination_' + jQuery(this).parent( 'li' ).attr( 'id' ).substr( 21 ) ).remove();
		});
		
		
		jQuery('.pluginbuddy_pop').click(function(e) {
			showpopup('#'+jQuery(this).attr('href'),'',e);
			return false;
		});
	});
</script>

<div class="wrap">
	<?php $this->title( __('Scheduled Backups', 'it-l10n-backupbuddy') ); ?><br />
	
	
	
	<form id="posts-filter" enctype="multipart/form-data" method="post" action="<?php echo $this->_selfLink; ?>-scheduling">
		<div class="tablenav">
			<div class="alignleft actions">
				<input type="submit" name="delete_schedules" value="<?php _e('Delete', 'it-l10n-backupbuddy');?>" class="button-secondary delete" />
			</div>
		</div>
		<table class="widefat">
			<thead>
				<tr class="thead">
					<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
					<?php
						$last_run_explanation = __( 'Last run time is the last time that this scheduled backup started. This does not imply that the backup completed, only that it began at this time. The last run time is reset if the schedule is edited.', 'it-l10n-backupbuddy' );
					
						echo '<th>', __('Name', 'it-l10n-backupbuddy'), '</th>',
							 '<th>', __('Type', 'it-l10n-backupbuddy'), '</th>',
							 '<th>', __('Interval', 'it-l10n-backupbuddy'), '</th>',
							 '<th>', __('Destinations', 'it-l10n-backupbuddy'), '</th>',
							 '<th>', __('First Run', 'it-l10n-backupbuddy'), '</th>',
							 '<th>' . __('Last Run', 'it-l10n-backupbuddy') . $this->tip( $last_run_explanation, '', false ) . '</th>',
							 '<th>', __('ID', 'it-l10n-backupbuddy'), '</th>';
					?>
				</tr>
			</thead>
			<tfoot>
				<tr class="thead">
					<th scope="col" class="check-column"><input type="checkbox" class="check-all-entries" /></th>
					<?php
						echo '<th>', __('Name', 'it-l10n-backupbuddy'), '</th>',
							 '<th>', __('Type', 'it-l10n-backupbuddy'), '</th>',
							 '<th>', __('Interval', 'it-l10n-backupbuddy'), '</th>',
							 '<th>', __('Destinations', 'it-l10n-backupbuddy'), '</th>',
							 '<th>', __('First Run', 'it-l10n-backupbuddy'), '</th>',
							 '<th>' . __('Last Run', 'it-l10n-backupbuddy') . $this->tip( $last_run_explanation, '', false ) . '</th>',
							 '<th>', __('ID', 'it-l10n-backupbuddy'), '</th>';
					?>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$file_count = 0;
				foreach ( $this->_options['schedules'] as $schedule_id => $schedule ) {
					?>
					<tr class="entry-row alternate">
						<th scope="row" class="check-column"><input type="checkbox" name="schedules[]" class="entries" value="<?php echo $schedule_id; ?>" /></th>
						<td>
							<?php echo $schedule['title']; ?>
							<div class="row-actions" style="margin:0; padding:0;">
								<a href="<?php echo $this->_selfLink . '-scheduling&edit=' . $schedule_id; ?>"><?php _e('Edit this schedule', 'it-l10n-backupbuddy');?></a>
							</div>
						</td>
						<td style="white-space: nowrap;">
							<?php
							if ( $schedule['type'] == 'full' ) {
								echo __('full', 'it-l10n-backupbuddy');
							} elseif ( $schedule['type'] == 'db' ) {
								echo __('database', 'it-l10n-backupbuddy');
							} else {
								echo __('unknown', 'it-l10n-backupbuddy'), ': ' . $schedule['type'];
							}
							?>
						</td>
						<td style="white-space: nowrap;">
							<?php echo $schedule['interval']; ?>
						</td>
						<td>
							<?php
							$destinations = explode( '|', $schedule['remote_destinations'] );
							$destination_array = array();
							foreach( $destinations as $destination ) {
								if ( isset( $destination ) && ( $destination != '' ) ) {
									$destination_array[] = $this->_options['remote_destinations'][$destination]['title'] . ' (' . $this->pretty_destination_type( $this->_options['remote_destinations'][$destination]['type'] ) . ')';
								}
							}
							unset( $destinations );
							unset( $destination );
							echo implode( ', ', $destination_array );
							?>
						</td>
						<td>
							<?php echo $this->_parent->format_date( $this->_parent->localize_time( $schedule['first_run'] ) ); ?>
						</td>
						<td>
							<?php
							if ( isset( $schedule['last_run'] ) ) { // backward compatibility before last run tracking added. Pre v2.2.11. Eventually remove this.
								if ( $schedule['last_run'] == 0 ) {
									echo '<i>' . __( 'never', 'it-l10n-backupbuddy' ) . '</i>';
								} else {
									echo $this->_parent->format_date( $this->_parent->localize_time( $schedule['last_run'] ) );
								}
							} else { // backward compatibility for before last run tracking was added.
								echo '<i> ' . __( 'unknown', 'it-l10n-backupbuddy' ) . '</i>';
							}
							?>
						</td>
						<td>
							<?php echo $schedule_id; ?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="alignleft actions">
				<input type="submit" name="delete_schedules" value="<?php _e('Delete', 'it-l10n-backupbuddy');?>" class="button-secondary delete" />
			</div>
			<div style="float: right;"><small><i><?php _e('Hover over a schedule above for additional options.' , 'it-l10n-backupbuddy');?></i></small></div>
		</div>
		
		<?php $this->nonce(); ?>
	</form>

	<br />
	
	
	
	
	
	<?php
	if ( isset( $_GET['edit'] ) ) {
		$options = array_merge( $this->_parent->_scheduledefaults, (array)$this->_options['schedules'][$_GET['edit']] );
		
		echo '<h3>', __('Edit Schedule', 'it-l10n-backupbuddy'),'</h3>';
		echo '<form method="post" id="pb_backupbuddy_schedulesave" action="' . $this->_selfLink . '-scheduling&edit=' . htmlentities( $_GET['edit'] ) . '">';
		echo '	<input type="hidden" name="savepoint" value="schedules#' . htmlentities( $_GET['edit'] ) . '" />';
	} else {
		$options = $this->_parent->_scheduledefaults;
		
		echo '<h3>', __('Add New Schedule', 'it-l10n-backupbuddy'), ' ' . $this->video( 'W6vQtnKM1uk', __('Add a new schedule', 'it-l10n-backupbuddy'), false ) . '</h3>';
		echo '<form method="post" id="pb_backupbuddy_schedulesave" action="' . $this->_selfLink . '-scheduling">';
	}
	?>
		
		<table class="form-table">
			<tr><th scope="row"><label for="title"><?php _e('Name for backup schedule', 'it-l10n-backupbuddy'); $this->tip( __('This is a name for your reference only.', 'it-l10n-backupbuddy') ); ?></label></th>
				<td><input type="text" name="#title" id="title" size="30" maxlength="45" value="<?php echo $options['title']; ?>" /></td>
			</tr>
			<tr><th scope="row"><label for="type"><?php _e('Backup type', 'it-l10n-backupbuddy'); $this->tip( __('Full backups contain all files (except exclusions) and your database. Database only backups consist of an export of your mysql database; no WordPress files or media. Database backups are typically much smaller and faster to perform and are typically the most quickly changing part of a site.', 'it-l10n-backupbuddy') ); ?></label></th>
				<td>
					<select name="#type">
						<option value="db" <?php if ( $options['type'] == 'db' ) { echo 'selected'; } ?>><?php _e('Database Only', 'it-l10n-backupbuddy');?></option>
						<option value="full" <?php if ( $options['type'] == 'full' ) { echo 'selected'; } ?>><?php _e('Full (database + files)', 'it-l10n-backupbuddy');?></option>
					</select>
				</td>
			</tr>
			<tr><th scope="row"><label for="interval"><?php _e('Backup interval', 'it-l10n-backupbuddy'); $this->tip( __('Time period between backups.', 'it-l10n-backupbuddy') ); ?></label></th>
				<td>
					<select name="#interval">
						<option value="monthly" <?php if ( $options['interval'] == 'monthly' ) { echo 'selected'; } ?>><?php _e('Monthly', 'it-l10n-backupbuddy');?></option>
						<option value="twicemonthly" <?php if ( $options['interval'] == 'twicemonthly' ) { echo 'selected'; } ?>><?php _e('Twice Monthly', 'it-l10n-backupbuddy');?></option>
						<option value="weekly" <?php if ( $options['interval'] == 'weekly' ) { echo 'selected'; } ?>><?php _e('Weekly', 'it-l10n-backupbuddy');?></option>
						<option value="daily" <?php if ( $options['interval'] == 'daily' ) { echo 'selected'; } ?>><?php _e('Daily', 'it-l10n-backupbuddy');?></option>
						<option value="hourly" <?php if ( $options['interval'] == 'hourly' ) { echo 'selected'; } ?>><?php _e('Hourly', 'it-l10n-backupbuddy');?></option>
					</select>
				</td>
			</tr>
			<tr><th scope="row"><label for="name"><?php _e('Date/time of next run', 'it-l10n-backupbuddy'); $this->tip( __('IMPORTANT: For scheduled events to be occur someone (or you) must visit this site on or after the scheduled time. If no one visits your site for a long period of time some backup events may not be triggered.', 'it-l10n-backupbuddy') ); ?></label></th>
				<td>
					<input type="text" name="#first_run" id="first_run" size="30" maxlength="45" value="<?php if ( !empty( $options['first_run'] ) ) { echo date('m/d/Y h:i a', $options['first_run'] + ( get_option( 'gmt_offset' ) * 3600 ) ); } else { echo date('m/d/Y h:i a', time() + ( ( get_option( 'gmt_offset' ) * 3600 ) + 86400 ) ); } ?>" /> <?php _e('Currently', 'it-l10n-backupbuddy');?> <code><?php echo date( 'm/d/Y h:i a ' . get_option( 'gmt_offset' ), time() + ( get_option( 'gmt_offset' ) * 3600 ) ); ?> UTC</code><?php echo ' ',__('based on', 'it-l10n-backupbuddy');?> <a href="<?php echo admin_url( 'options-general.php' ); ?>"><?php _e('WordPress settings', 'it-l10n-backupbuddy');?></a>.
					<br />
					<small><?php $date_format; ?></small>
				</td>
			</tr>
			<tr><th scope="row"><label for="send_ftp"><?php _e('Remote backup destination', 'it-l10n-backupbuddy'); $this->tip( __('Automatically sends generated backups to a remote destination such as Amazon S3, Rackspace Cloudsites, Email, or FTP.', 'it-l10n-backupbuddy') ); ?></label></th>
				<td>
					<span>
						<ul id="pb_backupbuddy_remotedestinations_list">
							<?php
							$remote_destinations = explode( '|', $options['remote_destinations'] );
							foreach( $remote_destinations as $destination ) {
								if ( isset( $destination ) && ( $destination != '' ) ) {
									echo '<li id="pb_remotedestination_' . $destination . '">';
									echo $this->_options['remote_destinations'][$destination]['title'];
									echo ' (' . $this->pretty_destination_type( $this->_options['remote_destinations'][$destination]['type'] ) . ') ';
									echo '<img class="pb_remotedestionation_delete" src="' . $this->_pluginURL . '/images/bullet_delete.png" style="vertical-align: -3px; cursor: pointer;" title="' . __( 'Remove remote destination from this schedule.', 'it-l10n-backupbuddy' ) . '" />';
									echo '</li>';
								}
							}
							?>
						</ul>
					</span>
					<a href="<?php echo admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination'; ?>&#038;TB_iframe=1&#038;width=640&#038;height=400" class="thickbox button secondary-button" style="margin-top: 3px;"><?php echo '+ ', __('Add Remote Destination', 'it-l10n-backupbuddy');?></a>
					
					<input type="hidden" name="#remote_destinations" id="pb_backupbuddy_remotedestinations" value="<?php echo $options['remote_destinations']; ?>" />
					
					<div id="pb_backupbuddy_deleteafter" style="<?php if ( !isset( $_GET['edit'] ) ) { echo 'display: none; '; } ?>background-color: #EAF2FA; border: 1px solid #E3E3E3; width: 250px; padding: 10px; margin: 5px; margin-left: 0px;">
						<input type="hidden" name="#delete_after" value="0" />
						<input type="checkbox" name="#delete_after" id="delete_after" value="1" <?php if ( $options['delete_after'] == '1' ) { echo 'checked'; } ?> /> <label for="delete_after"><?php _e('Delete local copy after remote send', 'it-l10n-backupbuddy'); $this->tip( __('[Default: disabled] - When enabled the local copy of the backup will be deleted after the file has been sent to the remote destination(s).', 'it-l10n-backupbuddy') ); ?></label>
					</div>
				</td>
			</tr>
		</table>
		
		<?php
		if ( isset( $_GET['edit'] ) ) {
			echo '<p class="submit"><input type="submit" name="edit_schedule" value="', __('Save Changes', 'it-l10n-backupbuddy'), '" class="button-primary" /></p>';
		} else {
			echo '<p class="submit"><input type="submit" name="add_schedule" value="+ ', __('Add Schedule', 'it-l10n-backupbuddy'), '" class="button-primary" /></p>';
		}
		
		$this->nonce();
		?>
		<br />
	</form>
	
	<br /><br />
	<div style="color: #AFAFAF; width: 793px; text-align: center;">
		<?php
			_e('Due to the way schedules are triggered in WordPress someone must visit your site<br /> for scheduled backups to occur. If there are no visits, some schedules may not be triggered.', 'it-l10n-backupbuddy');
		?>
	</div>
	<br /><br />
	
</div>
