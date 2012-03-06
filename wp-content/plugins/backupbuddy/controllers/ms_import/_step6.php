<?php
$blog_id = isset( $_POST[ 'blog_id' ] ) ? absint( $_POST[ 'blog_id' ] ) : 0;
global $wpdb;
$new_db_prefix = $wpdb->get_blog_prefix( $blog_id );
//switch_to_blog( $blog_id );

echo $this->status_box( 'Migrating URLs in imported data . . .' );
echo '<div id="pb_importbuddy_working" style="width: 793px;"><center><img src="' . $this->_pluginURL . '/images/working.gif" title="Working... Please wait as this may take a moment..."></center></div>';
flush();

// Set up destination upload path and URL information.
/*
$wp_upload_dir = ABSPATH . $this->get_ms_option( $blog_id, 'upload_path' );
$wp_upload_dir = rtrim( $wp_upload_dir, "\\/" ); // Trim trailing slash if there (shouldnt be by default but someone could have manually edited it)
$this->status( 'details', 'Destination site uploads real file path: ' . $wp_upload_dir );
$wp_upload_url = $this->get_ms_option( $blog_id, 'fileupload_url' );
$wp_upload_url = rtrim( $wp_upload_url, "\\/" ); // Trim trailing slash if there (shouldnt be by default but someone could have manually edited it)
*/
$wp_upload_dir = $_POST['upload_path'];
$wp_upload_url = $_POST['fileupload_url'];
$this->status( 'details', 'Destination site real local path: ' . $wp_upload_dir );
$this->status( 'details', 'Destination site virtual uploads URL: ' . $wp_upload_url );

$this->load_backup_dat(); // Needed by _migrate_database.php.

// Figure out destination site URL for replacements.
$destination_url = '';
if ( is_subdomain_install() ) {
	$destination_url = 'http://' . trim( $_POST[ 'blog_path' ], '/' ) . '/';
	$this->status( 'details', 'Sub-domain installation.' );
} else {
	$destination_url = 'http://' . $current_blog->domain . '/' . trim( $_POST[ 'blog_path' ], '/' ) . '/'; // Slashes should already be handled but just in case..
	$this->status( 'details', 'NOT sub-domain installation.' );
}
$this->status( 'message', 'Destination URL: ' . $destination_url );
$this->import_options['siteurl'] = $destination_url;



global $wpdb;
// Clean out some spam. TODO: any particular reason why this is done?
$wpdb->query( $wpdb->prepare( "DELETE from {$new_db_prefix}_comments WHERE comment_approved = 'spam'" ) );



// Set up variables for _migrate_database.php
$multisite_network_db_prefix = $wpdb->prefix;
$destination_type = 'multisite_import'; // Used by _migrate_databasep.php to determine destination type specific data migration.
$destination_siteurl = $destination_url;
$destination_home = $destination_url;
$destination_db_prefix = $new_db_prefix;
if ( isset( $this->_options['domain'] ) ) { $destination_domain = $this->_options['domain']; } // Only in importbuddy.
if ( isset( $this->_options['path'] ) ) { $destination_path = $this->_options['path']; } // Only in importbuddy.

if ( isset( $this->advanced_options['skip_database_migration'] ) && ( $this->advanced_options['skip_database_migration'] == 'true' ) ) {
	$this->status( 'message', 'Skipping database migration based on advanced settings.' );
} else {
	require_once( $this->_parent->_pluginPath . '/classes/_migrate_database.php' );
}
/*
require_once( $this->_parent->_pluginPath . '/lib/dbreplace/dbreplace.php' );
$dbreplace = new pluginbuddy_dbreplace( $this );
$this->migrate_database( $upload_url );
*/


$this->status( 'message', 'Database migrated.' );
echo '<script type="text/javascript">jQuery("#pb_importbuddy_working").hide();</script>';
flush();


global $current_site;
	$errors = false;	
	$blog = $domain = $path = '';
	$form_url = add_query_arg( array(
		'step' => '7',
		'action' => 'step7'
	) , $this->_parent->_selfLink . '-msimport' );
?>

<form method="post" action="<?php echo esc_url( $form_url ); ?>">
<?php wp_nonce_field( 'bbms-migration', 'pb_bbms_migrate' ); ?>
<input type='hidden' name='backup_file' value='<?php echo esc_attr( $_POST[ 'backup_file' ] ); ?>' />
<input type='hidden' name='blog_id' value='<?php echo esc_attr( absint( $_POST[ 'blog_id' ] ) ); ?>' />
<input type='hidden' name='blog_path' value='<?php echo esc_attr( $_POST[ 'blog_path' ] ); ?>' />
<input type='hidden' name='global_options' value='<?php echo base64_encode( serialize( $this->advanced_options ) ); ?>' />
<?php submit_button( __('Next Step') . ' &raquo;', 'primary', 'add-site' ); ?>
</form>