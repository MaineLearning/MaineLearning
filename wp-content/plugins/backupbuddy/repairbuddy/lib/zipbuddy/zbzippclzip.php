<?php
/**
 *	pluginbuddy_zbzippclzip Class
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
if ( !class_exists( "pluginbuddy_zbzippclzip" ) ) {

	class pluginbuddy_zbzippclzip extends pluginbuddy_zbzipcore {
	
		const ZIP_CONTENT_FILE_NAME = 'last_pclzip_list.txt';

        /**
         * method tag used to refer to the method and entities associated with it such as class name
         * 
         * @var $_method_tag 	string
         */
		public static $_method_tag = 'pclzip';
			
        /**
         * This tells us whether this method is regarded as a "compatibility" method
         * 
         * @var bool
         */
		public static $_is_compatibility_method = true;
			
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
			
			// Define the initial details
			$this->_method_details[ 'attr' ] = array( 'name' => 'PclZip Method', 'compatibility' => pluginbuddy_zbzippclzip::$_is_compatibility_method );
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
		
			return pluginbuddy_zbzippclzip::$_method_tag;
			
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
		
			return pluginbuddy_zbzippclzip::$_is_compatibility_method;
			
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
			$zip = NULL;
			
			// The class has to be available for us
			@include_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
			
			if ( class_exists( 'PclZip' ) ) {
			
				$test_file = $tempdir . 'temp_test_' . uniqid() . '.zip';
				
				$zip = new PclZip( $test_file );
				
				if ( $zip->create( __FILE__ , PCLZIP_OPT_REMOVE_PATH, dirname( __FILE__)  ) != 0 ) {
						
					if ( file_exists( $test_file ) ) {
					
						if ( !unlink( $test_file ) ) {
					
							$status[] = sprintf( __('Error #564634. Unable to delete test file (%s)!', 'it-l10n-backupbuddy'), $test_file );
						
						}
					
						$status[] = __('PclZip test PASSED.', 'it-l10n-backupbuddy');
						$result = true;
						
					} else {
					
						$status[] = __('PclZip test FAILED: Zip file not found.', 'it-l10n-backupbuddy');
						$result = false;
						
					}
					
				} else {
				
					$status[] = __('PclZip test FAILED: Unable to create/open zip file.', 'it-l10n-backupbuddy');
					$status[] = __('PclZip Error: ', 'it-l10n-backupbuddy') . $zip->errorInfo( true );
					$result = false;
					
				}
				
			} else {
			
				$status[] = __('PclZip test FAILED: PclZip class does not exist.', 'it-l10n-backupbuddy');
				$result = false;
		  
		  	}
		  	
		  	if ( NULL != $zip ) { unset( $zip ); }
		  	
		  	return $result;
		  	
		}
		
		/**
		 *	create()
		 *	
		 *	A function that creates an archive file
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
		
			$exitcode = 0;
			$zip_output = array();
			$temp_zip = '';
			$excluding_additional = false;
			$exclude_count = 0;
			$exclusions = array();
		
			// The basedir must have a trailing directory separator
			$basedir = ( rtrim( trim( $dir ), DIRECTORY_SEPARATOR ) ) . DIRECTORY_SEPARATOR;

			$this->status( 'message', __('Using Compatibility Mode.', 'it-l10n-backupbuddy') );
			$this->status( 'message', __('If your backup times out in Compatibility Mode try disabling zip compression in Settings.', 'it-l10n-backupbuddy') );
			
			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
			
			if ( empty( $tempdir ) || !file_exists( $tempdir ) ) {
			
				$this->status( 'details', __('Temporary working directory must be available.', 'it-l10n-backupbuddy') );				
				return false;
				
			}
			
			// Decide whether we are offering exclusions or not
			// Note that unlike proc and zip we always use inclusion if available to offer exclusion capability for pclzip
			if ( is_object( $listmaker ) ) {
				
				// Need to get the relative exclusions so we can log what is being excluded...
				$exclusions = $listmaker->get_relative_excludes( $basedir );
				
				// Build the exclusion list - first the relative directories
				if ( count( $exclusions ) > 0 ) {
					$this->status( 'details', __('Calculating directories to exclude from backup.', 'it-l10n-backupbuddy') );
					
					foreach ( $exclusions as $exclude ) {
					
						if ( !strstr( $exclude, 'backupbuddy_backups' ) ) { // Set variable to show we are excluding additional directories besides backup dir.
	
							$excluding_additional = true;
								
						}
							
						$this->status( 'details', __('Excluding', 'it-l10n-backupbuddy') . ': ' . $exclude );
						
						$exclude_count++;
							
					}
					
				}
				
				
				if ( $excluding_additional === true ) {
				
					$this->status( 'message', __( 'Excluding archives directory and additional directories defined in settings.', 'it-l10n-backupbuddy' ) . ' ' . $exclude_count . ' ' . __( 'total', 'it-l10n-backupbuddy' ) . '.' );
					
				} else {
				
					$this->status( 'message', __( 'Only excluding archives directory based on settings.', 'it-l10n-backupbuddy' ) . ' ' . $exclude_count . ' ' . __( 'total', 'it-l10n-backupbuddy' ) . '.' );
					
				}
				
				// Now get the list from the top node
				$the_list = $listmaker->get_terminals();
				
				// Retain this for reference for now
				file_put_contents( ( dirname( $tempdir ) . DIRECTORY_SEPARATOR . self::ZIP_CONTENT_FILE_NAME ), print_r( $the_list, true ) );
			
			} else {
		
				// We don't have the inclusion list so we are not offering exclusions
				$this->status( 'message', __('WARNING: Directory/file exclusion unavailable in Compatibility Mode. Even existing old backups will be backed up.', 'it-l10n-backupbuddy') );
				$the_list = array( $dir );
			
			}
		
			// Get started with out zip object
			// Put our final zip file in the temporary directory - it will be moved later
			$temp_zip = $tempdir . basename( $zip );		
			$pclzip = new PclZip( $temp_zip );
			
			if ( $compression !== true ) {
			
				$this->status( 'details', __('Compression disabled based on settings.', 'it-l10n-backupbuddy') );
				$arguments = array( $the_list, PCLZIP_OPT_NO_COMPRESSION, PCLZIP_OPT_REMOVE_PATH, $dir );
				
			} else {

				$arguments = array( $the_list, PCLZIP_OPT_REMOVE_PATH, $dir );

			}
			
			if ( file_exists( $zip ) ) {

				$this->status( 'details', __('Existing ZIP Archive file will be replaced.', 'it-l10n-backupbuddy') );
				unlink( $zip );

			}
			
			// Now actually create the zip archive file
			$this->status( 'details', $this->get_method_tag() . __( ' commmand arguments', 'it-l10n-backupbuddy') . ': ' . implode( ';', $arguments ) );
			
			$retval = call_user_func_array( array( &$pclzip, 'create' ), $arguments );
			
			// Work out whether we have a problem or not
			if ( is_array( $retval ) ) {
			
				// It's an array so a good result
				$exitcode = 0;
			
			} else {
			
				// Not an array so a bad error code
				$exitcode = $pclzip->errorCode();
			
			}
			
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
				
				// Put the error information into an array for consistency
				$zip_output[] = $pclzip->errorInfo( true );
				
				// Now we don't move it (because either it doesn't exist or may be incomplete) but we'll show any error/wartning output
				if ( !empty( $zip_output ) ) {
				
					// Assume we don't have a lot of lines for now - could be risky assumption!
					foreach ( $zip_output as $line ) {
					
						$this->status( 'details', __( 'Zip process reported: ', 'it-l10n-backupbuddy') . $line );
					
					}
				
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
								
			}			

			// Cleanup the temporary directory that will have all detritus and maybe incomplete zip file			
			$this->status( 'details', __('Removing temporary directory.', 'it-l10n-backupbuddy') );
			
			if ( !( $this->delete_directory_recursive( $tempdir ) ) ) {
			
					$this->status( 'details', __('Temporary directory could not be deleted: ', 'it-l10n-backupbuddy') . $tempdir );
			
			}
			
			return $result;
															
		}
		
	} // end pluginbuddy_zbzippclzip class.	
	
}
?>