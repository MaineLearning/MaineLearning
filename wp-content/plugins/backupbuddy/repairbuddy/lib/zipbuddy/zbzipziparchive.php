<?php
/**
 *	pluginbuddy_zbzipziparchive Class
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
if ( !class_exists( "pluginbuddy_zbzipziparchive" ) ) {

	class pluginbuddy_zbzipziparchive extends pluginbuddy_zbzipcore {
	
        /**
         * method tag used to refer to the method and entities associated with it such as class name
         * 
         * @var $_method_tag 	string
         */
		public static $_method_tag = 'ziparchive';
			
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
			
			// Define the initial details
			$this->_method_details[ 'attr' ] = array( 'name' => 'ZipArchive Method', 'compatibility' => pluginbuddy_zbzipziparchive::$_is_compatibility_method );
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
		
			return pluginbuddy_zbzipziparchive::$_method_tag;
			
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
		
			return pluginbuddy_zbzipziparchive::$_is_compatibility_method;
			
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
			
			if ( class_exists( 'ZipArchive' ) ) {
			
				$test_file = $tempdir . 'temp_test_' . uniqid() . '.zip';
				
				$zip = new ZipArchive;
				
				$res = $zip->open( $test_file, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE );
				
				if ( $res === true ) {
				
					$zip->addFile( __FILE__, 'this_is_a_test.txt');
					$zip->close();
					
					if ( file_exists( $test_file ) ) {
					
						if ( !unlink( $test_file ) ) {
					
							$status[] = sprintf( __('Error #564634. Unable to delete test file (%s)!', 'it-l10n-backupbuddy'), $test_file );
						
						}
					
						$status[] = __('ZipArchive test PASSED.', 'it-l10n-backupbuddy');
						$result = true;
						
					} else {
					
						$status[] = __('ZipArchive test FAILED: Zip file not found.', 'it-l10n-backupbuddy');
						$result = false;
						
					}
					
				} else {
				
					$status[] = __('ZipArchive test FAILED: Unable to create/open zip file.', 'it-l10n-backupbuddy');
					$status[] = __('ZipArchive Error: ', 'it-l10n-backupbuddy') . $res;
					$result = false;
					
				}
				
			} else {
			
				$status[] = __('ZipArchive test FAILED: ZipArchive class does not exist.', 'it-l10n-backupbuddy');
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
		
			$this->status( 'details', __('The ', 'it-l10n-backupbuddy') . $this->get_method_tag() . __(' method is not currently supported for backup.', 'it-l10n-backupbuddy') );
			return false;
		
		}
		
	} // end pluginbuddy_zbzipziparchive class.	
	
}
?>