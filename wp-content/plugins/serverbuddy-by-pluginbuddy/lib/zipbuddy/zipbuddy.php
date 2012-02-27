<?php
/*
Constructor arg1:

$temp_dir		string		Absolute path to a writable directory for temp files.
$zip_methods	array		Array of available zip methods to use. Useful for not having to re-test every time.
*/

if ( !class_exists( "pluginbuddy_zipbuddy" ) ) {
	class pluginbuddy_zipbuddy {
		
		function pluginbuddy_zipbuddy( $temp_dir, $zip_methods = '' ) {
			$this->_status = array();
			$this->_tempdir = $temp_dir;
			
			if ( !empty( $zip_methods ) ) {
				$this->_zip_methods = $zip_methods;
			} else {
				$this->_zip_methods = $this->available_zip_methods();
			}
			
			/*
			echo 'available zip: <br /><pre>';
			print_r( $this->_zip_methods );
			echo '</pre>';
			*/
		}
		
		
		// Returns true if the file (with path) exists in the ZIP.
		// If leave_open is true then the zip object will be left open for faster checking for subsequent files within this zip
		function file_exists( $zip_file, $locate_file, $leave_open = false ) {
			if ( in_array( 'ziparchive', $this->_zip_methods ) ) {
				$this->_zip = new ZipArchive;
				if ( $this->_zip->open( $zip_file ) === true ) {
						if ( $this->_zip->locateName( $locate_file ) === false ) { // File not found in zip.
							$this->_zip->close();
							$this->_status[] = 'File not found (ziparchive): ' . $locate_file;
							return false;
						}
						$this->_zip->close();
					return true; // Never ran into a file missing so must have found them all.
				} else {
					$this->_status[] = 'ZipArchive failed to open file to check if file exists (looking for ' . $locate_file . ' in ' . $zip_file . ').';
					
					return false;
				}
			}
			
			// If we made it this far then ziparchive not available/failed.
			if ( in_array( 'pclzip', $this->_zip_methods ) ) {
				require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
				$this->_zip = new PclZip( $zip_file );
				if ( ( $file_list = $this->_zip->listContent() ) == 0 ) { // If zero, zip is corrupt or empty.
					$this->_status[] = $this->_zip->errorInfo( true );
				} else {
					foreach( $file_list as $file ) {
						if ( $file['filename'] == $locate_file ) { // Found file.
							return true;
						}
					}
					$this->_status[] = 'File not found (pclzip): ' . $locate_file;
					return false;
				}
			} else {
				$this->_status[] = 'Unable to check if file exists: No compatible zip method found.';
				return false;
			}
		}
		
		
		/**
		 *	add_directory_to_zip()
		 *
		 *	Adds a directory to a new or existing (TODO: not yet available) ZIP file.
		 *
		 *	$zip_file					string				Full path & filename of ZIP file to create.
		 *	$add_directory				string				Full directory to add to zip file.
		 *	$compression				boolean				True to enable ZIP compression,
		 *													(if possible with available zip methods)
		 *	$excludes					array( string )		Array of strings of paths/files to exclude from zipping,
		 *													(if possible with available zip methods).
		 *	$temporary_zip_directory	string				Optional. Full directory path to directory to temporarily place ZIP
		 *													file while creating. Uses same directory if omitted.
		 *	$force_compatibility_mode	boolean				True: only use PCLZip. False: try exec first if available,
		 *													and fallback to lesser methods as required.
		 *
		 *	@return									true on success, false otherwise
		 *
		 */
		function add_directory_to_zip( $zip_file, $add_directory, $compression, $excludes = array(), $temporary_zip_directory = '', $force_compatibility_mode = false ) {
			if ( $force_compatibility_mode === true ) {
				$zip_methods = array( 'pclzip' );
				$this->status( 'message', 'Forced compatibility mode (PCLZip) based on settings. This is slower and less reliable.' );
			} else {
				$zip_methods = $this->_zip_methods;
				$this->status( 'details', 'Using all available zip methods in preferred order.' );
			}
			
			$append = false; // Possible future option to allow appending if file exists.
			
			if ( !empty( $temporary_zip_directory ) ) {
				if ( !file_exists( $temporary_zip_directory ) ) { // Create temp dir if it does not exist.
					mkdir( $temporary_zip_directory );
				}
			}
			
			$this->status( 'details', 'Creating ZIP file `' . $zip_file . '`. Adding directory `' . $add_directory . '`. Compression: ' . $compression . '; excludes: ' . implode( ',', $excludes ) );
			
			if ( in_array( 'exec', $zip_methods ) ) {
				$this->status( 'details', 'Using exec() method for ZIP.' );
				
				$command = 'zip -q -r';
				
				if ( $compression !== true ) {
					$command .= ' -0';
					$this->status( 'details', 'Exec compression disabled based on settings.' );
				}
				if ( file_exists( $zip_file ) ) {
					if ( $append === true ) {
						$this->status( 'details', 'ZIP file exists. Appending based on options.' );
						$command .= ' -g';
					} else {
						$this->status( 'details', 'ZIP file exists. Deleting & writing based on options.' );
						unlink( $zip_file );
					}
				}
				
				//$command .= " -r";
				
				// Set temporary directory to store ZIP while it's being generated.
				if ( !empty( $temporary_zip_directory ) ) {
					$command .= " -b '{$temporary_zip_directory}'";
				}
				
				$command .= " '{$zip_file}' . -i '*'";
				
				if ( count( $excludes ) > 0 ) {
					$this->status( 'details', 'Calculating directories to exclude from backup.' );
					$command .= ' -x';
					
					$excluding_additional = false;
					$exclude_count = 0;
					foreach ( $excludes as $exclude ) {
						//$exclude = preg_replace( '|[/\\\\]$|', '', $exclude );
						$exclude = trim( $exclude, "\n\r\0" );
						if ( $exclude != '' ) {
							if ( !strstr( $exclude, 'backupbuddy_backups' ) ) { // Set variable to show we are excluding additional directories besides backup dir.
								$excluding_additional = true;
							}
							
							//$exclude = $exclude . '/';
							
							if ( substr( $exclude, -1, 1) != '/' ) {
								$exclude = $exclude . '/';
							}
							
							$this->status( 'details', 'Excluding: ' . $exclude );
							$command .= " '{$exclude}*'";
							
							$exclude_count++;
						}
					}
				}
				
				$command .= ' "/importbuddy.php"';
				
				if ( $excluding_additional === true ) {
					$this->status( 'message', 'Excluding archives directory and additional directories defined in settings. ' . $exclude_count . ' total.' );
				} else {
					$this->status( 'message', 'Only excluding archives directory based on settings. ' . $exclude_count . ' total.' );
				}
				unset( $exclude_count );
				
				$working_dir = getcwd();
				chdir( $add_directory ); // Change directory to the path we are adding.
				
				// Run ZIP command.
				if ( stristr( PHP_OS, 'WIN' ) && !stristr( PHP_OS, 'DARWIN' ) ) { // Running Windows. (not darwin)
					if ( file_exists( ABSPATH . 'zip.exe' ) ) {
						echo 'Attempting to use provided zip.exe for native Windows zip functionality.<br /><br />';
						$this->status( 'message', 'Attempting to use provided Windows zip.exe.' );
						$command = str_replace( '\'', '"', $command ); // Windows wants double quotes
						$command = ABSPATH . $command;
					}
					
					$this->status( 'details', 'Exec command (Windows): ' . $command );
					@exec( $command, $exec_return_a, $exec_return_b); // Suppress errors in Windows since it gives major forking warnings in Windows.
				} else { // Allow exec warnings on windows
					$this->status( 'details', 'Exec command (Linux): ' . $command );
					exec( $command, $exec_return_a, $exec_return_b);
				}
				
				// Verify zip command was created and exec reports no errors. If fails then falls back to other methods.
				if ( ( ! file_exists( $zip_file ) ) || ( $exec_return_b == '-1' ) ) { // File not made or error returned.
					if ( $exec_return_b == '-1' ) {
						$this->status( 'details', 'Exec command returned -1.' );
					}
					if ( ! file_exists( $zip_file ) ) {
						$this->status( 'details', 'Exec command ran but ZIP file did not exist.' );
					}
					$this->status( 'message', 'Full speed mode did not complete. Trying compatibility mode next.' );
					if ( file_exists( $zip_file ) ) { // If file was somehow created, its likely damaged since an error was thrown. Delete it.
						$this->status( 'details', 'Unlinking damaged ZIP file. Error #3489328998.' );
						unlink( $zip_file );
					}
				} else {
					$this->status( 'message', 'Full speed mode completed & generated ZIP file.' );
					return true;
				}
				
				chdir( $working_dir );
				
				unset( $command );
				unset( $exclude );
				unset( $excluding_additional );
				
				$this->status( 'details', 'Exec command did not succeed. Falling back.' );
			}
			
			if ( in_array( 'pclzip', $zip_methods ) ) {
				$this->status( 'message', 'Using Compatibility Mode for ZIP. This is slower and less reliable.' );
				$this->status( 'message', 'If your backup times out in compatibility mode try disabled zip compression.' );
				$this->status( 'message', 'WARNING: Directory/file exclusion unavailable in Compatibility Mode. Even existing old backups will be backed up.' );
				
				require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
				
				if ( !empty( $temporary_zip_directory ) ) {
					$pclzip = new PclZip( $temporary_zip_directory . basename( $zip_file ) );
				} else {
					$pclzip = new PclZip( $zip_file );
				}
				
				if ( $compression !== true ) {
					$this->status( 'details', 'PCLZip compression disabled based on settings.' );
					$arguments = array( $add_directory, PCLZIP_OPT_NO_COMPRESSION, PCLZIP_OPT_REMOVE_PATH, $add_directory );
				} else {
					$this->status( 'details', 'PCLZip compression enabled based on settings.' );
					$arguments = array( $add_directory, PCLZIP_OPT_REMOVE_PATH, $add_directory );
				}
				
				$mode = 'create';
				if ( file_exists( $zip_file ) && ( $append === true ) ) {
					$this->status( 'details', 'ZIP file exists. Appending based on options.' );
					$mode = 'append';
				}
				
				if ( $mode == 'append' ) {
					$this->status( 'details', 'Appending to ZIP file via PCLZip.' );
					$result = call_user_func_array( array( &$pclzip, 'add' ), $arguments );
				} else { // create
					$this->status( 'details', 'Creating ZIP file via PCLZip:' . implode( ';', $arguments ) );
					//error_log( 'pclzip args: ' . print_r( $arguments, true ) . "\n" );
					$result = call_user_func_array( array( &$pclzip, 'create' ), $arguments );
				}
				
				if ( !empty( $temporary_zip_directory ) ) {
					if ( file_exists( $temporary_zip_directory . basename( $zip_file ) ) ) {
						$this->status( 'details', 'Renaming PCLZip File...' );
						rename( $temporary_zip_directory . basename( $zip_file ), $zip_file );
						if ( file_exists( $zip_file ) ) {
							$this->status( 'details', 'Renaming PCLZip success.' );
						} else {
							$this->status( 'details', 'Renaming PCLZip failure.' );
						}
					} else {
						$this->status( 'details', 'Temporary PCLZip archive file expected but not found.' );
					}
				}
				
				// If not a result of 0 and the file exists then it looks like the backup was a success.
				if ( ( $result != 0 ) && file_exists( $zip_file ) ) {
					$this->status( 'details', 'Backup file created in compatibility mode (PCLZip).' );
					return true;
				} else {
					if ( $result == 0 ) {
						$this->status( 'details', 'PCLZip returned status 0.' );
					}
					if ( !file_exists( $zip_file ) ) {
						$this->status( 'details', 'PCLZip archive ZIP file was not found.' );
					}
				}
				
				unset( $result );
				unset( $mode );
				unset( $arguments );
				unset( $pclzip );
			}
			
			// If we made it this far then something didnt result in a success.
			return false;
		}
		
		
		// Test availability of ZipArchive and that it actually works.
		function test_ziparchive() {
			if ( class_exists( 'ZipArchive' ) ) {
				$test_file = $this->_tempdir . 'temp_test_' . uniqid() . '.zip';
				
				$zip = new ZipArchive;
				if ( $zip->open( $test_file, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE ) === true ) {
					$zip->addFile( __FILE__, 'this_is_a_test.txt');
					$zip->close();
					if ( file_exists( $test_file ) ) {
						unlink( $test_file );
						$this->_status[] = 'ZipArchive test PASSED.';
						return true;
					} else {
						$this->_status[] = 'ZipArchive test FAILED: Zip file not found.';
						return false;
					}
				} else {
					$this->_status[] = 'ZipArchive test FAILED: Unable to create/open zip file.';
					return false;
				}
			}
		}
		
		
		// Test availability of zip methods to determine which exist and actually work.
		function available_zip_methods( $return_best = true ) {
			
			$return = array();
			$test_file = $this->_tempdir . 'temp_' . uniqid() . '.zip';
			
			// Test command-line ZIP.
			if ( function_exists( 'exec' ) ) {
				//chdir( ABSPATH );
				@exec( 'zip "' . $test_file . '" "' . __FILE__ . '"' );
				if ( file_exists( $test_file ) ) {
					if ( !unlink( $test_file ) ) {
						echo 'Error #564634. Unable to delete test file (' . $test_file . ')!';
					}
					array_push( $return, 'exec' );
				}
			}
			
			// Test ZipArchive
			if ( class_exists( 'ZipArchive' ) ) {
				if ( $this->test_ziparchive() === true ) {
					array_push( $return, 'ziparchive' );
				}
			}
			// Test PCLZip
			array_push( $return, 'pclzip' );
			
			return $return;
		}
		
		
		function clear_status() {
			$this->_status = array();
		}
		
		
		function status() {
			if ( !empty( $this->_status_function ) ) {
				$args = func_get_args();
				call_user_func_array( $this->_status_function, $args );
			}
		}
		
		
		/**
		 *	set_status_callback()
		 *
		 *	Sets a reference to the function to call for each status update.
		 *
		 *	$callback	reference	Reference to function to call for status updates.
		 *							Ex: $this->_zipbuddy->set_status_callback( array( &$this, 'status' ) );
		 *	@return		null
		 *
		 */
		function set_status_callback( $callback ) {
			$this->_status_function = $callback;
		}
		
		
		function set_zip_methods( $methods ) {
			$this->_zip_methods = $methods;
		}
		
	} // End class
	
	//$pluginbuddy_zipbuddy = new pluginbuddy_zipbuddy( $this->_options['backup_directory'] );
}