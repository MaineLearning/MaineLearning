<?php
die( 'OBSELETE' );
check_admin_referer( 'bbms-migration', 'pb_bbms_migrate' );
if ( !current_user_can( 'manage_sites' ) ) 
	wp_die( __( 'You do not have permission to access this page.', 'it-l10n-backupbuddy' ) );
global $current_site;




/* Import Buddy Classes */
if ( !class_exists( 'PluginBuddyImportBuddy' ) ) {
	class PluginBuddyImportBuddy {
		var $_version = '2.1.14';
		var $_timestamp = 'M j, Y, g:iA';						// PHP timestamp format.
		var $_total_steps = '7';
		var $_defaults = array(
			'password'					=>		'#PASSWORD#',	// MD5 hash of the import password. Prevents unauthorized access.
			'file'						=>		'',				// Selected backup import file.
			'zip_id'					=>		'',				// ID from the random strings in the ZIP filename. This allows finding of the temp directory in the wp-uploads directory.
			'type'						=>		'',				// Set on step 4 via a POST. Valid values: migrate, restore
			
			'skip_files'				=>		false,
			'skip_database_import'		=>		false,
			'skip_database_migration'	=>		false,
			'wipe_database'				=>		false,
			'skip_htaccess'				=>		false,
			'force_compatibility_medium'=>		false,
			'force_compatibility_slow'	=>		false,
			'show_php_warnings'			=>		false,
			'replace_existing_tables'	=>		false,
			
			'log_level'					=>		2,				// 0 = none, 1 = errors only, 2 = errors + warnings, 3 = debugging (all kinds of actions)
			'db_server'					=>		'',
			'db_user'					=>		'',
			'db_password'				=>		'',
			'db_name'					=>		'',
			'db_prefix'					=>		'',
			'siteurl'					=>		'',
		);
		var $_backupdata;
		
		
		/**
		 *	PluginBuddyImportBuddy()
		 *
		 *	Default constructor.
		 *
		 */
		function PluginBuddyImportBuddy( $parent ) {
			$this->_parent = $parent;
			// Prevent access to importbuddy.php if it is still in plugin directory.
			if ( file_exists( dirname( __FILE__ ) . '/backupbuddy.php' ) ) {
				echo 'The BackupBuddy importer, ImportBuddy, can ONLY be accessed on the destination server that you wish to import your backup to.<br>';
				echo 'Upload the importer in the root web directory on the destination server and try again.<br><br>';
				echo 'If you need assistance visit <a href="http://pluginbuddy.com">http://pluginbuddy.com</a>';
				die();
			}
						
			$this->time_start = microtime( true );
			
						
			


			
			
			// Set up PHP error levels.
			if ( $this->_options['show_php_warnings'] === true ) {
				error_reporting( E_ERROR | E_WARNING | E_PARSE | E_NOTICE ); // HIGH
				$this->log( 'PHP error reporting set HIGH.' );
			} else {
				error_reporting( E_ALL ^ E_NOTICE ); // LOW
			}
			
			$this->detected_max_execution_time = str_ireplace( 's', '', ini_get( 'max_execution_time' ) );
			if ( is_numeric( $detected_max_execution_time ) === false ) {
				$detected_max_execution_time = 30;
			}
						
			// Try to set timeouts to 30 minutes.
			//header( 'Keep-Alive: 3600' );
			//header( 'Connection: keep-alive' );
			ini_set( 'default_socket_timeout', '3600' );
			set_time_limit( '3600' );
			
			// Determine the current step.
			if ( ( isset( $_GET['step'] ) ) && ( is_numeric( $_GET['step'] ) ) ) {
				$this->_step = $_GET['step'];
			} else {
				$this->_step = 1;
			}
			
			
			// Run function for the requested step.
			if ( method_exists( $this, 'view_step_' . $this->_step ) ) {
				$this->log( 'Initiating step #' . $this->_step . '.' );
				
				echo "\n\n<!--\n\n";
				print_r( $this->_options );
				echo "\n\n-->\n\n";
				
				echo 'step: ' . $this->_step;
				call_user_func( array(&$this, 'view_step_' . $this->_step ) );
				//$this->print_html_footer();
				
				$this->log( 'Completed step #' . $this->_step . '.' );
			} else {
				$this->log( 'Unable to initiate step #' . $this->_step . '. Halted.', 'error' );
				die( 'ERROR #546542. Invalid step "' . $this->_step . '".' );
			}
		}
		

	
		
		function remove_directory( $dir ) {
			if (is_dir($dir)) {
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						if (filetype($dir."/".$object) == "dir") $this->remove_directory($dir."/".$object); else unlink($dir."/".$object);
					}
				}
				reset($objects);
				rmdir($dir);
			}
		} //end remove_directory 
		function remove_file( $file, $description, $error_on_missing = false ) {
			$this->log( 'Deleting ' . $description . ' ... ' );

			if ( file_exists( $file ) ) {
				chmod( $file, 0755 ); // High permissions to delete.
				echo 'Deleting ' . $description . ' ... ';
				if ( @unlink( $file ) != 1 ) {
					$this->alert( 'File ' . $description . ' couldn\'t be deleted. File permissions (' . $this->get_fileperms( $file ) . ') did not allow it. You may manually delete this file if you wish.', true );
				} else {
					echo ' Done.<br>';
					$this->log( 'Finished deleting ' . $description . '.' );
				}
			} else {
				if ( $error_on_missing === true ) {
					$this->alert( 'File ' . $description . ' was not found to delete. Already deleted?', true );
				} else {
					$this->log( 'File ' . $description . ' was not found to delete. Already deleted?' );
				}
			}
		}
		
		
		function get_fileperms( $file ) {
			$perms = fileperms( $file );

			if (($perms & 0xC000) == 0xC000) {
				// Socket
				$info = 's';
			} elseif (($perms & 0xA000) == 0xA000) {
				// Symbolic Link
				$info = 'l';
			} elseif (($perms & 0x8000) == 0x8000) {
				// Regular
				$info = '-';
			} elseif (($perms & 0x6000) == 0x6000) {
				// Block special
				$info = 'b';
			} elseif (($perms & 0x4000) == 0x4000) {
				// Directory
				$info = 'd';
			} elseif (($perms & 0x2000) == 0x2000) {
				// Character special
				$info = 'c';
			} elseif (($perms & 0x1000) == 0x1000) {
				// FIFO pipe
				$info = 'p';
			} else {
				// Unknown
				$info = 'u';
			}
			
			// Owner
			$info .= (($perms & 0x0100) ? 'r' : '-');
			$info .= (($perms & 0x0080) ? 'w' : '-');
			$info .= (($perms & 0x0040) ?
						(($perms & 0x0800) ? 's' : 'x' ) :
						(($perms & 0x0800) ? 'S' : '-'));
			
			// Group
			$info .= (($perms & 0x0020) ? 'r' : '-');
			$info .= (($perms & 0x0010) ? 'w' : '-');
			$info .= (($perms & 0x0008) ?
						(($perms & 0x0400) ? 's' : 'x' ) :
						(($perms & 0x0400) ? 'S' : '-'));
			
			// World
			$info .= (($perms & 0x0004) ? 'r' : '-');
			$info .= (($perms & 0x0002) ? 'w' : '-');
			$info .= (($perms & 0x0001) ?
						(($perms & 0x0200) ? 't' : 'x' ) :
						(($perms & 0x0200) ? 'T' : '-'));
			
			return $info;
		}
		
		function notify_existing_wordpress() {
			if ( file_exists( $this->_options[ 'extract_to' ] . '/wp-config.php' ) ) {
				$this->log( 'Found existing WordPress installation.', 'warning' );
				echo '<br><br><div>';
				echo ezimg::genImageTag( 'bullet_error.png' ) . ' WARNING: There appears to already be a WordPress installation at this location. It is recommended that existing WordPress files and database be removed prior to migrating or restoring to avoid conflicts. You should not install WordPress prior to migrating.<br>';
				echo '</div>';
			}
		}
		
		
		
		/**
		 *	load_backup_dat()
		 *
		 *	Gets the serialized data from the backupbuddy_dat.php file inside of the backup ZIP.
		 *	This happens post-file-extraction.
		 *
		 *	Saves data to $this->_backupdata.
		 *
		 *	@return			null
		 *
		 */
		function load_backup_dat() {
			$this->log( 'STARTING Loading backup dat file....' );
			$backupdata_file = $this->_options[ 'extract_to' ] . '/wp-content/uploads/temp_'. $this->_options['zip_id'] .'/backupbuddy_dat.php'; // Full backup dat file location
			$backupdata_file_new = $this->_options[ 'extract_to' ] . '/wp-content/uploads/backupbuddy_temp/'. $this->_options['zip_id'] .'/backupbuddy_dat.php'; // Full backup dat file location
			
			if ( file_exists( $backupdata_file ) ) { // Full backup location.
				$backupdata = file_get_contents( $backupdata_file );
			} elseif ( file_exists( $backupdata_file_new ) ) { // Full backup location.
				$backupdata = file_get_contents( $backupdata_file_new );
			} elseif ( file_exists( $this->_options[ 'extract_to' ] . '/backupbuddy_dat.php' ) ) { // DB only location.
				$backupdata = file_get_contents( $this->_options[ 'extract_to' ] . '/backupbuddy_dat.php' );
			} else { // Missing.
				$this->alert( 'Error #9003: BackupBuddy data file (backupbuddy_dat.php) missing or unreadable. There may be a problem with the backup file, the files could not be extracted (you may manually extract the zip file in this directory to manually do this portion of restore), or the files were deleted before this portion of the restore was reached.  Start the import process over or try manually extracting (unzipping) the files then starting over. Restore will not continue to protect integrity of any existing data.', true, '9003' );
				die( ' Halted.' );
			}
			
			// Unserialize data; If it fails it then decodes the obscufated data then unserializes it. (new dat file method starting at 2.0).
			if ( false === ( $this->_backupdata = unserialize( $backupdata ) ) ) {
				// Skip first line.
				$second_line_pos = strpos( $backupdata, "\n" ) + 1;
				$backupdata = substr( $backupdata, $second_line_pos );
				
				// Decode back into an array.
				$this->_backupdata = unserialize( base64_decode( $backupdata ) );
			}
			
			$this->log( 'DONE Loading backup dat file.' );
		}
		
		
		/**
		 *	migrate_htaccess()
		 *
		 *	Migrates .htaccess file if it exists.
		 *
		 *	@return		boolean		True on success. Currently always true.
		 *
		 */
		function migrate_htaccess() {
			// If there is no .htaccess file then return.
			if ( !file_exists( $this->_options[ 'extract_to' ] . '/.htaccess' ) ) {
				$this->log( 'No htaccess file found. Skipping.' );
				return;
			}
			
			$this->log( 'Migrating htaccess file.' );
			
			$oldurl = strtolower( $this->_backupdata['siteurl'] );
			$oldurl = str_replace( '/', '\\', $oldurl );
			$oldurl = str_replace( 'http:\\', '', $oldurl );
			$oldurl = trim( $oldurl, '\\' );
			$oldurl = explode( '\\', $oldurl );
			$oldurl[0] = '';
			
			$newurl = strtolower( $this->_options['siteurl'] );
			$newurl = str_replace( '/', '\\', $newurl );
			$newurl = str_replace( 'http:\\', '', $newurl );
			$newurl = trim( $newurl, '\\' );
			$newurl = explode( '\\', $newurl );
			$newurl[0] = '';
			
			echo 'Checking .htaccess file ... ';
			
			// If the URL (domain and/or URL subdirectory ) has changed, then need to update .htaccess file.
			if ( $newurl !== $oldurl ) {
				echo 'URL is different from source site. Updating .htaccess ... ';
				$this->log( 'HTAccess updating... Old URL: ' . $oldurl . '; New URL: ' . $newurl . '.' );
				
				$rewrite_lines = array();
				$got_rewrite = false;
				$rewrite_path = implode( '/', $newurl );
				$file_array = file( $this->_options[ 'extract_to' ] . '/.htaccess' );
				
				foreach ($file_array as $line_number => $line) {
					if ( $got_rewrite == true ) { // In a WordPress section.
						if ( strstr( $line, 'END WordPress' ) ) { // End of a WordPress block so stop replacing.
							$got_rewrite = false;
							$rewrite_lines[] =  $line; // Captures end of WordPress block.
						} else {
							if ( strstr( $line, 'RewriteBase' ) ) { // RewriteBase
								$rewrite_lines[] = 'RewriteBase ' . $rewrite_path . '/' . "\n";
							} elseif ( strstr( $line, 'RewriteRule' ) ) { // RewriteRule
								if ( strstr( $line, '^index\.php$' ) ) { // Handle new strange rewriterule. Leave as is.
									$rewrite_lines[] = $line;
									$this->log( 'Htaccess ^index\.php$ detected. Leaving as is.' );
								} else { // Normal spot.
									$rewrite_lines[] = 'RewriteRule . ' . $rewrite_path . '/index.php' . "\n";
								}
							} else {
								$rewrite_lines[] =  $line; // Captures everything inside WordPress block we arent modifying.
							}
						}
					} else { // Outside a WordPress section.
						if ( strstr( $line, 'BEGIN WordPress' ) ) {
							$got_rewrite = true; // Beginning of a WordPress block so start replacing.
						}
						$rewrite_lines[] =  $line; // Captures everything outside of WordPress block.
					}
				}
					
				$handling = fopen( $this->_options[ 'extract_to' ] . '/.htaccess', 'w');
				fwrite( $handling, implode( $rewrite_lines ) );
				fclose( $handling );
				unset( $handling );
				echo 'Done.<br>';
				
				$this->log( 'Finished migrating htaccess file.' );
			} else {
				echo 'No changes needed. Done.<br>';
				
				$this->log( 'htaccess file did not need changed.' );
			}
			
			return true;
		}
		
		
		function connect_database() {
			// Set up database connection.
			if ( false === @mysql_connect( $this->_options['db_server'], $this->_options['db_user'], $this->_options['db_password'] ) ) {
				$this->alert( 'ERROR: Unable to connect to database server and/or log in. Verify the database server name, username, and password. Details: ' . mysql_error(), true, '9006' );
				return false;
			}
			$database_name = mysql_real_escape_string( $this->_options['db_name'] );
			
			flush();
			
			// Select the database.
			if ( false === @mysql_select_db( $this->_options['db_name'] ) ) {
				$this->alert( 'ERROR: Unable to select your specified database. Verify the database name and that you have set up proper permissions for your specified username to access it. Details: ' . mysql_error(), true, '9007' );
				return false;
			}
			
			// Set up character set. Important.
			mysql_query("SET NAMES 'utf8'");
		}
		
		
		/**
		 *	restore_database()
		 *
		 *	Preset the following variables within $this->_options before continuing:
		 *		db_server, db_user, db_password, db_name
		 *
		 *	@return			mixed		true: success, false: failure, integer: query to start next db import chunk on
		 */
		function restore_database( $query_start = 0 ) {
			$this->log( 'Beginning database restoration.' );
			
			$this->connect_database();
			
			// Require a table prefix.
			if ( $this->_options['db_prefix'] == '' ) {
				$this->alert( 'ERROR: A database prefix is required for importing. Details: ' . mysql_error(), true, '9008' );
			}
			
			if ( $query_start > 0 ) {
				echo 'Continuing to restore database dump starting at query ' . $query_start . '. ';
			} else {
				echo 'Restoring database dump ... ';
			}
			
			flush();	
			
			// Import SQL dump onto new server. NOTE: This data has NOT been migrated. It is identical to the source server still at this point.
			$import_result = $this->import_sql_dump( $query_start );
			
			// CLEANUP
			mysql_close();
			
			//$this->log( 'Finished restoring database.' );
			
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
			
			$this->log( 'Beginning importing of DB dump.' );
			
			$file_stream = false; // Default state.
			if ( file_exists ( $this->_options[ 'extract_to' ] . '/wp-content/uploads/temp_' . $this->_options['zip_id'] . '/db.sql' ) ) { // Full backup found.
				$file_stream = fopen( $this->_options[ 'extract_to' ] . '/wp-content/uploads/temp_'.$this->_options['zip_id'].'/db.sql', 'r' );
			} elseif ( file_exists ( $this->_options[ 'extract_to' ] . '/db.sql' ) ) { // DB-only backup found.
				$file_stream = fopen( $this->_options[ 'extract_to' ] . '/db.sql', 'r' );
			} elseif ( file_exists ( $this->_options[ 'extract_to' ] . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/db_1.sql' ) ) { // Full backup found. 2.0 method.
				$file_stream = fopen( $this->_options[ 'extract_to' ] . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/db_1.sql', 'r' );
			} elseif ( file_exists ( $this->_options[ 'extract_to' ] . '/db_1.sql' ) ) { // DB-only backup found. 2.0 method.
				$file_stream = fopen( $this->_options[ 'extract_to' ] . '/db_1.sql', 'r' );
			}
			
			if ( false === $file_stream ) {
				$this->alert( 'ERROR: Unable to find any database backup data in the selected backup.', true, '9009' );
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
					
					if ( 0 === ( $query_count % 100 ) ) {
						echo '.';
					}
					if ( 0 === ( $query_count % 6000 ) ) {
						echo "<br>\n";
					}
					
					// If we are within 1 second of reaching maximum PHP runtime then stop here so that it can be picked up in another PHP process...
					if ( ( ( microtime( true ) - $this->time_start ) + 1 ) >= $this->_options['max_execution_time'] ) {
					// TODO: Debugging:
					//if ( ( ( microtime( true ) - $this->time_start ) ) >= $this->_options['max_execution_time'] ) {
						echo ' Exhausted available PHP time to import for this page load... Stopped after query ' . $query_count . '. ';
						
						fclose( $file_stream );
						$this->log( 'Database too large to import in one pass. Breaking into chunks and continuing at query ' . ( $query_count + 1 ) );
						
						return ( $query_count + 1 );
						//break 2;
					}
				}
				
			}
			
			fclose( $file_stream );
			
			$this->log( 'Finished importing of DB dump.' );
			
			echo ' Took ' . round( microtime( true ) - $this->time_start, 3 ) . ' seconds on ' . $query_count . ' queries. ';
			
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
			$old_prefix = $this->_backupdata['db_prefix'];
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
				//Skip alerts
				//$this->alert( 'ERROR: Unable to import SQL query: ' . mysql_error(), true, '9010' );
				return false;
			} else {
				return true;
			}
		}
		
		
		/**
		 *	migrate_database()
		 *
		 *	Migrates the already imported database's content for updates $this->_options[ 'extract_to' ] and URL.
		 *
		 *	@return		boolean		True=success, False=failed.
		 *
		 */
		function migrate_database( $uploads_url = '' ) {
			$blog_id = isset( $_POST[ 'blog_id' ] ) ? absint( $_POST[ 'blog_id' ] ) : 0;
			$uploads_url = $_POST[ 'upload_url' ];
			$old_abspath = $this->_backupdata['abspath'];
			//$old_abspath = str_replace( '\\', '\\\\', $this->_backupdata['abspath'] ); // Remove escaping of windows paths. - Caused problems. REMOVE?
			$old_abspath = preg_replace( '|/+$|', '', $old_abspath ); // Remove escaping of windows paths.
			//$new_abspath = str_replace( '\\', '\\\\', $this->_options[ 'extract_to' ] ); Caused problems. REMOVE?
			$new_abspath = $this->_options[ 'extract_to' ];
			$this->log( 'ABSPATH change for database ... Old Path: ' . $old_abspath . ', New Path: ' . $new_abspath . '.' );
			
			$old_url = $this->_backupdata['siteurl'];  // the value you want to search for
			
			// If http://www.blah.... then also we will replace http://blah... and vice versa.
			if ( stristr( $old_url, 'http://www.' ) || stristr( $old_url, 'https://www.' ) ) {
				$old_url_alt = str_ireplace( 'http://www.', 'http://', $old_url );
				$old_url_alt = str_ireplace( 'https://www.', 'https://', $old_url_alt );
			} else {
				$old_url_alt = str_ireplace( 'http://', 'http://www.', $old_url );
				$old_url_alt = str_ireplace( 'https://', 'https://www.', $old_url_alt );
			}
			
			$new_url = preg_replace( '|/*$|', '', $this->_options['siteurl'] );  // the value to replace it with
			$this->log( 'URL change for database ... Old URL: ' . $old_url . ', New URL: ' . $new_url . '.' );
			
			$count_tables_checked = 0;
			$count_items_checked = 0;
			$count_items_changed = 0;
			
			// Connect to database.
			$this->connect_database();
			
			flush();
			
			$row_loop = 0;
			
			// Loop through the tables matching this prefix. Does NOT change data in other tables.
			// This changes actual data on a column by column basis for very row in every table.
			$main_result = mysql_query( "SHOW TABLES LIKE '" . $this->_options['db_prefix'] . "%'" );
			//echo 'Found ' . mysql_num_rows( $main_result ) . ' WordPress tables. ';
			while ( $table = mysql_fetch_row( $main_result ) ) {
				
				$count_tables_checked++;
				
				$fields_list = mysql_query( "DESCRIBE `" . $table[0] . "`" );
				$index_fields = '';  // Reset fields for each table.
				$column_name = '';
				$table_index = '';
				$i = 0;
				
				while ( $field_rows = mysql_fetch_array( $fields_list ) ) {
					$column_name[$i++] = $field_rows['Field'];
					if ( $field_rows['Key'] == 'PRI' ) {
						$table_index[$i] = true;
					}
				}

				$data = mysql_query( "SELECT * FROM `" . $table[0] . "`" );
				if (!$data) {
					$this->alert( 'ERROR #44545343 ... SQL ERROR: ' . mysql_error(), true );
				}
				
				while ( $row = mysql_fetch_array( $data ) ) {
					$need_to_update = false;
					$UPDATE_SQL = 'UPDATE `' . $table[0] . '` SET ';
					$WHERE_SQL = ' WHERE ';
					
					$j = 0;
					foreach ( $column_name as $current_column ) {
						$j++;
						$count_items_checked++;
						$row_loop++;
						if ( $row_loop > 5000 ) {
							$row_loop = 0;
							echo '.';
							flush();
						}
						
						$data_to_fix = $row[$current_column];
						$edited_data = $data_to_fix;						// Set the same now - if they're different later we know we need to update.
						
						$unserialized = unserialize( $data_to_fix );		// unserialise - if false returned we don't try to process it as serialised.
						
						// PREPARE TO UPDATE UPLOADS URLS
						global $current_blog;
						$old_wpcontent_dir = $old_url . '/wp-content/uploads/';
						if ( empty( $uploads_url ) ) { // standalone source
							$new_wp_uploads = wp_upload_dir();
							$new_wpcontent_dir = $new_wp_uploads[ 'baseurl' ] . '/';
						} else { // ms source
							$new_wpcontent_dir = $uploads_url . '/';
						}
						$test_string = 'http://zainity.com/mytest/mayday/wp-content/blogs.dir/14/files/2010/10/itunes-pc-importing-song-300x154.png';
						
						//For strings like this:  http://domain.com/wp-content/blogs.dir/200/files/2010/10/file.png
						$pattern = '/(?!blogs\.dir\/)(\d+)(?=\/files\/)/'; //Replace just the blog ID
						
						// END PREPARE TO UPDATE UPLOADS URLS
						
						if ( $unserialized !== false ) {					// Serialized data so recursive array replace.
							$this->log ( 'Serialized ORIGINAL data: ' . $data_to_fix );
							$this->log( 'THIS IS ANGRY: ' . print_r( $unserialized, true ) );
							
							$this->recursive_array_replace( $old_url, $new_url, $unserialized );
							$this->recursive_array_replace( $old_url_alt, $new_url, $unserialized );
							$this->recursive_array_replace( $old_abspath, $new_abspath, $unserialized );
							$this->recursive_array_replace( $old_wpcontent_dir, $new_wpcontent_dir, $unserialized );
							$this->recursive_array_replace( '\"/wp-content/uploads/\"', '"' . $new_wpcontent_dir . 'files/' . '"', $unserialized );
							$this->recursive_array_replace( "\'/wp-content/uploads/\'", "'" . $new_wpcontent_dir . 'files/' . "'", $unserialized );
							$this->recursive_preg_replace( $pattern, $blog_id, $unserialized );
							$edited_data = serialize( $unserialized );
							$this->log ( 'Serialized EDITED data: ' . $edited_data );
						}	else {											// Not serialized data so just string replace.
							//if ( is_string( $data_to_fix ) ) {
							$this->log ( 'Not serialized ORIGINAL data: ' . $data_to_fix );
							
							$edited_data = str_replace( $old_wpcontent_dir, $new_wpcontent_dir, $data_to_fix );
							$edited_data = str_replace( $old_url, $new_url, $edited_data );
							$edited_data = str_replace( $old_url_alt, $new_url, $edited_data );
							$edited_data = str_replace( $old_abspath, $new_abspath, $edited_data );
							
							$edited_data = preg_replace( '/(\'|\")\/wp\-content\/uploads/', '${1}' . $new_wpcontent_dir . 'files', $edited_data );
							$edited_data = preg_replace( $pattern, $blog_id, $edited_data );
							
							$this->log ( 'Not serialized EDITED data: ' . $edited_data );
							//}
						}
							
						if ( $data_to_fix != $edited_data ) {				// If they're not the same, we need to add them to the update string.
							$this->log( 'Data changed!' );
							$count_items_changed++;
							if ( $need_to_update != false ) {				// If this isn't our first time here, we need to add a comma.
								$UPDATE_SQL = $UPDATE_SQL . ',';
							}
							$UPDATE_SQL = $UPDATE_SQL . ' ' . $current_column . ' = "' . mysql_real_escape_string( $edited_data ) . '"';
							$need_to_update = true;							// Only set if we need to update - avoids wasted UPDATE statements.
						}
						
						if ( $table_index[$j] ) {
							$WHERE_SQL = $WHERE_SQL . $current_column . ' = "' . $row[$current_column] . '" AND ';
						}
					}
					
					if ( $need_to_update ) {
						$this->log( 'Updated data.' );
						$WHERE_SQL = substr( $WHERE_SQL , 0, -4 );				// Strip off the excess AND - the easiest way to code this without extra flags, etc.
						$UPDATE_SQL = $UPDATE_SQL . $WHERE_SQL;
						$result = mysql_query( $UPDATE_SQL );
						if ( !$result ) {
							$this->alert( 'ERROR: mysql error updating db: ' . mysql_error() . '. SQL Query that failed on next line:', true );
							$this->alert( $UPDATE_SQL, true );
						} 
					}
					
				}
				
			}
			
			unset( $main_result );
			
			// Update table prefixes in some WordPress meta data.
			$old_prefix = $this->_backupdata['db_prefix'];
			$new_prefix = mysql_real_escape_string( $this->_options['db_prefix'] );
			if ($old_prefix != $new_prefix ) {
				mysql_query("UPDATE `".$new_prefix."usermeta` SET meta_key = REPLACE(meta_key, '".$old_prefix."', '".$new_prefix."' );");
				mysql_query("UPDATE `".$new_prefix."options` SET option_name = '".$new_prefix."user_roles' WHERE option_name ='".$old_prefix."user_roles' AND blog_id='0';");
				echo ' Updated prefix META data. ';
			}
			
			/*
			$old_blogname = $this->_backupdata['blogname'];
			$new_blogname = mysql_real_escape_string( $_POST['blogname'] );
			if ($old_blogname != $new_blogname ) {
				mysql_query( "UPDATE ".$new_prefix."options SET option_value = '" . $new_blogname . "' WHERE option_name='blogname' LIMIT 1;" );
				echo 'Updated blog name.';
			}
			
			$old_blogdescription = $this->_backupdata['blogdescription'];
			$new_blogdescription = mysql_real_escape_string( $_POST['blogdescription'] );
			if ($old_blogdescription != $new_blogdescription ) {
				mysql_query( "UPDATE ".$new_prefix."options SET option_value = '" . $new_blogdescription . "' WHERE option_name='blogdescription' LIMIT 1;" );
				echo 'Updated blog description.';
			}
			*/
			
			echo '<br>Checked ' . $count_tables_checked . ' tables & ' . $count_items_checked . ' items. ' . $count_items_changed . ' items updated. Done.<br>';
			
			$this->log( 'Finished migration of DB.' );
			
			// Migrate WP config.
			$this->migrate_wp_config();
			
			return true;
		}
		
		
		function wipe_database() {
			$this->log( 'Beginning wipe of database.' );
			$this->log( '_options[] value: ' . serialize( $this->_options ) );
			
			// Connect to database.
			$this->connect_database();
			
			$result = mysql_query( 'SHOW TABLES' );
			echo 'Dropping ' . mysql_num_rows( $result ) . ' tables ... ';
			while( $row = mysql_fetch_row( $result ) ) {
				mysql_query( 'DROP TABLE `' . $row[0] . '`' );
			}
			mysql_free_result( $result ); // Free memory.
			
			return true;
		}
		
		
		/*	extract_files()
		 *
		 *	Extracts files trying the following methods:
		 *
		 *	1) Command line UNZIP executable.
		 *	2) PHP ZipArchive Class compiled in PHP.
		 *	3) PCLZip PHP-based zip emulation.
		 *
		 */
		function extract_files() {
			$this->log( 'Starting to unzip.' );
			
			$failed = true;
			$this->remove_directory( $this->_options[ 'extract_to' ] );

			// HIGHSPEED
			if ( ( $this->_options['force_compatibility_medium'] === false ) && ( $this->_options['force_compatibility_slow'] === false ) ) {
				echo 'Attempting high speed extraction (normal mode) ... ';
				if ( $this->extract_files_highspeed() === true ) {
					$failed = false;
					echo 'Done.<br><br>';
					$this->log( 'Highspeed Native command line unzip success!' );
					
					return true;
				} else {
					$failed = true;
					echo 'Failed.<br><br>Falling back to slower method.<br><br>';
					$this->log( 'High speed extraction FAILED! Falling back to slower method.', 'error' );
				}
			} else {
				echo 'Skipping high speed extraction based on advanced settings.<br><br>';
			}
			
			// MEDIUMSPEED
			if ( $this->_options['force_compatibility_slow'] === false ) {
				if ( $failed === true ) {
					echo 'Attempting medium speed extraction (compatibility mode: ZipArchive) ... ';
					if ( $this->extract_files_mediumspeed() === true ) {
						$failed = false;
						echo 'Done.<br><br>';
						$this->log( 'Mediumspeed ZipArchive class unzip success!' );
						
						return true;
					} else {
						$failed = true;
						echo 'Failed.<br><br>Falling back to slower method.<br><br>';
						$this->log( 'Medium speed extraction FAILED! Falling back to slower method.', 'error' );
					}
				}
			} else {
				echo 'Skipping medium speed extraction based on advanced settings.<br><br>';
			}
			
			if ( $failed === true ) {
				// LOWSPEED
				echo 'Attempting low speed extraction (compatibility mode: PCLZip) ... ';
				if ( $this->extract_files_lowspeed() === true ) {
					$failed = false;
					echo 'Done.<br><br>';
					$this->log( 'Lowspeed PCLZIP unzip success!' );
					
					return true;
				} else {
					$failed = true;
					echo 'Failed. Final compatibility unzip method has failed.<br><br>';
					$this->log( 'Low speed extraction FAILED! No more fallbacks.', 'error' );
				}
			}
			
			unset( $failed );
			
			return false; // If we got this far, nothing succeeded!
		}
		
		
		function extract_files_highspeed() {
			$this->log( 'Starting highspeed extraction.' );
			
			$file = ABSPATH . '/' . $this->_options['file'];
			$directory = $this->_options[ 'extract_to' ];
			$options = '';
		
			$phpinfo = $this->phpinfo_array();
			if ( strpos( $phpinfo['PHP Core']['disable_functions'], 'exec') != true ) { // If exec is not explicitly disabled in PHP...
				$command = 'unzip -qo'; // q = quiet, o = overwrite without prompt.
				$command .= " '$file' -d '$directory' -x 'importbuddy.php'"; // x excludes importbuddy script to prevent overwriting newer importbuddy on extract step.

				if ( file_exists( $this->_options[ 'extract_to' ] . '\unzip.exe' ) ) {
					$this->alert( 'Attempting to use provided unzip.exe for Windows zip functionality.' );
					
					$command = str_replace( '\'', '"', $command ); // Windows wants double quotes
					$command = $this->_options[ 'extract_to' ] . '\\' . $command;
				}
				
				exec( $command, $exec_return_a, $exec_return_b);
				
				if ( $exec_return_b != '' ) { // UNZIP FAILED!
					// ERROR LIST: http://www.mkssoftware.com/docs/man1/unzip.1.asp
					if ( $exec_return_b == '50' ) {
						$this->alert( 'The disk is (or was) full during extraction <b>OR</b> the zip/unzip command does not have write permission to your directory.  Try increasing permissions for the directory.', true );
					}
					
					return false;
				}
			} else {
				echo '<br><br><br>';
				$this->alert( 'Highspeed extraction failed. Exec() function is explicitly disabled in PHP. Falling back to slower mode to try again.', true );
				
				return false;
			}
			
			// Unzip reported success so make sure key files exist!
			if ( 
			  ( !file_exists ( $this->_options[ 'extract_to' ] . '/wp-content/uploads/temp_'.$this->_options['zip_id'].'/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( $this->_options[ 'extract_to' ] . '/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( $this->_options[ 'extract_to' ] . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/backupbuddy_dat.php' ) )
			  ) {
				$this->alert( 'Highspeed extraction reported success; HOWEVER, key files are missing. Falling back to slower mode to try again.', true );
				return false;
			}
			
			return true;
		}
		
		
		function extract_files_mediumspeed() {
			$this->log( 'Starting mediumspeed extraction.' );
			
			$file = ABSPATH . '/' . $this->_options['file'];
			$directory = $this->_options[ 'extract_to' ];
			
			if ( class_exists( 'ZipArchive' ) ) {
				$this->log( 'ZipArchive class exists.' );
				$zip = new ZipArchive;
				if ( $zip->open( $file ) === true ) {
					if ( $zip->extractTo( $directory ) === true ) {
						$this->log( 'ZipArchive extraction success.' );
						return true;
					} else {
						$this->alert( 'ZipArchive was available but failed extracting files.', true );
						return false;
					}
				} else {
					$this->alert( 'Unable to open ZipArchive ZIP: ' . $file, true );
					return false;
				}
			} else {
				$this->log( 'ZipArchive class unavailable.' );
				if ( version_compare( phpversion(), '5.0.0', '<' ) ) {
					$reason = 'as this server runs PHP 4. See if your host can upgrade to PHP 5.2+.';
				} elseif ( version_compare( phpversion(), '5.2.0', '<' ) ) {
					$reason = 'as this server runs PHP ' . phpversion() . '. See if your host can upgrade to PHP 5.2+.';
				} else {
					$reason = 'as this server\'s PHP wasn\'t compiled with the --enable-zip. See if your host is able to update PHP with this option.';
				}
				$this->alert( 'Mediumspeed extraction failed. ZipArchive is not available ' . $reason . '. Falling back to slower mode to try again.', true );
				return false;
			}
			
			// Unzip reported success so make sure key files exist!
			if ( 
			  ( !file_exists ( $this->_options[ 'extract_to' ] . '/wp-content/uploads/temp_'.$this->_options['zip_id'].'/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( $this->_options[ 'extract_to' ] . '/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( $this->_options[ 'extract_to' ] . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/backupbuddy_dat.php' ) )
			  ) {
				$this->alert( 'Highspeed extraction reported success; HOWEVER, key files are missing. Falling back to slower mode to try again.', true );
				return false;
			}
			
			return true;
		}
		
		
		function extract_files_lowspeed() {
			$this->log( 'Starting lowspeed extraction.' );
			
			$file = ABSPATH . '/' . $this->_options['file'];
			
			$archive = new PclZip( $file );
			
			if ( file_exists( $file ) ) {
				$result = $archive->extract(); // Extract to current directory. Explicity using PCLZIP_OPT_PATH results in extraction to a PCLZIP_OPT_PATH subfolder.
			} else {
				$this->alert( 'Unable to open ZIP for PCLZIP. Error #54556565834.', true );
			}
			
			if ( 0 == $result ) {
				echo 'Error: '.$archive->errorInfo(true);
				$this->alert( 'Lowspeed extraction failed. Message: ' . $archive->errorInfo(true), true );
				return false;
			}
			
			// Unzip reported success so make sure key files exist!
			if ( 
			  ( !file_exists ( $this->_options[ 'extract_to' ] . '/wp-content/uploads/temp_'.$this->_options['zip_id'].'/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( $this->_options[ 'extract_to' ] . '/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( $this->_options[ 'extract_to' ] . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/backupbuddy_dat.php' ) )
			  ) {
				$this->alert( 'Highspeed extraction reported success; HOWEVER, key files are missing. Falling back to slower mode to try again.', true );
				return false;
			}
			
			return true;
		}
		
		
		// Used for database serialization replacing.
		function recursive_array_replace( $find, $replace, &$data ) {
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
		
		// Used for database serialization replacing.
		function recursive_preg_replace( $find, $replace, &$data ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					if ( is_array( $value ) ) {
						$this->recursive_preg_replace( $find, $replace, $data[$key] );
					} else {
						// Have to check if it's string to ensure no switching to string for booleans/numbers/nulls - don't need any nasty conversions.
						if ( is_string( $value ) ) $data[$key] = preg_replace( $find, $replace, $value );
					}
				}
			} else {
				if ( is_string( $data ) ) $data = preg_replace( $find, $replace, $data );
			}
		}
		
		
		function remove_dir( $dir ) {
			if ( !file_exists( $dir ) ) {
				return true;
			}
			if ( !is_dir( $dir ) || is_link( $dir ) ) {
				return unlink($dir);
			}
			foreach ( scandir( $dir ) as $item ) {
				if ( $item == '.' || $item == '..' ) {
					continue;
				}
				if ( !remove_dir( $dir . "/" . $item ) ) {
					chmod( $dir . "/" . $item, 0777 );
					if ( !remove_dir( $dir . "/" . $item ) ) {
						return false;
					}
				}
			}
			return rmdir($dir);
		}
		
		
		// Get phpinfo() data as an array.
		function phpinfo_array() {
			ob_start();
			phpinfo(-1);
			
			$pi = preg_replace(
			array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
			'#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
			"#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
			'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
			.'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
			'#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
			'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
			"# +#", '#<tr>#', '#</tr>#'),
			array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
			'<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
			"\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
			'<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
			'<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
			'<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
			ob_get_clean());
			
			$sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
			unset($sections[0]);
			
			$pi = array();
			foreach($sections as $section){
				$n = substr($section, 0, strpos($section, '</h2>'));
				preg_match_all( '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $section, $askapache, PREG_SET_ORDER);
				foreach($askapache as $m) {
					if (isset($m[2])) { // Fix undefined offset warning.
						$pi[$n][$m[1]]=(!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m,2);
					}
				}
			}
			
			return $pi;
		}
		
		
		function rand_string( $length = 32, $chars = 'abcdefghijkmnopqrstuvwxyz1234567890' ) {
			$chars_length = ( strlen( $chars ) - 1 );
			$string = $chars{rand(0, $chars_length)};
			for ( $i = 1; $i < $length; $i = strlen( $string ) ) {
				$r = $chars{rand(0, $chars_length)};
				if ($r != $string{$i - 1}) $string .=  $r;
			return $string;
			}
		}
		
		
		function migrate_wp_config() {
			return;
			$this->log( 'Beginning migrating wp-config' );
			
			echo 'Migrating wp-config.php ... ';
			
			flush();
			
			if (file_exists($this->_options[ 'extract_to' ].'/wp-config.php')) {
				$wp_config = array();
				$lines = file( $this->_options[ 'extract_to' ] . '/wp-config.php' );
				foreach($lines as $line) {
					if (strstr($line,'DB_NAME')) {
						$wp_config[] = "define('DB_NAME', '".$this->_options['db_name']."');\r\n";
					} elseif (strstr($line,'DB_USER')) {
						$wp_config[] = "define('DB_USER', '".$this->_options['db_user']."');\r\n";
					} elseif (strstr($line,'DB_PASSWORD')) {
						$wp_config[] = "define('DB_PASSWORD', '".$this->_options['db_password']."');\r\n";
					} elseif (strstr($line,'DB_HOST')) {
						$wp_config[] = "define('DB_HOST', '".$this->_options['db_server']."');\r\n";
					} elseif (strstr($line,'$table_prefix')) {
						$wp_config[] = '$table_prefix = '."'". $this->_options['db_prefix'] ."';\r\n";
					} else {
						$wp_config[] = $line;
					}
				}
				unset($lines);
				unset($line);
				
				if ( chmod( $this->_options[ 'extract_to' ].'/wp-config.php', 0755 ) ) {
					$this->log( 'Changed wp-config permissions to 755.' );
				}
				
				// Write changes to config file.
				if ( false === ( $fh = fopen( $this->_options[ 'extract_to' ] . '/wp-config.php', 'w' ) ) ) {
					$this->alert( 'ERROR: Unable to save changes to wp-config.php. Make sure this file has write permissions!', true );
					return false;
				}
				
				foreach ($wp_config as $key => $value) {
					fwrite($fh, $value);
				}
				unset($value);
				unset($key);
				fclose($fh);
				unset($fh);
			} else {
				echo '<br>' . ezimg::genImageTag( 'bullet_error.png' ) . 'Note: wp-config.php not found.  This is only a database restoration.<br>';
			}
			
			echo 'Done.<br>';
			
			$this->log( 'Finished migrating wp-config' );
			
			return true;
		}
		
	}
}
$PluginBuddyImportBuddy = new PluginBuddyImportBuddy( $this );
?>