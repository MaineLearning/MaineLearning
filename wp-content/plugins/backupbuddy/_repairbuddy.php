<?php
/**
 *
 * -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
 *
 * WARNING:	THERE ARE ABSOLUTELY NO EDITABLE PORTIONS OF THIS SCRIPT.
 * 			ALL OPTIONS ARE CONFIGURABLE VIA THE WEB INTERFACE.
 *			YOU CAN EXTEND THE FUNCTIONALITY BY WRITING A MODULE
 *			SEE /repairbuddy/modules/ FOR EXAMPLES ON FUNCTIONAL MODULES
 *
 * -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
 *
 * Script Name: RepairBuddy.php for use with BackupBuddy backups.
 * Plugin URI: http://pluginbuddy.com/backupbuddy/
 * Description: Backup - Restore - Migrate. Backs up files, settings, and content for a complete snapshot of your site. Allows migration to a new host or URL.
 * Version: 0.0.1 - See repairbuddy/history.txt
 * Author: Dustin Bolton and Ronald Huereca
 * Author URI: http://pluginbuddy.com/
 *
 * Usage:
 * 
 * 1. Upload this script to the server you would like to repair/troubleshoot.
 * 2. Upload your backup ZIP file created with BackupBuddy.
 * 3. Navigate to the web address of this script. Ex: http://yoursite.com/repairbuddy.php
 * 4. Follow the on screen instructions.
 * 
 */

//EXTRACT REPAIRBUDDY
// Unpack files into directory.
/**
 *	mkdir_recursive()
 *
 *	Recursively creates the directories needed to generate a full directory path.
 *
 *	$path		string		Full absolute path to generate.
 *	@return		null
 *
 */
function mkdir_recursive( $path ) {
	if ( empty( $path ) ) { // prevent infinite loop on bad path
		return;
	}
	is_dir( dirname( $path ) ) || mkdir_recursive( dirname( $path ) );
	return is_dir( $path ) || mkdir( $path );
}

/**
*	unpack_packbuddy()
*
*	Unpacks required files encoded in importbuddy.php into stand-alone files.
*
*	@return		null
*/
function unpack_repairbuddy( $pb_abspath = '' ) {
	if ( !is_writable( $pb_abspath ) ) {
		echo 'Error #224834. This directory is not write enabled. Please verify write permissions to continue.';
		die();
	} else {
		$unpack_file = '';
		
		$handle = @fopen( $pb_abspath . 'repairbuddy.php', 'r' );
		if ( $handle ) {
			while ( ( $buffer = fgets( $handle ) ) !== false ) {
				if ( substr( $buffer, 0, 11 ) == '###PACKDATA' ) {
					$packdata_commands = explode( ',', trim( $buffer ) );
					array_shift( $packdata_commands );
					
					if ( $packdata_commands[0] == 'BEGIN' ) {
						// Start packed data.
					} elseif ( $packdata_commands[0] == 'FILE_START' ) {
						$unpack_file = $packdata_commands[2];
					} elseif ( $packdata_commands[0] == 'FILE_END' ) {
						$unpack_file = '';
					} elseif ( $packdata_commands[0] == 'END' ) {
						return;
					}
				} else {
					if ( $unpack_file != '' ) {
						if ( !is_dir( dirname( $pb_abspath . $unpack_file ) ) ) {
							mkdir_recursive( dirname( $pb_abspath . $unpack_file ) );
						}
						file_put_contents( $pb_abspath . $unpack_file, trim( base64_decode( $buffer ) ) );
					}
				}
			}
			if ( !feof( $handle ) ) {
				echo "Error: unexpected fgets() fail\n";
			}
			fclose( $handle );
		}
	}
} //end unpack_repairbuddy

$pb_abspath = dirname( __FILE__ ) . '/';
if ( !file_exists( $pb_abspath . 'repairbuddy' ) ) {
	unpack_repairbuddy( $pb_abspath );
	if ( !file_exists( $pb_abspath . 'repairbuddy' ) ) {
		die( 'Unable to unpack RepairBuddy. Error #24349489.' );
	}
}

require_once( 'repairbuddy/_load.php' );

class pluginbuddy_repairbuddy {
	var $_version = '1.0.0';
	var $_bbversion = '2.2.25';
	var $_selfdestruct = '#SELFDESTRUCT#';
	
	var $debug = false;
	var $_timestamp = 'M j, Y, H:i:s';						// PHP timestamp format.
	var $_bootstrap_wordpress = false;
	
	var $_defaults = array(
		'repair_password'			=>		'#PASSWORD#',	// MD5 hash of the import password. Prevents unauthorized access. Default: #PASSWORD#
		'password'					=>		'',				// MD5 hash of password given.
	);
	var $_modules = array();
	var $_options = array();
	var $_database_connected = false;
	var $_wpconfig_loaded = false;
	
	/**
	 *	pluginbuddy_importbuddy()
	 *
	 *	Default constructor.
	 *
	 */
	function __construct() {
		// SELF DESTRUCT IF APPLICABLE!
		if ( $this->_selfdestruct != ' #SELFDESTRUCT#' ) {
			if ( $this->_selfdestruct > time() ) {
				echo '<html><body><h3>RepairBuddy is self-destructing . . .</h3>This copy of RepairBuddy has been set to self destruct & expire and the expiration time has passed.</body></html>';
				$this->wipe_repairbuddy();
			}
		}
		
		
		// Prevent access to importbuddy.php if it is still in plugin directory.
		if ( file_exists( dirname( __FILE__ ) . '/backupbuddy.php' ) ) {
			echo 'This file can ONLY be accessed on the destination server that you wish to use the script on.<br>';
			echo 'Upload the importer in the root web directory on the destination server and try again.<br><br>';
			echo 'If you need assistance visit <a href="http://pluginbuddy.com">http://pluginbuddy.com</a>';
			die();
		}
		
		
		// Start logging time for steps that report how long they took.
		$this->time_start = microtime( true );
		
		
		
		// Set up PHP error levels.
		if ( ( $this->debug === true ) || ( isset( $this->_options[ 'show_php_warnings' ] ) &&$this->_options['show_php_warnings'] === true ) ) {
			error_reporting( E_ERROR | E_WARNING | E_PARSE | E_NOTICE ); // HIGH
			$this->log( 'PHP error reporting set HIGH.' );
		} else {
			error_reporting( E_ALL ^ E_NOTICE ); // LOW
		}
		
		// Detect max execution time for database steps so they can pause when needed for additional PHP processes.
		$this->detected_max_execution_time = str_ireplace( 's', '', ini_get( 'max_execution_time' ) );
		if ( is_numeric( $this->detected_max_execution_time ) === false ) {
			$detected_max_execution_time = 30;
		}
		
		// Handle authentication (if needed).
		$this->has_access = false; // default
		if ( $this->_defaults['repair_password'] == '#PASSWORD#' ) {
			//$this->has_access = true;
			die( 'Error #454545. A password is required for this script to function.' );
		} else {
			if ( md5( $this->_options['password'] ) == $this->_defaults['repair_password'] ) {
				$this->has_access = true;
			} 
			if ( isset( $_POST['password'] ) || isset( $_GET['v'] ) ) {
				if	( md5( $_POST['password'] ) == $this->_defaults['repair_password'] ) {
					$this->_options['password'] = $_POST['password'];
					$this->has_access = true;
				}
				if ( isset( $_GET['v'] ) &&	( $_GET['v'] == ( 'xv' . md5( $this->_defaults['repair_password'] . 'repairbuddy' . $_GET['page'] ) ) ) ) {
					$this->has_access = true;
				}
			} elseif ( isset( $_POST[ 'hash' ] ) && isset( $_POST[ 'page' ] ) ) {
				if ( $_POST[ 'hash' ] == ( 'xv' . md5( $this->_defaults[ 'repair_password' ] . 'repairbuddy' . $_POST[ 'page' ] ) ) ) {
					$this->has_access = true;
				}
			}
			
		}
		
		//Initialize The database
		if ( $this->_wpconfig_loaded = ( defined( 'PB_WP_CONFIG' ) ) ) {
			$this->_database_connected = defined( 'PB_DB_LOADED' );	
		}

		
		pb_add_action( 'repairbuddy_init', array( &$this, 'init' ) );
		
		
		
		
	} //end constructor
	
	function has_db_access() {
		return $this->_database_connected;
	} //end has_db_access
	
	function has_access() {
		return $this->has_access;
	} //end has_db_access
	
	function ajax_url( ) {
		$path = 'repairbuddy/ajax.php';
		$plugin == __FILE__;
		$plugin_dir = rtrim( dirname( $plugin ), '/' );
		//die( $plugin_dir );
		$plugin_path = rtrim( str_replace( ABSPATH, '', $plugin_dir ), '/' );
		$root_path = 'http://' . $_SERVER[ 'HTTP_HOST' ] . str_replace( $plugin_path, '', $_SERVER[ 'REQUEST_URI' ] );
		
		$filename = basename( $_SERVER[ 'REQUEST_URI' ] );
		$full_url = "http://" . $_SERVER['HTTP_HOST']  .$_SERVER['REQUEST_URI'];
		$full_url = rtrim( str_replace( $filename, '', $full_url ), '/' ) . '/' . $plugin_path;
		
		if ( !empty( $path ) && is_string( $path) ) {
			$full_url .= ltrim( $path, '/' );
		}
			
		?>
		<script type='text/javascript'>
		var pb_ajaxurl = '<?php echo $full_url; ?>';
		</script>
		<?php
	} //end ajax_url
	
	function init() {
		pb_do_action( 'init' );
		// LOAD PAGE TEMPLATE.
		if ( defined( "PB_DOING_AJAX" ) ) return;
		require_once( 'repairbuddy/_template.php' );
	}
	
	//Returns a Module title, false on failure
	function get_module_title( $slug = '' ) {
		$page_slug = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : false;
		if ( !empty( $slug ) ) $page_slug = $slug;
		
		foreach ( $this->_modules as $priority => $modules ) {
			foreach ( $modules as $module ) {
				if ( $module[ 'slug' ] == $page_slug ) {
					return $module[ 'title' ];
				}
			}
		}
		return false;
	} //end get_module_name
	
	
	/**
	 *	register_module()
	 *
	 *	Registers a new module. Typically this is called from the module's init.php within the module directory.
	 *	Modules must be registered here to show on the main page.
	 *	
	 *	@param			$module_slug			string		Module slug. Alphanumeric + underscores + dashes permitted.
	 *	@param			$module_title			string		Informative short title. 1-3 words.
	 *	@param			$module_description		string		Short descriptive sentence explaining the module.
	 *	@param			$module_default_page	string		Base name of the default page to load when clicking the module. pagename.php in the pages directory within the module.
	 *	@param			$bootstrap_wordpress	boolean		True: Load WordPress backend via wp-load.php.
	 *	@param			$is_minimode			string		True: Display the module at the bottom of the page as a small button. False: Large button up top.
	 *	@param			$is_subtle				string		True: Display the button as less prominent than other buttons. Currently only in use for minimode.
	 *	@return			null
	 */
	function register_module( $args = array() ) {
		$defaults = array(
			'slug' => '',
			'title' => '',
			'description' => '',
			'page' => '',
			'bootstrap_wordpress' => false,
			'mini_mode' => '',
			'is_subtle' => '',
			'priority' => 10,
		);
		$defaults = array_merge( $defaults, $args );
		$this->_modules[ intval( $defaults[ 'priority' ] ) ][] = $defaults;
	} //end register_module
	
	
	/**
	 *	alert()
	 *
	 *	Displays a message to the user.
	 *
	 *	$message		string		Message you want to display to the user.
	 *	$error			boolean		OPTIONAL! true indicates this alert is an error and displays as red. Default: false
	 *	$error_code		int			OPTIONAL! Error code number to use in linking in the wiki for easy reference.
	 */
	function alert( $message_title, $message_details = '', $error_code = '' ) {
		?>
		<div class="alert">
			<img src="repairbuddy/images/alert.png" style="float: left;" height="55">
			<div style="margin-left: 65px;">
				<b><?php echo $message_title; ?></b><br><br>
				<?php echo $message_details; ?>
				<?php
				if ( ( $error_code != '' ) && ( $error_code != '9021' ) ) {
					echo '<p><a href="http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#' . $error_code . '" target="_new"><i>BackupBuddy Error Code ' . $error_code . ' - Click for more details.</i></a></p>';
					$this->log( 'Error #' . $error_code . ': ' . $message_title, 'error' );
				}
				?>
			</div>
		</div>
		<?php
	}
	
	function output_status( $message, $is_error = false ) {
		$class = $is_error ? 'error' : 'updated';
		?>
		<div class='<?php echo $class; ?>'><p><strong><?php echo $message; ?></strong></p></div>
		<?php
	} //end status
	
	
	/**
	 *	log()
	 *
	 *	Logs to a text file depending on settings.
	 *	0 = none, 1 = errors only, 2 = errors + warnings, 3 = debugging (all kinds of actions)
	 *
	 *	$text		string		Text to log.
	 *	$log_type	string		Valid options: error, warning, all (default so may be omitted).
	 *
	 */
	function log( $text, $log_type = 'all' ) {
		$write = false;
		
		if ( $this->_options['log_level'] == 0 ) { // No logging.
			return;
		} elseif ( $this->_options['log_level'] == 1 ) { // Errors only.
			if ( $log_type == 'error' ) {
				$write = true;
			}
		} elseif ( $this->_options['log_level'] == 2 ) { // Errors and warnings only.
			if ( ( $log_type == 'error' ) || ( $log_type == 'warning' ) ) {
				$write = true;
			}
		} elseif ( $this->_options['log_level'] == 3 ) { // Log all; Errors, warnings, actions, notes, etc.
			$write = true;
		}
		if ( is_writable( ABSPATH ) ) {
			$fh = fopen( ABSPATH . 'importbuddy.txt', 'a');
			if ( $fh !== false ) {
				fwrite( $fh, '[' . date( $this->_timestamp, time() ) . '-' . $log_type . '] ' . $text . "\n" );
				fclose( $fh );
			} else {
				// Don't use alert here since it could recursively look writing to the log.
				echo 'Warning: Unable to write to log file. Verify write permissions to this directory.' ;
			}
		}
	}
	
	
	/**
	 *	tip()
	 *
	 *	Displays a message to the user when they hover over the question mark. Gracefully falls back to normal tooltip.
	 *	HTML is supposed within tooltips.
	 *
	 *	$message		string		Actual message to show to user.
	 *	$title			string		Title of message to show to user. This is displayed at top of tip in bigger letters. Default is blank. (optional)
	 *	$echo_tip		boolean		Whether to echo the tip (default; true), or return the tip (false). (optional)
	 */
	function tip( $message, $title = '', $echo_tip = true ) {
		$tip = ' <a class="pluginbuddy_tip" title="' . $title . ' - ' . $message . '"><img src="repairbuddy/images/pluginbuddy_tip.png"></a>';
		if ( $echo_tip === true ) {
			echo $tip;
		} else {
			return $tip;
		}
	}
	
	
	function page_link( $module_slug, $page, $force_wordpress_bootstrap = 'false' ) {
		if ( $this->has_access != true ) {
			return '?';
		}
		
		if ( $this->_modules[$module_slug]['bootsrap_wordpress'] == 'true' ) {
			$bootstrap = 'true';
		} else {
			if ( $force_wordpress_bootstrap == true ) {
				$bootstrap = 'true';
			} else {
				$bootstrap = 'false';
			}
		}
		return '?module=' . $module_slug . '&page=' . $page . '&v=xv' . md5( $this->_defaults['repair_password'] . 'repairbuddy' . $page ) . '&bootstrap=' . $bootstrap;
	}
	
	
	
	
	
	
	/**
	 *	status_box()
	 *
	 *	Displays a textarea for placing status text into.
	 *
	 *	@param			$default_text	string		First line of text to display.
	 *	@return							string		HTML for textarea.
	 */
	function status_box( $default_text = '' ) {
		return '<textarea style="width: 100%; height: 120px;" id="importbuddy_status">' . $default_text . '</textarea>';
	}		
	
	
	/**
	 *	status()
	 *
	 *	Write a status line into an existing textarea created with the status_box() function.
	 *
	 *	@param			$type		string		message, details, error, or warning. Currently not in use.
	 *	@param			$message	string		Message to append to the status box.
	 *	@return			null
	 */
	function status( $type, $message ) {
		$message = htmlentities( addslashes( $message ) );
		$status = date( $this->_timestamp, time() ) . ': ' . $message;
		
		echo '<script type="text/javascript">jQuery( "#importbuddy_status" ).append( "\n' . $status . '");	textareaelem = document.getElementById( "importbuddy_status" );	textareaelem.scrollTop = textareaelem.scrollHeight;	</script>';
		flush();
		
		if ( $type == 'error' ) {
			$this->log( $message, 'error' );
		} elseif ( $type == 'warning' ) {
			$this->log( $message, 'warning' );
		} else {
			$this->log( '[' . $type . ']' . $message, 'all' );
		}
	}
	
	
	function format_size( $size ) {
		$sizes = array( ' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
		if ( $size == 0 ) {
			return( 'empty' );
		} else {
			return ( round( $size / pow( 1024, ( $i = floor( log( $size, 1024 ) ) ) ), $i > 1 ? 2 : 0) . $sizes[$i] );
		}
	}
	
	
	function wipe_repairbuddy() {		
		$this->remove_file( ABSPATH . 'repairbuddy/', 'RepairBuddy Directory', true );
		$this->remove_file( ABSPATH . 'repairbuddy.txt', 'RepairBuddy Log File', true );
		$this->remove_file( ABSPATH . 'repairbuddy.php', 'RepairBuddy.php main file.', true );
	}
	
	
	function remove_file( $file, $description, $error_on_missing = false ) {
		$this->status( 'message', 'Deleting `' . $description . '`...' );
		
		@chmod( $file, 0755 ); // High permissions to delete.
		
		if ( is_dir( $file ) ) { // directory.
			$this->remove_dir( $file );
			if ( file_exists( $file ) ) {
				$this->status( 'error', 'Unable to delete directory: `' . $description . '`. You should manually delete it.' );
			} else {
				$this->status( 'message', 'Deleted.' );
			}
		} else { // file
			if ( file_exists( $file ) ) {
				if ( @unlink( $file ) != 1 ) {
					$this->status( 'error', 'Unable to delete file: `' . $description . '`. You should manually delete it.' );
				} else {
					$this->status( 'message', 'Deleted.' );
				}
			}
		}
	}
	
	
	/**
	 *	remove_dir()
	 *
	 *	?
	 *
	 *	@return			?
	 */
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
			if ( !$this->remove_dir( $dir . "/" . $item ) ) {
				chmod( $dir . "/" . $item, 0777 );
				if ( !$this->remove_dir( $dir . "/" . $item ) ) {
					return false;
				}
			}
		}
		return rmdir($dir);
	}
	
	
} // end class.





define( 'pluginbuddy_importbuddy', true ); // Tell Server Info page to not load some sections.
global $pluginbuddy_repairbuddy;
$pluginbuddy_repairbuddy = new pluginbuddy_repairbuddy();
pb_do_action( 'repairbuddy_init' );

function pb_register_module( $args = array() ) {
	global $pluginbuddy_repairbuddy;
	$pluginbuddy_repairbuddy->register_module( $args );
} //end pb_register_module

function pb_has_db_access() {
	global $pluginbuddy_repairbuddy;
	return $pluginbuddy_repairbuddy->has_db_access();
} //end pb_has_database_access

function pb_has_access() {
	global $pluginbuddy_repairbuddy;
	return $pluginbuddy_repairbuddy->has_access();
}

?>