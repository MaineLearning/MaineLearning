<?php
global $wpdb, $current_site, $current_blog;
$blog_id = isset( $_POST[ 'blog_id' ] ) ? absint( $_POST[ 'blog_id' ] ) : 0;
//switch_to_blog( intval( $blog_id ) );
if ( $blog_id == '' ) {
	die ('Bad blog id for table prefix. Error #4334343443434354548398439.' );
}
//$new_db_prefix = $wpdb->prefix . $blog_id . '_';
$new_db_prefix = $wpdb->get_blog_prefix( $blog_id );
//die ( 'new prefix: ' . $new_db_prefix );

echo $this->status_box( 'Importing database content & data . . .' );
echo '<div id="pb_importbuddy_working" style="width: 793px;"><center><img src="' . $this->_pluginURL . '/images/working.gif" title="Working... Please wait as this may take a moment..."></center></div>';
flush();


// ********** BEGIN DROP TABLES **********
$drop_tables = array(
	'commentmeta',
	'comments',
	'links',
	'options',
	'postmeta',
	'posts',
	'terms',
	'term_relationships',
	'term_taxonomy',
	'usermeta',
	'users'
);
$this->status( 'message', 'Dropping existing tables (if any) for this specific site . . .' );
foreach ( $drop_tables as $table ) {
	$table = $new_db_prefix . $table;
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $table );
}
$this->status( 'message', 'Existing tables dropped.' );
// ********** END DROP TABLES **********


$this->load_backup_dat(); // Need for getting prefix for import update of prefix.


// ********** BEGIN IMPORT TABLES **********
$options['db_server'] = DB_HOST;
$options['db_name'] = DB_NAME;
$options['db_user'] = DB_USER;
$options['db_password'] = DB_PASSWORD;
$options['db_prefix'] = $new_db_prefix; // New prefix.
$options['zip_id'] = $this->import_options['zip_id'];
$options['old_prefix'] = $this->_backupdata['db_prefix'];
$options['max_execution_time'] = $this->advanced_options['max_execution_time'];
$options['database_directory'] = $this->import_options['extract_to'] . '/wp-content/uploads/backupbuddy_temp/' . $this->import_options['zip_id'] . '/';
$this->status( 'details', 'Looking for database to import in directory: `' . $options['database_directory'] . '`' );

$this->status( 'details', 'Starting actual import . . .' );

if ( isset( $this->advanced_options['skip_database_import'] ) && ( $this->advanced_options['skip_database_import'] == 'true' ) ) {
	$this->status( 'message', 'Skipping database import based on advanced settings.' );
	$import_result = true;
} else {
	require_once( $this->_parent->_pluginPath . '/lib/dbimport/dbimport.php' );
	if ( isset( $this->advanced_options['ignore_sql_errors'] ) && ( $this->advanced_options['ignore_sql_errors'] == 'true' ) ) {
		$ignore_sql_errors = true;
	} else {
		$ignore_sql_errors = false;
	}
	$dbimport = new pluginbuddy_dbimport( $options, $this, $ignore_sql_errors );
	$this->status( 'details', 'Actual import done.' );
	
	if ( isset( $_GET['continue'] ) && ( $_GET['continue'] != '' ) ) {
		$db_continue = (int)$_GET['continue'];
	} else {
		$db_continue = 0;
	}
	
	$import_result = $dbimport->restore_database( $db_continue, true ); // true: success, false: failure, integer: query to start next db import chunk on
}

if ( $import_result === true ) { // Finished import.
	$form_url = add_query_arg( array( 'step' => '6', 'action' => 'step6' ) , $this->_parent->_selfLink . '-msimport' );
	$this->status( 'message', 'Database imported.' );
} elseif ( $import_result === false ) { // Import Failed.
	die( 'Fatal import failure. Error #3489343' );
} else { // Need to resume.
	$this->status( 'message', 'The database import is taking too long and must be broken up into multiple steps. Please continue to resume the import.' );
	$form_url = add_query_arg( array( 'step' => '5', 'action' => 'step5', 'continue' => $import_result ) , $this->_parent->_selfLink . '-msimport' );
}
// ********** END IMPORT TABLES **********


echo '<script type="text/javascript">jQuery("#pb_importbuddy_working").hide();</script>';
flush();


if ( $import_result === false ) {
	echo '<p>' . __( 'Database import failed.', 'it-l10n-backupbuddy' ) . '</p>';
} else {
	global $current_site;
		$errors = false;	
		$blog = $domain = $path = '';
		
	?>
	
	<form method="post" action="<?php echo esc_url( $form_url ); ?>">
	<?php wp_nonce_field( 'bbms-migration', 'pb_bbms_migrate' ); ?>
	<input type='hidden' name='backup_file' value='<?php echo esc_attr( $_POST[ 'backup_file' ] ); ?>' />
	<input type='hidden' name='blog_id' value='<?php echo esc_attr( absint( $_POST[ 'blog_id' ] ) ); ?>' />
	<input type='hidden' name='blog_path' value='<?php echo esc_attr( $_POST[ 'blog_path' ] ); ?>' />
	<input type='hidden' name='upload_path' value='<?php echo esc_attr( $_POST['upload_path'] ); ?>' />
	<input type='hidden' name='global_options' value='<?php echo base64_encode( serialize( $this->advanced_options ) ); ?>' />
	<input type='hidden' name='fileupload_url' value='<?php echo esc_attr( $_POST['fileupload_url'] ); ?>' />
	<?php
	if ( $import_result === true ) {
		submit_button( __('Next Step') . ' &raquo;', 'primary', 'add-site' );
	} else {
		submit_button( __('Continue Database Import') . ' &raquo;', 'primary', 'add-site' );
	}
	?>
	</form>
<?php } ?>