<?php
class pluginbuddy_importbuddy_step_2 {
	function __construct( &$parent ) {
		$this->_parent = &$parent;
	}
	
	
	/**
	 *	parse_options()
	 *
	 *	Parses various submitted options and settings from step 1.
	 *
	 *	@return		null
	 */
	function parse_options() {
		// Set advanced debug options if user set any.
		if ( ( isset( $_POST['skip_files'] ) ) && ( $_POST['skip_files'] == 'on' ) ) { $this->_parent->_options['skip_files'] = true; }
		if ( ( isset( $_POST['skip_database_import'] ) ) && ( $_POST['skip_database_import'] == 'on' ) ) { $this->_parent->_options['skip_database_import'] = true; }
		if ( ( isset( $_POST['skip_database_migration'] ) ) && ( $_POST['skip_database_migration'] == 'on' ) ) { $this->_parent->_options['skip_database_migration'] = true; }
		if ( ( isset( $_POST['wipe_database'] ) ) && ( $_POST['wipe_database'] == 'on' ) ) { $this->_parent->_options['wipe_database'] = true; }
		if ( ( isset( $_POST['skip_htaccess'] ) ) && ( $_POST['skip_htaccess'] == 'on' ) ) { $this->_parent->_options['skip_htaccess'] = true; }
		if ( ( isset( $_POST['ignore_sql_errors'] ) ) && ( $_POST['ignore_sql_errors'] == 'on' ) ) { $this->_parent->_options['ignore_sql_errors'] = true; }
		if ( ( isset( $_POST['force_compatibility_medium'] ) ) && ( $_POST['force_compatibility_medium'] == 'on' ) ) { $this->_parent->_options['force_compatibility_medium'] = true; }
		if ( ( isset( $_POST['force_compatibility_slow'] ) ) && ( $_POST['force_compatibility_slow'] == 'on' ) ) { $this->_parent->_options['force_compatibility_slow'] = true; }
		if ( ( isset( $_POST['force_high_security'] ) ) && ( $_POST['force_high_security'] == 'on' ) ) { $this->_parent->_options['force_high_security'] = true; }
		if ( ( isset( $_POST['show_php_warnings'] ) ) && ( $_POST['show_php_warnings'] == 'on' ) ) { $this->_parent->_options['show_php_warnings'] = true; }
		if ( ( isset( $_POST['file'] ) ) && ( $_POST['file'] != '' ) ) { $this->_parent->_options['file'] = $_POST['file']; }
		if ( ( isset( $_POST['max_execution_time'] ) ) && ( is_numeric( $_POST['max_execution_time'] ) ) ) {
			$this->_parent->_options['max_execution_time'] = $_POST['max_execution_time'];
		} else {
			$this->_parent->_options['max_execution_time'] = 30;
		}
		if ( ( isset( $_POST['log_level'] ) ) && ( $_POST['log_level'] != '' ) ) { $this->_parent->_options['log_level'] = $_POST['log_level']; }
		
		// Set ZIP id (aka serial).
		$this->_parent->_options['zip_id'] = $this->_parent->get_zip_id( $this->_parent->_options['file'] );
	}
	
	
	/**
	 *	extract()
	 *
	 *	Extract backup zip file.
	 *
	 *	@return		array		True if the extraction was a success OR skipping of extraction is set.
	 */
	function extract() {
		if ( true === $this->_parent->_options['skip_files'] ) { // Option to skip all file updating / extracting.
			$this->_parent->status( 'message', 'Skipped extracting files based on debugging options.' );
			return true;
		} else {
			$this->_parent->set_greedy_script_limits();
			
			$this->_parent->status( 'message', 'Unzipping into `' . ABSPATH . '`' );
			
			$backup_archive = ABSPATH . $this->_parent->_options['file'];
			$destination_directory = ABSPATH;
			
			// Set compatibility mode if defined in advanced options.
			$compatibility_mode = false; // Default to no compatibility mode.
			if ( $this->_parent->_options['force_compatibility_medium'] != false ) {
				$compatibility_mode = 'ziparchive';
			} elseif ( $this->_parent->_options['force_compatibility_slow'] != false ) {
				$compatibility_mode = 'pclzip';
			}
			
			// Zip & Unzip library setup.
			require_once( ABSPATH . 'importbuddy/lib/zipbuddy/zipbuddy.php' );
			$_zipbuddy = new pluginbuddy_zipbuddy( ABSPATH, '', 'unzip' );
			$_zipbuddy->set_status_callback( array( &$this->_parent, 'status' ) );
			
			// Extract zip file & verify it worked.
			if ( true !== ( $result = $_zipbuddy->unzip( $backup_archive, $destination_directory, $compatibility_mode ) ) ) {
				$this->_parent->status( 'error', 'Failed unzipping archive.' );
				$this->_parent->alert( 'Failed unzipping archive.' );
				return false;
			} else { // Reported success; verify extraction.
				$this->_parent->_backupdata_file = ABSPATH . 'wp-content/uploads/temp_' . $this->_parent->_options['zip_id'] . '/backupbuddy_dat.php'; // Full backup dat file location
				$this->_parent->_backupdata_file_dbonly = ABSPATH . 'backupbuddy_dat.php'; // DB only dat file location
				$this->_parent->_backupdata_file_new = ABSPATH . 'wp-content/uploads/backupbuddy_temp/' . $this->_parent->_options['zip_id'] . '/backupbuddy_dat.php'; // Full backup dat file location
				if ( !file_exists( $this->_parent->_backupdata_file ) && !file_exists( $this->_parent->_backupdata_file_dbonly ) && !file_exists( $this->_parent->_backupdata_file_new ) ) {
					$this->_parent->status( 'error', 'Error #9004: Key files missing.', 'The unzip process reported success but the backup data file, backupbuddy_dat.php was not found in the extracted files. The unzip process either failed (most likely) or the zip file is not a proper BackupBuddy backup.' );
					$this->_parent->alert( 'Error: Key files missing.', 'The unzip process reported success but the backup data file, backupbuddy_dat.php was not found in the extracted files. The unzip process either failed (most likely) or the zip file is not a proper BackupBuddy backup.', '9004' );
					return false;
				}
				$this->_parent->status( 'details', 'Success extracting Zip File "' . ABSPATH . $this->_parent->_options['file'] . '" into "' . ABSPATH . '".' );
				return true;
			}
		}
	}


}
?>