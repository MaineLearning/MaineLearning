<?php
class pluginbuddy_importbuddy_step_6 {
	function __construct( &$parent ) {
		$this->_parent = &$parent;
	}
	
	
	function cleanup() {
		if ( isset( $_POST['delete_backup'] ) && ( $_POST['delete_backup'] == '1' ) ) {
			$this->_parent->remove_file( $this->_parent->_options['file'], 'backup .ZIP file (' . $this->_parent->_options['file'] . ')', true );
		}
		
		if ( isset( $_POST['delete_temp'] ) && ( $_POST['delete_temp'] == '1' ) ) {
			// Full backup .sql file
			$this->_parent->remove_file( ABSPATH . 'wp-content/uploads/temp_'.$this->_parent->_options['zip_id'].'/db.sql', 'db.sql (backup database dump)', false );
			$this->_parent->remove_file( ABSPATH . 'wp-content/uploads/temp_'.$this->_parent->_options['zip_id'].'/db_1.sql', 'db_1.sql (backup database dump)', false );
			$this->_parent->remove_file( ABSPATH . 'wp-content/uploads/backupbuddy_temp/'.$this->_parent->_options['zip_id'].'/db_1.sql', 'db_1.sql (backup database dump)', false );
			// DB only sql file
			$this->_parent->remove_file( ABSPATH . 'db.sql', 'db.sql (backup database dump)', false );
			$this->_parent->remove_file( ABSPATH . 'db_1.sql', 'db_1.sql (backup database dump)', false );
			
			// Full backup dat file
			$this->_parent->remove_file( ABSPATH . 'wp-content/uploads/temp_' . $this->_parent->_options['zip_id'] . '/backupbuddy_dat.php', 'backupbuddy_dat.php (backup data file)', false );
			$this->_parent->remove_file( ABSPATH . 'wp-content/uploads/backupbuddy_temp/' . $this->_parent->_options['zip_id'] . '/backupbuddy_dat.php', 'backupbuddy_dat.php (backup data file)', false );
			// DB only dat file
			$this->_parent->remove_file( ABSPATH . 'backupbuddy_dat.php', 'backupbuddy_dat.php (backup data file)', false );
			
			$this->_parent->remove_file( ABSPATH . 'wp-content/uploads/backupbuddy_temp/' . $this->_parent->_options['zip_id'] . '/', 'Temporary backup directory.', false );
			
			$this->_parent->remove_file( ABSPATH . 'importbuddy/', 'ImportBuddy Directory', true );
		}
		if ( isset( $_POST['delete_importbuddy'] ) && ( $_POST['delete_importbuddy'] == '1' ) ) {
			$this->_parent->remove_file( 'importbuddy.php', 'importbuddy.php (this script)', true );
		}
		// Delete log file last.
		if ( isset( $_POST['delete_importbuddylog'] ) && ( $_POST['delete_importbuddylog'] == '1' ) ) {
			$this->_parent->remove_file( 'importbuddy-' . $this->_options['log_serial'] . '.txt', 'importbuddy-' . $this->_options['log_serial'] . '.txt log file', true );
		}
	}
	
	
}