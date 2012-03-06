<?php
/**
 *
 * -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
 *
 * WARNING:	THERE ARE NO EDITABLE PORTIONS OF THIS SCRIPT.
 * 			ALL OPTIONS ARE CONFIGURABLE VIA THE WEB INTERFACE.
 *
 * -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
 *
 * Script Name: ImportBuddy.php for use with BackupBuddy backups.
 * Plugin URI: http://pluginbuddy.com/backupbuddy/
 * Description: Backup - Restore - Migrate. Backs up files, settings, and content for a complete snapshot of your site. Allows migration to a new host or URL.
 * Version: 2.2.3 - See importbuddy/history.txt
 * Author: Dustin Bolton (PluginBuddy.com)
 * Author URI: http://dustinbolton.com/
 *
 * Usage:
 * 
 * 1. Upload this script to the server you would like to restore/migrate to.
 * 2. Upload your backup ZIP file created with BackupBuddy.
 * 3. Navigate to the web address of this script. Ex: http://yoursite.com/importbuddy.php
 * 4. Follow the on screen instructions.
 * 
 */
 
$php_minimum = '5.1'; // User's PHP must be equal or newer to this version.

if ( version_compare( PHP_VERSION, $php_minimum ) < 0 ) {
	die( 'ERROR #9013. See <a href="http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#9013">this codex page for details</a>. Sorry! PHP version ' . $php_minimum . ' or newer is required for BackupBuddy to properly run. You are running PHP version ' . PHP_VERSION . '.' );
}

class pluginbuddy_importbuddy {
	var $_version = '2.2.3';
	var $_bbversion = '#VERSION#';
	
	var $_php_minimum = '5.2';
	var $debug = false;										// Displays PHP warnings.
	
	var $_timestamp = 'M j, Y, H:i:s';						// PHP timestamp format.
	var $_total_steps = '6';
	var $_defaults = array(
		'import_password'			=>		'#PASSWORD#',	// MD5 hash of the import password. Prevents unauthorized access. Default: #PASSWORD#
		'password'					=>		'',				// MD5 hash of password given.
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
		'ignore_sql_errors'			=>		false,
		
		'log_level'					=>		2,				// 0 = none, 1 = errors only, 2 = errors + warnings, 3 = debugging (all kinds of actions)
		'db_server'					=>		'',
		'db_user'					=>		'',
		'db_password'				=>		'',
		'db_name'					=>		'',
		'db_prefix'					=>		'',
		'siteurl'					=>		'',
	);
	
	var $has_access = false;
	var $_backupdata;
	
	const UPLOAD_ACCESS_DENIED = 'To prevent unauthorized file uploads an importbuddy password must be configured to use this feature.';
	
	
	/**
	 *	pluginbuddy_importbuddy()
	 *
	 *	Default constructor.
	 *
	 */
	function __construct() {
		// Prevent access to importbuddy.php if it is still in plugin directory.
		if ( file_exists( dirname( __FILE__ ) . '/backupbuddy.php' ) ) {
			echo 'The BackupBuddy importer, ImportBuddy, can ONLY be accessed on the destination server that you wish to import your backup to.<br>';
			echo 'Upload the importer in the root web directory on the destination server and try again.<br><br>';
			echo 'If you need assistance visit <a href="http://pluginbuddy.com">http://pluginbuddy.com</a>';
			die();
		}
		
		define( 'ABSPATH', dirname( __FILE__ ) . '/' );
		date_default_timezone_set( @date_default_timezone_get() ); // Prevents date() from throwing a warning if the default timezone has not been set.
		
		// Unpack importbuddy files into importbuddy directory.
		if ( !file_exists( ABSPATH . 'importbuddy' ) ) {
			unpack_importbuddy();
		}
		
		// Return image if requested.
		if ( isset( $_GET['ezimg'] ) ) {
			require_once( 'importbuddy/classes/ezimg.php' );
			ezimg::showImg( $_GET['ezimg'] );
		}
		
		// Start logging time for steps that report how long they took.
		$this->time_start = microtime( true );
		
		// Try to prevent browser timeouts. Greedy script limits are handled on the steps that need them.
		header( 'Keep-Alive: 3600' );
		header( 'Connection: keep-alive' );
		
		// Set up options.
		if ( isset( $_POST['options'] ) ) {
			$this->_options = unserialize( stripslashes( htmlspecialchars_decode( $_POST['options'] ) ) );
			$this->_options = array_merge( $this->_defaults, (array)$this->_options ); // Add in any defaults not explicitly set yet.
		} else {
			$this->_options = $this->_defaults;
		}
		
		if ( $this->_options['log_serial'] == '' ) {
			$this->_options['log_serial'] = $this->rand_string( 10 );
		}
		
		// Database step's AJAX-based tester.
		if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'mysql_test' ) ) {
			require_once( 'importbuddy/classes/mysql_test.php' );
		}
		
		// Set up PHP error levels.
		if ( ( $this->debug === true ) || ( $this->_options['show_php_warnings'] === true ) ) {
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
		
		// Determine the current step.
		if ( ( isset( $_GET['step'] ) ) && ( is_numeric( $_GET['step'] ) ) ) {
			$this->_step = $_GET['step'];
		} else {
			$this->_step = 1;
		}
		
		// Handle importbuddy authentication (if needed).
		$this->has_access = false; // default
		if ( $this->_defaults['import_password'] == '#PASSWORD#' ) {
			//$this->has_access = true;
			die( 'ERROR: A password is required to be set to use this script for security purposes.  This prevents unauthorized usage of the script.' );
		} else {
			if ( md5( $this->_options['password'] ) == $this->_defaults['import_password'] ) {
				$this->has_access = true;
			}
			if ( isset( $_POST['password'] ) || isset( $_GET['v'] ) ) {
				if	( md5( $_POST['password'] ) == $this->_defaults['import_password'] ) {
					$this->_options['password'] = $_POST['password'];
					$this->has_access = true;
				}
				if ( isset( $_GET['v'] ) &&	( $_GET['v'] == ( 'xv' . md5( $this->_defaults['import_password'] . 'importbuddy' ) ) ) ) {
					$this->has_access = true;
				}
			}
		}
		
		// Run function for the requested step.
		require_once( 'importbuddy/classes/ezimg.php' );
		
		// Handles displaying the current page and running the needed code for that step.
		$mode = 'html';
		if ( $mode == 'html' ) {
			require_once( 'importbuddy/classes/view_page.php' );
		} elseif ( $mode == 'api_1' ) {
			die( 'API not implemented yet.' );
			
			if ( $this->has_access === true ) {
				require_once( 'step_' . $this->_step . '_api.php' );
			} else {
				$this->status( 'error', 'Access Denied. You must authenticate first.' );
				die( "Access Denied.\n" );
			}
		}
	}
	
	function rand_string($length = 32, $chars = 'abcdefghijkmnopqrstuvwxyz1234567890') {
		$chars_length = (strlen($chars) - 1);
		$string = $chars{rand(0, $chars_length)};
		for ($i = 1; $i < $length; $i = strlen($string)) {
			$r = $chars{rand(0, $chars_length)};
			if ($r != $string{$i - 1}) $string .=  $r;
		}
		return $string;
	}
	
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
		if ( defined( 'PB_DEMO_MODE' ) ) {
			return;
		}
		$write = false;
		
		if ( !isset( $this->_options['log_level'] ) ) {
			$this->load();
		}
		
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
		
		if ( $write === true ) {
			$fh = @fopen( ABSPATH . '/importbuddy-' . $this->_options['log_serial'] . '.txt', 'a');
			if ( $fh ) {
				fwrite( $fh, '[' . date( $this->_timestamp ) . '-' . $log_type . '] ' . $text . "\n" );
				fclose( $fh );
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
		$tip = ' <a class="pluginbuddy_tip" title="' . $title . ' - ' . $message . '">' . ezimg::genImageTag( 'pluginbuddy_tip.png' ) . '</a>';
		if ( $echo_tip === true ) {
			echo $tip;
		} else {
			return $tip;
		}
	}
	
	
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
			<img src="?ezimg=alert.png" style="float: left;" height="55">
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
	 *	@param			$log		boolean		Default: true. Pass to log() function.
	 *	@return			null
	 */
	function status( $type, $message, $log = true ) {
		$status = date( $this->_timestamp, time() ) . ': ' . htmlentities( addslashes( $message ) );
		
		echo '<script type="text/javascript">jQuery( "#importbuddy_status" ).append( "\n' . $status . '");	textareaelem = document.getElementById( "importbuddy_status" );	textareaelem.scrollTop = textareaelem.scrollHeight;	</script>';
		flush();
		
		if ( $type == 'error' ) {
			$this->log( $message, 'error' );
		} elseif ( $type == 'warning' ) {
			$this->log( $message, 'warning' );
		} else {
			$this->log( '[' . $type . '] ' . $message, 'all' );
		}
	}
	
	
	/**
	 *	set_greedy_script_limits()
	 *
	 *	Sets greedy script limits to help prevent timeouts, running out of memory, etc.
	 *
	 *	@return		null
	 *
	 */
	function set_greedy_script_limits( &$status_callback = '' ) {
		$status_callback = &$status_callback;
		
		if ( ( $status_callback == '' ) && ( method_exists( $this, 'status' ) ) ) {
			$status_callback = &$this;
		}
		
		$this->status( 'message', 'Requisitioning increased server resources.' );
		
		// Don't abort script if the client connection is lost/closed
		@ignore_user_abort( true );
		
		// Set socket timeout to 2 hours.
		@ini_set( 'default_socket_timeout', 60 * 60 * 2 );
		
		// Set maximum runtime to 2 hours.
		$original_maximum_runtime = ini_get( 'max_execution_time' );
		@set_time_limit( 60 * 60 * 2 );
		$this->log( 'Attempted to increase maximum PHP runtime. Original: ' . $original_maximum_runtime . '; New: ' . @ini_get( 'max_execution_time' ) . '.' );
		
		// Increase the memory limit
		$current_memory_limit = trim( @ini_get( 'memory_limit' ) );
		
		// Make sure a minimum memory limit of 256MB is set.
		if ( preg_match( '/(\d+)(\w*)/', $current_memory_limit, $matches ) ) {
			$current_memory_limit = $matches[1];
			$unit = $matches[2];
			// Up memory limit if currently lower than 256M.
			if ( 'g' !== strtolower( $unit ) ) {
				if ( ( $current_memory_limit < 256 ) || ( 'm' !== strtolower( $unit ) ) )
					@ini_set('memory_limit', '256M');
					$this->log( 'Set memory limit to 256M. Previous value < 256M.' );
			}
		} else {
			// Couldn't determine current limit, set to 256M to be safe.
			@ini_set('memory_limit', '256M');
			$this->log( 'Set memory limit to 256M. Previous value unknown.' );
		}
		$this->log( 'Original PHP memory limit: ' . $current_memory_limit . '; New: ' . @ini_get( 'memory_limit' ) . '.' );
	}
	
	
	function remove_file( $file, $description, $error_on_missing = false ) {
		$this->status( 'message', 'Deleting `' . $description . '`...' );

		@chmod( $file, 0755 ); // High permissions to delete.
		
		if ( is_dir( $file ) ) { // directory.
			$this->remove_dir( $file );
			if ( file_exists( $file ) ) {
				$this->status( 'error', 'Unable to delete directory: `' . $description . '`. You should manually delete it.' );
			} else {
				$this->status( 'message', 'Deleted.', false ); // No logging of this action to prevent recreating log.
			}
		} else { // file
			if ( file_exists( $file ) ) {
				if ( @unlink( $file ) != 1 ) {
					$this->status( 'error', 'Unable to delete file: `' . $description . '`. You should manually delete it.' );
				} else {
					$this->status( 'message', 'Deleted.', false ); // No logging of this action to prevent recreating log.
				}
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
	
	
	/**
	 *	get_zip_id()
	 *
	 *	Given a BackupBuddy ZIP file, extracts the random ZIP ID from the filename. This random string determines
	 *	where BackupBuddy will find the temporary directory in the backup's wp-uploads directory. IE a zip ID of
	 *	3poje9j34 will mean the temporary directory is wp-uploads/temp_3poje9j34/. backupbuddy_dat.php is in this
	 *	directory as well as the SQL dump.
	 *
	 *	Currently handles old BackupBuddy ZIP file format. Remove this backward compatibility at some point.
	 *
	 *	$file			string		BackupBuddy ZIP filename.
	 *	@return			string		ZIP ID characters.
	 *
	 */
	function get_zip_id( $file ) {
		$posa = strrpos($file,'_')+1;
		$posb = strrpos($file,'-')+1;
		if ( $posa < $posb ) {
			$zip_id = strrpos($file,'-')+1;
		} else {
			$zip_id = strrpos($file,'_')+1;
		}
		
		$zip_id = substr( $file, $zip_id, - 4 );
		return $zip_id;
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
		$maybe_backupdata_file = ABSPATH . 'wp-content/uploads/temp_'. $this->_options['zip_id'] .'/backupbuddy_dat.php'; // Full backup dat file location
		$maybe_backupdata_file_new = ABSPATH . 'wp-content/uploads/backupbuddy_temp/'. $this->_options['zip_id'] .'/backupbuddy_dat.php'; // Full backup dat file location
		
		if ( file_exists( $maybe_backupdata_file ) ) { // Full backup location.
			$dat_file = $maybe_backupdata_file;
		} elseif ( file_exists( $maybe_backupdata_file_new ) ) { // Full backup location.
			$dat_file = $maybe_backupdata_file_new;
		} elseif ( file_exists( ABSPATH . 'backupbuddy_dat.php' ) ) { // DB only location.
			$dat_file = ABSPATH . 'backupbuddy_dat.php';
		} else {
			echo 'Error: Unable to find DAT file.';
		}
		
		$this->_backupdata = $this->get_backup_dat( $dat_file );
	}
	
	function get_backup_dat( $dat_file ) {
		require_once( 'importbuddy/classes/_get_backup_dat.php' );
		return $return;
	}
	
	/**
	 *	migrate_htaccess()
	 *
	 *	Migrates .htaccess file if it exists.
	 *
	 *	@return		boolean		False only if file is unwritable. True if write success; true if file does not even exist.
	 *
	 */
	function migrate_htaccess() {
		if ( $this->_options['skip_htaccess'] == true ) {
			$this->status( 'message', 'Skipping .htaccess migration based on settings.' );
			return true;
		} else {
			if ( !is_writable( ABSPATH . '.htaccess' ) ) {
				$this->status( 'error', 'Error #9020: Unable to write to .htaccess file. Verify permissions.' );
				$this->alert( 'ERROR: Unable to write to file .htaccess.', 'Verify this file has proper write permissions. You may receive 404 Not Found errors on your site if this is not corrected. Re-save permalinks to fix this.', '9020' );
				return false;
			}
			
			// If no .htaccess file exists then create a basic default one then migrate that as needed. @since 2.2.32.
			if ( !file_exists( ABSPATH . '.htaccess' ) ) {
				$this->status( 'message', 'No .htaccess file found. Creating basic default .htaccess file.' );
				
				// Default .htaccess file.
				$htaccess_contents = 
"# BEGIN WordPress\n
<IfModule mod_rewrite.c>\n
RewriteEngine On\n
RewriteBase /\n
RewriteRule ^index\\.php$ - [L]\n
RewriteCond %{REQUEST_FILENAME} !-f\n
RewriteCond %{REQUEST_FILENAME} !-d\n
RewriteRule . /index.php [L]\n
</IfModule>\n
# END WordPress\n";
				file_put_contents( ABSPATH . '.htaccess', $htaccess_contents );
				unset( $htaccess_contents );
			}
			
			$this->status( 'message', 'Migrating .htaccess file...' );
			
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
			
			$this->status( 'message', 'Checking .htaccess file.' );
			
			// If the URL (domain and/or URL subdirectory ) has changed, then need to update .htaccess file.
			if ( $newurl !== $oldurl ) {
				$this->status( 'message', 'URL directory has changed. Updating from `' . implode( '/', $oldurl ) . '` to `' . implode( '/', $newurl ) . '`.' );
				
				$rewrite_lines = array();
				$got_rewrite = false;
				$rewrite_path = implode( '/', $newurl );
				$file_array = file( ABSPATH . '.htaccess' );
				
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
					
				$handling = fopen( ABSPATH . '.htaccess', 'w');
				fwrite( $handling, implode( $rewrite_lines ) );
				fclose( $handling );
				unset( $handling );
				
				$this->status( 'message', 'Migrated .htaccess file.' );
			} else {
				$this->status( 'message', 'No changes needed for .htaccess file.' );
			}
		}
		return true;
	}
	
	
	/**
	 *	connect()
	 *
	 *	Initializes a connection to the mysql database.
	 *
	 *	@return		boolean		True on success; else false. Success testing is very loose.
	 */
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
			$this->status( 'error', 'Error: Unable to connect or authenticate to database `' . $this->_options['db_name'] . '`.' );
			return false;
		}
		
		// Set up character set. Important.
		mysql_query("SET NAMES 'utf8'");
		
		return true;
	}
	
	
	function restore_database( $query_start = 0 ) {
		$options['db_server'] = $this->_options['db_server'];
		$options['db_name'] = $this->_options['db_name'];
		$options['db_user'] = $this->_options['db_user'];
		$options['db_password'] = $this->_options['db_password'];
		$options['db_prefix'] = $this->_options['db_prefix'];
		$options['zip_id'] = $this->_options['zip_id'];
		$options['old_prefix'] = $this->_backupdata['db_prefix'];
		
		if ( $this->_options['ignore_sql_errors'] != false ) {
			$ignore_sql_errors = true;
		} else {
			$ignore_sql_errors = false;
		}
		
		require_once( 'importbuddy/lib/dbimport/dbimport.php' );
		$dbimport = new pluginbuddy_dbimport( $options, $this, $ignore_sql_errors );
		
		$import_result = $dbimport->restore_database( $query_start );
		return $import_result;
	}
	
	/**
	 *	migrate_database()
	 *
	 *	Migrates the already imported database's content for updates ABSPATH and URL.
	 *
	 *	@return		boolean		True=success, False=failed.
	 *
	 */
	function migrate_database() {
		require( 'importbuddy/classes/_migrate_database.php' );
		return $return;
	}
	
	
	/**
	 *	wipe_database()
	 *
	 *	Clear out the existing database to prepare for importing new data.
	 *
	 *	@return			boolean		Currently always true.
	 */
	function wipe_database() {
		$this->status( 'message', 'Beginning wipe of database...' );
		
		// Connect to database.
		$this->connect_database();
		
		$result = mysql_query( 'SHOW TABLES' );
		$table_wipe_count = mysql_num_rows( $result );
		while( $row = mysql_fetch_row( $result ) ) {
			mysql_query( 'DROP TABLE `' . $row[0] . '`' );
		}
		mysql_free_result( $result ); // Free memory.
		$this->status( 'message', 'Wiped database of ' . $table_wipe_count . ' tables.' );
		
		return true;
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
	
	
	/**
	 *	phpinfo_array()
	 *
	 *	Get phpinfo() data as an array.
	 *
	 *	@return			array		Array of phpinfo() data.
	 */
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
	
	
	// Escape backreferences from string for use with regex
	function preg_escape_back($string) {
		// Replace $ with \$ and \ with \\
		$string = preg_replace('#(?<!\\\\)(\\$|\\\\)#', '\\\\$1', $string);
		return $string;
	}
	
	
	/**
	 *	migrate_wp_config()
	 *
	 *	Migrates and updates the wp-config.php file contents as needed.
	 *
	 *	@return			null			True on success. Currently always true.
	 */
	function migrate_wp_config() {
		$this->status( 'message', 'Starting migration of wp-config.php file...' );
		
		flush();
		
		// Check that we can write to this file.
		if ( !is_writable( ABSPATH . 'wp-config.php' ) ) {
			$this->alert( 'ERROR: Unable to write to file wp-config.php.', 'Verify this file has proper write permissions.', '9020' );
			return false;
		}
		
		if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
			// Useful REGEX site: http://gskinner.com/RegExr/
			
			$updated_home_url = false;
			$wp_config = array();
			$lines = file( ABSPATH . 'wp-config.php' );
			
			$patterns = array();
			$replacements = array();
			
			/*
			Update WP_SITEURL, WP_HOME if they exist.
			Update database DB_NAME, DB_USER, DB_PASSWORD, and DB_HOST.
			RegExp: /define\([\s]*('|")WP_SITEURL('|"),[\s]*('|")(.)*('|")[\s]*\);/gi
			pattern: define\([\s]*('|")WP_SITEURL('|"),[\s]*('|")(.)*('|")[\s]*\);
			*/
			$pattern[0] = '/define\([\s]*(\'|")WP_SITEURL(\'|"),[\s]*(\'|")(.)*(\'|")[\s]*\);/i';
			$replace[0] = "define( 'WP_SITEURL', '" . trim( $this->_options['siteurl'], '/' ) . "' );";
			$pattern[1] = '/define\([\s]*(\'|")WP_HOME(\'|"),[\s]*(\'|")(.)*(\'|")[\s]*\);/i';
			$replace[1] = "define( 'WP_HOME', '" . trim( $this->_options['home'], '/' ) . "' );";
			
			$pattern[2] = '/define\([\s]*(\'|")DB_NAME(\'|"),[\s]*(\'|")(.)*(\'|")[\s]*\);/i';
			$replace[2] = "define( 'DB_NAME', '" . $this->_options['db_name'] . "' );";
			$pattern[3] = '/define\([\s]*(\'|")DB_USER(\'|"),[\s]*(\'|")(.)*(\'|")[\s]*\);/i';
			$replace[3] = "define( 'DB_USER', '" . $this->_options['db_user'] . "' );";
			$pattern[4] = '/define\([\s]*(\'|")DB_PASSWORD(\'|"),[\s]*(\'|")(.)*(\'|")[\s]*\);/i';
			$replace[4] = "define( 'DB_PASSWORD', '" . $this->preg_escape_back( $this->_options['db_password'] ) . "' );";
			$pattern[5] = '/define\([\s]*(\'|")DB_HOST(\'|"),[\s]*(\'|")(.)*(\'|")[\s]*\);/i';
			$replace[5] = "define( 'DB_HOST', '" . $this->_options['db_server'] . "' );";
			
			// If multisite, update domain.
			$pattern[6] = '/define\([\s]*(\'|")DOMAIN_CURRENT_SITE(\'|"),[\s]*(\'|")(.)*(\'|")[\s]*\);/i';
			$replace[6] = "define( 'DOMAIN_CURRENT_SITE', '" . $this->_options['domain'] . "' );";
			
			/*
			Update table prefix.
			RegExp: /\$table_prefix[\s]*=[\s]*('|")(.)*('|");/gi
			pattern: \$table_prefix[\s]*=[\s]*('|")(.)*('|");
			*/
			$pattern[7] = '/\$table_prefix[\s]*=[\s]*(\'|")(.)*(\'|");/i';
			$replace[7] = '$table_prefix = \'' . $this->_options['db_prefix'] . '\';';
			
			// Perform the actual replacement.
			$lines = preg_replace( $pattern, $replace, $lines );
			
			// Write changes to config file.
			if ( false === ( file_put_contents( ABSPATH . 'wp-config.php', $lines ) ) ) {
				$this->alert( 'ERROR: Unable to save changes to wp-config.php.', 'Verify this file has proper write permissions.', '9020' );
				return false;
			}
			
			unset( $lines );
		} else {
			$this->status( 'warning', 'Warning: wp-config.php file not found.' );
			$this->alert( 'Note: wp-config.php file not found. This is normal for a database only backup.' );
		}
		
		$this->status( 'message', 'Migration of wp-config.php complete.' );
		
		return true;
	}
	
	
	/**
	 *	array_remove()
	 *
	 *	Removes array values in $remove from $array.
	 *
	 *	@param			$array		array		Source array. This will have values removed and be returned.
	 *	@param			$remove		array		Array of values to search for in $array and remove.
	 *	@return						array		Returns array $array stripped of all values found in $remove
	 */
	function array_remove( $array, $remove ) {
		if ( !is_array( $remove ) ) {
			$remove = array( $remove );
		}
		return array_values( array_diff( $array, $remove ) );
	}
	
	
	// TODO: Modify all references to use unlink_recursive()
	function delete_directory( $dir ) {
		unlink_recursive( $dir );
	}
	
	
}
define( 'pluginbuddy_importbuddy', true ); // Tell Server Info page to not load some sections.
$pluginbuddy_importbuddy = new pluginbuddy_importbuddy();


// Compatibility with some WordPress localization.
function __( $text, $domain ) {
	return $text;
}
function _e( $text, $domain ) {
	echo $text;
}


/**
 *	unlink_recursive()
 *
 *	Recursively unlinks (deletes) the directories and all files and directories within.
 *
 *	$path		string		Full absolute path to file / directory to delete.
 *	@return		null
 *
 */
function unlink_recursive( $dir ) {
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
		if ( !unlink_recursive( $dir . "/" . $item ) ) {
			chmod( $dir . "/" . $item, 0777 );
			if ( !unlink_recursive( $dir . "/" . $item ) ) {
				return false;
			}
		}
	}
	return rmdir( $dir );
}


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
 * Check value to find if it was serialized.
 *
 * If $data is not an string, then returned value will always be false.
 * Serialized data is always a string.
 * Courtesy WordPress; since WordPress 2.0.5.
 *
 * @param mixed $data Value to check to see if was serialized.
 * @return bool False if not serialized and true if it was.
 */
function is_serialized( $data ) {
	// if it isn't a string, it isn't serialized
	if ( ! is_string( $data ) )
		return false;
	$data = trim( $data );
 	if ( 'N;' == $data )
		return true;
	$length = strlen( $data );
	if ( $length < 4 )
		return false;
	if ( ':' !== $data[1] )
		return false;
	$lastc = $data[$length-1];
	if ( ';' !== $lastc && '}' !== $lastc )
		return false;
	$token = $data[0];
	switch ( $token ) {
		case 's' :
			if ( '"' !== $data[$length-2] )
				return false;
		case 'a' :
		case 'O' :
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b' :
		case 'i' :
		case 'd' :
			return (bool) preg_match( "/^{$token}:[0-9.E-]+;\$/", $data );
	}
	return false;
}


/**
 *	array_remove()
 *
 *	Removes array values in $remove from $array.
 *
 *	@param			$array		array		Source array. This will have values removed and be returned.
 *	@param			$remove		array		Array of values to search for in $array and remove.
 *	@return						array		Returns array $array stripped of all values found in $remove
 */
function array_remove( $array, $remove ) {
	if ( !is_array( $remove ) ) {
		$remove = array( $remove );
	}
	return array_values( array_diff( $array, $remove ) );
}


/**
*	unpack_importbuddy()
*
*	Unpacks required files encoded in importbuddy.php into stand-alone files.
*
*	@return		null
*/
function unpack_importbuddy() {
	if ( !is_writable( ABSPATH ) ) {
		echo 'Error #224834. This directory is not write enabled. Please verify write permissions to continue.';
		die();
	} else {
		$unpack_file = '';
		
		$handle = @fopen( ABSPATH . 'importbuddy.php', 'r' );
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
						if ( !is_dir( dirname( ABSPATH . $unpack_file ) ) ) {
							mkdir_recursive( dirname( ABSPATH . $unpack_file ) );
						}
						file_put_contents( ABSPATH . $unpack_file, trim( base64_decode( $buffer ) ) );
					}
				}
			}
			if ( !feof( $handle ) ) {
				echo "Error: unexpected fgets() fail\n";
			}
			fclose( $handle );
		}
	}
}
die();
?>