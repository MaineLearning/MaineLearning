<?php
$this->title( __('Backup, Restore, Migrate', 'it-l10n-backupbuddy') );
$this->_parent->periodic_cleanup();
?>

<style> 
	.graybutton {
		background: url(<?php echo $this->_pluginURL; ?>/images/buttons/grays2.png) top repeat-x;
		min-width: 158px;
		height: 138px;
		display: block;
		float: left;
		-moz-border-radius: 6px;
		border-radius: 6px;
		border: 1px solid #c9c9c9;
		margin-bottom: 3px;
	}
	.graybutton:hover {
		background: url(<?php echo $this->_pluginURL; ?>/images/buttons/grays2.png) bottom repeat-x;
		border: 1px solid #aaaaaa;
	}
	.graybutton:active {
		background: url(<?php echo $this->_pluginURL; ?>/images/buttons/grays2.png) bottom repeat-x;
		border: 1px solid transparent;
	}
	.leftround {
		-moz-border-radius: 4px 0 0 4px;
		border-radius: 4px 0 0 4px;
		border-right: 1px solid #c9c9c9;
	}
	.rightround {
		-moz-border-radius: 0 4px 4px 0;
		border-radius: 0 4px 4px 0;
	}
	.dbonlyicon {
		background: url(<?php echo $this->_pluginURL; ?>/images/buttons/dbonly-icon.png);
		width: 60px;
		height: 60px;
		margin: 15px auto 0 auto;
		display: block;
		float: center;
	}
	.allcontenticon {
		background: url(<?php echo $this->_pluginURL; ?>/images/buttons/allcontent-icon.png);
		width: 60px;
		height: 60px;
		margin: 15px auto 0 auto;
		display: block;
		float: center;
	}
	.restoremigrateicon {
		background: url(<?php echo $this->_pluginURL; ?>/images/buttons/restoremigrate-icon.png);
		width: 60px;
		height: 60px;
		margin: 15px auto 0 auto;
		display: block;
		float: center;
	}
	.repairbuddyicon {
		background: url(<?php echo $this->_pluginURL; ?>/images/buttons/allcontent-icon.png);
		width: 60px;
		height: 60px;
		margin: 15px auto 0 auto;
		display: block;
		float: center;
	}
	.bbbutton-text {
		font-family: Georgia, Times, serif;
		font-size: 18px;
		font-style: italic;
		min-width: 158px;
		text-align: center;
		
		/* line-height: 60px; */
		padding: 13px;
		
		color: #666666;
		text-shadow: 1px 1px 1px #ffffff;
		clear: both;
	}
	.bbbutton-smalltext {
		font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
		font-size: 9px;
		font-style: normal;
		text-shadow: 0;
		padding-top: 3px;
	}
</style>

<p><?php _e('Select a backup option on the left or a script on the right from the buttons below to begin:','it-l10n-backupbuddy');?></p>
<br />

<?php
// Show warning if recent backups reports it is not complete yet. (3min is recent)
if ( isset( $this->_options['backups'][$this->_options['last_backup_serial']]['updated_time'] ) && ( time() - $this->_options['backups'][$this->_options['last_backup_serial']]['updated_time'] < 180 ) ) { // Been less than 3min since last backup.
	if ( !empty( $this->_options['backups'][$this->_options['last_backup_serial']]['steps'] ) ) {
		$this->alert( __('A backup was recently started and reports unfinished steps. You should not begin<br />another backup unless you are sure the previous backup has completed or failed.', 'it-l10n-backupbuddy'), true );
	}
}
?>

<div>
	<a href="<?php echo $this->_selfLink; ?>-backup&run_backup=db" style="text-decoration: none;" title="<?php _e('Backs up the database only. This backup is fast and creates a small backup file. Includes pages, posts, comments, etc. Files such as media are not included.', 'it-l10n-backupbuddy');?>">
		<div class="graybutton leftround">
			<div class="dbonlyicon"></div>
			<div class="bbbutton-text">
				<?php _e('Database Backup', 'it-l10n-backupbuddy');?><br />
				<div class="bbbutton-smalltext"><?php _e('back up content only', 'it-l10n-backupbuddy');?></div>
			</div>
		</div>
	</a>
	<a href="<?php echo $this->_selfLink; ?>-backup&run_backup=full" style="text-decoration: none;" title="<?php _e('Backs up everything including the database, settings, themes, plugins, files, media, etc. This backup takes more time and can create a large file.', 'it-l10n-backupbuddy');?>">
		<div class="graybutton rightround" style="margin-right: 30px">
			<div class="allcontenticon"></div>
			<div class="bbbutton-text">
				<?php _e('Full Backup', 'it-l10n-backupbuddy');?><br />
				<div class="bbbutton-smalltext"><?php _e('back up everything', 'it-l10n-backupbuddy');?></div>
			</div>
		</div>
	</a>
	<div style="display: inline-block;">
		<a href="<?php echo admin_url( 'admin-ajax.php' ) . '?action=backupbuddy_importbuddy_beta'; ?>" style="text-decoration: none;" title="BETA! <?php _e('Download the import & migration script, importbuddy.php', 'it-l10n-backupbuddy'); ?>">
		<div style=" position: absolute; width: 382px;">
			<div style="position: absolute; z-index: 42; right: 0px;">
				<img src="<?php echo $this->_pluginURL; ?>/images/beta.png" title="Beta" width="60" height="60">
			</div>
		</div>
		</a>
		
		<?php
			if ( $this->_options['import_password'] == '' ) {
				echo '<a onclick="alert(\'' . __( 'Please set an ImportBuddy password on the BackupBuddy Settings page to download this script. This is required to prevent unauthorized access to the script when in use.', 'it-l10n-backupbuddy' ) . '\'); return false;" href="" style="text-decoration: none;" title="' . __( 'Download the import & migration script, importbuddy.php', 'it-l10n-backupbuddy') . '">';
			} else {
				echo '<a href="' . admin_url( 'admin-ajax.php' ) . '?action=backupbuddy_importbuddy" style="text-decoration: none;" title="' . __('Download the import & migration script, importbuddy.php', 'it-l10n-backupbuddy') . '">';
			}
		?>
			<div class="graybutton">
				<div class="restoremigrateicon"></div>
				<div class="bbbutton-text">
					<?php _e('ImportBuddy', 'it-l10n-backupbuddy');?><br />
					<div class="bbbutton-smalltext"><?php _e('restoring & migration script', 'it-l10n-backupbuddy');?></div>
				</div>
			</div>
		</a>
		
		<?php
			if ( $this->_options['repairbuddy_password'] == '' ) {
				echo '<a onclick="alert(\'' . __( 'Please set a RepairBuddy password on the BackupBuddy Settings page to download this script. This is required to prevent unauthorized access to the script when in use.', 'it-l10n-backupbuddy' ) . '\'); return false;" href="" style="text-decoration: none;" title="' . __( 'Download the troubleshooting & repair script, repairbuddy.php', 'it-l10n-backupbuddy') . '">';
			} else {
				echo '<a href="' . admin_url( 'admin-ajax.php' ) . '?action=backupbuddy_repairbuddy" style="text-decoration: none;" title="' . __('Download the troubleshooting & repair script, repairbuddy.php', 'it-l10n-backupbuddy') . '">';
			}
		?>
			<div class="graybutton" style="margin-left: 10px;">
				<div class="repairbuddyicon"></div>
				<div class="bbbutton-text">
					<?php _e('RepairBuddy', 'it-l10n-backupbuddy');?><br />
					<div class="bbbutton-smalltext"><?php _e('troubleshooting & repair script', 'it-l10n-backupbuddy');?></div>
				</div>
			</div>
		</a>
		
	</div>
	
	
</div>
<?php
/*
 * <p>Get the <a href="<?php echo $this->importbuddy_link(); ?>" style="text-decoration: none;" title="<?php _e('Download the import & migration script, importbuddy.php', 'it-l10n-backupbuddy'); ?>">non-beta version of ImportBuddy here</a>.<br />For <a href='http://ithemes.com/codex/page/BackupBuddy_Multisite#BackupBuddy.27s_IMPORTBUDDY.PHP_File'>Multisite</a> (Codex), use ImportBuddy Beta.</p>
 */
?>

<br style="clear: both;" />

<?php
echo '<h3>', __('Backup Archives (local)', 'it-l10n-backupbuddy'), '</h3>';
flush();
require_once( 'view_backup-listing.php' );

echo '<br /><br />';
?>


