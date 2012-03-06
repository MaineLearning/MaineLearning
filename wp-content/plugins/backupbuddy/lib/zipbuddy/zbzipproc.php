<?php
/**
 *	pluginbuddy_zbzipproc Class
 *
 *  Extends the zip capability core class with proc specific capability
 *	
 *	Version: 1.0.0
 *	Author:
 *	Author URI:
 *
 *	@param		$parent		object		Optional parent object which can provide functions for reporting, etc.
 *	@return		null
 *
 */
if ( !class_exists( "pluginbuddy_zbzipproc" ) ) {

	class pluginbuddy_zbzipproc extends pluginbuddy_zbzipcore {
	
		const ZIP_LOG_FILE_NAME = 'temp_zip_proc_log.txt';
		const ZIP_ERROR_FILE_NAME = 'last_proc_errors.txt';
	
        /**
         * method tag used to refer to the method and entities associated with it such as class name
         * 
         * @var string
         */
		public static $_method_tag = 'proc';
			
	
        /**
         * This tells us whether this method is regarded as a "compatibility" method
         * 
         * @var bool
         */
		public static $_is_compatibility_method = false;
			
		/**
		 *	__construct()
		 *	
		 *	Default constructor.
		 *	
		 *	@param		reference	&$parent		[optional] Reference to the object containing the status() function for status updates.
		 *	@return		null
		 *
		 */
		public function __construct( &$parent = NULL ) {

			parent::__construct( $parent );
			
			// Define the initial default details
			$this->_method_details[ 'attr' ] = array( 'name' => 'Proc Method', 'compatibility' => pluginbuddy_zbzipproc::$_is_compatibility_method );
			$this->_method_details[ 'param' ] = array( 'path' => '' );
			
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
		
			parent::__destruct();

		}
		
		/**
		 *	get_method_tag()
		 *	
		 *	Returns the (static) method tag
		 *	
		 *	@return		string The method tag
		 *
		 */
		public function get_method_tag() {
		
			return pluginbuddy_zbzipproc::$_method_tag;
			
		}
		
		/**
		 *	get_is_compatibility_method()
		 *	
		 *	Returns the (static) is_compatibility_method boolean
		 *	
		 *	@return		bool
		 *
		 */
		public function get_is_compatibility_method() {
		
			return pluginbuddy_zbzipproc::$_is_compatibility_method;
			
		}
		
		/**
		 *	is_available()
		 *	
		 *	A function that tests for the availability of the specific method in the requested mode
		 *	
		 *	@parame	string	$tempdir	Temporary directory to use for any test files
		 *	@param	string	$mode		Method mode to test for
		 *	@param	array	$status		Array for any status messages
		 *	@return	bool				True if the method/mode combination is available, false otherwise
		 *
		 */
		public function is_available( $tempdir, $mode, &$status ) {
		
			$result = false;
			$exitcode = 0;
			$found_zip = false;
			$pstatus = array();
			
			if ( stristr( PHP_OS, 'WIN' ) && !stristr( PHP_OS, 'DARWIN' ) ) { // Running Windows. (not darwin)

				$status[] = __('proc_open test FAILED: Method not supported on Windows.', 'it-l10n-backupbuddy');
				$result = false;
			
			} else {
			
				if ( function_exists( 'proc_open' ) && function_exists( 'proc_close' ) &&
					 function_exists( 'proc_get_status' ) && function_exists( 'proc_terminate' ) ) {
					 
					$candidate_paths = $this->_executable_paths;
					
					// We are searching for zip using the list of possible paths
					while ( ( false == $found_zip ) && ( !empty( $candidate_paths ) ) ) {
					
						$path = array_shift( $candidate_paths );
						$status[] = __( 'Trying executable path for zip:', 'it-l10n-backupbuddy' ) . ' `' . $path . '`.';
	
						$test_file = $tempdir . 'temp_test_' . uniqid() . '.zip';
						
						$command = 'exec ' . $path . 'zip ' . ' "' . $test_file . '" "' . __FILE__ . '"';
		
						$descriptorspec = array(
							0 => array( "pipe", "r" ),
							1 => array( "file", "/dev/null", "a" ),
							2 => array( "file", "/dev/null", "a" )	
						);
						
						$process = NULL; // Maybe it doesn't work
						
						$process = @proc_open( $command, $descriptorspec, $pipes );
						
						if ( is_resource( $process ) ) {
						
							fclose( $pipes[0] ); // Never want to send input so just close it
							
							$pstatus = proc_get_status( $process );
							
							// Make sure we only do 4 loop max
							$count = 0;
							
							while ( true == $pstatus[ 'running'] && ( $count++ < 4 ) ) {
								usleep( 500000 );
								$pstatus = proc_get_status( $process );
							}
							
							if ( true === $pstatus[ 'running' ] ) {
								// Hmm, shouldn't still be running, try to kill it and move on
								@proc_terminate( $process );
								
							} else {
							
								// Process finished normally so get exit code for possible use
								$exitcode = $pstatus[ 'exitcode' ];
								
							}
							
							// Ignore any close issue, shouldn't get stuck here but it is possible if
							// we process was still running and we failed to terminate it - tricky one
							// to overcome but _should_ be rare
							@proc_close( $process );
							
							if ( file_exists( $test_file ) ) {
							
								if ( !unlink( $test_file ) ) {
								
									$status[] = sprintf( __('Error #564638. Unable to delete test file (%s)!', 'it-l10n-backupbuddy'), $test_file );
									
								}
								
								$status[] = __('Proc test PASSED.', 'it-l10n-backupbuddy');
								$result = true;
								
								// Set the parameter to be remembered
								$this->_method_details[ 'param' ][ 'path' ] = $path;
								
								// This will break us out of the loop
								$found_zip = true;
								
							} else {
							
								$status[] = __('Proc test FAILED: Test zip file not found.', 'it-l10n-backupbuddy');
								$status[] = __('Proc Exit Code: ', 'it-l10n-backupbuddy') . $exitcode;
								$result = false;
							
							}
							
						} else {
						
							$status[] = __('Proc test FAILED: Unable to create test zip file process.', 'it-l10n-backupbuddy');
							$result = false;
						
						}
					
					}
					
					if ( false == $found_zip ) {
					
						// Never found zip on any candidate path
						$status[] = __('Proc test Failed: Unable to find zip executable on any specified path.', 'it-l10n-backupbuddy');
						
					}
						  
				} else {
				
					$status[] = __('Proc test FAILED: One or more required function do not exist.', 'it-l10n-backupbuddy');
					$result = false;
			  
				}
		  	
		  	}
					  	
		  	return $result;
		  	
		}
		
		/**
		 *	create()
		 *	
		 *	A function that creates an archive file
		 *	Always cleans up after itself
		 *	
		 *	The $excludes will be a list or relative path excludes if the $listmaker object is NULL otehrwise
		 *	will be absolute path excludes and relative path excludes can be had from the $listmaker object
		 *	
		 *	@param		string	$zip			Full path & filename of ZIP Archive file to create
		 *	@param		string	$dir			Full path of directory to add to ZIP Archive file
		 *	@param		bool	$compression	True to enable compression of files added to ZIP Archive file
		 *	@parame		array	$excludes		List of either absolute path exclusions or relative exclusions
		 *	@param		string	$tempdir		Full path of directory for temporary usage
		 *	@param		object	$listmaker		The object from which we can get an inclusions list
		 *	@return		bool					True if the creation was successful, false otherwise
		 *
		 */
		public function create( $zip, $dir, $compression, $excludes, $tempdir, $listmaker = NULL ) {
		
			if ( stristr( PHP_OS, 'WIN' ) && !stristr( PHP_OS, 'DARWIN' ) ) { // Running Windows. (not darwin)

				$this->status( 'details', $this->_method_tag . __(' method not supported on Windows.', 'it-l10n-backupbuddy') );
				return false;
			
			} else {
			
				return $this->create_linux( $zip, $dir, $compression, $excludes, $tempdir, $listmaker );
				
			}
			
		}
			
		/**
		 *	create_linux()
		 *	
		 *	A function that creates an archive file on Linux
		 *	Always cleans up after itself
		 *
		 *	The $excludes will be a list or relative path excludes if the $listmaker object is NULL otehrwise
		 *	will be absolute path excludes and relative path excludes can be had from the $listmaker object
		 *	
		 *	@param		string	$zip			Full path & filename of ZIP Archive file to create
		 *	@param		string	$dir			Full path of directory to add to ZIP Archive file
		 *	@param		bool	$compression	True to enable compression of files added to ZIP Archive file
		 *	@parame		array	$excludes		List of either absolute path exclusions or relative exclusions
		 *	@param		string	$tempdir		Full path of directory for temporary usage
		 *	@param		object	$listmaker		The object from which we can get an inclusions list
		 *	@return		bool					True if the creation was successful, false otherwise
		 *
		 */
		protected function create_linux( $zip, $dir, $compression, $excludes, $tempdir, $listmaker ) {
		
			$exitcode = 0;
			$lines = array();
			$zippath = '';
			$command = '';
			$temp_zip = '';
			$excluding_additional = false;
			$exclude_count = 0;
			$exclusions = array();
			
			// The basedir must have a trailing directory separator
			$basedir = ( rtrim( trim( $dir ), DIRECTORY_SEPARATOR ) ) . DIRECTORY_SEPARATOR;
		
			if ( empty( $tempdir ) || !file_exists( $tempdir ) ) {
			
				$this->status( 'details', __('Temporary working directory must be available.', 'it-l10n-backupbuddy') );				
				return false;
				
			}
			
			// Determine if we are using an absolute path
			if ( isset( $this->_method_details[ 'param' ][ 'path' ] ) && !empty( $this->_method_details[ 'param' ][ 'path' ] ) ) {
			
				$zippath = trim( $this->_method_details[ 'param' ][ 'path' ] );
				$this->status( 'details', __( 'Using custom zip path: ', 'it-l10n-backupbuddy' ) . $zippath );
				$command = $zippath . 'zip ';
				
			} else {
			
				$command = 'zip ';
				
			}

			// Hardcoding some additional options for now
			$command .= '-q -r ';
			
			if ( $compression !== true ) {
			
				$command .= '-0 ';
				$this->status( 'details', __('Compression disabled based on settings.', 'it-l10n-backupbuddy') );
				
			}
			
			if ( file_exists( $zip ) ) {

				$this->status( 'details', __('Existing ZIP Archive file will be replaced.', 'it-l10n-backupbuddy') );
				unlink( $zip );

			}
						
			// Set temporary directory to store ZIP while it's being generated.			
			$command .= "-b '{$tempdir}' ";

			// Put our final zip file in the temporary directory - it will be moved later
			$temp_zip = $tempdir . basename( $zip );		
			$command .= "'{$temp_zip}' . ";
			
			// Now work out exclusions dependent on what we have been given
			if ( is_object( $listmaker ) && ( defined( 'USE_EXPERIMENTAL_ZIPBUDDY_INCLUSION' ) && ( true === USE_EXPERIMENTAL_ZIPBUDDY_INCLUSION ) ) ) {
			
				// We're doing an inclusion operation, but first we'll just show the exclusiosn
				
				// For zip we need relative rather than absolute exclusion paths
				$exclusions = $listmaker->get_relative_excludes( $basedir );
				
				if ( count( $exclusions ) > 0 ) {
				
					$this->status( 'details', __('Calculating directories to exclude from backup.', 'it-l10n-backupbuddy') );
					
					$excluding_additional = false;
					$exclude_count = 0;
					foreach ( $exclusions as $exclude ) {
					
						if ( !strstr( $exclude, 'backupbuddy_backups' ) ) { // Set variable to show we are excluding additional directories besides backup dir.
	
							$excluding_additional = true;
								
						}
							
						$this->status( 'details', __('Excluding', 'it-l10n-backupbuddy') . ': ' . $exclude );
													
						$exclude_count++;
							
					}
											
				}
				
				// Get the list of inclusions to process - these wil be normalized
				$inclusions = $listmaker->get_terminals();
				
				// For each directory we need to put the "wildcard" on the end
				foreach ( $inclusions as &$inclusion ) {
				
					if ( is_dir( $inclusion ) ) {
					
						$inclusion .= DIRECTORY_SEPARATOR . "*";
					}
				
					// Remove directory path prefix excluding leading slash to make relative (needed for zip)
					$inclusion = str_replace( rtrim( $basedir, DIRECTORY_SEPARATOR ), '', $inclusion );
									
				}
				
				// Now create the inclusions file in the tempdir
				
				// And update the command options
				$ifile = dirname( $tempdir ) . DIRECTORY_SEPARATOR . 'inclusions_file.txt';
				if ( file_exists( $ifile ) ) {
				
					@unlink( $ifile );
				
				}
				
				file_put_contents( $ifile, implode( PHP_EOL, $inclusions ) . PHP_EOL . PHP_EOL );
				
				$command .= "-i@" . $ifile . " ";
			
			} else {
			
				// We're doing an exclusion operation
			
				$command .= "-i '*' ";

				// Since we had no $listmaker object or not using it get the standard relative excludes to process
				$exclusions = $excludes;
				
				if ( count( $exclusions ) > 0 ) {
				
					$this->status( 'details', __('Calculating directories to exclude from backup.', 'it-l10n-backupbuddy') );
					$command .= '-x ';
					
					$excluding_additional = false;
					$exclude_count = 0;
					foreach ( $exclusions as $exclude ) {
					
						if ( !strstr( $exclude, 'backupbuddy_backups' ) ) { // Set variable to show we are excluding additional directories besides backup dir.
	
							$excluding_additional = true;
								
						}
							
						$this->status( 'details', __('Excluding', 'it-l10n-backupbuddy') . ': ' . $exclude );
						
						if ( substr( $exclude, -1, 1) == DIRECTORY_SEPARATOR ) {
						
							// It's a directory so append a wildcard
							$command .= "'{$exclude}*' ";
							
						} else {
						
							// It's a file so no wildcard
							$command .= "'{$exclude}' ";
						
						}
							
						$exclude_count++;
							
					}
										
				}
				
			}
						
			if ( $excluding_additional === true ) {
			
				$this->status( 'message', __( 'Excluding archives directory and additional directories defined in settings.', 'it-l10n-backupbuddy' ) . ' ' . $exclude_count . ' ' . __( 'total', 'it-l10n-backupbuddy' ) . '.' );
				
			} else {
			
				$this->status( 'message', __( 'Only excluding archives directory based on settings.', 'it-l10n-backupbuddy' ) . ' ' . $exclude_count . ' ' . __( 'total', 'it-l10n-backupbuddy' ) . '.' );
				
			}
						
			// Remember the current directory and change to the directory being added so that "." is valid in command
			$working_dir = getcwd();
			chdir( $dir );
			
			// Execute ZIP command - we don't care a about Windows here because this method isn't supported yet
			// Prepend "exec" so that spawned process becomes the actual zip process
			$command = 'exec ' . $command;
			$this->status( 'details', $this->get_method_tag() . __(' command (Linux)', 'it-l10n-backupbuddy') . ': ' . $command );
			
			// Set stdin to be a pipe that we'll close immediately anyway
			// Send stdiout and stderr to a file in the temporary directory that we could access
			// Actually have to put in the parent directory because currently the ajax monitor function only expects
			// a single file in the temporary directory, that being the zip file as it is created or finalized
			$descriptorspec = array(
				0 => array( "pipe", "r" ),
				1 => array( "file", dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_LOG_FILE_NAME, "w" ),
				2 => array( "file", dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_LOG_FILE_NAME, "w" )	
			);
			
			$process = proc_open( $command, $descriptorspec, $pipes );
			
			if ( is_resource( $process ) ) {
			
				fclose( $pipes[ 0 ] ); // Never want to send input so just close it
				
				$status = proc_get_status( $process );
				
				while ( true == $status[ 'running'] ) {
					$this->status( 'details', __('Zip Archive file creation in progress.', 'it-l10n-backupbuddy' ) );
					sleep(5);
					$status = proc_get_status( $process );
					
					// Could also check for persistent final zip file (i.e., present over two loops)
					// which might mean we are somehow not seeing the process as finished so we should
					// terminate it and close it - and indicate some failure
				}
				
				$exitcode = $status[ 'exitcode' ];
				
				proc_close( $process );
				
			} else {
			
				// Must clean up temporary directory
			
				$this->status( 'details', __('Failed to create Zip process: ', 'it-l10n-backupbuddy' ) );
				$this->status( 'details', __('Removing temporary directory.', 'it-l10n-backupbuddy') );
				
				if ( !( $this->delete_directory_resursive( $tempdir ) ) ) {
				
						$this->status( 'details', __('Temporary directory could not be deleted: ', 'it-l10n-backupbuddy') . $tempdir );
				
				}
				
				chdir( $working_dir );
				return false;
				
			}
			
			// Set current working directory back to where we were
			chdir( $working_dir );
			
			// Convenience for handling different scanarios
			$result = false;
			
			// See if we can figure out what happened - note that $exitcode could be non-zero for a warning or error
			if ( ( ! file_exists( $temp_zip ) ) || ( $exitcode != 0 ) ) {
			
				// If we had a non-zero exit code then should report it (file may or may not be created)
				if ( $exitcode != 0 ) {
				
					$this->status( 'details', __('Zip process exit code: ', 'it-l10n-backupbuddy' ) . $exitcode );
					
				}

				// Report whether or not the zip file was created				
				if ( ! file_exists( $temp_zip ) ) {
				
					$this->status( 'details', __( 'Zip Archive file not created - check process exit code.', 'it-l10n-backupbuddy' ) );
					
				} else {
					
					$this->status( 'details', __( 'Zip Archive file created - check process exit code.', 'it-l10n-backupbuddy' ) );

				}
				
				// Now we don't move it (because either it doesn't exist or may be incomplete) but we'll show any error/wartning output
				if ( file_exists( dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_LOG_FILE_NAME ) ) {
				
					$lines = file( dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_LOG_FILE_NAME, FILE_IGNORE_NEW_LINES );
					if ( !( false === $lines ) ) {
					
						// Output only the first max_lines lines at most - if more then indicate this and move the file so can been reviewed
						if ( count( $lines ) > self::MAX_ERROR_LINES_TO_SHOW ) {
						
							$first_lines = array_slice( $lines, 0, self::MAX_ERROR_LINES_TO_SHOW );
						
							foreach ( $first_lines as $line ) {
						
								$this->status( 'details', __( 'Zip process reported: ', 'it-l10n-backupbuddy') . $line );
						
							}
							
							$error_file = dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_ERROR_FILE_NAME;
							@copy( dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_LOG_FILE_NAME, $error_file );
							
							if ( file_exists ( $error_file ) ) {
							
								$this->status( 'details', __( 'Zip process reported ', 'it-l10n-backupbuddy') . ( count( $lines ) - self::MAX_ERROR_LINES_TO_SHOW ) . __( ' more errors - please review in: ', 'it-l10n-backupbuddy') . $error_file );
								
							}
							
						
						} else {
						
							// Small number of lines so just show them all
							foreach ( $lines as $line ) {
						
								$this->status( 'details', __( 'Zip process reported: ', 'it-l10n-backupbuddy') . $line );
						
							}
							
						}
					
					}
					
					// Should be able to delete this file but not tragic is we cannot
					@unlink( dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_LOG_FILE_NAME );
						
				}
				
				$result = false;
				
			} else {
			
				// Got file with no error or warnings at all so just move it to the local archive
				$this->status( 'details', __('Moving Zip Archive file to local archive directory.', 'it-l10n-backupbuddy') );
				
				rename( $temp_zip, $zip );
				if ( file_exists( $zip ) ) {
				
					$this->status( 'details', __('Zip Archive file moved to local archive directory.', 'it-l10n-backupbuddy') );
					$this->status( 'message', __( 'Zip Archive file successfully created with no errors or warnings.', 'it-l10n-backupbuddy' ) );
					$result = true;
					
				} else {
				
					$this->status( 'details', __('Zip Archive file could not be moved to local archive directory.', 'it-l10n-backupbuddy') );
					$result = false;
					
				}
								
				// As we had a good result we should clean up any error output file from a previous bad run
				$error_file = dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_ERROR_FILE_NAME;
				if ( file_exists( $error_file ) ) {
				
					@unlink( $error_file );
					
				}
								
			}
			
			// Must remove the temporary zip output file
			if ( file_exists( dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_LOG_FILE_NAME ) ) {
			
				unlink( dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_LOG_FILE_NAME );
				
			}
			

			// Cleanup the temporary directory that will have all detritus and maybe incomplete zip file			
			$this->status( 'details', __('Removing temporary directory.', 'it-l10n-backupbuddy') );
			
			if ( !( $this->delete_directory_recursive( $tempdir ) ) ) {
			
					$this->status( 'details', __('Temporary directory could not be deleted: ', 'it-l10n-backupbuddy') . $tempdir );
			
			}
			
			return $result;
									
		}
		
	} // end pluginbuddy_zbzipproc class.	
	
}
?>