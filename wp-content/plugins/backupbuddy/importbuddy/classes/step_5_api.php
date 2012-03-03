<?php
class pluginbuddy_importbuddy_step_5 extends pluginbuddy_importbuddy {
	function __construct( &$parent ) {
		$this->_parent = &$parent;
		// Set up backup data from the backupbuddy_dat.php.
		$this->_parent->load_backup_dat();
	}
	
	
	/**
	 *	migrate_database()
	 *
	 *	Connects database and performs migration of DB content. Handles skipping.
	 *
	 *	@return		null
	 */
	function migrate_database() {
		$this->_parent->connect_database();
		
		if ( false === $this->_parent->_options['skip_database_migration'] ) {
			return $this->_parent->migrate_database();
		} else {
			$this->_parent->status( 'message', 'Skipping database migration based on settings.' );
			return true;
		}
	}
	
	
	/**
	 *	migrate_wp_config()
	 *
	 *	Passthrough for suture use; trying to funnel all essential migration steps through the API files.
	 *
	 *	@return		null
	 */
	function migrate_wp_config() {
		return $this->_parent->migrate_wp_config();
	}
	
	
}
?>