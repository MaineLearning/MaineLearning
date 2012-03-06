<?php
$this->admin_scripts();



wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );


// for Directory Exclusion:
wp_enqueue_script( $this->_var . '_filetree', $this->_pluginURL . '/js/filetree.js' );
wp_print_scripts( $this->_var . '_filetree' );
echo '<link rel="stylesheet" href="'.$this->_pluginURL . '/css/filetree.css" type="text/css" media="all" />';

// Used for drag & drop / collapsing boxes.
wp_enqueue_style('dashboard');
wp_print_styles('dashboard');
wp_enqueue_script('dashboard');
//wp_print_scripts('dashboard'); BREAKS VIDEO THICKBOX when play button is in the collaspible top.

if ( !empty( $_POST['save'] ) ) {
	$errors = array();
	if ( $_POST['#repairbuddy_password'] != $_POST['#repairbuddy_password_again'] ) {
		$errors[] = 'RepairBuddy password and confirmation password inputs do not match.';
	}
	if ( $_POST['#import_password'] != $_POST['#import_password_again'] ) {
		$errors[] = 'ImportBuddy password and confirmation password inputs do not match.';
	}
	
	if ( count( $errors ) == 0 ) {
		unset( $_POST['#repairbuddy_password_again'] );
		unset( $_POST['#import_password_again'] );
		
		$this->savesettings();
	} else {
		$this->alert( __( 'One or more errors encountered. Please correct the following errors and try again:', 'it-l10n-backupbuddy' ) . '<br>' . implode( '<br>', $errors ), true );
	}
}
?>


<style type="text/css">
	.form-table tr > td:first-child {
		width: 300px;
	}
</style>


<?php // The following section is for directory exclusion. ?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('#exlude_dirs').fileTree({ root: '/', multiFolder: false, script: '<?php echo admin_url('admin-ajax.php').'?action=pb_backupbuddy_filetree'; ?>' }, function(file) {
				//alert('file:'+file);
			}, function(directory) {
				if ( ( directory == '/wp-content/' ) || ( directory == '/wp-content/uploads/' ) || ( directory == '/wp-content/uploads/backupbuddy_backups/' ) || ( directory == '/wp-content/uploads/backupbuddy_temp/' ) ) {
					alert( '<?php _e('You cannot exclude /wp-content/, /wp-content/uploads/, or BackupBuddy directories.  However, you may exclude subdirectories within these. BackupBuddy directories such as backupbuddy_backups & backupbuddy_temp are automatically excluded and cannot be added to exclusion list.', 'it-l10n-backupbuddy');?>' );
				} else {
					jQuery('#exclude_dirs').val( directory + "\n" + jQuery('#exclude_dirs').val() );
				}
				
			});
		});
		
		function pb_backupbuddy_selectdestination( destination_id, destination_title, callback_data ) {
			window.location.href = '<?php echo $this->_selfLink; ?>&custom=remoteclient&destination_id=' + destination_id;
		}
	</script>
<style type="text/css">
	/* Core Styles */
	.jqueryFileTree LI.directory { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/directory.png') left top no-repeat; }
	.jqueryFileTree LI.expanded { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/folder_open.png') left top no-repeat; }
	.jqueryFileTree LI.file { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/file.png') left top no-repeat; }
	.jqueryFileTree LI.wait { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/spinner.gif') left top no-repeat; }
	/* File Extensions*/
	.jqueryFileTree LI.ext_3gp { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/film.png') left top no-repeat; }
	.jqueryFileTree LI.ext_afp { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_afpa { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_asp { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_aspx { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_avi { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/film.png') left top no-repeat; }
	.jqueryFileTree LI.ext_bat { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/application.png') left top no-repeat; }
	.jqueryFileTree LI.ext_bmp { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/picture.png') left top no-repeat; }
	.jqueryFileTree LI.ext_c { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_cfm { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_cgi { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_com { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/application.png') left top no-repeat; }
	.jqueryFileTree LI.ext_cpp { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_css { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/css.png') left top no-repeat; }
	.jqueryFileTree LI.ext_doc { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/doc.png') left top no-repeat; }
	.jqueryFileTree LI.ext_exe { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/application.png') left top no-repeat; }
	.jqueryFileTree LI.ext_gif { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/picture.png') left top no-repeat; }
	.jqueryFileTree LI.ext_fla { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/flash.png') left top no-repeat; }
	.jqueryFileTree LI.ext_h { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_htm { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/html.png') left top no-repeat; }
	.jqueryFileTree LI.ext_html { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/html.png') left top no-repeat; }
	.jqueryFileTree LI.ext_jar { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/java.png') left top no-repeat; }
	.jqueryFileTree LI.ext_jpg { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/picture.png') left top no-repeat; }
	.jqueryFileTree LI.ext_jpeg { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/picture.png') left top no-repeat; }
	.jqueryFileTree LI.ext_js { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/script.png') left top no-repeat; }
	.jqueryFileTree LI.ext_lasso { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_log { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/txt.png') left top no-repeat; }
	.jqueryFileTree LI.ext_m4p { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/music.png') left top no-repeat; }
	.jqueryFileTree LI.ext_mov { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/film.png') left top no-repeat; }
	.jqueryFileTree LI.ext_mp3 { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/music.png') left top no-repeat; }
	.jqueryFileTree LI.ext_mp4 { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/film.png') left top no-repeat; }
	.jqueryFileTree LI.ext_mpg { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/film.png') left top no-repeat; }
	.jqueryFileTree LI.ext_mpeg { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/film.png') left top no-repeat; }
	.jqueryFileTree LI.ext_ogg { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/music.png') left top no-repeat; }
	.jqueryFileTree LI.ext_pcx { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/picture.png') left top no-repeat; }
	.jqueryFileTree LI.ext_pdf { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/pdf.png') left top no-repeat; }
	.jqueryFileTree LI.ext_php { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/php.png') left top no-repeat; }
	.jqueryFileTree LI.ext_png { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/picture.png') left top no-repeat; }
	.jqueryFileTree LI.ext_ppt { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/ppt.png') left top no-repeat; }
	.jqueryFileTree LI.ext_psd { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/psd.png') left top no-repeat; }
	.jqueryFileTree LI.ext_pl { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/script.png') left top no-repeat; }
	.jqueryFileTree LI.ext_py { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/script.png') left top no-repeat; }
	.jqueryFileTree LI.ext_rb { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/ruby.png') left top no-repeat; }
	.jqueryFileTree LI.ext_rbx { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/ruby.png') left top no-repeat; }
	.jqueryFileTree LI.ext_rhtml { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/ruby.png') left top no-repeat; }
	.jqueryFileTree LI.ext_rpm { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/linux.png') left top no-repeat; }
	.jqueryFileTree LI.ext_ruby { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/ruby.png') left top no-repeat; }
	.jqueryFileTree LI.ext_sql { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/db.png') left top no-repeat; }
	.jqueryFileTree LI.ext_swf { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/flash.png') left top no-repeat; }
	.jqueryFileTree LI.ext_tif { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/picture.png') left top no-repeat; }
	.jqueryFileTree LI.ext_tiff { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/picture.png') left top no-repeat; }
	.jqueryFileTree LI.ext_txt { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/txt.png') left top no-repeat; }
	.jqueryFileTree LI.ext_vb { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_wav { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/music.png') left top no-repeat; }
	.jqueryFileTree LI.ext_wmv { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/film.png') left top no-repeat; }
	.jqueryFileTree LI.ext_xls { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/xls.png') left top no-repeat; }
	.jqueryFileTree LI.ext_xml { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/code.png') left top no-repeat; }
	.jqueryFileTree LI.ext_zip { background: url('<?php echo $this->_pluginURL; ?>/images/filetree/zip.png') left top no-repeat; }
</style>


<div class="wrap">
	<?php $this->title( __('Settings', 'it-l10n-backupbuddy') ); ?><br />
	<form method="post" action="<?php echo $this->_selfLink; ?>-settings">
		<?php // Ex. for saving in a group you might do something like: $this->_options['groups'][$_GET['group_id']['settings'] which would be: ['groups'][$_GET['group_id']['settings'] ?>
		<input type="hidden" name="savepoint" value="" />
		
		
		
		
		
		
		<div class="postbox-container" style="width: 80%; min-width: 750px;">
			<div class="metabox-holder">
				<div class="meta-box-sortables">
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php echo __('Email Notifications', 'it-l10n-backupbuddy'), ' '; $this->video( 'E37G9X6eNpg#8', __('Email Notifications Tutorial', 'it-l10n-backupbuddy') ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<td><label for="email_notify_scheduled"><?php _e('Scheduled backup completion recipients', 'it-l10n-backupbuddy'); $this->tip( __('Email address to send notifications to upon scheduled backup completion. Use commas to separate multiple email addresses.', 'it-l10n-backupbuddy') ); ?></label></td>
									<td><input type="text" name="#email_notify_scheduled" id="email_notify_scheduled" size="30" maxlength="255" value="<?php echo $this->_options['email_notify_scheduled']; ?>" /></td>
								</tr>
								<tr>
									<td><label for="email_notify_manual"><?php _e('Manual backup completion recipients', 'it-l10n-backupbuddy'); $this->tip( __('Email address to send notifications to upon manually triggered backup completion. Use commas to separate multiple email addresses.', 'it-l10n-backupbuddy') ); ?></label></td>
									<td><input type="text" name="#email_notify_manual" id="email_notify_manual" size="30" maxlength="255" value="<?php echo $this->_options['email_notify_manual']; ?>" /></td>
								</tr>
								<tr>
									<td><label for="email_notify_error"><?php _e('Backup failure/error recipients','it-l10n-backupbuddy'); $this->tip( __('Email address to send notifications to upon encountering any errors or problems. Use commas to separate multiple email addresses.', 'it-l10n-backupbuddy') ); ?></label></td>
									<td><input type="text" name="#email_notify_error" id="email_notify_error" size="30" maxlength="255" value="<?php echo $this->_options['email_notify_error']; ?>" /></td>
								</tr>
							</table>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php echo __('General Options', 'it-l10n-backupbuddy'), ' '; $this->video( 'E37G9X6eNpg#26', __('General Options Tutorial', 'it-l10n-backupbuddy') ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<td><label for="import_password"><?php _e('ImportBuddy password', 'it-l10n-backupbuddy'); $this->tip( __('[Example: myp@ssw0rD] - Required password for running the ImportBuddy import/migration script. This prevents unauthorized access when using this tool.', 'it-l10n-backupbuddy') ); ?></label></td>
									<td><input type="password" name="#import_password" id="import_password" size="20" maxlength="45" value="<?php echo $this->_options['import_password']; ?>"> &nbsp;&nbsp;&nbsp; Confirm: <input type="password" name="#import_password_again" id="import_password_again" size="20" maxlength="45" value="<?php echo $this->_options['import_password']; ?>"></td>
								</tr>
								<tr>
									<td><label for="repairbuddy_password"><?php _e('RepairBuddy password', 'it-l10n-backupbuddy'); $this->tip( __('[Example: myp@ssw0rD] - Required password for running the RepairBuddy troubleshooting/repair script. This prevents unauthorized access when using this tool.', 'it-l10n-backupbuddy') ); ?></label></td>
									<td><input type="password" name="#repairbuddy_password" id="repairbuddy_password" size="20" maxlength="45" value="<?php echo $this->_options['repairbuddy_password']; ?>"> &nbsp;&nbsp;&nbsp; Confirm: <input type="password" name="#repairbuddy_password_again" id="repairbuddy_password_again" size="20" maxlength="45" value="<?php echo $this->_options['repairbuddy_password']; ?>"></td>
								</tr>
								<tr>
									<td valign="top"><label><?php _e('Backup reminders', 'it-l10n-backupbuddy'); $this->tip( __('[Default: enabled] - When enabled links will be displayed upon post or page edits and during WordPress upgrades to remind and allow rapid backing up after modifications or before upgrading.', 'it-l10n-backupbuddy') ); ?></label></td>
									<td>
										<input type="hidden" name="#backup_reminders" value="0" />
										<input type="checkbox" name="#backup_reminders" id="backup_reminders" value="1" <?php if ( $this->_options['backup_reminders'] == '1' ) { echo 'checked'; } ?> /> <label for="backup_reminders"><?php _e('Enable backup reminders', 'it-l10n-backupbuddy');?></label>
									</td>
								</tr>
								<tr>
									<td valign="top"><label><?php _e('Backup all database tables', 'it-l10n-backupbuddy'); $this->tip( __('[Default: disabled] - Checking this box will result in ALL tables and data in the database being backed up, even database content not related to WordPress, its content, or plugins (based on prefix).  This is useful if you have other software installed on your hosting that stores data in your database.', 'it-l10n-backupbuddy') ); ?></label></td>
									<td>
										<input type="hidden" name="#backup_nonwp_tables" value="0" />
										<input type="checkbox" name="#backup_nonwp_tables" id="backup_nonwp_tables" value="1" <?php if ( $this->_options['backup_nonwp_tables'] == '1' ) { echo 'checked'; } ?> />
										<label for="backup_nonwp_tables">
											<?php _e("Enable backing up all tables in your database (not just WordPress')",'it-l10n-backupbuddy');?>
											<br />
											<span style="color: #AFAFAF; margin-left: 26px;"><?php _e('Use this if your plugins create their own custom tables.', 'it-l10n-backupbuddy');?></span>
										</label>
										<br />
									</td>
								<tr>
									<td valign="top"><label><?php _e('Compatibility/Troubleshooting options', 'it-l10n-backupbuddy'); $this->tip( __('Various options to aid in making BackupBuddy work in less than ideal server environments. These are useful for working around problems. You may be directed by support to modify these if you encounter problems.', 'it-l10n-backupbuddy') ); ?>  <?php $this->video( 'E37G9X6eNpg#69', __('Compatibility Options Details', 'it-l10n-backupbuddy') ); ?></label></td>
									<td>
										<input type="hidden" name="#compression" value="0" />
										<input type="checkbox" name="#compression" id="compression" value="1" <?php if ( $this->_options['compression'] == '1' ) { echo 'checked'; } ?> />
										<label for="compression">
											<?php _e('Enable ZIP compression', 'it-l10n-backupbuddy'); $this->tip( __('[Default: enabled] - ZIP compression decreases file sizes of stored backups. If you are encountering timeouts due to the script running too long, disabling compression may allow the process to complete faster.', 'it-l10n-backupbuddy') ); ?>
											<br />
											<span style="color: #AFAFAF; margin-left: 26px;"><?php _e('Disable for large sites causing backups to not complete.', 'it-l10n-backupbuddy'); ?></span>
										</label>
										<br />
										<input type="hidden" name="#integrity_check" value="0" />
										<input type="checkbox" name="#integrity_check" id="integrity_check" value="1" <?php if ( $this->_options['integrity_check'] == '1' ) { echo 'checked'; } ?> />
										<label for="integrity_check">
											<?php _e('Perform integrity check on backup files', 'it-l10n-backupbuddy'); $this->tip( __('[Default: enabled] - By default each backup file is checked for integrity and completion the first time it is viewed on the Backup page.  On some server configurations this may cause memory problems as the integrity checking process is intensive.  If you are experiencing out of memory errors on the Backup file listing, you can uncheck this to disable this feature.', 'it-l10n-backupbuddy') ); ?>
											<br />
											<span style="color: #AFAFAF; margin-left: 26px;"><?php _e('Use if you have problems viewing your backup listing.', 'it-l10n-backupbuddy');?></span>
										</label>
										<br />
										<input type="hidden" name="#force_compatibility" value="0" />
										<input type="checkbox" name="#force_compatibility" id="force_compatibility" value="1" <?php if ( $this->_options['force_compatibility'] == '1' ) { echo 'checked'; } ?> />
										<label for="force_compatibility">
											<?php _e('Advanced: Force compatibility mode backups', 'it-l10n-backupbuddy'); $this->tip( __('[Default: disabled] - (WARNING: This forces the less reliable mode of operation. Only use if absolutely necessary. Checking this box can cause backup failures if it is not needed.) Under normal circumstances compatibility mode is automatically entered as needed without user intervention. However under some server configurations the native backup system is unavailable but is incorrectly reported as functioning by the server.  Forcing compatibility may fix problems in this situation by bypassing the native backup system check entirely.', 'it-l10n-backupbuddy' ) ); ?>
											<br />
											<span style="color: #AFAFAF; margin-left: 26px;"><?php _e('Only use if absolutely necessary or directed by support.', 'it-l10n-backupbuddy');?></span>
										</label>
										<br />
										<input type="hidden" name="#skip_database_dump" value="0" />
										<input type="checkbox" name="#skip_database_dump" id="skip_database_dump" value="1" <?php if ( $this->_options['skip_database_dump'] == '1' ) { echo 'checked'; } ?> />
										<label for="skip_database_dump">
											<?php _e('Skip database dump on backup', 'it-l10n-backupbuddy'); $this->tip( __('[Default: disabled] - (WARNING: This prevents BackupBuddy from backing up the database during any kind of backup. This is for troubleshooting / advanced usage only to work around being unable to backup the database.', 'it-l10n-backupbuddy' ) ); ?>
											<br />
											<span style="color: #AFAFAF; margin-left: 26px;"><?php _e('Only use if absolutely necessary or directed by support.', 'it-l10n-backupbuddy');?></span>
										</label>
									</td>
								</tr>
								<tr>
									<td><label for="log_level"><?php echo __('Logging level', 'it-l10n-backupbuddy'); $this->tip( sprintf( __('[Default: Errors Only] - This option controls how much activity is logged for records or debugging. When in debug mode error emails will contain encrypted debugging data for support. Logs saved in %s/uploads/backupbuddy.txt', 'it-l10n-backupbuddy'), WP_CONTENT_DIR) ); ?></label></td>
									<td>
										<select name="#log_level">
											<option value="0" <?php if ( $this->_options['log_level'] == '0' ) { echo 'selected'; } ?>><?php _e('None', 'it-l10n-backupbuddy');?></option>
											<option value="1" <?php if ( $this->_options['log_level'] == '1' ) { echo 'selected'; } ?>><?php _e('Errors Only', 'it-l10n-backupbuddy');?></option>
											<option value="2" <?php if ( $this->_options['log_level'] == '2' ) { echo 'selected'; } ?>><?php _e('Errors & Warnings', 'it-l10n-backupbuddy');?></option>
											<option value="3" <?php if ( $this->_options['log_level'] == '3' ) { echo 'selected'; } ?>><?php _e('Everything (debug mode)', 'it-l10n-backupbuddy');?></option>
										</select>
									</td>
								</tr>
								<tr>
									<td><label for="backup_mode"><?php echo __('Manual backup mode', 'it-l10n-backupbuddy'), ' '; $this->tip( __('[Default: Modern] - If you are encountering difficulty backing up due to WordPress cron, HTTP Loopbacks, or other features specific to version 2.x you can try classic mode which runs like BackupBuddy v1.x did.', 'it-l10n-backupbuddy') ); ?></label></td>
									<td>
										<select name="#backup_mode">
											<option value="1" <?php if ( $this->_options['backup_mode'] == '1' ) { echo 'selected'; } ?>><?php _e('Classic (v1.x)', 'it-l10n-backupbuddy');?></option>
											<option value="2" <?php if ( $this->_options['backup_mode'] == '2' ) { echo 'selected'; } ?>><?php _e('Modern (v2.x)', 'it-l10n-backupbuddy');?></option>
										</select>
									</td>
								</tr>
								<?php
								if ( is_multisite() ) {
									?>
									<tr>
										<td><?php esc_html_e( 'Multisite: Allow individual sites to be exported?', 'it-l10n-backupbuddy' ); ?> (BETA)</td>
										<td>
											<select name="#multisite_export">
												<option value="1" <?php if ( $this->_options['multisite_export'] == '1' ) { echo 'selected'; } ?>><?php _e('Yes', 'it-l10n-backupbuddy');?></option>
												<option value="2" <?php if ( $this->_options['multisite_export'] == '2' ) { echo 'selected'; } ?>><?php _e('No', 'it-l10n-backupbuddy');?></option>
											</select>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td><?php esc_html_e( 'Alternative Zip System', 'it-l10n-backupbuddy' ); $this->tip( __('[Default: Disabled] - ', 'it-l10n-backupbuddy') ); ?> (BETA)</td>
									<td>
										<select name="#temporary_options#experimental_zip">
											<option value="1" <?php if ( $this->_options['temporary_options']['experimental_zip'] == '1' ) { echo 'selected'; } ?>><?php _e('Enabled', 'it-l10n-backupbuddy');?></option>
											<option value="0" <?php if ( $this->_options['temporary_options']['experimental_zip'] == '0' ) { echo 'selected'; } ?>><?php _e('Disabled (default)', 'it-l10n-backupbuddy');?></option>
										</select>
									</td>
								</tr>
								<?php
								/*

								<tr>
									<td><label for="role_access">Plugin access limits <?php $this->tip( '[Default: administrator] - Determine which level of users are allowed to have access to all of BackupBuddy\'s features. WARNING: This will allow other roles access to configure BackupBuddy & download your backup files which may release sensitive information and be a security risk. Use caution.' ); ?></label></td>
									<td>
										<select name="#role_access">
											<option value="administrator" <?php if ( $this->_options['role_access'] == 'administrator' ) { echo 'selected'; } ?>>Administrator</option>
											<option value="editor" <?php if ( $this->_options['role_access'] == 'editor' ) { echo 'selected'; } ?>>Editor (use caution)</option>
											<option value="author" <?php if ( $this->_options['role_access'] == 'author' ) { echo 'selected'; } ?>>Author (use caution)</option>
											<option value="contributer" <?php if ( $this->_options['role_access'] == 'contributer' ) { echo 'selected'; } ?>>Contributer (use caution)</option>
											<option value="subscriber" <?php if ( $this->_options['role_access'] == 'subscriber' ) { echo 'selected'; } ?>>Subscriber (use caution)</option>
										</select>
									</td>
								</tr>
								*/
								?>
							</table>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php _e('Archive Storage Limits', 'it-l10n-backupbuddy');?>  <?php $this->video( 'E37G9X6eNpg#176', __('Archive Storage Limits Tutorial', 'it-l10n-backupbuddy') ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<td><label for="archive_limit"><?php _e('Maximum number of archived backups', 'it-l10n-backupbuddy');?> <?php $this->tip( __('[Example: 10] - Maximum number of archived backups to store. Any new backups created after this limit is met will result in your oldest backup(s) being deleted to make room for the newer ones. Changes to this setting take place once a new backup is made. Set to zero (0) for no limit.', 'it-l10n-backupbuddy') ); ?></label></td>
									<td>
										<input type="text" name="#archive_limit" id="archive_limit" size="2" maxlength="6" value="<?php echo $this->_options['archive_limit']; ?>" style="text-align: right;" /> <?php _e('total', 'it-l10n-backupbuddy');?>
									</td>
								</tr>
								<tr>
									<td><label for="archive_limit_size"><?php _e('Maximum size of archived backups', 'it-l10n-backupbuddy');?> <?php $this->tip( __('[Example: 350] - Maximum size (in MB) to allow your total archives to reach. Any new backups created after this limit is met will result in your oldest backup(s) being deleted to make room for the newer ones. Changes to this setting take place once a new backup is made. Set to zero (0) for no limit.', 'it-l10n-backupbuddy') ); ?></label></td>
									<td>
										<input type="text" name="#archive_limit_size" id="archive_limit_size" size="2" maxlength="6" value="<?php echo $this->_options['archive_limit_size']; ?>" style="text-align: right;" /> <?php _e('MB total', 'it-l10n-backupbuddy');?>
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php _e('Remote Offsite Storage / Destinations', 'it-l10n-backupbuddy');?>  <?php $this->video( 'E37G9X6eNpg#262', __('Remote Offsite Management / Remote Clients Tutorial', 'it-l10n-backupbuddy') ); ?></span></h3>
						<div class="inside">
							<a href="<?php echo admin_url('admin-ajax.php') . '?action=pb_backupbuddy_remotedestination'; ?>&#038;TB_iframe=1&#038;width=640&#038;height=600" class="thickbox button secondary-button" style="margin-top: 3px;" title="<?php _e('Select a destination within to manage archives within the destination', 'it-l10n-backupbuddy');?>"><?php _e('Manage Remote Destinations & Archives', 'it-l10n-backupbuddy');?></a>
							<span style="color: #AFAFAF;">
								&nbsp;&nbsp;&nbsp;
								<?php _e('Manage Amazon S3, Rackspace Cloudfiles, Email, and FTP.', 'it-l10n-backupbuddy');?>
							</span>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e('Backup Exclusions', 'it-l10n-backupbuddy');?>  <?php $this->video( 'E37G9X6eNpg#294', __('Backup Directory Excluding Tutorial', 'it-l10n-backupbuddy') ); ?></span></h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<td>
										<?php echo sprintf( __('Click directory to navigate to it or %s to exclude it.', 'it-l10n-backupbuddy'), '<img src="' . $this->_pluginURL .'/images/bullet_delete.png" style="vertical-align: -3px;" />'); $this->tip( __('Click on a directory name to navigate directories. Click the red minus sign to the right of a directory to place it in the exclusion list. /wp-content/, /wp-content/uploads/, and BackupBuddy backup & temporary directories cannot be excluded. BackupBuddy directories are automatically excluded.', 'it-l10n-backupbuddy' ) ); ?><br />
										<div id="exlude_dirs" class="jQueryOuterTree">
										</div>
										<small><?php _e('Available if server does not require compatibility mode.', 'it-l10n-backupbuddy');?> <?php $this->tip( __('If you receive notifications that your server is entering compatibility mode or that native zip functionality is unavailable then this feature will not be available due to technical limitations of the compatibility mode.  Ask your host to correct the problems causing compatibility mode or move to a new server.', 'it-l10n-backupbuddy') ); ?></small>
									</td>
									<td>
										<?php
											_e('Excluded directories (path relative to root)' , 'it-l10n-backupbuddy');
											$this->tip( __('List paths relative to root to be excluded from backups.  You may use the directory selector to the left to easily exclude directories by ctrl+clicking them.  Paths are relative to root. Ex: /wp-content/uploads/junk/', 'it-l10n-backupbuddy') );
											echo '<br />';
										?>
										<textarea name="#excludes" wrap="off" rows="4" cols="35" maxlength="9000" id="exclude_dirs"><?php echo $this->_options['excludes']; ?></textarea>
										<br /><small><?php _e('List one path per line. Remove a line to remove exclusion.', 'it-l10n-backupbuddy');?></small>
									</td>
								</tr>
								
							</table>
						</div>
					</div>
					
					
					
					
					
				</div>
			</div>
		</div>

		
		
		<p class="submit clear"><input type="submit" name="save" value="<?php _e('Save Settings', 'it-l10n-backupbuddy');?>" class="button-primary" id="save" /></p>
		<?php $this->nonce(); ?>
		<br />
	</form>
</div>
