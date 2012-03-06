<?php

wp_enqueue_script( 'jquery-ui-tabs' );
wp_print_scripts( 'jquery-ui-tabs' );

$this->admin_scripts();
?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#pluginbuddy-tabs").tabs();
		
		jQuery('.pb_backupbuddy_selectdestination').click(function(e) {
			var win = window.dialogArguments || opener || parent || top;
			win.pb_backupbuddy_selectdestination( jQuery(this).attr( 'href' ), jQuery(this).attr( 'alt' ), '<?php if ( !empty( $_GET['callback_data'] ) ) { echo $_GET['callback_data']; } ?>' );
			win.tb_remove();
			return false;
		});
	});
</script>

<br />
<?php
if ( defined( 'PB_DEMO_MODE' ) ) {
	$this->alert( 'Note: You cannot add remote destinations in demonstration mode.' );
	echo '<br>';
}
?>

<div id="pluginbuddy-tabs">
	<ul>
		<li><a href="#pb_backupbuddy_tab_s3"><span><?php _e('Amazon S3', 'it-l10n-backupbuddy'); ?></span></a></li>
		<li><a href="#pb_backupbuddy_tab_dropbox"><span><?php _e('Dropbox', 'it-l10n-backupbuddy'); ?></span></a></li>
		<li><a href="#pb_backupbuddy_tab_rackspace"><span><?php _e('Rackspace Cloudfiles', 'it-l10n-backupbuddy');?></span></a></li>
		<li><a href="#pb_backupbuddy_tab_ftp"><span><?php _e('FTP', 'it-l10n-backupbuddy'); ?></span></a></li>
		<li><a href="#pb_backupbuddy_tab_email"><span><?php _e('Email', 'it-l10n-backupbuddy'); ?></span></a></li>
	</ul>
	<br />
	<?php
	if ( isset( $_POST['delete_destinations'] ) ) {
		if ( ! empty( $_POST['destinations'] ) && is_array( $_POST['destinations'] ) ) {
			$deleted_groups = '';
			
			foreach ( (array) $_POST['destinations'] as $id ) {
				$deleted_groups .= ' "' . stripslashes( $this->_options['remote_destinations'][$id]['title'] ) . '",';
				unset( $this->_options['remote_destinations'][$id] );
				
				// Remove this destination from all schedules using it.
				foreach( $this->_options['schedules'] as $schedule_id => $schedule ) {
					$remote_list = '';
					$trimmed_destination = false;
					
					$remote_destinations = explode( '|', $schedule['remote_destinations'] );
					foreach( $remote_destinations as $remote_destination ) {
						if ( $remote_destination == $id ) {
							$trimmed_destination = true;
						} else {
							$remote_list .= $remote_destination . '|';
						}
					}
					
					if ( $trimmed_destination === true ) {
						$this->_options['schedules'][$schedule_id]['remote_destinations'] = $remote_list;
					}
				}
			}
			
			$this->_parent->save();
			$this->alert( __('Deleted destination(s) ', 'it-l10n-backupbuddy') . trim( $deleted_groups, ',' ) . '.' );
		}
	}
	
	if ( isset( $_GET['edit'] ) && ( $_GET['edit'] != '' ) ) {
		$edit_id = $_GET['edit'];
	} else {
		$edit_id = '';
	}
	
	if ( isset( $_GET['clear_dropboxtemptoken'] ) && ( $_GET['clear_dropboxtemptoken'] == 'true' ) ) {
		$this->_options['dropboxtemptoken'] = ''; // Clear temp token.
		$this->_parent->save();
	}
	
	if ( !empty( $_POST['add_destination'] ) ) {
		if ( $_POST['#title'] == '' ) {
			$_POST['#title'] = '[no name]';
		}
		
		if ( $_POST['#type'] == 'dropbox' ) {
			$_POST['#token'] = $this->_options['dropboxtemptoken'];
			$this->_options['dropboxtemptoken'] = ''; // Clear temp token.
			$this->_parent->save();
		}
		
		if ( $_POST['#type'] == 'ftp' ) {
			// Require leading slash for FTP path.
			if ( ( $_POST['#path'] != '' ) && ( substr( $_POST['#path'], 0, 1 ) != '/' ) ) {
				$_POST['#path'] = '/' . $_POST['#path'];
			}
		}
		
		if ( empty( $this->_options['remote_destinations'] ) || !is_array( $this->_options['remote_destinations'] ) ) {
			$next_index = 0; // Empty remote destination array or not an array so set index to 0.
		} else { // Remote destinations is an array so determine index.
			$next_index = end( array_keys( $this->_options['remote_destinations'] ) ) + 1;
			if ( empty( $next_index ) ) {
				// No index so set it to 0.
				$next_index = 0;
			}
		}
		
		if ( !defined( 'PB_DEMO_MODE' ) ) {
			$this->savesettings( 'remote_destinations#' . $next_index );
		} else {
			$this->alert( 'Access denied in demo mode.', true );
		}
	} elseif ( !empty( $_POST['edit_destination'] ) ) {
		if ( $_POST['#title'] == '' ) {
			$_POST['#title'] = '[no name]';
		}
		
		if ( $_POST['#type'] == 'ftp' ) {
			if ( ( $_POST['#path'] != '' ) && ( substr( $_POST['#path'], 0, 1 ) != '/' ) ) {
				// Require leading slash for FTP path.
				$_POST['#path'] = '/' . $_POST['#path'];
			}
		}
		
		if ( !defined( 'PB_DEMO_MODE' ) ) {
			$this->savesettings( $_POST['savepoint'] );
		} else {
			$this->alert( 'Access denied in demo mode.', true );
		}
	}
?>

	<div class="tabs-borderwrap" id="editbox" style="position: relative; height: 100%; -moz-border-radius-topleft: 0px; -webkit-border-top-left-radius: 0px;">
		<div id="pb_backupbuddy_tab_s3">
			<?php require_once( $this->_pluginPath . '/classes/ajax_remotedestination_s3.php' ); ?>
		</div>
		<div id="pb_backupbuddy_tab_dropbox">
			<?php require_once( $this->_pluginPath . '/classes/ajax_remotedestination_dropbox.php' ); ?>
		</div>
		<div id="pb_backupbuddy_tab_rackspace">
			<?php require_once( $this->_pluginPath . '/classes/ajax_remotedestination_rackspace.php' ); ?>
		</div>
		<div id="pb_backupbuddy_tab_ftp">
			<?php require_once( $this->_pluginPath . '/classes/ajax_remotedestination_ftp.php' ); ?>
		</div>
		<div id="pb_backupbuddy_tab_email">
			<?php require_once( $this->_pluginPath . '/classes/ajax_remotedestination_email.php' ); ?>
		</div>
	</div>
</div>
