<?php
// Used for drag & drop / collapsing boxes.
wp_enqueue_style('dashboard');
wp_print_styles('dashboard');
wp_enqueue_script('dashboard');
wp_print_scripts('dashboard');

wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );
// Handles resizing thickbox.
if ( !wp_script_is( 'media-upload' ) ) {
	wp_enqueue_script( 'media-upload' );
	wp_print_scripts( 'media-upload' );
}
wp_enqueue_script( 'backupbuddy-ms-export', $this->_parent->_pluginURL . '/js/ms.js', array( 'jquery' ) );
wp_print_scripts( 'backupbuddy-ms-export' );

$action = isset( $_GET[ 'action' ] ) ? $_GET[ 'action' ] : false;

$this->title( 'Multisite Import' );
?>
<div class='wrap'>
<p>For BackupBuddy Multisite documentation, please visit the <a href='http://ithemes.com/codex/page/BackupBuddy_Multisite'>BackupBuddy Multisite Codex</a>.</p>
<?php
//check_admin_referer( 'bbms-migration', 'pb_bbms_migrate' );
if ( !current_user_can( 'manage_sites' ) ) 
	wp_die( __( 'You do not have permission to access this page.', 'it-l10n-backupbuddy' ) );
//global $current_blog;
$errors = false;	
$blog = $domain = $path = '';

// ********** BEGIN IMPORT OPTIONS **********
$import_options = array(
	'zip_id' => '',
	'extract_to' => '',
		
	'show_php_warnings' => false,
	'type' => 'zip',
	'skip_files' => false,
	'force_compatibility_slow' => false,
	'force_compatibility_medium' => true,
	'skip_database_import' => false,
);

// Set backup file.
if ( isset( $_POST[ 'backup_file' ] ) ) {
	$import_options['file'] = ABSPATH . $_POST[ 'backup_file' ];
}
// ********** END IMPORT OPTIONS **********


$pluginbuddy_ms_import = new pluginbuddy_ms_import( $this, $action, $import_options );

class pluginbuddy_ms_import {
	var $import_options;
	
	public function __construct( &$parent, $action, $import_options ) {
		$this->_parent = &$parent;
		$this->_var = &$parent->_var;
		$this->_name = &$parent->_name;
		$this->_options = &$parent->_options;
		$this->_pluginPath = &$parent->_pluginPath;
		$this->_pluginURL = &$parent->_pluginURL;
		$this->_selfLink = &$parent->_selfLink;
		
		$this->import_options = $import_options;
		if ( ( $this->import_options['zip_id'] == '' ) && ( isset( $this->import_options['file'] ) ) ) {
			$this->import_options['zip_id'] = $this->get_zip_id( basename( $this->import_options['file'] ) );
		}
		
		// Detect max execution time for database steps so they can pause when needed for additional PHP processes.
		$this->detected_max_execution_time = str_ireplace( 's', '', ini_get( 'max_execution_time' ) );
		if ( is_numeric( $this->detected_max_execution_time ) === false ) {
			$this->detected_max_execution_time = 30;
		}
		
		
		// Set advanced options if they have been passed along.
		if ( isset( $_POST['global_options'] ) && ( $_POST['global_options'] != '' ) ) {
			$this->advanced_options = unserialize( base64_decode( $_POST['global_options'] ) );
		}
		
		$this->time_start = microtime( true );
		
		// Temporarily unzips into the main sites uploads temp 
		$wp_uploads = wp_upload_dir();
		$this->import_options[ 'extract_to' ] = $wp_uploads[ 'basedir' ] . '/backupbuddy_temp/import_' . $this->import_options[ 'zip_id' ];
		
		global $current_blog;
		$import_steps = 8;
		
		switch( $action ) {
			case 'step2':
				echo '<h3>Step 2 of ' . $import_steps . '</h3>';
				require( $this->_parent->_pluginPath . '/controllers/ms_import/_step2.php' );
				break;
			case 'step3':
				echo '<h3>Step 3 of ' . $import_steps . '</h3>';
				require( $this->_parent->_pluginPath . '/controllers/ms_import/_step3.php' );
				break;
			case 'step4':
				echo '<h3>Step 4 of ' . $import_steps . '</h3>';
				require( $this->_parent->_pluginPath . '/controllers/ms_import/_step4.php' );
				break;
			case 'step5':
				echo '<h3>Step 5 of ' . $import_steps . '</h3>';
				require( $this->_parent->_pluginPath . '/controllers/ms_import/_step5.php' );
				break;
			case 'step6':
				echo '<h3>Step 6 of ' . $import_steps . '</h3>';
				require( $this->_parent->_pluginPath . '/controllers/ms_import/_step6.php' );
				break;
			case 'step7':
				echo '<h3>Step 7 of ' . $import_steps . '</h3>';
				require( $this->_parent->_pluginPath . '/controllers/ms_import/_step7.php' );
				break;
			case 'step8':
				echo '<h3>Step 8 of ' . $import_steps . '</h3>';
				require( $this->_parent->_pluginPath . '/controllers/ms_import/_step8.php' );
				break;
			default:
				//require( $this->_parent->_pluginPath . '/classes/' . 'msimport_steps/step1.php' );
				echo '<h3>Step 1 of ' . $import_steps . '</h3>';
				require( $this->_parent->_pluginPath . '/controllers/ms_import/_step1.php' );
				break;
		} //end switch
	}
	
	function load_backup_dat() {
		$dat_file = $this->import_options[ 'extract_to' ] . '/wp-content/uploads/backupbuddy_temp/' . $this->import_options[ 'zip_id' ] . '/backupbuddy_dat.php';
		$this->_backupdata = $this->get_backup_dat( $dat_file );
	}
	
	
	function get_backup_dat( $dat_file ) {
		require_once( $this->_parent->_pluginPath . '/classes/_get_backup_dat.php' );
		return $return;
	}
	
	
	function get_ms_option( $blogID, $option_name ) {
		global $wpdb;
		
		$sql = "SELECT * FROM `" . DB_NAME . "`.`" . $wpdb->get_blog_prefix( $blogID ) . "options` WHERE `option_name` = '" . $option_name . "'";
		//echo $sql;
		$query = $wpdb->prepare( $sql );
		$option_value = $wpdb->get_var( $query, 3 );
		return $option_value;
	}
	
	
	/**
	 *	array_remove()
	 *
	 *	Removes array values in $remove from $array.
	 *
	 *	@param			$array		array		Source array. This will have values removed and be returned.
	 *	@param			$remove		array		Array of values to search for in $array and remove.
	 *	@return						array		Returns array $array stripped of all values found in $remove
	 */
	function array_remove( $array, $remove ) {
		if ( !is_array( $remove ) ) {
			$remove = array( $remove );
		}
		return array_values( array_diff( $array, $remove ) );
	}
	
	
	/**
	 *	status_box()
	 *
	 *	Displays a textarea for placing status text into.
	 *
	 *	@param			$default_text	string		First line of text to display.
	 *	@return							string		HTML for textarea.
	 */
	function status_box( $default_text = '' ) {
		return '<textarea style="width: 793px;" rows="6" cols="75" id="importbuddy_status">' . $default_text . '</textarea><br><br>';
	}		
	
	
	/**
	 *	status()
	 *
	 *	Write a status line into an existing textarea created with the status_box() function.
	 *
	 *	@param			$type		string		message, details, error, or warning. Currently not in use.
	 *	@param			$message	string		Message to append to the status box.
	 *	@return			null
	 */
	function status( $type, $message ) {
		$message = htmlentities( addslashes( $message ) );
		$status = date( $this->_parent->_parent->_timestamp, time() ) . ': ' . $message;
		
		echo '<script type="text/javascript">jQuery( "#importbuddy_status" ).append( "\n' . $status . '");	textareaelem = document.getElementById( "importbuddy_status" );	textareaelem.scrollTop = textareaelem.scrollHeight;	</script>';
		flush();
		
		if ( $type == 'error' ) {
			$this->log( $message, 'error' );
		} elseif ( $type == 'warning' ) {
			$this->log( $message, 'warning' );
		} else {
			$this->log( '[' . $type . ']' . $message, 'all' );
		}
	}
	
	
	/**
	 *	get_zip_id()
	 *
	 *	Given a BackupBuddy ZIP file, extracts the random ZIP ID from the filename. This random string determines
	 *	where BackupBuddy will find the temporary directory in the backup's wp-uploads directory. IE a zip ID of
	 *	3poje9j34 will mean the temporary directory is wp-uploads/temp_3poje9j34/. backupbuddy_dat.php is in this
	 *	directory as well as the SQL dump.
	 *
	 *	Currently handles old BackupBuddy ZIP file format. Remove this backward compatibility at some point.
	 *
	 *	$file			string		BackupBuddy ZIP filename.
	 *	@return			string		ZIP ID characters.
	 *
	 */
	function get_zip_id( $file ) {
		$posa = strrpos($file,'_')+1;
		$posb = strrpos($file,'-')+1;
		if ( $posa < $posb ) {
			$zip_id = strrpos($file,'-')+1;
		} else {
			$zip_id = strrpos($file,'_')+1;
		}
		
		$zip_id = substr( $file, $zip_id, - 4 );
		return $zip_id;
	}
	
	function log() {
		$args = func_get_args();
		return call_user_func_array( array( $this->_parent, 'log' ), $args );
	}
	
	function alert() {
		$args = func_get_args();
		return call_user_func_array( array( $this->_parent, 'alert' ), $args );
	}
	
	function remove_file( $file, $description, $error_on_missing = false ) {
		$this->status( 'message', 'Deleting ' . $description . '...' );
	
		@chmod( $file, 0755 ); // High permissions to delete.
		
		if ( is_dir( $file ) ) { // directory.
			$this->remove_dir( $file );
			if ( file_exists( $file ) ) {
				$this->status( 'error', 'Unable to delete directory: `' . $description . '`. You should manually delete it.' );
			} else {
				$this->status( 'message', 'Deleted.' );
			}
		} else { // file
			if ( file_exists( $file ) ) {
				if ( @unlink( $file ) != 1 ) {
					$this->status( 'error', 'Unable to delete file: `' . $description . '`. You should manually delete it.' );
				} else {
					$this->status( 'message', 'Deleted.' );
				}
			} else {
				$this->status( 'message', 'File does not exist; nothing to delete.' );
			}
		}
	}
}


?>
</div><!-- .wrap-->