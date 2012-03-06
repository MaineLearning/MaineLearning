<?php
/**
 *	pluginbuddy_zbzipcore Class
 *
 *  Provides an abstract zip capability core class
 *	
 *	Version: 1.0.0
 *	Author:
 *	Author URI:
 *
 *	@param		$parent		object		Optional parent object which can provide functions for reporting, etc.
 *	@return		null
 *
 */
if ( !class_exists( "pluginbuddy_zbzipcore" ) ) {

	abstract class pluginbuddy_zbzipcore {
	
		// status method type parameter values - would like a class for this
		const STATUS_TYPE_DETAILS = 'details';
		const MAX_ERROR_LINES_TO_SHOW = 10;

		public $_version = '1.0';

        /**
         * parent object
         * 
         * @var parent object
         */
        protected $_parent = NULL;

        /**
         * The plugin path for this plugin
         * 
         * @var $_pluginPath string
         */
        public $_pluginPath = '';

        /**
         * The path of this directory node
         * 
         * @var path string
         */
        protected $_path = "";
        
        /**
         * The absolute paths to be excluded, must be / terminated
         * 
         * @var paths_to_exclude array of string
         */
        protected $_paths_to_exclude = array();

        /**
         * The details of the method
         * 
         * @var method_details array
         */
		protected $_method_details = array();
		
        /**
         * The set of paths for where to look for zip or other executables
         *
         * Applies to Linux only - first path is empty so that default environment PATH is used
         * first, after that possible paths (must include leading and trailing slash)
         * 
         * @var  executable_paths	array
         */
		protected $_executable_paths = array( '', '/usr/bin/', '/usr/local/bin/' );
		
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
         * Array of status information
         * 
         * @var status array
         */
		protected $_status = array();
		
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

			$this->_parent = &$parent;
			$this->_pluginPath = $this->_parent->_pluginPath;
									
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
		 *	Clears the internal status array
		 *	
		 *	@return		array	The status array
		 *
		 */
		public function clear_status() {
		
			$this->_status = array();
		
		}
		
		/**
		 *	get_method_tag()
		 *	
		 *	Returns the (static) method tag
		 *	
		 *	@return		string The method tag
		 *
		 */
		abstract public function get_method_tag();

		/**
		 *	get_is_compatibility_method()
		 *	
		 *	Returns the (static) is_compatibility_method boolean
		 *	
		 *	@return		bool
		 *
		 */
		abstract public function get_is_compatibility_method();

		/**
		 *	get_method_details()
		 *	
		 *	Returns the details array
		 *	
		 *	@return		array
		 *
		 */
		public function get_method_details() {
		
			return $this->_method_details;
			
		}

		/**
		 *	set_method_details()
		 *	
		 *	Sets the internal (settable) details
		 *	
		 *	@param		array
		 *	@return		null
		 *
		 */
		public function set_method_details( array $details, $merge = true ) {
		
			if ( true === $merge ) {
			
				$this->_method_details[ 'attr' ] = array_merge( $this->_method_details[ 'attr' ], $details[ 'attr' ] );
				$this->_method_details[ 'param' ] = array_merge( $this->_method_details[ 'param' ], $details[ 'param' ] );
			
			} else {
			
				$this->_method_details = $details;
			
			}
						
		}

		/**
		 *	get_executable_paths()
		 *	
		 *	Returns the executable_paths array
		 *	
		 *	@return		array
		 *
		 */
		public function get_executable_paths() {
		
			return $this->_executable_paths;
			
		}

		/**
		 *	set_executable_paths()
		 *	
		 *	Sets the executable_paths array so can be used to augment or override the default
		 *	
		 *	@param		array
		 *	@return		null
		 *
		 */
		public function set_executable_paths( array $paths, $merge = true ) {
		
			if ( true === $merge ) {
			
				$this->_executable_paths = array_merge( $this->_executable_paths, $paths );
			
			} else {
			
				$this->_executable_paths = $paths;
			
			}
						
		}

		/**
		 *	delete_directory_recursive()
		 *	
		 *	Recursively delete a directory and it's content
		 *	
		 *	@param		string	$directory	Directory to delete
		 *	@return		bool				True if operation fully successful, otherwise false
		 *
		 */
		public function delete_directory_recursive( $directory ) {
		
			$directory = preg_replace( '|[/\\\\]+$|', '', $directory );

			$files = glob( $directory . DIRECTORY_SEPARATOR . '*', GLOB_MARK );
			if ( is_array( $files ) && !empty( $files ) ) {
			
				foreach( $files as $file ) {
				
					if( DIRECTORY_SEPARATOR === substr( $file, -1 ) ) {
					
						$this->delete_directory_recursive( $file );
						
					} else {
					
						unlink( $file );
						
					}
					
				}
				
			}
			
			// It really should be a directory but check in case
			if ( is_dir( $directory ) ) {
			
				rmdir( $directory );
				
			}
			
			// Check if we failed to delete it - possibly not all content was able to be deleted
			if ( is_dir( $directory ) ) {
			
				return false;
				
			} else {
				
				return true;
				
			}
			
		}
		
		/**
		 *	is_available()
		 *	
		 *	A function that tests for the availability of the specific method in the requested mode
		 *	
		 *	@param		string	$tempdir	Temporary directory to use for any test files (must be writeable)
		 *	@param		string	$mode		Method mode to test for
		 *	@param		array	$status		Array for any status messages
		 *	@return		bool				True if the method/mode combination is available, false otherwise
		 *
		 */
		abstract public function is_available( $tempdir, $mode, &$status );
		
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
		abstract public function create( $zip, $dir, $compression, $excludes, $tempdir, $listmaker = NULL );
		
	} // end pluginbuddy_zbzipcore class.	
	
}
?>