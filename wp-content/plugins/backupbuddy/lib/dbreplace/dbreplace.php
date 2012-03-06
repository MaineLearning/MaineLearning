<?php
/**
 *	pluginbuddy_dbreplace Class
 *
 *	Handles replacement of data in a table/database, text or serialized. A database connection should be initialized before instantiation.
 *	
 *	Version: 1.0.0
 *	Author: Dustin Bolton
 *	Author URI: http://dustinbolton.com/
 *
 *	@param		$status_callback		object		Optional object containing the status() function for reporting back information.
 *	@return		null
 *
 */
if (!class_exists("pluginbuddy_dbreplace")) {
	class pluginbuddy_dbreplace {
		var $_version = '1.0';
		
		
		/**
		 *	__construct()
		 *	
		 *	Default constructor. Sets up optional status() function class if applicable.
		 *	
		 *	@param		reference	&$status_callback		[optional] Reference to the class containing the status() function for status updates.
		 *	@return		null
		 *
		 */
		function __construct( &$status_callback = '' ) {
			$this->status_callback = &$status_callback;
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
		 *	text()
		 *	
		 *	Replaces text within a table by specifying the table, rows to replace within and the old and new value(s).
		 *	
		 *	@param		string		$table		Table to replace text in.
		 *	@param		mixed		$olds		Old value(s) to find for replacement. May be a string or array of values.
		 *	@param		mixed		$news		New value(s) to be replaced with. May be a string or array. If array there must be the same number of values as $olds.
		 *	@param		mixed		$rows		Table row(s) to replace within. May be an array of tables.
		 *	@return		null
		 *
		 */
		public function text( $table, $olds, $news, $rows ) {
			$rows_sql = array();
			
			if ( !is_array( $olds ) ) {
				$olds = array( $olds );
			}
			if ( !is_array( $news ) ) {
				$news = array( $news );
			}
			if ( !is_array( $rows ) ) {
				$rows = array( $rows );
			}
			
			// Prevent trying to replace data with the same data for performance.
			$this->remove_matching_array_elements( $olds, $news );
			
			foreach ( $rows as $row ) {
				$i = 0;
				foreach ( $olds as $old ) {
					$rows_sql[] = $row . " = replace( {$row}, '{$old}', '{$news[$i]}')";
					$i++;
				}
			}
			
			return mysql_query( "UPDATE `{$table}` SET " . implode( ',', $rows_sql ) . ";" );
		}
		
		
		/**
		 *	serialized()
		 *	
		 *	Replaces serialized text within a table by specifying the table, rows to replace within and the old and new value(s).
		 *	
		 *	@param		string		$table		Table to replace text in.
		 *	@param		mixed		$olds		Old value(s) to find for replacement. May be a string or array of values.
		 *	@param		mixed		$news		New value(s) to be replaced with. May be a string or array. If array there must be the same number of values as $olds.
		 *	@param		mixed		$rows		Table row(s) to replace within. May be an array of tables.
		 *	@return		null
		 *
		 */
		public function serialized( $table, $olds, $news, $rows ) {
			if ( !is_array( $olds ) ) {
				$olds = array( $olds );
			}
			if ( !is_array( $news ) ) {
				$news = array( $news );
			}
			if ( !is_array( $rows ) ) {
				$rows = array( $rows );
			}
			
			// Prevent trying to replace data with the same data for performance.
			$this->remove_matching_array_elements( $olds, $news );
			$key_result = mysql_query( "show keys from {$table} WHERE Key_name='PRIMARY';" );
			if ( $key_result === false ) {
				$this->status( 'details', 'Table `' . $table . '` does not exist; skipping migration of this table.' );
				return;
			}
			
			// No primary key found; unsafe to edit this table. @since 2.2.32.
			if ( mysql_num_rows( $key_result ) == 0 ) {
				$this->status( 'message', 'Error #9029: Table `'.  $table .'` does not contain a primary key; BackupBuddy cannot safely modify the contents of this table. Skipping migration of this table. (serialized()).' );
				return;
			}
			
			$key_result = mysql_fetch_array( $key_result );
			$primary_key = $key_result['Column_name'];
			unset( $key_result );
			
			$result = mysql_query( "SELECT `" . implode( '`,`', $rows ) . "`,`{$primary_key}` FROM `{$table}`");
			
			$updated = false;
			while ( $row = mysql_fetch_array( $result, MYSQL_ASSOC ) ) {
				$needs_update = false;
				$sql_update = array();
				
				foreach( $row as $column => $value ) {
					if ( $column != $primary_key ) {
						if ( false !== ( $edited_data = $this->replace_maybe_serialized( $value, $olds, $news ) ) ) { // Data changed.
							$needs_update = true;
							$sql_update[] = $column . "= '" . mysql_real_escape_string( $edited_data ) . "'";
						}
					} else {
						$primary_key_value = $value;
					}
				}
				
				if ( $needs_update === true ) {
					$updated = true;
					mysql_query( "UPDATE `{$table}` SET " . implode( ',', $sql_update ) . " WHERE `{$primary_key}` = '{$primary_key_value}' LIMIT 1" );
				}
			}
			
			if ( $updated === true ) {
				$this->status( 'details', 'Updated serialized data in ' . $table . '.' );
			}
		}
		
		
		/**
		 *	replace_maybe_serialized()
		 *	
		 *	Replaces possibly serialized (or non-serialized) text if a change is needed. Returns false if there was no change.
		 *	
		 *	@param		string		$table		Text (possibly serialized) to update.
		 *	@param		mixed		$olds		Text to search for to replace. May be an array of strings to search for.
		 *	@param		mixed		$news		New value(s) to be replaced with. May be a string or array. If array there must be the same number of values as $olds.
		 *	@return		mixed					Returns modified string data if serialized data was replaced. False if no change was made.
		 *
		 */
		function replace_maybe_serialized( $data, $olds, $news ) {
			if ( !is_array( $olds ) ) {
				$olds = array( $olds );
			}
			if ( !is_array( $news ) ) {
				$news = array( $news );
			}
			
			$type = '';
			$unserialized = false; // first assume not serialized data
			if ( is_serialized( $data ) ) { // check if this is serialized data
				$unserialized = @unserialize( $data ); // unserialise - if false is returned we won't try to process it as serialised.
			}
			if ( $unserialized !== false ) { // Serialized data.
				$type = 'serialized';
				$i = 0;
				foreach ( $olds as $old ) {
					$this->recursive_array_replace( $old, $news[$i], $unserialized );
					$i++;
				}
				$edited_data = serialize( $unserialized );
			}	else { // Non-serialized data.
				$type = 'text';
				$edited_data = $data;
				$i = 0;
				foreach ( $olds as $old ) {
					$edited_data =str_ireplace( $old, $news[$i], $edited_data );
					$i++;
				}
			}
			
			// Return the results.
			if ( $data != $edited_data ) {
				return $edited_data;
			} else {
				return false;
			}
		}
		
		
		/**
		 *	bruteforce_table()
		 *	
		 *	Replaces text, serialized or not, within the entire table. Bruteforce method iterates through every row & column in the entire table and replaces if needed.
		 *	
		 *	@param		string		$table		Text (possibly serialized) to update.
		 *	@param		mixed		$olds		Text to search for to replace. May be an array of strings to search for.
		 *	@param		mixed		$news		New value(s) to be replaced with. May be a string or array. If array there must be the same number of values as $olds.
		 *	@return		boolean					Always true currently.
		 *
		 */
		function bruteforce_table( $table, $olds, $news ) {
			$this->status( 'message', 'Starting brute force data migration for table `' . $table . '`...' );
			if ( !is_array( $olds ) ) {
				$olds = array( $olds );
			}
			if ( !is_array( $news ) ) {
				$news = array( $news );
			}
			
			$count_items_checked = 0;
			$count_items_changed = 0;
			
			$fields_list = mysql_query( "DESCRIBE `" . $table . "`" );
			$index_fields = '';  // Reset fields for each table.
			$column_name = '';
			$table_index = '';
			$i = 0;
			
			$found_primary_key = false;
			
			while ( $field_rows = mysql_fetch_array( $fields_list ) ) {
				$column_name[$i++] = $field_rows['Field'];
				if ( $field_rows['Key'] == 'PRI' ) {
					$table_index[$i] = true;
					$found_primary_key = true;
				}
			}
			
			// Skips migration of this table if there is no primary key. Modifying on any other key is not safe. mysql automatically returns a PRIMARY if a UNIQUE non-primary is found according to http://dev.mysql.com/doc/refman/5.1/en/create-table.html  @since 2.2.32.
			if ( $found_primary_key === false ) {
				$this->status( 'message', 'Error #9029: Table `'.  $table .'` does not contain a primary key; BackupBuddy cannot safely modify the contents of this table. Skipping migration of this table. (bruteforce_table()).' );
				return false;
			}
			
			$data = mysql_query( "SELECT * FROM `" . $table . "`" );
			if (!$data) {
				$this->status( 'error', 'ERROR #44545343 ... SQL ERROR: ' . mysql_error() );
			}
			
			$row_loop = 0;
			while ( $row = mysql_fetch_array( $data ) ) {
				$need_to_update = false;
				$UPDATE_SQL = 'UPDATE `' . $table . '` SET ';
				$WHERE_SQL = ' WHERE ';
				
				$j = 0;
				foreach ( $column_name as $current_column ) {
					$j++;
					$count_items_checked++;
					$row_loop++;
					if ( $row_loop > 5000 ) {
						$this->status( 'message', 'Working...' );
						$row_loop = 0;
					}
					
					$data_to_fix = $row[$current_column];
					if ( false !== ( $edited_data = $this->replace_maybe_serialized( $data_to_fix, $olds, $news ) ) ) { // no change needed
						$count_items_changed++;
						if ( $need_to_update != false ) { // If this isn't our first time here, we need to add a comma.
							$UPDATE_SQL = $UPDATE_SQL . ',';
						}
						$UPDATE_SQL = $UPDATE_SQL . ' ' . $current_column . ' = "' . mysql_real_escape_string( $edited_data ) . '"';
						$need_to_update = true; // Only set if we need to update - avoids wasted UPDATE statements.
					}
					
					if ( isset( $table_index[$j] ) ) {
						$WHERE_SQL = $WHERE_SQL . '`' . $current_column . '` = "' . $row[$current_column] . '" AND ';
					}
				}
				
				if ( $need_to_update ) {
					$WHERE_SQL = substr( $WHERE_SQL , 0, -4 ); // Strip off the excess AND - the easiest way to code this without extra flags, etc.
					$UPDATE_SQL = $UPDATE_SQL . $WHERE_SQL;
					$result = mysql_query( $UPDATE_SQL );
					if ( !$result ) {
						$this->status( 'error', 'ERROR: mysql error updating db: ' . mysql_error() . '. SQL Query: ' . htmlentities( $UPDATE_SQL ) );
					} 
				}
				
			}
			
			unset( $main_result );
			$this->status( 'message', 'Brute force data migration for table `' . $table . '` complete. Checked ' . $count_items_checked . ' items; ' . $count_items_changed . ' changed.' );
			
			return true;
		}
		
		
		/**
		 *	recursive_array_replace()
		 *	
		 *	Recursively replace text in an array, stepping through arrays within arrays as needed.
		 *	
		 *	@param		string		$find		Text to find.
		 *	@param		string		$replace	Text to replace found text with.
		 *	@param		reference	&$data		Pass the variable to change the data within.
		 *	@return		boolean					Always true currently.
		 *
		 */
		public function recursive_array_replace( $find, $replace, &$data ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					if ( is_array( $value ) ) {
						$this->recursive_array_replace( $find, $replace, $data[$key] );
					} else {
						// Have to check if it's string to ensure no switching to string for booleans/numbers/nulls - don't need any nasty conversions.
						if ( is_string( $value ) ) $data[$key] = str_replace( $find, $replace, $value );
					}
				}
			} else {
				if ( is_string( $data ) ) $data = str_replace( $find, $replace, $data );
			}
		}
		
		
		/**
		 * Check value to find if it was serialized.
		 *
		 * If $data is not an string, then returned value will always be false.
		 * Serialized data is always a string.
		 *
		 * Courtesy WordPress; since WordPress 2.0.5.
		 *
		 * @param mixed $data Value to check to see if was serialized.
		 * @return bool False if not serialized and true if it was.
		 */
		function is_serialized( $data ) {
			// if it isn't a string, it isn't serialized
			if ( ! is_string( $data ) )
				return false;
			$data = trim( $data );
		 	if ( 'N;' == $data )
				return true;
			$length = strlen( $data );
			if ( $length < 4 )
				return false;
			if ( ':' !== $data[1] )
				return false;
			$lastc = $data[$length-1];
			if ( ';' !== $lastc && '}' !== $lastc )
				return false;
			$token = $data[0];
			switch ( $token ) {
				case 's' :
					if ( '"' !== $data[$length-2] )
						return false;
				case 'a' :
				case 'O' :
					return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
				case 'b' :
				case 'i' :
				case 'd' :
					return (bool) preg_match( "/^{$token}:[0-9.E-]+;\$/", $data );
			}
			return false;
		}
		
		
		/**
		 *	remove_matching_array_elements()
		 *	
		 *	Removes identical elements (same index and value) from both arrays where they match.
		 *
		 *	Ex:
		 *		// Before:
		 *		$a = array( 'apple', 'banana', 'carrot' );
		 *		$b = array( 'apple', 'beef', 'cucumber' );
		 *		remove_matching_array_elements( $a, $b );
		 *		// After:
		 *		$a = array( 'banana', 'carrot' );
		 *		$b = array( 'beef', 'cucumber' );
		 *	
		 *	@param		array		&$a		First array to compare with second. (reference)
		 *	@param		array		&$b		Second array to compare with first. (reference)
		 *	@return		null				Arrays passed are updated as they are passed by reference.
		 *
		 */
		function remove_matching_array_elements( &$a, &$b ) {
			$sizeof = sizeof( $a );
			for( $i=0; $i < $sizeof; $i++ ) {
				if ( $a[$i] == $b[$i] ) {
					unset( $a[$i] );
					unset( $b[$i] );
				}
			}
		}
		
		
	} // end pluginbuddy_dbreplace class.
}
?>