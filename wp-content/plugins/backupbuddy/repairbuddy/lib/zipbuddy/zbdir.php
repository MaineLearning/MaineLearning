<?php
/**
 *	pluginbuddy_zbdir Class
 *
 *  Provides a directory class for zipbuddy for building a directory tree for backup
 *	
 *	Version: 1.0.0
 *	Author:
 *	Author URI:
 *
 *	@param		$parent		object		Optional parent object which can provide functions for reporting, etc.
 *	@return		null
 *
 */
if ( !class_exists( "pluginbuddy_zbdir" ) ) {

	class pluginbuddy_zbdir {
	
		// status method type parameter values - would like a class for this
		const STATUS_TYPE_DETAILS = 'details';

		public $_version = '1.0';

        /**
         * parent object
         * 
         * @var parent object
         */
        protected $_parent = NULL;

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
         * The directory listing items to be ignored
         * 
         * @var items_to_ignore array of string
         */
        protected $_items_to_ignore = array( ".", "..", ".DS_Store" );

        /**
         * The items that are terminals and we can add directly for this directory (absolute paths)
         * 
         * @var terminals array of string
         */
        protected $_terminals = array();

        /**
         * The branch nodes of subordinate directories that are on an exclusion path
         * 
         * @var branches array of string => pluginbuddy_zbdir
         */
        protected $_branches = array();

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
		 *	__construct()
		 *	
		 *	Default constructor.
		 *	
		 *	@param		string		$path			The path to form a node for
		 *	@param		array		$excludes		The list of dirs/files to exclude (absolute paths with / terminator for dirs)
		 *	@param		reference	&$parent		[optional] Reference to the object containing the status() function for status updates.
		 *	@return		null
		 *
		 */
		public function __construct( $path, $excludes = array(), &$parent = NULL ) {

			$this->_path = $path;
			$this->_paths_to_exclude = $excludes;
			$this->_parent = &$parent;
			
			$content = scandir( $this->_path ); // Get the directory content, will be simple names
			
			// Process each item for ignoring, treating as a terminal or as a branch
			foreach ( $content as &$item ) {

				// Initially check the simple name
				if ( in_array( $item, $this->_items_to_ignore ) ) {

					// This is just fluff in the directory listing
					continue;
					
				} elseif ( is_dir( ( $this->_path . $item ) ) ) {

					// It's a directory, check for matching exclusion or being prefix of exclusion
					if ( in_array( ( $this->_path . $item . DIRECTORY_SEPARATOR ), $this->_paths_to_exclude ) ) {
					
						// Exact match to an exclusion, exclude this directory completely
						continue;
						
					} elseif ( $this->in_array_prefix( ( $this->_path . $item . DIRECTORY_SEPARATOR ), $this->_paths_to_exclude ) ) {

						// Need a new node, add to the node array (absolute dir path is key)
						$this->_branches[ ( $this->_path . $item ) ] = new pluginbuddy_zbdir( ( $this->_path . $item . DIRECTORY_SEPARATOR ), $this->_paths_to_exclude, $this );
						
					} else {
					
						// Neither exclusion nor exclusion prefix so well treat it as a terminal
						$this->_terminals[] = ( $this->_path . $item );
					
					}
					
				} else {

					// Assume it's a file, check for matching exclusion
					if ( in_array( ( $this->_path . $item ), $this->_paths_to_exclude ) ) {
					
						// Exact match to an exclusion, exclude this file completely
						continue;
						
					} else {
					
						// Not an exclusion so it's a terminal
						$this->_terminals[] = ( $this->_path . $item );
						
					}
					
				}	
			}
							
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
		 *	get_terminals()
		 *	
		 *	Returns the array of terminals from this dir plus subordinates
		 *	
		 *	@return		array	Flat array of terminal filenames and directory names
		 *
		 */
		public function get_terminals( ) {
		
			// Minimum is our terminals
			$all_terminals = $this->_terminals;
			
			// Now add terminals from each subordinate
			foreach ( $this->_branches as $branch ) {
			
				$all_terminals = array_merge( $all_terminals, $branch->get_terminals() );
				
			}
			
			return $all_terminals;
			
		}
		
		/**
		 *	get_relative_excludes()
		 *	
		 *	Returns the array of exclusions with optional directory prefix removed prefix removed
		 *	
		 *	@param		string	The base directory prefix to be removed
		 *	@return		array	Flat array of relative (to site root) excluded filenames and directory names
		 *
		 */
		public function get_relative_excludes( $base = '' ) {
		
			// The basedir must have a trailing directory separator
			$basedir = ( rtrim( trim( $base ), DIRECTORY_SEPARATOR ) ) . DIRECTORY_SEPARATOR;
		
			$relative_excludes = $this->_paths_to_exclude;
			
			foreach ( $relative_excludes as &$exclude ) {
			
				// Remove base prefix but leave leading slash
				$exclude = str_replace( rtrim( $basedir, DIRECTORY_SEPARATOR ), '', $exclude );
			
			}
			
			return $relative_excludes;
			
		}
		
		/**
		 *	in_array_prefix()
		 *	
		 *	Check if the given string is a prefix of any string in the given array
		 *	
		 *  @param		string	$prefix		The prefix string
		 *  @param		array	$candidates	The array of strings
		 *	@return		bool	true if the string is a prefix, false otherwise
		 *
		 */
		public function in_array_prefix( $prefix, array $candidates ) {

			foreach ( $candidates as $candidate ) {
			
				if ( !( false === strpos( $candidate, $prefix ) ) ) {

					// We found the prefix
					return true;
					
				}
				
			}
			
			// Got this far so not a prefix
			return false;
			
		}
		
	} // end pluginbuddy_zbdir class.	
	
}
?>