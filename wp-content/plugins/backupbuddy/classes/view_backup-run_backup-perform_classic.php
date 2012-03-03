<?php
$this->title( 'Backing Up [Classic Mode]' );
?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#pb_backupbuddy_advanced_details").click(function() {
			jQuery("#pb_backupbuddy_advanced_details_div").slideToggle();
		});
	});
</script>

<br />
<div id="pb_backupbuddy_archive_download" style="display: none; width: 793px; text-align: center;">
	<a id="pb_backupbuddy_archive_url" href="#" style="text-decoration: none;">Download backup ZIP archive (<span class="backupbuddy_archive_size">0 MB</span>)</a>
	|
	<a href="<?php echo $this->_selfLink; ?>-backup" style="text-decoration: none;">Back to backup page</a>
</div>
<br />

<span style="font-size: 1.17em; font-weight: bold;">Status</span><br>
<textarea id="backupbuddy_messages" cols="75" rows="6" style="width: 793px;">Backing up with BackupBuddy v<?php echo $this->_parent->plugin_info( 'version' ); ?>...</textarea>

<p><a id="pb_backupbuddy_advanced_details" class="button secondary-button">Advanced Details</a></p>
<div id="pb_backupbuddy_advanced_details_div">
	<textarea id="backupbuddy_details" cols="75" rows="6" style="width: 793px;">Backing up with BackupBuddy v<?php echo $this->_parent->plugin_info( 'version' ); ?>...</textarea>
</div>

<br /><br />

<div style="color: #AFAFAF; width: 793px; text-align: center;">
	Warning. While in classic mode you leaving this page before the backup is complete may interupt it.
</div>

<br /><br />


<?php
require_once( $this->_pluginPath . '/classes/backup.php' );
$backup = new pluginbuddy_backupbuddy_backup( $this );


if ( $backup->start_backup_process( $_GET['run_backup'], 'manual' ) !== true ) {
	echo 'Error #4233344443: Backup failure';
	echo $backup->get_errors();
}
?>

