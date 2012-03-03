<?php
/**
 *	pluginbuddy_dbimport Class
 *
 *	Handles import of a database from a BackupBuddy dump. Used by importbuddy and MultiSite import features.
 *	Expects database to already be set up and initialized.
 *	
 *	Version: 1.0.0
 *	Author: Dustin Bolton
 *	Author URI: http://dustinbolton.com/
 *
 *	@param		$status_callback		object		Optional object containing the status() function for reporting back information.
 *	@return		null
 *
 */
if (!class_exists("pluginbuddy_dbimport")) {
	class pluginbuddy_dbimport {
		var $_version = '1.0';
		var $_options = array(
							'zip_id'				=>		'',
							'db_server'				=>		'localhost',
							'db_name'				=>		'',
							'db_user'				=>		'',
							'db_password'			=>		'',
							'db_prefix'				=>		'',
							'old_prefix'			=>		'',
							'max_execution_time'	=>		'30',
							);
		
		
		/**
		 *	__construct()
		 *	
		 *	Default constructor. Sets up optional status() function class if applicable.
		 *	
		 *	@param		array		$options				Array of options.
		 *	@param		reference	&$status_callback		[optional] Reference to the class containing the status() function for status updates.
		 *	@param		boolean		$ignore_sql_errors		If true then any SQL errors will not be displayed to the user. Default: false
		 *	@return		null
		 *
		 */
		function __construct( $options, &$status_callback = '', $ignore_sql_errors = false ) {
			$this->_options = array_merge( $this->_options, $options );
			$this->status_callback = &$status_callback;
			$this->_ignore_sql_errors = $ignore_sql_errors;
			
			if (
				( $this->_options['zip_id'] == '' )
				||
				( $this->_options['db_server'] == '' )
				||
				( $this->_options['db_name'] == '' )
				||
				( $this->_options['db_user'] == '' )
				||
				( $this->_options['db_password'] == '' )
				||
				( $this->_options['db_prefix'] == '' )
				||
				( $this->_options['old_prefix'] == '' )
			   ) {
				die( 'Error #8438934984: dbimport class missing option in constructor $options variable.' );
			}
			
			$this->status( 'details', 'Maximum execution time for this run: ' . $this->_options['max_execution_time'] . ' seconds.' );
			$this->status( 'details', 'Old prefix: ' . $this->_options['old_prefix'] . '; new prefix: ' . $this->_options['db_prefix'] );
			
			// breaks for some reason: $this->status( 'details', 'Import replace settings: ' . htmlentities( print_r( $this->_options, true ) ) );
		}
		
		
		/**
		 *	status()
		 *	
		 *	Pass status back to callback class. If there is no callback then this this is ignored.
		 *	
		 *	@param		string		$table		Status message type.
		 *	@param		string		$message	Status message.
		 *	@return		null
		 *
		 */
		function status( $type = '', $message = '' ) {
			if ( isset( $this->status_callback ) ) {
				$this->status_callback->status( $type, $message );
			}
		}
		
		
		/**
		 *	restore_database()
		 *
		 *	Preset the following variables within $this->_options before continuing:
		 *		db_server, db_user, db_password, db_name
		 *
		 *	@param			string		$query_start		Currently doesnt work;
		 *	@param			string		
		 *	@return			mixed		true: success, false: failure, integer: query to start next db import chunk on
		 */
		function restore_database( $query_start = 0, $ignore_existing = false ) {
			$this->time_start = microtime( true );
			$this->status( 'message', 'Beginning database import...' );
			
			//$this->connect_database();
			
			// Require a table prefix.
			if ( $this->_options['db_prefix'] == '' ) {
				$this->status( 'error', 'ERROR 9008: A database prefix is required for importing. Details: ' . mysql_error() );
			}
			
			if ( $query_start > 0 ) {
				$this->status( 'message', 'Continuing to restore database dump starting at query ' . $query_start . '.' );
			} else {
				$this->status( 'message', 'Restoring database dump. This may take a moment...' );
			}
			
			flush();
			
			if ( $ignore_existing === false ) {
				// Check number of tables already existing with this prefix. Skips this check on substeps of DB import.
				if ( $query_start == 0 ) {
					$result = mysql_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( $this->_options['db_prefix'] ) . "%'" );
					if ( mysql_num_rows( $result ) > 0 ) {
						//echo ezimg::genImageTag('bullet_error.png').' Found ' . mysql_num_rows( $result ) . ' existing tables with same prefix ... Restore stopped to prevent accidental overwrite of existing data.';
						$this->status( 'error', 'Error #9014: Database import halted to prevent overwriting existing WordPress data.', 'The database already contains a WordPress installation with this prefix (' . mysql_num_rows( $result ) . ' tables). Restore has been stopped to prevent overwriting existing data. Please go back and enter a new database name and/or prefix OR select the option to wipe the database prior to import from the advanced settings on the first import step.' );
						return false;
					}
					unset( $result );
				}
			}
			
			// Import SQL dump onto new server. NOTE: This data has NOT been migrated. It is identical to the source server still at this point.
			$import_result = $this->import_sql_dump( $query_start );
			
			// CLEANUP
			mysql_close();
			
			$this->status( 'message', 'Database import complete.' );
			
			return $import_result;
		}
		
		
		/**
		 *	import_sql_dump()
		 *
		 *	Directly inserts the source SQL dump into the new database.
		 *	Does NOT modify any data or do any migration.
		 *
		 *	@return		boolean		True: success, False: failed.
		 *
		 */
		function import_sql_dump( $query_start = 0 ) {
			// TODO: debugging
			//$query_start = 839;
			$this->status( 'message', 'Starting import of SQL data... This may take a moment...' );
			
			$file_stream = false; // Default state.
			if ( file_exists ( ABSPATH . 'wp-content/uploads/temp_' . $this->_options['zip_id'] . '/db.sql' ) ) { // Full backup found.
				$file_stream = fopen( ABSPATH . 'wp-content/uploads/temp_'.$this->_options['zip_id'].'/db.sql', 'r' );
			} elseif ( file_exists ( ABSPATH . 'db.sql' ) ) { // DB-only backup found.
				$file_stream = fopen( ABSPATH . 'db.sql', 'r' );
			} elseif ( file_exists ( ABSPATH . 'wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/db_1.sql' ) ) { // Full backup found. 2.0 method.
				$file_stream = fopen( ABSPATH . 'wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/db_1.sql', 'r' );
			} elseif ( file_exists ( ABSPATH . 'db_1.sql' ) ) { // DB-only backup found. 2.0 method.
				$file_stream = fopen( ABSPATH . 'db_1.sql', 'r' );
			}  elseif ( file_exists ( ABSPATH . 'wp-content/uploads/backupbuddy_temp/import_' . $this->_options['zip_id'] . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/db_1.sql' ) ) { // Multisite import.
				$file_stream = fopen( ABSPATH . 'wp-content/uploads/backupbuddy_temp/import_' . $this->_options['zip_id'] . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/db_1.sql', 'r' );
			}  elseif ( file_exists ( $this->_options['database_directory'] . 'db_1.sql' ) ) { // Multisite import.
				$file_stream = fopen( $this->_options['database_directory'] . 'db_1.sql', 'r' );
			}
			
			if ( false === $file_stream ) {
				//$this->alert( 'ERROR: Unable to find any database backup data in the selected backup.', true, '9009' );
				$this->status( 'error', 'Error #9009: Unable to find any database backup data in the selected backup. Error #9009.' );
				return false;
			}
			
			// Iterate through each full row action and import it one at a time.
			
			$query_count = 0;
			$file_data = '';
			
			while ( ! feof( $file_stream ) ) {
			
				while ( false === strpos( $file_data, "\n" ) ) {
					$file_data .= fread( $file_stream, 4096 );
				}
				
				$queries = explode( "\n", $file_data );
				
				if ( preg_match( "/\n$/", $file_data ) ) {
					$file_data = '';
				} else {
					$file_data = array_pop( $queries );
				}
				
				// TODO: DEBUGGING:
				//$this->_options['max_execution_time'] = 0.41;
				
				// Loops through each full query.
				foreach ( (array) $queries as $query ) {
					if ( $query_count < ( $query_start - 1 ) ) { // Handle skipping any queries up to the point we are at.
						$query_count++;
						continue; // Continue to next foreach iteration.
					} else {
						$query_count++;
					}
					
					$query = trim( $query );
					
					if ( empty( $query ) ) {
						continue;
					}
					
					$result = $this->import_sql_dump_line( $query );
					
					if ( false === $result ) { // Skipped query
						continue;
					}
					
					if ( 0 === ( $query_count % 2000 ) ) { // Display Working every 1500 queries imported.
						$this->status( 'message', 'Working...' );
					}
					/*
					if ( 0 === ( $query_count % 6000 ) ) {
						echo "<br>\n";
					}
					*/
					
					// If we are within 1 second of reaching maximum PHP runtime then stop here so that it can be picked up in another PHP process...
					if ( ( ( microtime( true ) - $this->time_start ) + 1 ) >= $this->_options['max_execution_time'] ) {
					// TODO: Debugging:
					//if ( ( ( microtime( true ) - $this->time_start ) ) >= $this->_options['max_execution_time'] ) {
						$this->status( 'message', 'Exhausted available PHP time to import for this page load. Last query: ' . $query_count . '.' );
						
						fclose( $file_stream );
						
						return ( $query_count + 1 );
						//break 2;
					}
				}
				
			}
			
			fclose( $file_stream );
			
			$this->status( 'message', 'Import of SQL data complete.' );			
			$this->status( 'message', 'Took ' . round( microtime( true ) - $this->time_start, 3 ) . ' seconds on ' . $query_count . ' queries. ' );
			
			return true;
		}
		
		
		/**
		 *	import_sql_dump_line()
		 *
		 *	Imports a line/query into the database.
		 *	Handles using the specified table prefix.
		 *
		 *	$query		string		Query string to run for importing.
		 *	@return		boolean		True=success, False=failed.
		 *
		 */
		function import_sql_dump_line( $query ) {
			//$old_prefix = $this->_backupdata['db_prefix'];
			$old_prefix = $this->_options['old_prefix'];
			$new_prefix = $this->_options['db_prefix'];
			
			$query_operators = 'INSERT INTO|CREATE TABLE|REFERENCES|CONSTRAINT';
			
			// Replace database prefix in query.
			if ( $old_prefix !== $new_prefix ) {
				$query = preg_replace( "/^($query_operators)(\s+`?)$old_prefix/i", "\${1}\${2}$new_prefix", $query ); // 4-29-11
			}
			
			// Run the query
			// Disabled to prevent from running on EVERY line. Now just running before this. mysql_query("SET NAMES 'utf8'"); // Force UTF8
			$result = mysql_query( $query );
			
			if ( false === $result ) {
				if ( $this->_ignore_sql_errors !== true ) {
					$this->status( 'error', 'Error #9010: Unable to import SQL query: ' . mysql_error() );
				}
				return false;
			} else {
				return true;
			}
		}
		
		
	} // end pluginbuddy_dbimport class.
}
?>