<?php
/**
 *	pluginbuddy_zipbuddy Class (Experimental)
 *
 *	Handles zipping and unzipping, using the best methods available and falling back to worse methods
 *	as needed for compatibility. Allows for forcing compatibility modes.
 *	
 *	Version: 1.0.0
 *	Author: 
 *	Author URI: 
 *
 *
 */
if ( !class_exists( "pluginbuddy_zipbuddy" ) ) {

	class pluginbuddy_zipbuddy {
	
		const ZIP_METHODS_TRANSIENT = 'pluginbuddy_backupbuddy_avail_zip_methods';
		const ZIP_METHODS_TRANSIENT_LIFE = 60;

    	/**
         * parent object
         * 
         * @var object
         */
        protected $_parent = NULL;

        /**
         * The plugin path for this plugin
         * 
         * @var string
         */
        public $_pluginPath = '';

        /**
         * The path of the temporary directory that can be used for creating files and stuff
         * 
         * @var string
         */
        protected $_tempdir = "";
        
        /**
         * The list of zip methods that are requested to be used
         * 
         * @var array of string
         */
        protected $_requested_zip_methods = array();

        /**
         * The mode which the object is being created for
         * 
         * @var string
         */
        protected $_mode = "";

        /**
         * Status message array used when calling other methods to get status information back
         * 
         * @var array of string
         */
        public $_status = array();

        /**
         * The list of zip methods that are to be used or are available
         * Had to make this public for now because something accesses it directly - bad karma
         * 
         * @var array of string
         */
        public $_zip_methods = array();
        
        /**
         * The details of the various zip methods that are available
         * Have to make this a separate array indexed by the method tag. Ideally would be combined
         * with the zip methods array but that would involve more general changes elsewhere so that
         * refactoring can be done later - main problem is the direct access to the zip methods
         * array that is made rather than through a function.
         * 
         * @var array of array of array
         */
        protected $_zip_methods_details = array();
        
        /**
         * The list of zip methods that are supported, i.e., there is a supporting class defined
         * 
         * @var array of string
         */
        protected $_supported_zip_methods = array();

        /**
         * Whether or not we can call a status calback
         * 
         * @var bool
         */
		protected $_have_status_callback = false;
		
        /**
         * Object->method array for status function
         * 
         * @var array
         */
		protected $_status_callback = array();
		
		/**
		 *	__construct()
		 *	
		 *	Default constructor.
		 *	
		 *	@param		string		The path of the temporary directory to use
		 *	@param		string		The list of zip methods requested to use (this should be an array really)
		 *	@param		string		The zip mode for th eobject
		 *	@param		reference	[optional] Reference to the parent
		 *	@return		null
		 *
		 */
		public function __construct( $temp_dir, $zip_methods = '', $mode = 'zip', &$parent = NULL ) {

			$this->_tempdir = $temp_dir;
			$this->_mode = $mode;
			$this->_parent = &$parent;
			
			// Major kludge to get me a plugin path so I can load other libs as normal - have to do
			// this because not passed parent object reference and cannot derive it
			$this->_pluginPath = dirname( dirname( dirname( __FILE__ ) ) );
			
			// Make sure we load the core abstract class as this will always be needed
			@require_once( $this->_pluginPath . '/lib/zipbuddy/zbzipcore.php' );
			
			// If we loaded that ok then try the method specific classes
			// Could make this more generic based on config or somesuch
			if ( class_exists( 'pluginbuddy_zbzipcore' ) ) {
			
				@include_once( $this->_pluginPath . '/lib/zipbuddy/zbzipproc.php' );
				if ( class_exists( 'pluginbuddy_zbzipproc' ) ) {
					array_push( $this->_supported_zip_methods, pluginbuddy_zbzipproc::$_method_tag );
				}
				
				@include_once( $this->_pluginPath . '/lib/zipbuddy/zbzipexec.php' );
				if ( class_exists( 'pluginbuddy_zbzipexec' ) ) {
					array_push( $this->_supported_zip_methods, pluginbuddy_zbzipexec::$_method_tag );
				}
				
				@include_once( $this->_pluginPath . '/lib/zipbuddy/zbzipziparchive.php' );
				if ( class_exists( 'pluginbuddy_zbzipziparchive' ) ) {
					array_push( $this->_supported_zip_methods, pluginbuddy_zbzipziparchive::$_method_tag );
				}
				
				@include_once( $this->_pluginPath . '/lib/zipbuddy/zbzippclzip.php' );
				if ( class_exists( 'pluginbuddy_zbzippclzip' ) ) {
					array_push( $this->_supported_zip_methods, pluginbuddy_zbzippclzip::$_method_tag );
				}
				
			}

			// Need to deal with the string - only explode if it has some content
			$zip_methods = trim( $zip_methods );
			if ( !empty( $zip_methods ) ) {
				// Translate from a string into an array
				$this->_requested_zip_methods = array_map( 'trim', explode( ",", $zip_methods ) );
			}

			// Work out the list of zip methods from the requested and available along with their details
			$this->deduce_zip_methods( $this->_zip_methods, $this->_zip_methods_details, $this->_requested_zip_methods, false, $this->_mode );
			
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
		 *	deduce_zip_methods()
		 *	
		 *	Returns the array of zip methods that are available (or just the best) filtered by requested methods.
		 *	Because the available methods don't really change often (rarely once stable) we use a transient
		 *	which has a lifetime of 60s so we don't waste time repeating the testing which involves creating
		 *	objects and processes and files which can be time consuming. In future we could make this a longer
		 *	lived transient and and provide a manual way to delete it if we need to refresh the list.
		 *	
		 *	@param		array	Array reference for the deduced zip methods
		 *	@param		array	Arry reference for the details of the deduced methods
		 *	@param		array	Flat array of requested (preferred) zip methods
		 *	@param		bool	True if only the best available method wanted
		 *	@param		string	Which zip mode being tested
		 *	@return		bool	True if methods are available, False otherwise
		 *
		 */
		protected function deduce_zip_methods( array &$methods, array &$methods_details, array $requested, $best_only, $mode ) {
			
			$available_methods = array();
			$available_methods_details = array();
			$aggregate_available_methods = array();
			
			// Get our transient to save repeated testing over a short period
			if ( false === ( $aggregate_available_methods = get_transient( self::ZIP_METHODS_TRANSIENT ) ) ) {

				// Get all available methods in $available_methods - must return them in order best -> worst
				// Also getting the method details array which is keyed by method tag
				$this->get_available_zip_methods( $this->_supported_zip_methods, $available_methods, $available_methods_details );
				
				// Now we have to combine the two arrays into an aggregate to save
				$aggregate_available_methods[ 'methods' ] = $available_methods;
				$aggregate_available_methods[ 'details' ] = $available_methods_details;
				
				// Save it				
				set_transient( self::ZIP_METHODS_TRANSIENT, $aggregate_available_methods, self::ZIP_METHODS_TRANSIENT_LIFE );
							
			} else {
			
				// We got a valid transient value so now separate the aggregate into two
				$available_methods = $aggregate_available_methods[ 'methods' ];
				$available_methods_details = $aggregate_available_methods[ 'details' ];
			
			}
			
			// Check whether these need to be filtered by requested methods
			if ( !empty( $requested ) ) {
			
				// Filter the available methods - result could be empty
				// Order will be retained regardless of order of requested methods
				$available_methods = array_intersect( $available_methods, $requested );
				
			}

			// If just the best available requested then slice it off
			if ( ( true === $best_only ) && ( !empty( $available_methods ) ) ) {
			
				$methods = array_slice( $available_methods, 0, 1 );
				$methods_details = $available_methods_details;
				
			} else {
			
				$methods = $available_methods;
				$methods_details = $available_methods_details;
			
			}
			
			if ( !empty( $methods ) ) {
			
				return true;
				
			} else {
			
				return false;
				
			}
		
		}
				
		/**
		 *	get_zip_methods()
		 *	
		 *	Returns the array of zip methods previously deduced
		 *	
		 *	@return		array	Flat array of zip methods (could be empty)
		 *
		 */
		public function get_zip_methods() {
			
			return $this->_zip_methods;
		
		}
				
		/**
		 *	set_zip_methods()
		 *	
		 *	Resets the zip methods based on new criteria and returns the array of zip methods
		 *	
		 *	@param		array	Flat array of requested (preferred) zip methods
		 *	@param		bool	True if only the best available method wanted
		 *	@return		array	Flat array of zip methods (could be empty)
		 *
		 */
		public function set_zip_methods( array $requested, $best_only = false ) {
			
			// Update the memory of what zip methods were requested - make it clean
			$this->_requested_zip_methods = array_map( 'trim', $requested );
			
			// Work out the list of zip methods from the requested and available
			$this->deduce_zip_methods( $this->_zip_methods, $this->_zip_methods_details, $this->_requested_zip_methods, $best_only, $this->_mode );
			
			// Make the zip methods known directly
			return $this->_zip_methods;
		
		}
				
		/**
		 *	file_exists()
		 *	
		 *	Tests whether a file (with path) exists in the given zip file
		 *	If leave_open is true then the zip object will be left open for faster checking for subsequent files within this zip
		 *	
		 *	@param		string	The zip file to check
		 *	@param		string	The file to test for
		 *	@param		bool	True if the zip file should be left open
		 *	@return		bool	True if the file is found in teh zip otherwise false
		 *
		 */
		public function file_exists( $zip_file, $locate_file, $leave_open = false ) {
		
			$this->clear_status();
		
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
		 *	@param	string				Full path & filename of ZIP file to create.
		 *	@param	string				Full directory to add to zip file.
		 *	@param	boolean				True to enable ZIP compression
		 *	@param	array( string )		Array of strings of paths/files to exclude from zipping
		 *	@param	string				Full directory path to directory to temporarily place ZIP
		 *	@param	boolean				True: only use PCLZip. False: try all available
		 *
		 *	@return						true on success, false otherwise
		 *
		 */
		function add_directory_to_zip( $zip_file, $add_directory, $compression, $excludes = array(), $temporary_zip_directory = '', $force_compatibility_mode = false ) {

			$zip_methods = array();
			$sanitized_excludes = array();
			$listmaker = NULL;
			
			// Set some additional system excludes here for now - these are all from the site install root
			$additional_excludes = array( DIRECTORY_SEPARATOR . 'importbuddy' . DIRECTORY_SEPARATOR,
										  DIRECTORY_SEPARATOR . 'importbuddy.php',
										  DIRECTORY_SEPARATOR . 'importbuddy.txt',
										  DIRECTORY_SEPARATOR . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'pluginbuddy_backupbuddy.txt'
										);
			
			// Decide which methods we are going to try
			if ( $force_compatibility_mode === true ) {

				$zip_methods = $this->get_compatibility_zip_methods();				
				$this->status( 'message', __('Forced Compatibility Mode based on settings.', 'it-l10n-backupbuddy') );
				
			} else {
			
				$zip_methods = $this->_zip_methods;
				$this->status( 'details', __('Using all available zip methods in preferred order.', 'it-l10n-backupbuddy') );
			}
			
			// Better make sure we have some available methods
			if ( empty( $zip_methods ) ) {
			
				// Hmm, we don't seem to have any available methods, oops, best go no further
				$this->status( 'details', __('Failed to create a Zip Archive file - no available methods.', 'it-l10n-backupbuddy') );
				
				// We should have a temporary directory, must get rid of it, can simply rmdir it as it will (should) be empty
				if ( !empty( $temporary_zip_directory ) && file_exists( $temporary_zip_directory ) ) {
					
					if ( !rmdir( $temporary_zip_directory ) ) {
					
						$this->status( 'details', __('Temporary directory could not be deleted: ', 'it-l10n-backupbuddy') . $temporary_zip_directory );
					
					}
						
				}

				return false;
				
			}
			
			$this->status( 'details', __('Creating ZIP file', 'it-l10n-backupbuddy') . ' `' . $zip_file . '`. ' . __('Adding directory', 'it-l10n-backupbuddy') . ' `' . $add_directory . '`. ' . __('Compression', 'it-l10n-backupbuddy') . ': ' . $compression . '; ' . __('Excludes', 'it-l10n-backupbuddy') . ': ' . implode( ',', $excludes ) );
			
			// We'll try and allow exclusions for pclzip if we can
			@include_once( $this->_pluginPath . '/lib/zipbuddy/zbdir.php' );
			if ( class_exists( 'pluginbuddy_zbdir' ) ) {
			
				// Generate our sanitized list of directories/files to exclude as absolute paths (normalized) for zbdir
				$sanitized_excludes = $this->sanitize_excludes( $excludes, $additional_excludes, $add_directory );
			
				// Now let's create the list of items to add to the zip - first build the tree
				$listmaker = new pluginbuddy_zbdir( $add_directory, $sanitized_excludes );
				
				// Re-generate our sanitized list of directories/files to exclude as relative paths
				// Slight kludge to deal with being able to enable/disable the inclusion processing
				// (currently configured in wp-config.php) so always need to provide the excludes as
				// relative path for now. This needs to be tidied up in future if/when the capability
				// is established as standard
				$sanitized_excludes = $this->sanitize_excludes( $excludes, $additional_excludes );				
				
			} else {
			
				// Generate our sanitized list of directories/files to exclude as relative paths
				$sanitized_excludes = $this->sanitize_excludes( $excludes, $additional_excludes );
			
			}
			
			// Iterate over the methods - once we succeed just return directly otherwise drop through
			foreach ( $zip_methods as $method_tag ) {

				$class_name = 'pluginbuddy_zbzip' . $method_tag;
	
				$zipper = new $class_name( $this );
				$zipper->set_status_callback( array( &$this, 'status' ) );
				
				// We need to tell the method what details belong to it
				$zipper->set_method_details( $this->_zip_methods_details[ $method_tag ] );
				
				$this->status( 'details', __('Trying ',  'it-l10n-backupbuddy') . $method_tag . __(' method for ZIP.', 'it-l10n-backupbuddy') );
				
				// The temporary zip directory _must_ exist
				if ( !empty( $temporary_zip_directory ) ) {
				
					if ( !file_exists( $temporary_zip_directory ) ) { // Create temp dir if it does not exist.
					
						mkdir( $temporary_zip_directory );
						
					}
					
				}
				
				// Now we are ready to try and produce the backup
				if ( $zipper->create( $zip_file, $add_directory, $compression, $sanitized_excludes, $temporary_zip_directory, $listmaker ) === true ) {
				
					// Got a valid zip file so we can just return - method will have cleaned up the temporary directory
					$this->status( 'details', __('The ',  'it-l10n-backupbuddy') . $method_tag . __(' method for ZIP was successful.', 'it-l10n-backupbuddy') );
					unset( $zipper );
					
					// We have to return here because we cannot break out of foreach
					return true;

				} else {
				
					// Method will have cleaned up the temporary directory				
					$this->status( 'details', __('The ',  'it-l10n-backupbuddy') . $method_tag . __(' method for ZIP was unsuccessful.', 'it-l10n-backupbuddy') );
															
					unset( $zipper );
					
				}
				
			}
			
			// If we get here then have failed in all attempts
			$this->status( 'details', __('Failed to create a Zip Archive file with any available method.', 'it-l10n-backupbuddy') );
			
			return false;
	
		}
		
		
		/**
		 *	sanitize_excludes()
		 *
		 *	Take an exclusion list of directories and/or files and produce a sanitized exclusion list
		 *	Directories will always have a trailing slash and files will not
		 *
		 *	@param	array		List of primary exclusions (currently only directories) - may be empty
		 *	@param	array		List of secondary exclusions - may be empty
		 *	@param	string		The base directory to be used if normalizing
		 *
		 *	@return	mixed		array on success, false otherwise
		 */
		protected function sanitize_excludes( $primary, $secondary, $base = '' ) {
		
			$sanitized = array();
			$basedir = trim( $base );
			$normalize = !empty( $basedir );
		
			// $primary is considered to be unclean
			foreach ( $primary as $exclude ) {
			
				// Get rid of standard prefix/suffix detritus
				$exclude = trim( $exclude );
				
				// Possible that we could end up with an empty entry
				if ( !empty( $exclude ) ) {
				
					// Remove what could be multiple prefix or suffix directory separators
					$exclude = trim( $exclude, DIRECTORY_SEPARATOR );
					
					// And add back a single instance in each case
					$exclude = DIRECTORY_SEPARATOR . $exclude . DIRECTORY_SEPARATOR;
										
					$sanitized[] = $exclude;
					
				}
				
			}
			
			// $secondary is considered to be clean
			if ( !empty( $secondary ) ) {
			
				$sanitized = array_merge( $sanitized, $secondary ); 
			
			}
			
			// Get unique entries and renumber numeric keys
			$sanitized = array_merge( array_unique( $sanitized ) );
			
			if ( true == $normalize ) {
			
				// Make sure the normalize base has a trailing directory separator
				$basedir = ( rtrim( $basedir, DIRECTORY_SEPARATOR ) ) . DIRECTORY_SEPARATOR;
			
				foreach ( $sanitized as &$exclusion ) {
				
					// Must remove any leading DIRECTORY_SEPARATOR because $basedir always has trailing
					$exclusion = ltrim( $exclusion, DIRECTORY_SEPARATOR );
					$exclusion = ( $basedir . $exclusion );
					
				}
								
			}
			
		
			return $sanitized;
		
		}


		/**
		 *	unzip()
		 *
		 *	Extracts the contents of a zip file to the specified directory using the best unzip methods possible.
		 *
		 *	@param	string		Full path & filename of ZIP file to create.
		 *	@param	string		Full directory path to extract into.
		 *	@param	bool		True: only use PclZip, False: try all available 
		 *
		 *	@return	bool		true on success, false otherwise
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
				
				$this->status( 'details', 'Running ZIP command: ' . $command );
				exec( $command, $exec_return_a, $exec_return_b );
								
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
		
		/**
		 *	get_available_zip_methods()
		 *	
		 *	Returns the array of zip methods that are available for the mode of this object
		 *	Libraries must have been loaded already
		 *	
		 *	@param		array	The supported zip methods
		 *	@param		array	The array which will hold the available methods
		 *	@param		array	The array that will hold the available methods attributes (method tag is key)
		 *	@return		bool	True if methods available, False otherwise
		 *
		 */
		protected function get_available_zip_methods( array $supported_zip_methods, &$available_methods, &$available_methods_details ) {
		
			// Make sure these are cleared as the caller might not have done so
			$available_methods = array();
			$available_methods_details = array();
			
			// Currently we will send any error status messages here
			$error_file = $this->_tempdir . DIRECTORY_SEPARATOR . 'methods_test_errors.txt';
			if ( file_exists( $error_file ) ) {
			
				@unlink( $error_file );
				
			}
			
			foreach ( $supported_zip_methods as $method_tag ) {

				$this->clear_status();
			
				$class_name = 'pluginbuddy_zbzip' . $method_tag;
	
				$zipper = new $class_name( $this );
				
				if ( true === $zipper->is_available( $this->_tempdir, $this->_mode, $this->_status ) ) {
				
					$available_methods[] = $method_tag;
					$available_methods_details[ $method_tag ] = $zipper->get_method_details();
					
				} else {
					
					// As we may have errors from multiple methods use append mode
					file_put_contents( $error_file, print_r( $this->_status, true ), FILE_APPEND );
									
				}
				
				unset( $zipper );
			}
						
			return ( !empty( $available_methods ) );

		}
						
		/**
		 *	get_compatibility_zip_methods()
		 *	
		 *	Returns the array of zip methods that are regarded as "compatibility" methods
		 *	Libraries must have been loaded already
		 *	
		 *	@return		array	Flat array of zip methods (could be empty)
		 *
		 */
		protected function get_compatibility_zip_methods() {
		
			$compatibility_methods = array();
			$this->clear_status();
			
			foreach ( $this->_zip_methods as $method_tag ) {

				$class_name = 'pluginbuddy_zbzip' . $method_tag;
	
				$zipper = new $class_name( $this );
				
				if ( $zipper->get_is_compatibility_method() === true ) {
				
					$compatibility_methods[] = $method_tag;
					
				}
				
				unset( $zipper );
			}
						
			return $compatibility_methods;

		}
						
		/**
		 *	get_status()
		 *	
		 *	Returns the status array
		 *	
		 *	@return		array	The status array
		 *
		 */
		public function get_status() {
		
			return $this->_status;
		
		}
		
		/**
		 *	clear_status()
		 *
		 *	Simply clear the internal status array.
		 *
		 *	@return		null
		 *
		 */
		public function clear_status() {
		
			$this->_status = array();
			
		}
		
		/**
		 *	set_status_callback()
		 *
		 *	Sets a reference to the function to call for each status update.
		 *  Argument must at least be a non-empty array with 2 elements
		 *
		 *	@param		array 	Object->method to call for status updates.
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
		 *	@param		string		(Expected) Status message type.
		 *	@param		string		(Expected) Status message.
		 *	@return		null
		 *
		 */
		public function status() {
		
			if ( $this->_have_status_callback && ( func_num_args() > 0 ) ) {

				$args = func_get_args();
				call_user_func_array( $this->_status_callback, $args );
				
			}
			
		}
	
	} // End class
	
	//$pluginbuddy_zipbuddy = new pluginbuddy_zipbuddy( $this->_options['backup_directory'] );

}