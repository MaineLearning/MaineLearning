<?php
echo $this->status_box( 'Cleaning up . . .' );
echo '<div id="pb_importbuddy_working" style="width: 793px;"><center><img src="' . $this->_pluginURL . '/images/working.gif" title="Working... Please wait as this may take a moment..."></center></div>';
flush();

$this->load_backup_dat(); // Set up backup data from the backupbuddy_dat.php.

$url = '';
if ( is_subdomain_install() ) {
	$url = 'http://' . $_POST[ 'blog_path' ];
} else {
	global $current_site;
	$url = 'http://' . $current_site->domain . $_POST[ 'blog_path' ];
}


if ( isset( $_POST['delete_backup'] ) && ( $_POST['delete_backup'] == '1' ) ) {
	$this->status( 'message', 'Deleting backup file.' );
	$this->remove_file( ABSPATH . $this->import_options[ 'file' ], 'backup .ZIP file (' . $this->import_options['file'] . ')', true );
} else {
	$this->status( 'message', 'Skipping backup file deletion.' );
}

if ( isset( $_POST['delete_temp'] ) && ( $_POST['delete_temp'] == '1' ) ) {
	$this->status( 'message', 'Deleting temporary files.' );
	$this->_parent->_parent->delete_directory_recursive( $this->import_options[ 'extract_to' ] );
} else {
	$this->status( 'message', 'Skipping temporary file deletion.' );
}


$this->status( 'message', 'Cleanup complete.' );
echo '<script type="text/javascript">jQuery("#pb_importbuddy_working").hide();</script>';
flush();
?>

<h3>Site Import Complete</h3>
Your site has been succesfully imported into the Multisite Network.

<br><br>

<b>Site</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $url; ?>"><?php echo $url; ?></a>