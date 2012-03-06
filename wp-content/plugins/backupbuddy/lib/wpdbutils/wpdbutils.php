<?php
/**
 *	pluginbuddy_wpdbutils Class
 *
 *  Provides utility functions for helping with WordPress database handling
 *	
 *	Version: 1.0.0
 *	Author:
 *	Author URI:
 *
 *  @param		$db			object		Mandatory WordPress database object which is the database to operate on
 *	@param		$parent		object		Optional parent object which can provide functions for reporting, etc.
 *	@return		null
 *
 */
if ( !class_exists( "pluginbuddy_wpdbutils" ) ) {

	class pluginbuddy_wpdbutils {
	
		// status method type parameter values - would like a class for this
		const STATUS_TYPE_DETAILS = 'details';

		public $_version = '1.0';

        /**
         * wpdb object 
         * 
         * @var wpdb
         */
        protected $_db = NULL;
    
        /**
         * parent object
         * 
         * @var parent object
         */
        protected $_parent = NULL;

        /**
         * Whether or not we can call a status calback
         * 
         * @var have_status_callback bool
         */
		protected $_have_status_callback = false;
		
        /**
         * Object->method array for status function
         * 
         * @var status_callback array
         */
		protected $_status_callback = array();
		
		/**
		 *	__construct()
		 *	
		 *	Default constructor. Sets up optional status() function linkage if applicable.
		 *	
		 *  @param		reference	&$db			[mandatory] Reference to the database object
		 *	@param		reference	&$parent		[optional] Reference to the object containing the status() function for status updates.
		 *	@return		null
		 *
		 */
		public function __construct( wpdb &$db, &$parent = NULL ) {
		
			$this->_db = &$db;
			$this->_parent = &$parent;
			
		}
				
		/**
		 *	__destruct()
		 *	
		 *	Default destructor.
		 *	
		 *	@return		null
		 *
		 */
		public function __destruct( ) {

		}
				
		/**
		 *	set_status_callback()
		 *
		 *	Sets a reference to the function to call for each status update.
		 *  Argument must at least be a non-empty array with 2 elements
		 *
		 *	@param		array 	$callback	Object->method to call for status updates.
		 *	@return		null
		 *
		 */
		public function set_status_callback( $callback = array() ) {
		
			if ( is_array( $callback ) && !empty( $callback ) && ( 2 == count( $callback ) ) ) {
			
				$this->_status_callback = $callback;
				$this->_have_status_callback = true;

			}
			
		}
		
		/**
		 *	status()
		 *	
		 *	Invoke status method of parent if it exists
		 *  Must be at least one parameter otherwise ignore the call
		 *	
		 *	@param		string		$type		(Expected) Status message type.
		 *	@param		string		$message	(Expected) Status message.
		 *	@return		null
		 *
		 */
		public function status() {
		
			if ( $this->_have_status_callback && ( func_num_args() > 0 ) ) {

				$args = func_get_args();
				call_user_func_array( $this->_status_callback, $args );
				
			}
			
		}
		
		/**
		 *	status()
		 *	
		 *	Invoke status() method of parent if it exists
		 *	
		 *	@param		string		$type		Status message type.
		 *	@param		string		$message	Status message.
		 *	@return		null
		 *
		 */
		public function old_status( $type = '', $message = '' ) {
		
			( $this->_parent_has_status_method && $this->_parent->status( $type, $message ) );
			
		}
				
		/**
		 *	kick()
		 *	
		 *	Kicks the database to see if the conenction is still alive and if it isn't then tries to reconnect
		 *	
		 *	@return		true if connection alive (may have been reconnected), false otherwise (dead and couldn't be reconnected)
		 *
		 */
		public function kick( ) {
			
			// Initialize result to assume failure
			$result = false; 
			
			// Use ping to check if server is still present - note will not reconnect automatically for MySQL >= 5.0.13
			// and actually we don't want it to as that is bad karma
			if ( !mysql_ping( $this->_db->dbh ) ) {
			
			  // Database connection appears to have gone away
			  $this->status( self::STATUS_TYPE_DETAILS, __('Database Server has gone away, attempting to reconnect.', 'it-l10n-backupbuddy') );
			  
			  // Close things down cleanly (from a local perspective)
			  @mysql_close( $this->_db->dbh );
			  unset( $this->_db->dbh);
			  $this->_db->ready = false;
			  
			  // And attempt to reconnect
			  $this->_db->db_connect();

			  // Reconnect failed if we have a null resource or ping fails
			  if ( ( NULL == $this->_db->dbh ) || ( !mysql_ping( $this->_db->dbh ) ) ) {
			  
			    // Reconnection failed, make sure user knows
			    $this->status( self::STATUS_TYPE_DETAILS, __('Database Server reconnection failed.', 'it-l10n-backupbuddy') );
					
				// Make sure failure is notified (no need to close things down locally as it's a wrap anyway)
				$result = false;
				
			  } else {
			  
			    // Reconnection successful, make sure user knows
			  	$this->status( self::STATUS_TYPE_DETAILS, __('Database Server reconnection successful.', 'it-l10n-backupbuddy') );
			  	$result = true;
			  	
			  }
			  
			} else {
			
			  // Just to let user know that database is still connected
			  $this->status( self::STATUS_TYPE_DETAILS, __('Database Server connection status verified.', 'it-l10n-backupbuddy') );
			  $result = true;
			  
			}
			
			return $result;
		}		
		
	} // end pluginbuddy_wpdbutils class.
	
}
?>