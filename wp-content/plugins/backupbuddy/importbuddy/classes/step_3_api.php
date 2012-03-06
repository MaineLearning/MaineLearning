<?php
class pluginbuddy_importbuddy_step_3 {
	
	
	function __construct( &$parent ) {
		$this->_parent = &$parent;
		// Set up backup data from the backupbuddy_dat.php.
		$this->_parent->load_backup_dat();
	}
	
	
	function get_previous_database_settings() {
		// If in high security mode then no guesses or previous values will be given.
		if ( ( $this->_parent->_options['force_high_security'] != false ) || ( isset( $this->_parent->_backupdata['high_security'] ) && ( $this->_parent->_backupdata['high_security'] === true ) ) ) {
			$response['server'] = '';
			$response['database'] = '';
			$response['user'] = '';
			$response['password'] = '';
			$response['prefix'] = '';
			return $response;
		} else { // normal mode. provide previous values.
			$response['server'] = $this->_parent->_backupdata['db_server'];
			$response['database'] = $this->_parent->_backupdata['db_name'];
			$response['user'] = $this->_parent->_backupdata['db_user'];
			$response['password'] = $this->_parent->_backupdata['db_password'];
			$response['prefix'] = $this->_parent->_backupdata['db_prefix'];
			return $response;
		}
	}
	
	
	/**
	 *	get_default_values()
	 *
	 *	Parses various submitted options and settings from step 1.
	 *
	 *	@return		null
	 */
	function get_database_defaults() {
		// Database defaults.
		$response['server'] = 'localhost';
		$response['database'] = '';
		$response['user'] = '';
		$response['password'] = '';
		$response['prefix'] = 'wp_';
		$response['wipe'] = $this->_parent->_options['wipe_database'];
		
		// If in high security mode then no guesses or previous values will be given.
		if ( isset( $this->_backupdata['high_security'] ) && ( $this->_backupdata['high_security'] === true ) ) { 
			return $response;
		}
		
		if ( false !== @mysql_connect( $this->_parent->_backupdata['db_server'], $this->_parent->_backupdata['db_user'], $this->_parent->_backupdata['db_password'] ) ) { // Couldnt connect to server or invalid credentials.
			$response['server'] = $this->_parent->_backupdata['db_server'];
			$response['user'] = $this->_parent->_backupdata['db_user'];
			$response['password'] = $this->_parent->_backupdata['db_password'];
			
			if ( false !== @mysql_select_db( $this->_parent->_backupdata['db_name'] ) ) {
				$response['database'] = $this->_parent->_backupdata['db_name'];
				
				$result = mysql_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( $this->_parent->_backupdata['db_prefix'] ) . "%'" );
				if ( mysql_num_rows( $result ) == 0 ) {
					$response['prefix'] = $this->_parent->_backupdata['db_prefix'];
				}
			}
		}
		
		return $response;
	}
	
	
	/**
	 *	get_default_url()
	 *
	 *	Returns the default site URL.
	 *
	 *	@return		string		URL.
	 */
	function get_default_url() {
		// Get the current URL of where the importbuddy script is running.
		$url = str_replace( $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'] );
		$url = str_replace( basename( $url ) , '', $url );
		$url = preg_replace( '|/*$|', '', $url );  // strips trailing slash(es).
		$url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
		
		return $url;
	}
	
	
	function get_default_domain() {
		preg_match("/^(http:\/\/)?([^\/]+)/i", $this->get_default_url(), $domain );
		return $domain[2];
	}
	
	
}
?>