<?php
class pluginbuddy_importbuddy_step_4 {
	function __construct( &$parent ) {
		$this->_parent = &$parent;
		
		$this->parse_options();
		// Set up backup data from the backupbuddy_dat.php.
		$this->_parent->load_backup_dat();
	}
	
	
	/**
	 *	parse_options()
	 *
	 *	Parses various submitted options and settings from step 1.
	 *
	 *	@return		null
	 */
	function parse_options() {
		if ( isset( $_POST['siteurl'] ) ) { $this->_parent->_options['siteurl'] = $_POST['siteurl']; }
		if ( isset( $_POST['custom_home'] ) ) {
			$this->_parent->_options['home'] = $_POST['home'];
		} else {
			$this->_parent->_options['home'] = $_POST['siteurl'];
		}
		
		// Multisite domain.
		if ( isset( $_POST['domain'] ) ) { $this->_parent->_options['domain'] = $_POST['domain']; }
		
		if ( isset( $_POST['db_server'] ) ) { $this->_parent->_options['db_server'] = $_POST['db_server']; }
		if ( isset( $_POST['db_user'] ) ) { $this->_parent->_options['db_user'] = $_POST['db_user']; }
		if ( isset( $_POST['db_password'] ) ) { $this->_parent->_options['db_password'] = $_POST['db_password']; }
		if ( isset( $_POST['db_name'] ) ) { $this->_parent->_options['db_name'] = $_POST['db_name']; }
		if ( isset( $_POST['db_prefix'] ) ) { $this->_parent->_options['db_prefix'] = $_POST['db_prefix']; }
	}
	
	
	/**
	 *	import_database()
	 *
	 *	Parses various submitted options and settings from step 1.
	 *
	 *	@return		array		array( import_success, import_complete ).
	 *							import_success: false if unable to import into database, true if import is working thus far/completed.
	 *							import_complete: If incomplete, an integer of the next query to begin on. If complete, true. False if import_success = false.
	 */
	function import_database() {
		$this->_parent->set_greedy_script_limits();
		
		$this->_parent->status( 'message', 'Verifying database connection and settings...' );
		if ( $this->_parent->connect_database() === false ) {
			$this->_parent->alert( 'ERROR: Unable to select your specified database. Verify the database name and that you have set up proper permissions for your specified username to access it. Details: ' . mysql_error(), true, '9007' );
			return( array( false, false ) );
		} else {
			$this->_parent->migrate_htaccess();
			$this->_parent->status( 'message', 'Database connection and settings verified.' );
			// Import database unless disabled.
			$db_continue = false;
			if ( false === $this->_parent->_options['skip_database_import'] ) {
				// Wipe database if option was selected.
				if ( $this->_parent->_options['wipe_database'] == true ) {
					if ( isset( $_POST['db_continue'] ) && ( is_numeric( $_POST['db_continue'] ) ) ) {
						// do nothing
					} else { // dont wipe on substeps of db import.
						$this->_parent->status( 'message', 'Wiping existing database based on settings...' );
						$failed = !$this->_parent->wipe_database();
						if ( false !== $failed ) {
							$this->_parent->message( 'error', 'Unable to wipe database as configured in the settings.' );
							$this->_parent->alert( 'Error', 'Unable to wipe database as configured in the settings.' );
						}
					}
				}
				
				// Sanitize db continuation value if needed.
				if ( isset( $_POST['db_continue'] ) && ( is_numeric( $_POST['db_continue'] ) ) ) {
					// do nothing
				} else {
					$_POST['db_continue'] = 0;
				}
				$import_result = $this->_parent->restore_database( $_POST['db_continue'] );
				if ( true === $import_result ) { // Fully finished successfully.
					return( array( true, true ) );
				} elseif ( false === $import_result ) { // Full failure.
					return( array( false, false ) );
				} else { // Needs to chunk up DB import and continue...
					//$db_continue = true;
					// Continue on query $import_result...
					$this->_parent->status( 'message', 'Next step will begin import on query ' . $import_result . '.' );
					return array( true, $import_result );
				}
			} else {
				$this->_parent->status( 'message', 'Skipping database restore based on settings.' );
				return array( true, true );
			}
		}
	}


}
?>