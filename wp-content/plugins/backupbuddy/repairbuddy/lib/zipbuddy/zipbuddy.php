<?php
/**
 *	pluginbuddy_zipbuddy Class
 *
 *	Handles zipping and unzipping, using the best methods available and falling back to worse methods
 *	as needed for compatibility. Allows for forcing compatibility modes.
 *	
 *	Version: 2.0.0
 *	Author: Dustin Bolton
 *	Author URI: http://dustinbolton.com/
 *
 *	$temp_dir		string		Temporary directory absolute path for temporary file storage. Must be writable!
 *	$zip_methods	array		Optional. Array of available zip methods to use. Useful for not having to re-test every time.
 *								If omitted then a test will be performed to find the methods that work on this host.
 *	$mode			string		Future use to allow for other compression methods other than zip. Currently not in use.
 *
 */
// Try and load the experimental version - if successful then class will exist and remaining code will be ignored
if ( defined( 'USE_EXPERIMENTAL_ZIPBUDDY' ) && ( true === USE_EXPERIMENTAL_ZIPBUDDY ) ) {
	@require_once( dirname( __FILE__ ) . '/x-zipbuddy.php' );
}
if ( !class_exists( "pluginbuddy_zipbuddy" ) ) {
	class pluginbuddy_zipbuddy {
		
		function pluginbuddy_zipbuddy( $temp_dir, $zip_methods = '', $mode = 'zip' ) {
			$this->_status = array();
			$this->_tempdir = $temp_dir;
			$this->_execpath = '';
			
			if ( !empty( $zip_methods ) ) {
				$this->_zip_methods = $zip_methods;
			} else {
				$this->_zip_methods = $this->available_zip_methods( false, $mode );
			}
		}
		
		
		// Returns true if the file (with path) exists in the ZIP.
		// If leave_open is true then the zip object will be left open for faster checking for subsequent files within this zip
		function file_exists( $zip_file, $locate_file, $leave_open = false ) {
			if ( in_array( 'ziparchive', $this->_zip_methods ) ) {
				$this->_zip = new ZipArchive;
				if ( $this->_zip->open( $zip_file ) === true ) {
						if ( $this->_zip->locateName( $locate_file ) === false ) { // File not found in zip.
							$this->_zip->close();
							$this->_status[] = __('File not found (ziparchive)', 'it-l10n-backupbuddy') . ': ' . $locate_file;
							return false;
						}
						$this->_zip->close();
					return true; // Never ran into a file missing so must have found them all.
				} else {
					$this->_status[] = sprintf( __('ZipArchive failed to open file to check if file exists (looking for %1$s in %2$s).', 'it-l10n-backupbuddy'), $locate_file , $zip_file );
					
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
					$this->_status[] = __('File not found (pclzip)', 'it-l10n-backupbuddy') . ': ' . $locate_file;
					return false;
				}
			} else {
				$this->_status[] = __('Unable to check if file exists: No compatible zip method found.', 'it-l10n-backupbuddy');
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
				$this->status( 'message', __('Forced compatibility mode (PCLZip) based on settings. This is slower and less reliable.', 'it-l10n-backupbuddy') );
			} else {
				$zip_methods = $this->_zip_methods;
				$this->status( 'details', __('Using all available zip methods in preferred order.', 'it-l10n-backupbuddy') );
			}
			
			$append = false; // Possible future option to allow appending if file exists.
			
			if ( !empty( $temporary_zip_directory ) ) {
				if ( !file_exists( $temporary_zip_directory ) ) { // Create temp dir if it does not exist.
					mkdir( $temporary_zip_directory );
				}
			}
			
			$this->status( 'details', __('Creating ZIP file', 'it-l10n-backupbuddy') . ' `' . $zip_file . '`. ' . __('Adding directory', 'it-l10n-backupbuddy') . ' `' . $add_directory . '`. ' . __('Compression', 'it-l10n-backupbuddy') . ': ' . $compression . '; ' . __('Excludes', 'it-l10n-backupbuddy') . ': ' . implode( ',', $excludes ) );
			
			if ( in_array( 'exec', $zip_methods ) ) {
				$this->status( 'details', __('Using exec() method for ZIP.', 'it-l10n-backupbuddy') );
				
				$command = 'zip -q -r';
				
				if ( $compression !== true ) {
					$command .= ' -0';
					$this->status( 'details', __('Exec compression disabled based on settings.', 'it-l10n-backupbuddy') );
				}
				if ( file_exists( $zip_file ) ) {
					if ( $append === true ) {
						$this->status( 'details', __('ZIP file exists. Appending based on options.', 'it-l10n-backupbuddy') );
						$command .= ' -g';
					} else {
						$this->status( 'details', __('ZIP file exists. Deleting & writing based on options.', 'it-l10n-backupbuddy') );
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
					$this->status( 'details', __('Calculating directories to exclude from backup.', 'it-l10n-backupbuddy') );
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
							
							$this->status( 'details', __('Excluding', 'it-l10n-backupbuddy') . ': ' . $exclude );
							$command .= " '{$exclude}*'";
							
							$exclude_count++;
						}
					}
				}
				
				$command .= ' "/importbuddy.php"';
				
				if ( $excluding_additional === true ) {
					$this->status( 'message', __( 'Excluding archives directory and additional directories defined in settings.', 'it-l10n-backupbuddy' ) . ' ' . $exclude_count . ' ' . __( 'total', 'it-l10n-backupbuddy' ) . '.' );
				} else {
					$this->status( 'message', __( 'Only excluding archives directory based on settings.', 'it-l10n-backupbuddy' ) . ' ' . $exclude_count . ' ' . __( 'total', 'it-l10n-backupbuddy' ) . '.' );
				}
				unset( $exclude_count );
				
				$working_dir = getcwd();
				chdir( $add_directory ); // Change directory to the path we are adding.
				
				if ( $this->_execpath != '' ) {
					$this->status( 'details', __( 'Using custom exec() path: ', 'it-l10n-backupbuddy' ) . $this->_execpath );
				}
				
				// Run ZIP command.
				if ( stristr( PHP_OS, 'WIN' ) && !stristr( PHP_OS, 'DARWIN' ) ) { // Running Windows. (not darwin)
					if ( file_exists( ABSPATH . 'zip.exe' ) ) {
						$this->status( 'message', __('Attempting to use provided Windows zip.exe.', 'it-l10n-backupbuddy') );
						$command = str_replace( '\'', '"', $command ); // Windows wants double quotes
						$command = ABSPATH . $command;
					}
					
					$this->status( 'details', __('Exec command (Windows)', 'it-l10n-backupbuddy') . ': ' . $command );
					@exec( $this->_execpath . $command, $exec_return_a, $exec_return_b); // Suppress errors in Windows since it gives major forking warnings in Windows.
				} else { // Allow exec warnings on windows
					$this->status( 'details', __('Exec command (Linux)', 'it-l10n-backupbuddy') . ': ' . $command );
					exec( $this->_execpath . $command, $exec_return_a, $exec_return_b);
				}
				
				// Verify zip command was created and exec reports no errors. If fails then falls back to other methods.
				if ( ( ! file_exists( $zip_file ) ) || ( $exec_return_b == '-1' ) ) { // File not made or error returned.
					if ( $exec_return_b == '-1' ) {
						$this->status( 'details', __( 'Exec command returned -1.', 'it-l10n-backupbuddy' ) );
					}
					if ( ! file_exists( $zip_file ) ) {
						$this->status( 'details', __( 'Exec command ran but ZIP file did not exist.', 'it-l10n-backupbuddy' ) );
					}
					$this->status( 'message', __( 'Full speed mode did not complete. Trying compatibility mode next.', 'it-l10n-backupbuddy' ) );
					if ( file_exists( $zip_file ) ) { // If file was somehow created, its likely damaged since an error was thrown. Delete it.
						$this->status( 'details', __( 'Cleaning up damaged ZIP file. Issue #3489328998.', 'it-l10n-backupbuddy' ) );
						unlink( $zip_file );
					}
					
					// If exec completed but left behind a temporary file/directory (often happens if a third party process killed off exec) then clean it up.
					if ( file_exists( $temporary_zip_directory ) ) {
						$this->status( 'details', __( 'Cleaning up incomplete temporary ZIP file. Issue #343894.', 'it-l10n-backupbuddy' ) );
						$this->delete_directory_recursive( $temporary_zip_directory );
					}
				} else {
					$this->status( 'message', __( 'Full speed mode completed & generated ZIP file.', 'it-l10n-backupbuddy' ) );
					return true;
				}
				
				chdir( $working_dir );
				
				unset( $command );
				unset( $exclude );
				unset( $excluding_additional );
				
				$this->status( 'details', __('Exec command did not succeed. Falling back.', 'it-l10n-backupbuddy') );
			}
			
			if ( in_array( 'pclzip', $zip_methods ) ) {
				$this->status( 'message', __('Using Compatibility Mode for ZIP. This is slower and less reliable.', 'it-l10n-backupbuddy') );
				$this->status( 'message', __('If your backup times out in compatibility mode try disabled zip compression.', 'it-l10n-backupbuddy') );
				$this->status( 'message', __('WARNING: Directory/file exclusion unavailable in Compatibility Mode. Even existing old backups will be backed up.', 'it-l10n-backupbuddy') );
				
				require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
				
				if ( !empty( $temporary_zip_directory ) ) {
					$pclzip = new PclZip( $temporary_zip_directory . basename( $zip_file ) );
				} else {
					$pclzip = new PclZip( $zip_file );
				}
				
				if ( $compression !== true ) {
					$this->status( 'details', __('PCLZip compression disabled based on settings.', 'it-l10n-backupbuddy') );
					$arguments = array( $add_directory, PCLZIP_OPT_NO_COMPRESSION, PCLZIP_OPT_REMOVE_PATH, $add_directory );
				} else {
					$this->status( 'details', __('PCLZip compression enabled based on settings.', 'it-l10n-backupbuddy') );
					$arguments = array( $add_directory, PCLZIP_OPT_REMOVE_PATH, $add_directory );
				}
				
				$mode = 'create';
				if ( file_exists( $zip_file ) && ( $append === true ) ) {
					$this->status( 'details', __('ZIP file exists. Appending based on options.', 'it-l10n-backupbuddy') );
					$mode = 'append';
				}
				
				if ( $mode == 'append' ) {
					$this->status( 'details', __('Appending to ZIP file via PCLZip.', 'it-l10n-backupbuddy') );
					$result = call_user_func_array( array( &$pclzip, 'add' ), $arguments );
				} else { // create
					$this->status( 'details', __( 'Creating ZIP file via PCLZip', 'it-l10n-backupbuddy') . ':' . implode( ';', $arguments ) );
					//error_log( 'pclzip args: ' . print_r( $arguments, true ) . "\n" );
					$result = call_user_func_array( array( &$pclzip, 'create' ), $arguments );
				}
				
				if ( !empty( $temporary_zip_directory ) ) {
					if ( file_exists( $temporary_zip_directory . basename( $zip_file ) ) ) {
						$this->status( 'details', __('Renaming PCLZip File...', 'it-l10n-backupbuddy') );
						rename( $temporary_zip_directory . basename( $zip_file ), $zip_file );
						if ( file_exists( $zip_file ) ) {
							$this->status( 'details', __('Renaming PCLZip success.', 'it-l10n-backupbuddy') );
						} else {
							$this->status( 'details', __('Renaming PCLZip failure.', 'it-l10n-backupbuddy') );
						}
					} else {
						$this->status( 'details', __('Temporary PCLZip archive file expected but not found.', 'it-l10n-backupbuddy') );
					}
				}
				
				// If not a result of 0 and the file exists then it looks like the backup was a success.
				if ( ( $result != 0 ) && file_exists( $zip_file ) ) {
					$this->status( 'details', __('Backup file created in compatibility mode (PCLZip).', 'it-l10n-backupbuddy') );
					return true;
				} else {
					if ( $result == 0 ) {
						$this->status( 'details', __('PCLZip returned status 0.', 'it-l10n-backupbuddy') );
					}
					if ( !file_exists( $zip_file ) ) {
						$this->status( 'details', __('PCLZip archive ZIP file was not found.', 'it-l10n-backupbuddy') );
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
		
		
		/**
		 *	unzip()
		 *
		 *	Extracts the contents of a zip file to the specified directory using the best unzip methods possible.
		 *
		 *	$zip_file					string		Full path & filename of ZIP file to create.
		 *	$destination_directory		string		Full directory path to extract into.
		 *	$force_compatibility_mode	mixed		false (default): use best methods available (zip exec first), falling back as needed.
		 *											ziparchive: first fallback method. (Medium performance)
		 *											pclzip: second fallback method. (Worst performance; buggy)
		 *
		 *	@return``								true on success, false otherwise
		 */
		function unzip( $zip_file, $destination_directory, $force_compatibility_mode = false ) {
			if ( $force_compatibility_mode == 'ziparchive' ) {
				$zip_methods = array( 'ziparchive' );
				$this->status( 'message', __('Forced compatibility mode (ZipArchive; medium speed) based on settings. This is slower and less reliable.', 'it-l10n-backupbuddy') );
			} elseif ( $force_compatibility_mode == 'pclzip' ) {
				$zip_methods = array( 'pclzip' );
				$this->status( 'message', __('Forced compatibility mode (PCLZip; slow speed) based on settings. This is slower and less reliable.', 'it-l10n-backupbuddy') );
			} else {
				$zip_methods = $this->_zip_methods;
				$this->status( 'details', __('Using all available zip methods in preferred order.', 'it-l10n-backupbuddy') );
			}
			
			if ( in_array( 'exec', $zip_methods ) ) {
				$this->status( 'details',  'Starting highspeed extraction (exec)... This may take a moment...' );
				
				$command = 'unzip -qo'; // q = quiet, o = overwrite without prompt.
				$command .= " '$zip_file' -d '$destination_directory' -x 'importbuddy.php'"; // x excludes importbuddy script to prevent overwriting newer importbuddy on extract step.
			
				// Handle windows.
				if ( stristr( PHP_OS, 'WIN' ) && !stristr( PHP_OS, 'DARWIN' ) ) { // Running Windows. (not darwin)
					if ( file_exists( ABSPATH . 'unzip.exe' ) ) {
						$this->status( 'details',  'Attempting to use Windows unzip.exe.' );
						$command = str_replace( '\'', '"', $command ); // Windows wants double quotes
						$command = ABSPATH . $command;
					}
				}
				
				if ( $this->_execpath != '' ) {
					$this->status( 'details', __( 'Using custom exec() path: ', 'it-l10n-backupbuddy' ) . $this->_execpath );
				}
				
				$this->status( 'details', 'Running ZIP command: ' . $command );
				exec( $this->_execpath . $command, $exec_return_a, $exec_return_b );
								
				if ( ( ! file_exists( 'wp-config.php' ) ) || ( $exec_return_b != '' ) ) { // File not made or error returned.
				//if ( $exec_return_b != '' ) { // File not made or error returned.

					// ERROR LIST: http://www.mkssoftware.com/docs/man1/unzip.1.asp
					if ( $exec_return_b == '50' ) {
						$this->status( 'error',  'The disk is (or was) full during extraction <b>OR</b> the zip/unzip command does not have write permission to your directory.  Try increasing permissions for the directory.', true );
					}
					
					
					if ( ! file_exists( 'wp-config.php' ) ) {
						$this->status( 'error',  'wp-config.php file was not found after extraction using high speed mode.' );
					}
					
					
					$this->status( 'message',  'Falling back to next compatilbity mode.' );
				} else {
					$this->status( 'message', 'File extraction complete.' );
					return true;
				}
			}
			
			if ( in_array( 'ziparchive', $zip_methods ) ) {
				$this->status( 'details',  'Starting medium speed extraction (ziparchive)... This may take a moment...' );
				
				$zip = new ZipArchive;
				if ( $zip->open( $zip_file ) === true ) {
					if ( true === $zip->extractTo( $destination_directory ) ) {
						$this->status( 'details',  'ZipArchive extraction success.' );
						return true;
					} else {
						$this->status( 'message',  'Error: ZipArchive was available but failed extracting files.  Falling back to next compatibility mode.' );
					}
				} else {
					$this->status( 'message',  'Error: Unable to open zip file via ZipArchive. Falling back to next compatibility mode.' );
				}
			}
			
			if ( in_array( 'pclzip', $zip_methods ) ) {
				$this->status( 'details',  'Starting low speed extraction (pclzip)... This may take a moment...' );
				
				if ( !class_exists( 'PclZip' ) ) {
					$pclzip_file = str_replace( '/zipbuddy', '/pclzip/pclzip.php', dirname( __FILE__ ) );
					if ( file_exists( $pclzip_file ) ) {
						require_once( $pclzip_file );
					}
				}
				$archive = new PclZip( $zip_file );
				$result = $archive->extract(); // Extract to current directory. Explicity using PCLZIP_OPT_PATH results in extraction to a PCLZIP_OPT_PATH subfolder.
				
				if ( 0 == $result ) {
					$this->status( 'details',  'PCLZip Failure: ' . $archive->errorInfo( true ) );
					$this->status( 'message',  'Low speed (PCLZip) extraction failed.', $archive->errorInfo( true ) );
				} else {
					return true;
				}
			}
			
			// Nothing succeeded if we made it this far...
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
						$this->_status[] = __('ZipArchive test PASSED.', 'it-l10n-backupbuddy');
						return true;
					} else {
						$this->_status[] = __('ZipArchive test FAILED: Zip file not found.', 'it-l10n-backupbuddy');
						return false;
					}
				} else {
					$this->_status[] = __('ZipArchive test FAILED: Unable to create/open zip file.', 'it-l10n-backupbuddy');
					return false;
				}
			}
		}
		
		
		// Test availability of zip methods to determine which exist and actually work.
		// $mode	string		Valid options: zip, unzip
		//							todo: actually test unzipping in unzip mode not just zipping and assuming the other will work
		function available_zip_methods( $return_best = true, $mode = 'zip' ) {
			$return = array();
			$test_file = $this->_tempdir . 'temp_' . uniqid() . '.zip';
			
			// Test command-line ZIP.
			if ( function_exists( 'exec' ) ) {
				$command = 'zip';
				
				// Handle windows.
				if ( stristr( PHP_OS, 'WIN' ) && !stristr( PHP_OS, 'DARWIN' ) ) { // Running Windows. (not darwin)
					if ( file_exists( ABSPATH . 'zip.exe' ) ) {
						$command = ABSPATH . $command;
					}
					// If unzip mode and unzip.exe is found then assume we have that option for unzipping since we arent actually testing unzip.
					if ( ( $mode == 'unzip' ) && file_exists( ABSPATH . '/unzip.exe' ) ) {
						array_push( $return, 'exec' );
					}
				}
				
				// Possible locations to find the ZIP executable. Start with a blank string to attempt to run in current directory.
				$exec_paths = array( '', '/usr/bin/', '/usr/local/bin/' ); // Include preceeding & trailing slash.
				
				$exec_completion = false; // default state.
				while( $exec_completion === false ) { // Check all possible zip path locations starting with current dir. Usually the path is set to make this work without hunting.
					if ( empty( $exec_paths ) ) {
						$exec_completion = true;
						$this->status( 'error', __( 'Exhausted all known exec() path possibilities with no success.', 'it-l10n-backupbuddy' ) );
						break;
					}
					$path = array_shift( $exec_paths );
					$this->status( 'details', __( 'Trying exec() ZIP path:', 'it-l10n-backupbuddy' ) . ' `' . $path . '`.' );
					
					@exec( $path . $command . ' "' . $test_file . '" "' . __FILE__ . '"', $exec_return_a, $exec_return_b );
					
					if ( ( !file_exists( $test_file ) ) || ( $exec_return_b == '-1' ) ) { // File not made or error returned.
						$exec_completion = false;
						
						if ( $exec_return_b == '-1' ) {
							$this->status( 'details', __( 'Exec command returned -1.', 'it-l10n-backupbuddy' ) );
						}
						if ( !file_exists( $test_file ) ) {
							$this->status( 'details', __( 'Exec command ran but ZIP file did not exist.', 'it-l10n-backupbuddy' ) );
						}
						if ( file_exists( $test_file ) ) { // If file was somehow created, do cleanup on it.
							$this->status( 'details', __( 'Cleaning up damaged ZIP file. Issue #3489328998.', 'it-l10n-backupbuddy' ) );
							unlink( $test_file );
						}
					} else { // Success.
						$exec_completion = true;
						
						if ( !unlink( $test_file ) ) {
							echo sprintf( __( 'Error #564634. Unable to delete test file (%s)!', 'it-l10n-backupbuddy' ), $test_file );
						}
						array_push( $return, 'exec' );
						$this->_execpath = $path;
						
						break;
					}
				} // end while
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
		
		
		// Recursively delete a directory and all content within.
		function delete_directory_recursive( $directory ) {
			$directory = preg_replace( '|[/\\\\]+$|', '', $directory );
			
			$files = glob( $directory . '/*', GLOB_MARK );
			if ( is_array( $files ) && !empty( $files ) ) {
				foreach( $files as $file ) {
					if( '/' === substr( $file, -1 ) )
						$this->rmdir_recursive( $file );
					else
						unlink( $file );
				}
			}
			
			if ( is_dir( $directory ) ) rmdir( $directory );
			
			if ( is_dir( $directory ) )
				return false;
			return true;
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
?>