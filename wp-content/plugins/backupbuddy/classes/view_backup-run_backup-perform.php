<?php
$this->title( __('Backing Up', 'it-l10n-backupbuddy') );

require_once( $this->_pluginPath . '/classes/backup.php' );
$backup = new pluginbuddy_backupbuddy_backup( $this );

if ( defined( 'PB_DEMO_MODE' ) ) {
	echo '<br>';
	$this->alert( 'You are currently running in demo mode. A backup file will not be created.', true );
}

if ( $backup->start_backup_process( $_GET['run_backup'], 'manual' ) !== true ) {
	echo __('Error #4344443: Backup failure', 'it-l10n-backupbuddy');
	echo $backup->get_errors();
}
?>

<script type="text/javascript">
	jQuery(document).ready(function() {
			// Wait 2 seconds before first poll.
			setTimeout( 'backupbuddy_poll()' , 2000 );
			
			jQuery("#pb_backupbuddy_advanced_details").click(function() {
				jQuery("#pb_backupbuddy_advanced_details_div").slideToggle();
			});
			
			setInterval( 'blink_ledz()' , 600 );
	});
	
	
	var stale_archive_time_trigger = 25; // If this time ellapses without archive size increasing warn user that something may have gone wrong.
	
	keep_polling = 1;
	pb_blink_status = 1;
	var last_archive_change = 0; // Time where archive size last changed.
	var last_archive_size = ''; // Last archive size string.
	
	function blink_ledz( this_status ) {
		if ( pb_blink_status == 1 ) {
			jQuery( '.pb_backupbuddy_blinkz' ).css( 'background-position', 'bottom' );
			pb_blink_status = 0;
		} else {
			jQuery( '.pb_backupbuddy_blinkz' ).css( 'background-position', 'top' );
			pb_blink_status = 1;
		}
	}
	
	function unix_timestamp() {
		return Math.round( ( new Date() ).getTime() / 1000 );
	}
	
	function backupbuddy_poll_altcron() {
		if ( keep_polling != 1 ) {
			return;
		}
		
		jQuery.get(
			'<?php echo admin_url('admin.php').'?page=pluginbuddy_backupbuddy&pb_backupbuddy_alt_cron=true'; ?>',
			function(data) {
			}
		);
	}
	
	function backupbuddy_poll() {
		if ( keep_polling != 1 ) {
			return;
		}
		
		//alert( 'string: ' + ( last_archive_size ) + '; timediff: ' + ( unix_timestamp() - last_archive_change ) );
		// Check to make sure archive size is increasing. Warn if it seems to hang.
		if ( ( last_archive_change != 0 ) && ( ( ( unix_timestamp() - last_archive_change ) > stale_archive_time_trigger ) ) ) {
			alert( <?php echo "'", __('Warning: The backup archive file size has not increased in', 'it-l10n-backupbuddy'), " ' + stale_archive_time_trigger + ' ", __('seconds. The backup may have failed. If you wish to wait you will be notified if there is no progress in 5 minutes.', 'it-l10n-backupbuddy'),"'"; ?> );
			stale_archive_time_trigger = 60 * 5;
		}
		
		jQuery('#pb_backupbuddy_loading').show();
		jQuery.ajax({
			url:	'<?php echo admin_url('admin-ajax.php').'?action=pb_backupbuddy&actionb=backup_status&type=' . htmlentities( $_GET['run_backup'] ); ?>',
			type:	'post',
			data:	{ serial: '<?php echo $backup->_backup['serial']; ?>' },
			context: document.body,
			success: function( data ) {
						jQuery('#pb_backupbuddy_loading').hide();
						
						data = data.split( "\n" );
						for( var i = 0; i < data.length; i++ ) {
							messages_output = '';
							details_output = '';
							
							if ( data[i].substring( 0, 1 ) == '!' ) { // Expected command since it begins with `!`.
								data[i] = data[i].substring(1); // Strip exclamation point.
								line = data[i].split( "|" );
								
								//Convert timestamp to readable format. Server timestamp based on GMT so undo localization with offset.
								var date = new Date();
								var date = new Date(  ( line[0] * 1000 ) + date.getTimezoneOffset() * 60000 );
								var seconds = date.getSeconds();
								if ( seconds < 10 ) {
									seconds = '0' + seconds;
								}
								date = date.getHours() + ':' + date.getMinutes() + ':' + seconds;
								
								// Process commands.
								if ( line[1] == 'message' ) {
									messages_output = date + ': ' + line[2];
								} else if ( line[1] == 'error' ) { // Process errors.
									messages_output = date + ': ' + line[2];
								} else if ( line[1] == 'ping' ) { // Ping.
									details_output = date + ': Ping. Waiting for server . . .';
								} else if ( line[1] == 'action' ) { // Process action commands.
									action_line = line[2].split( "^" );
									if ( action_line[0] == 'archive_size' ) { // Process action sub-commands.
										if ( last_archive_size != action_line[1] ) { // Track time archive size last changed.
											last_archive_size = action_line[1];
											last_archive_change = unix_timestamp();
										}
										jQuery( '.backupbuddy_archive_size' ).html( action_line[1] );
									} else if ( action_line[0] == 'finish_settings' ) {
										jQuery( '.pb_backupbuddy_blinkz' ).css( 'background-position', 'top' ); // turn off led
										jQuery( '#pb_backupbuddy_slot1_led' ).removeClass( 'pb_backupbuddy_blinkz' ); // disable blinking
									} else if ( action_line[0] == 'start_database' ) {
										jQuery( '#pb_backupbuddy_slot2_led' ).addClass( 'pb_backupbuddy_blinkz' ); // enable blinking
										jQuery( '#pb_backupbuddy_slot2_led' ).addClass( 'pb_backupbuddy_glow-light' ); // use led made for the lighter bg
										jQuery( '#pb_backupbuddy_slot2' ).addClass( 'light' ); // lighten the bg
										jQuery( '#pb_backupbuddy_slot2_header' ).addClass( 'light' ); // use text made for lighter bg
									} else if ( action_line[0] == 'finish_database' ) {
										jQuery( '.pb_backupbuddy_blinkz' ).css( 'background-position', 'top' ); // turn off led
										jQuery( '#pb_backupbuddy_slot2_led' ).removeClass( 'pb_backupbuddy_blinkz' ); // disable blinking
									} else if ( action_line[0] == 'start_files' ) {
										jQuery( '#pb_backupbuddy_slot3_led' ).addClass( 'pb_backupbuddy_blinkz' ); // enable blinking
										jQuery( '#pb_backupbuddy_slot3_led' ).addClass( 'pb_backupbuddy_glow-light' ); // use led made for the lighter bg
										jQuery( '#pb_backupbuddy_slot3' ).addClass( 'light' ); // lighten the bg
										jQuery( '#pb_backupbuddy_slot3_header' ).addClass( 'light' ); // use text made for lighter bg
									} else if ( action_line[0] == 'finish_backup' ) {
										jQuery( '.pb_backupbuddy_blinkz' ).css( 'background-position', 'top' ); // turn off led
										jQuery( '#pb_backupbuddy_slot3_led' ).removeClass( 'pb_backupbuddy_blinkz' ); // disable blinking
										jQuery( '#pb_backupbuddy_slot4' ).addClass( 'light' ); // lighten the bg of checkmark space
										jQuery( '#pb_backupbuddy_slot4_led' ).css( 'background-position', 'bottom' ); // set checkmark
										keep_polling = 0; // Stop polling server for status updates.
									} else if ( action_line[0] == 'archive_url' ) {
										<?php if ( defined( 'PB_DEMO_MODE' ) ) { ?>
											jQuery( '#pb_backupbuddy_archive_download' ).slideDown();
										<?php } else { ?>
											jQuery( '#pb_backupbuddy_archive_url' ).attr( 'href', action_line[1] );
											jQuery( '#pb_backupbuddy_archive_download' ).slideDown();
										<?php } ?>
									} else if ( action_line[0] == 'halt_script' ) {
										jQuery( '.pb_backupbuddy_blinkz' ).css( 'background-position', 'top' ); // turn off led
										jQuery( '#pb_backupbuddy_slot1_led' ).removeClass( 'pb_backupbuddy_blinkz' ); // disable blinking
										jQuery( '#pb_backupbuddy_slot2_led' ).removeClass( 'pb_backupbuddy_blinkz' ); // disable blinking
										jQuery( '#pb_backupbuddy_slot3_led' ).removeClass( 'pb_backupbuddy_blinkz' ); // disable blinking
										keep_polling = 0; // Stop polling server for status updates.
										messages_output = 'A fatal error has been encountered.  The backup has halted.';
										alert( '<?php _e('A fatal error has been encountered.  The backup has halted.', 'it-l10n-backupbuddy');?>' );
									} else {
										details_output = '<?php _e('Unknown action', 'it-l10n-backupbuddy');?>: ' + action_line[0] + "\n";
									}
								} else { // Unknown command so send to details.
									details_output = date + ': ' + line[2];
								}
								
								// Mirror mesages to details if no details have been set so far.
								if ( details_output == '' ) {
									details_output = messages_output;
								}
							} else { // Unrecognized command since it does not begin with `!`. Possible PHP error.
								details_output = data[i];
							}
							
							// Display messages.
							if ( messages_output != '' ) {
								jQuery( '#backupbuddy_messages' ).append( "\n" + messages_output );
								textareaelem = document.getElementById( 'backupbuddy_messages' );
								textareaelem.scrollTop = textareaelem.scrollHeight;
								messages_output = '';
							}
							
							// Display details.
							if ( details_output != '' ) {
								jQuery( '#backupbuddy_details' ).append( "\n" + details_output );
								textareaelem = document.getElementById( 'backupbuddy_details' );
								textareaelem.scrollTop = textareaelem.scrollHeight;
								details_output = '';
							}
						}
						
						// Set the next server poll if applicable.
						setTimeout( 'backupbuddy_poll()' , 3000 );
						<?php // Handles alternate WP cron forcing.
						if ( defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON ) {
							echo '	setTimeout( \'backupbuddy_poll_altcron()\' , 3000 );';
						}
						?>
					 },
			complete: function( jqXHR, status ) {
				if ( ( status != 'success' ) && ( status != 'notmodified' ) ) {
					jQuery('#pb_backupbuddy_loading').hide();
					alert( '<?php _e('BackupBuddy encountered the following error receiving data from the server', 'it-l10n-backupbuddy');?>: ' . status );
				}
			}
		});
	}
</script>

<style type="text/css">
	.pb_backupbuddy_status {
		background: #636363 url('<?php echo $this->_pluginURL; ?>/images/status/bg_dark.png') top repeat-x;
		border: 1px solid #636363;
		min-width: 20px;
		height: 29px;
		float: left;
		padding-bottom: 8px;
		text-align: right;
		-moz-border-radius: 8px 0 0 8px;
		border-radius: 8px 0 0 8px;
		margin-top: 20px;
		z-index: 0;
		position: relative;
	}
	.pb_backupbuddy_status.light{
		background: #dfdfdf url('<?php echo $this->_pluginURL; ?>/images/status/bg_light.png') top repeat-x;
		border: 1px solid #cdcdcd;
	}
	.pb_backupbuddy_status.L {
		-moz-border-radius: 8px 0 0 8px;
		border-radius: 8px 0 0 8px;
		border-right: 0;
	}
	.pb_backupbuddy_status.M {
		-moz-border-radius: 0;
		border-radius: 0;
		border-right: 0;
	}
	.pb_backupbuddy_status.R {
		-moz-border-radius: 0 8px 8px 0;
		border-radius: 0 8px 8px 0;
	}
	.pb_backupbuddy_status_header {
		padding: 9px 12px;
		font-size: 14px;
		font-weight: bold;
		height: 24px;
		float: left;
		font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
		color: #ebebeb;
	}
	.pb_backupbuddy_status_header.light {
		color: #666666;
	}
	.iconwp {
		padding-top: 5px;
		float: left;
		margin-left: 8px;
		width: 25px;
	}
	.pb_backupbuddy_glow {
		background: url('<?php echo $this->_pluginURL; ?>/images/status/ledz_dark.png');
		background-position: top;
		margin: 4px;
		height: 30px;
		float: right;
		width: 30px;
		display: block;
	}
	/*
	.pb_backupbuddy_glow:hover, .pb_backupbuddy_glow.w:hover, .pb_backupbuddy_glow-light:hover {
		background-position: bottom;
	}
	*/
	.pb_backupbuddy_glow-light {
		background: url('<?php echo $this->_pluginURL; ?>/images/status/ledz_light.png');
		background-position: top;
		margin: 4px;
		height: 30px;
		float: right;
		width: 30px;
		display: block;
	}
</style>


<br />


<div>

	<div class="pb_backupbuddy_status light L" id="pb_backupbuddy_slot1">
		<img class="iconwp" src="<?php echo $this->_pluginURL; ?>/images/status/controlz.png"/>
		<div class="pb_backupbuddy_status_header light" id="pb_backupbuddy_slot1_header"><?php _e('Settings export', 'it-l10n-backupbuddy');?></div>
		<div class="pb_backupbuddy_glow-light pb_backupbuddy_blinkz" id="pb_backupbuddy_slot1_led"></div>
	</div>

	<div class="pb_backupbuddy_status M" id="pb_backupbuddy_slot2">
		<img class="iconwp" src="<?php echo $this->_pluginURL; ?>/images/status/db.png"/>
		<div class="pb_backupbuddy_status_header" id="pb_backupbuddy_slot2_header"><?php _e('Database export', 'it-l10n-backupbuddy');?></div>
		<div class="pb_backupbuddy_glow" id="pb_backupbuddy_slot2_led"></div>
	</div>

	<div class="pb_backupbuddy_status M" id="pb_backupbuddy_slot3">
		<img class="iconwp" src="<?php echo $this->_pluginURL; ?>/images/status/filez.png"/>
		<div class="pb_backupbuddy_status_header" id="pb_backupbuddy_slot3_header"><?php _e('Files Export', 'it-l10n-backupbuddy');?></div>
		<div class="pb_backupbuddy_glow" id="pb_backupbuddy_slot3_led"></div>
	</div>

	<div class="pb_backupbuddy_status R" id="pb_backupbuddy_slot4" style="width: 38px;">
		<div class="pb_backupbuddy_glow" id="pb_backupbuddy_slot4_led" style="background: url('<?php echo $this->_pluginURL; ?>/images/status/ledz_checkmark.png');"></div>
	</div>

</div>

<div style="clear: both;"></div>
<br />
<div id="pb_backupbuddy_archive_download" style="display: none; width: 793px; text-align: center;">
	<a id="pb_backupbuddy_archive_url" href="#" style="text-decoration: none;"><?php _e('Download backup ZIP archive', 'it-l10n-backupbuddy'); if ( defined( 'PB_DEMO_MODE' ) ) { echo ' [demo mode]'; } ?> (<span class="backupbuddy_archive_size">0 MB</span>)</a>
	|
	<a href="<?php echo $this->_selfLink; ?>-backup" style="text-decoration: none;"><?php _e('Back to backup page', 'it-l10n-backupbuddy');?></a>
</div>
<br />

<table width="793"><tr>
	<td><span style="font-size: 1.17em; font-weight: bold;"><?php _e('Status', 'it-l10n-backupbuddy');?></span></td>
	<td width="16"><span id="pb_backupbuddy_loading" style="display: none; margin-left: 10px;"><img src="<?php echo $this->_pluginURL; ?>/images/loading.gif" <?php echo 'alt="', __('Loading...', 'it-l10n-backupbuddy'),'" title="',__('Loading...', 'it-l10n-backupbuddy'),'"';?> width="16" height="16" style="vertical-align: -3px;" /></span></td>
	<td width="100%" align="right"><?php _e('Archive size', 'it-l10n-backupbuddy');?>: <span class="backupbuddy_archive_size">0 MB</span> <?php $this->tip( __('This is the current size of the backup archive as it is being generated. This size will grow until the backup is complete.','it-l10n-backupbuddy') ); ?></td>
</tr></table>
<textarea id="backupbuddy_messages" cols="75" rows="6" style="width: 793px;"><?php _e('Backing up with BackupBuddy', 'it-l10n-backupbuddy');?> v<?php echo $this->_parent->plugin_info( 'version' ); ?>...</textarea>

<p><a id="pb_backupbuddy_advanced_details" class="button secondary-button"><?php _e('Advanced Details', 'it-l10n-backupbuddy'); ?></a></p>
<div id="pb_backupbuddy_advanced_details_div" style="display: none;">
	<textarea id="backupbuddy_details" cols="75" rows="6" style="width: 793px;"><?php _e('Backing up with BackupBuddy', 'it-l10n-backupbuddy');?> v<?php echo $this->_parent->plugin_info( 'version' ); ?>...</textarea>
</div>

<br /><br />

<div style="color: #AFAFAF; width: 793px; text-align: center;">
	<?php _e('You may leave this page at any time and the backup will continue uninterrupted.', 'it-l10n-backupbuddy'); ?>
</div>

<br /><br />
