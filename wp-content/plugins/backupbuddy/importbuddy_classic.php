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
 * Version: 2.2.4 - Iteration 185 - 07/12/2011
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

if ( !class_exists( 'PluginBuddyImportBuddy' ) ) {
	class PluginBuddyImportBuddy {
		var $_version = '2.2.4';
		var $_timestamp = 'M j, Y, g:iA';						// PHP timestamp format.
		var $_total_steps = '7';
		var $_defaults = array(
			'password'					=>		'#PASSWORD#',	// MD5 hash of the import password. Prevents unauthorized access.
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
			
			'log_level'					=>		2,				// 0 = none, 1 = errors only, 2 = errors + warnings, 3 = debugging (all kinds of actions)
			'db_server'					=>		'',
			'db_user'					=>		'',
			'db_password'				=>		'',
			'db_name'					=>		'',
			'db_prefix'					=>		'',
			'siteurl'					=>		'',
		);
		var $_backupdata;
		
		
		/**
		 *	PluginBuddyImportBuddy()
		 *
		 *	Default constructor.
		 *
		 */
		function PluginBuddyImportBuddy() {
			// Prevent access to importbuddy.php if it is still in plugin directory.
			if ( file_exists( dirname( __FILE__ ) . '/backupbuddy.php' ) ) {
				echo 'The BackupBuddy importer, ImportBuddy, can ONLY be accessed on the destination server that you wish to import your backup to.<br>';
				echo 'Upload the importer in the root web directory on the destination server and try again.<br><br>';
				echo 'If you need assistance visit <a href="http://pluginbuddy.com">http://pluginbuddy.com</a>';
				die();
			}
			
			// Return image if requested.
			if ( isset( $_GET['ezimg'] ) ) {
				ezimg::showImg( $_GET['ezimg'] );
			}
			
			$this->time_start = microtime( true );
			
			if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'mysql_test' ) ) {
				$connect_status = '<font color=red>Failed</font>';
				$connect_status_error = '';
				$select_status = 'N/A';
				$select_status_error = '';
				$overall_status = '<font color=red>Failed</font>';
				$existing_status = 'N/A';
				
				if ( false === @mysql_connect( $_POST['server'], $_POST['user'], $_POST['pass'] ) ) { // Couldnt connect to server or invalid credentials.
					$connect_status = '<font color=red>Failed</font>';
					$connect_status_error = mysql_error();
					$this->log( 'mysql ajax test FAILED: Connection failed. Error: ' . mysql_error(), true );
				} else {
					$connect_status = 'Success';
					if ( false === @mysql_select_db( $_POST['name'] ) ) {
						$select_status = '<font color=red>Failed</font>';
						$select_status_error = mysql_error();
						$this->log( 'mysql ajax test FAILED: Connected but database access denied. Error: ' . mysql_error(), true );
					} else {
						$select_status = 'Success';
						
						// Check number of tables already existing with this prefix.
						$result = mysql_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( $_POST['prefix'] ) . "%'" );
						if ( mysql_num_rows( $result ) > 0 ) {
							$this->log( 'Database already contains a WordPress installation with this prefix (' . mysql_num_rows( $result ) . ' tables). Restore halted.', 'error' );
							$existing_status = '<font color=red>Failed</font>';
							$exiting_status_error = mysql_error();
						} else {
							$existing_status = 'Success';
							$overall_status = 'Success';
							$this->log( 'mysql ajax test SUCCESS' );
						}
						unset( $result );
					}
				}
				
				echo '1. Logging in to server ... ' . $connect_status . '.<br>';
				if ( $connect_status != 'Success' ) {
					echo '&nbsp;&nbsp;&nbsp;&nbsp;Error: ' . $connect_status_error . '<br>';;
				}
				
				echo '2. Verifying database access & permission ... ' . $select_status . '.<br>';
				if ( ( $select_status != 'Success' ) && ( $select_status != 'N/A' ) ) {
					echo '&nbsp;&nbsp;&nbsp;&nbsp;Error: ' . $select_status_error . '<br>';
				}
				
				echo '3. Verifying no existing WP data ... ' . $existing_status . '.<br>';
				if ( $select_status == 'N/A' ) {
					//echo '&nbsp;&nbsp;&nbsp;&nbsp;N/A';
				} else {
					if ( $existing_status != 'Success' ) {
						echo '&nbsp;&nbsp;&nbsp;&nbsp;Error: WordPress already exists in this database with this prefix.<br>';
					}
				}
				echo '4. Overall mySQL test result ... ' . $overall_status . '.<br>';

				
				die();
			}
			
			define( 'ABSPATH', dirname( __FILE__ ) );
			
			// Set up options.
			if ( isset( $_POST['options'] ) ) {
				$this->_options = unserialize( stripslashes( htmlspecialchars_decode( $_POST['options'] ) ) );
				$this->_options = array_merge( $this->_defaults, (array)$this->_options ); // Add in any defaults not explicitly set yet.
			} else {
				$this->_options = $this->_defaults;
			}
			
			// If we know the backup file then do some set-up...
			if ( $this->_options['file'] != '' ) {
				// Extract ZIP ID from the ZIP filename.  This is used to find the temp directory in the wp-uploads directory of the backup.
				if ( $this->_options['zip_id'] == '' ) {
					$this->_options['zip_id'] = $this->get_zip_id( $this->_options['file'] );
				}
			}
			
			// Set up PHP error levels.
			if ( $this->_options['show_php_warnings'] === true ) {
				error_reporting( E_ERROR | E_WARNING | E_PARSE | E_NOTICE ); // HIGH
				$this->log( 'PHP error reporting set HIGH.' );
			} else {
				error_reporting( E_ALL ^ E_NOTICE ); // LOW
			}
			
			$this->detected_max_execution_time = str_ireplace( 's', '', ini_get( 'max_execution_time' ) );
			if ( is_numeric( $this->detected_max_execution_time ) === false ) {
				$detected_max_execution_time = 30;
			}
						
			// Try to set timeouts to 30 minutes.
			header( 'Keep-Alive: 3600' );
			header( 'Connection: keep-alive' );
			ini_set( 'default_socket_timeout', '3600' );
			set_time_limit( '3600' );
			
			// Determine the current step.
			if ( ( isset( $_GET['step'] ) ) && ( is_numeric( $_GET['step'] ) ) ) {
				$this->_step = $_GET['step'];
			} else {
				$this->_step = 1;
			}
			
			// Run function for the requested step.
			if ( method_exists( $this, 'view_step_' . $this->_step ) ) {
				$this->log( 'Initiating step #' . $this->_step . '.' );
				
				$this->print_html_header();
				
				echo "\n\n<!--\n\n";
				print_r( $this->_options );
				echo "\n\n-->\n\n";
				
				call_user_func( array(&$this, 'view_step_' . $this->_step ) );
				$this->print_html_footer();
				
				$this->log( 'Completed step #' . $this->_step . '.' );
			} else {
				$this->log( 'Unable to initiate step #' . $this->_step . '. Halted.', 'error' );
				die( 'ERROR #546542. Invalid step "' . $this->_step . '".' );
			}
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
			$fh = fopen( ABSPATH . '/importbuddy.txt', 'a');
			if ( $fh !== false ) {
				fwrite( $fh, '[' . date( $this->_timestamp, time() ) . '-' . $log_type . '] ' . $text . "\n" );
				fclose( $fh );
			} else {
				// Don't use alert here since it could recursively look writing to the log.
				echo 'Warning: Unable to write to log file. Verify write permissions to this directory.' ;
			}
		}
		
		
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
		function alert( $message, $error = false, $error_code = '' ) {
			$log_error = false;
		
			echo '<div id="message" class="';
			if ( $error == false ) {
				echo 'updated fade';
			} else {
				echo 'error';
				$log_error = true;
			}
			if ( $error_code != '' ) {
				$message .= '<p><a href="http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#' . $error_code . '" target="_new"><i>BackupBuddy Error Code ' . $error_code . ' - Click for more details.</i></a></p>';
				$log_error = true;
			}
			if ( $log_error === true ) {
				$this->log( $message . ' Error Code: ' . $error_code, 'error' );
			}
			echo '"><p><strong>' . ezimg::genImageTag( 'bullet_error.png' ) . ' ' . $message . '</strong></p></div>';
		}
		
		function test() {
			echo 'test';
		}
		
		function view_step_1() {
			if ( ( $this->_defaults['password'] != '#PASSWORD#' ) && ( md5( $_POST['password'] ) != $this->_defaults['password'] ) ) {
				echo 'Please enter your BackupBuddy import password to continue. This was set from the BackupBuddy settings page.<br><br>';
				echo '<form action="?step=1" method="post">';
				echo '<input type="password" name="password" value="" />';
				echo '<input type="submit" name="submit" value="Authenticate &raquo;" class="button" />';
				echo '</form>';
			} else {
				?>
				Please select the backup file you would like to import or migrate.
				You will be given the ability to modify server settings on Step 4.<br><br>
				Throughout the restore process you may hover over question marks <?php $this->tip( 'This is an example help tip. Hover over these for additional help.' ); ?> for additional help. 
				For support see our <a href="http://ithemes.com/codex/page/BackupBuddy" target="_blank">Knowledge Base</a> or <a href="http://pluginbuddy.com/support/" target="_blank">Support Forum</a>.
				<br><br><br><form action="?step=2" method=post>
				<?php
				// List backup files in this directory.
				if ( $handle = opendir( dirname( __FILE__ ) ) ) {
					echo ezimg::genImageTag( 'bullet_go.png' );
					echo '&nbsp;<select name="file">';
					$count = 0;
					while ( false !== ( $file = readdir( $handle ) ) ) {
						if ( ( $file != "." ) && ( $file != ".." ) && ( substr( $file, 0, 7 ) == 'backup_' || substr( $file, 0, 7 ) == 'backup-' ) && ( substr( $file, -4, 4 ) == '.zip' ) ) {
							echo '<option value="' . $file . '">' . $file . '</option>';
							$count++;
						}
					}
					if ($count == 0) {
						echo '<option>No backup files found</option>';
						$this->log( 'No backup files found.' );
					}
					echo '</select>';
					echo $this->tip( 'Select the backup file you would like to restore data from. This must be a valid BackupBuddy ZIP file.', '', true );
					echo '<br><br>';
					if ($count == 0) {
						echo ezimg::genImageTag('bullet_error.png');
						echo 'ERROR: Unable to find any BackupBuddy ZIP files. Upload the BackupBuddy ZIP file into the same directory as this file, keeping the original file name. If you manually extracted, upload the backup ZIP, select it above, then select <i>Advanced Troubleshooting Options</i> & click <i>Skip Zip Extraction</i>.';
						echo '<br><br>';
					}
					closedir($handle);
				} else {
					echo 'ERROR: Unable to open current directory. Please check file permissions.';
				}
				?>
				<div style="margin-left: 20px;">
					<span class="toggle button-secondary" id="advanced">Advanced Troubleshooting Options</span>
					<div id="toggle-advanced" class="toggled">
						<b>WARNING: Advanced use only. Use only if directed by support. Improper use may result in lost data (or worse).</b><br><br>
						<input type="checkbox" name="skip_files" /> Skip zip file extraction. <?php $this->tip( 'Checking this box will result in importbuddy.php NOT extracting/unzipping your backup ZIP file for you.  You will need to manually extract it either on your local computer then upload it or using a server-based tool such as cPanel to extract it. This feature is useful if your host cannot extract the ZIP file for some reason or is timing out.' ); ?><br>
						<input type="checkbox" name="wipe_database" onclick="
							if ( !confirm( 'WARNING! WARNING! WARNING! WARNING! WARNING! \n\nThis will clear any existing WordPress installation or other content in this database. This could result in loss of posts, comments, pages, settings, and other software data loss. Verify you are using the exact database settings you want to be using. PluginBuddy & all related persons hold no responsibility for any loss of data caused by using this option. \n\n Are you sure you want to do this and potentially wipe existing data? \n\n WARNING! WARNING! WARNING! WARNING! WARNING!' ) ) {
								return false;
							}
						" /> Wipe database on import. WARNING: Use with caution. <br>
						<input type="checkbox" name="skip_database_import" /> Skip import of database. <br>
						<input type="checkbox" name="skip_database_migration" /> Skip migration of database. <br>
						<input type="checkbox" name="skip_htaccess" /> Skip migration of .htaccess file. <br>
						<!-- TODO: <input type="checkbox" name="merge_databases" /> Ignore existing WordPress data & merge database.<?php $this->tip( 'This may result in data conflicts, lost database data, or incomplete restores.', 'WARNING' ); ?></a><br> -->
						<input type="checkbox" name="force_compatibility_medium" /> Force medium speed compatibility mode (ZipArchive). <br>
						<input type="checkbox" name="force_compatibility_slow" /> Force slow speed compatibility mode (PCLZip). <br>
						<input type="checkbox" name="show_php_warnings" /> Show detailed PHP warnings. <br>
						<br>
						<b>PHP Maximum Execution Time:</b> <input type="text" name="max_execution_time" value="<?php echo $this->detected_max_execution_time; ?>" size="5"> seconds. <?php $this->tip( 'The maximum allowed PHP runtime. If your database import step is timing out then lowering this value will instruct the script to limit each `chunk` to allow it to finish within this time period.' ); ?>
						<br>
						<b>Error Logging to importbuddy.txt:</b> <select name="log_level">
							<option value="0">None</option>
							<option value="1" selected>Errors Only (default)</option>
							<option value="2">Errors & Warnings</option>
							<option value="3">Everything (debug mode)</option>
						</select> <?php $this->tip( 'Errors and other debugging information will be written to importbuddy.txt in the same directory as importbuddy.php.  This is useful for debugging any problems encountered during import.  Support may request this file to aid in tracking down any problems or bugs.' ); ?>
					</div>
				</div>
				<?php
				$this->notify_existing_wordpress();
				echo '<br><br><br>';
				
				// If one or more backup files was found then provide a button to continue.
				if ($count > 0) {
					echo '<input type="submit" name="submit" value="Next Step &raquo;" class="button" />';
				}
				echo '</form>';
			}
		}
		
		
		function view_step_2() {
			// Set advanced debug options if user set any.
			if ( ( isset( $_POST['skip_files'] ) ) && ( $_POST['skip_files'] == 'on' ) ) { $this->_options['skip_files'] = true; }
			if ( ( isset( $_POST['skip_database_import'] ) ) && ( $_POST['skip_database_import'] == 'on' ) ) { $this->_options['skip_database_import'] = true; }
			if ( ( isset( $_POST['skip_database_migration'] ) ) && ( $_POST['skip_database_migration'] == 'on' ) ) { $this->_options['skip_database_migration'] = true; }
			if ( ( isset( $_POST['wipe_database'] ) ) && ( $_POST['wipe_database'] == 'on' ) ) { $this->_options['wipe_database'] = true; }
			if ( ( isset( $_POST['skip_htaccess'] ) ) && ( $_POST['skip_htaccess'] == 'on' ) ) { $this->_options['skip_htaccess'] = true; }
			if ( ( isset( $_POST['force_compatibility_medium'] ) ) && ( $_POST['force_compatibility_medium'] == 'on' ) ) { $this->_options['force_compatibility_medium'] = true; }
			if ( ( isset( $_POST['force_compatibility_slow'] ) ) && ( $_POST['force_compatibility_slow'] == 'on' ) ) { $this->_options['force_compatibility_slow'] = true; }
			if ( ( isset( $_POST['show_php_warnings'] ) ) && ( $_POST['show_php_warnings'] == 'on' ) ) { $this->_options['show_php_warnings'] = true; }
			if ( ( isset( $_POST['file'] ) ) && ( $_POST['file'] != '' ) ) { $this->_options['file'] = $_POST['file']; }
			if ( ( isset( $_POST['max_execution_time'] ) ) && ( is_numeric( $_POST['max_execution_time'] ) ) ) {
				$this->_options['max_execution_time'] = $_POST['max_execution_time'];
			} else {
				$this->_options['max_execution_time'] = 30;
			}
			if ( ( isset( $_POST['log_level'] ) ) && ( $_POST['log_level'] != '' ) ) { $this->_options['log_level'] = $_POST['log_level']; }
			
			echo 'Please select whether you are migrating to a new server with new database settings OR restoring the site to the same server the backup was made on.';
			echo ' This will help the script determine the best server defaults for you. You may preview & change these server settings on Step 4.';
			echo '<br>';
			
			echo '<form action="?step=3" method=post>';
			echo '<input type="hidden" name="options" value="' . htmlspecialchars( serialize( $this->_options ) ) . '" />';
			echo '<p><label for="type_migrate" style="width: 170px;"><b>Migrate to new server:</b></label><input type="radio" name="type" id="type_migrate" value="migrate" style="margin-top: 6px;" checked /></p>';
			echo '<p><label for="type_restore" style="width: 170px;"><b>Restore to same server:</b></label><input type="radio" name="type" id="type_restore" value="restore" style="margin-top: 6px;" /></p>';

			echo '<br>Your site will be imported to the following path on the next step. Conflicts may occur if an existing WordPress installation exists here.<br><br><b>Path:</b> ' . ABSPATH;
			
			// Quick check to see if a WordPress installation seems to already exist.
			$this->notify_existing_wordpress();
			
			echo '<br><br><br><input type="submit" name="submit" value="Next &raquo;" class="button" />';
			echo '</form>';
		}
		
		
		function view_step_3() {
			$failed = false; // Default value.
			
			if ( ( isset( $_POST['type'] ) ) && ( $_POST['type'] != '' ) ) { $this->_options['type'] = $_POST['type']; }
			
			if ( false === $this->_options['skip_files'] ) { // Option to skip all file updating / extracting.
				$this->log( 'STARTING extracting Zip File "' . ABSPATH . '/' . $this->_options['file'] . '" into "' . ABSPATH . '".' );
				
				echo '<b>Extracting files ' . $this->tip( 'Your site files and/or database must be extracted/unzipped from the ZIP backup file for further processing. This may take some time depending on the size of your size and the speed of the server and server load. If this fails you may need to manually extract the files locally then upload them or use a server tool such as cPanel.', '', false ) . ' into path:</b><br><br>' . ABSPATH . '<br><br>';
				
				flush();
				
				$failed = !$this->extract_files();
				
				$this->_backupdata_file = ABSPATH . '/wp-content/uploads/temp_' . $this->_options['zip_id'] . '/backupbuddy_dat.php'; // Full backup dat file location
				$this->_backupdata_file_new = ABSPATH . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/backupbuddy_dat.php'; // Full backup dat file location
				if ( !file_exists( $this->_backupdata_file ) && !file_exists( $this->_backupdata_file_new ) && ( !file_exists( ABSPATH . '/backupbuddy_dat.php' ) ) ) {
					$failed = true;
					$this->log( 'MISSING dat file extracting Zip File "' . ABSPATH . '/' . $this->_options['file'] . '" into "' . ABSPATH . '".' );
					$this->alert( 'Error! The unzip process reported success but the backup data file, backupbuddy_dat.php was not found in the extracted files. The unzip process either failed (most likely) or the zip file is not a proper BackupBuddy backup.', true, '9004' );
				}
			} else {
				$this->alert( 'Skipped extracting files based on debugging options.' );
				$this->log( 'Skipped extracting files based on debugging options.' );
			}
			
			if ( false === $failed ) {
				$this->log( 'SUCCESS extracting Zip File "' . ABSPATH . '/' . $this->_options['file'] . '" into "' . ABSPATH . '".' );
				
				echo '<br><b>Files extracted.</b>';
				echo '<form action="?step=4" method=post>';
				echo '<input type="hidden" name="options" value="' . htmlspecialchars( serialize( $this->_options ) ) . '" />';
				echo '<br><br><input type="submit" name="submit" value="Next Step &raquo;" class="button" />';
				echo '</form>';
			} else {
				$this->log( 'FAILURE extracting Zip File "' . ABSPATH . '/' . $this->_options['file'] . '" into "' . ABSPATH . '".' );
				$this->alert( 'File extraction process did not complete successfully. Unable to continue to next step. Manually extract the backup ZIP file and choose to "Skip File Extraction" from the advanced options on Step 1.', true, '9005' );
				echo '<a href="http://pluginbuddy.com/tutorials/unzip-backup-zip-file-in-cpanel/">Click here for instructions on manual ZIP extraction as a work-around.</a>';
			}
		}
		
		
		function view_step_4() {
			// Set up backup data from the backupbuddy_dat.php.
			$this->load_backup_dat();
			
			$this->log( 'VERSION of BackupBuddy used to create backup ZIP: ' . $this->_backupdata['backupbuddy_version'] . '.' );
			$this->log( 'VERSION of importbuddy running now: ' . $this->_version . '.' );
			
			if ( $this->_options['type'] == 'migrate' ) { // Migrating to a new server; wipe server-specific defaults since they do not apply.
				echo 'Please enter the settings for the new server you are migrating to.  You will need to create a new database on the new server if you have not already done so.<br><br><br>';
				$this->_backupdata['db_server'] = 'localhost';
				$this->_backupdata['db_name'] = '';
				$this->_backupdata['db_user'] = '';
				$this->_backupdata['db_password'] = '';
			} else { // Staying on the same server; preserve defaults.
				echo 'Please enter the settings for restoring to your same server using the same database.  Your previous settings have been entered for you for convenience.<br><br>';
			}
			
			echo '<form action="?step=5" method=post>';
			echo '<input type="hidden" name="options" value="' . htmlspecialchars( serialize( $this->_options ) ) . '" />';
			
			// Get the current URL of where the importbuddy script is running.
			$url = str_replace( $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'] );
			$url = str_replace( basename( $url ) , '', $url );
			$url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
			
			if ( $this->_backupdata['db_prefix'] == '' ) {
				$this->_backupdata['db_prefix'] = 'wp_';
			}
			if ( $this->_backupdata['db_server'] == '' ) {
				$this->_backupdata['db_server'] = 'localhost';
			}
			
			$custom_home_tip = 'OPTIONAL. This is also known as the site address. This is the home address where your main site resides. This may differ from your WordPress URL. Ex: http://foo.com/';
			echo '<label>WordPress Address (Site URL)' . $this->tip( 'This is the address where you want the final WordPress site you are restoring / migrating to reside. Ex: http://foo.com/wp/', '', false ) . '</label> <input type="text" name="siteurl" value="'. $url .'" size="50" /><br>';
			echo '<label style="width: 100%; margin-left: 150px;" class="light"><input type="checkbox" name="custom_home" class="option_toggle" value="on" id="custom_home"> Use optional custom site address (Home URL) ' . $this->tip( $custom_home_tip, '', false ) . '</label>';
			echo '<br><br>';
			echo '<div class="custom_home_toggle" style="display: none;">';
			echo '<label>Site Address (Home URL) ' . $this->tip( $custom_home_tip, '', false ) . '</label> <input type="text" name="home" value="'. $url .'" size="50" /><br>';
			echo '</div><br><br>';
			
			echo '<b>mySQL Database Settings (unique per WordPress install) ' . $this->tip( 'These settings control where your backed up database will be restored to.  If you are restoring to the same server, the settings below will import the database to your existing WordPress database location, overwriting your existing WordPress database already on the server.  If you are moving to a new host you will need to create a database to import into. The database settings MUST be unique for each WordPress installation.  If you use the same settings for multiple WordPress installations then all blog content and settings will be shared, causing conflicts!', '', false ) . '</b><br><br>';
			echo 'IMPORTANT: Two WordPress installations CANNOT share the same database settings at the same time.  You must create a new database or use a different prefix if you will be using two different WordPress installs simultaneously.<br><br>';
			echo '<label>MySQL Server ' . $this->tip( 'This is the address to the mySQL server where your database will be stored.  99% of the time this is localhost.  The location of your mySQL server will be provided to you by your host if it differs.', '', false ) . '</label> <input type="text" name="db_server" id="mysql_server" value="'.$this->_backupdata['db_server'].'" size="50" /><br>';
			echo '<label>Database Name ' . $this->tip( 'This is the name of the database you want to import your blog into. The database user must have permissions to be able to access this database.  If you are migrating this blog to a new host you will need to create this database (ie using CPanel or phpmyadmin) and create a mysql database user with permissions.', '', false ) . '</label> <input type="text" name="db_name" id="mysql_name" value="'.$this->_backupdata['db_name'].'" size="50" /><br>';
			echo '<label>Database User ' . $this->tip( 'This is the database user account that has permission to access the database name in the input above.  This user must be given permission to this database for the import to work.', '', false ) . '</label> <input type="text" name="db_user" id="mysql_user" value="'.$this->_backupdata['db_user'].'" size="50" /><br>';
			echo '<label>Database Pass ' . $this->tip( 'This is the password for the database user.', '', false ) . '</label> <input type="text" name="db_password" id="mysql_password" value="'.$this->_backupdata['db_password'].'" size="50" /><br>';
			echo '<label>Database Prefix ' . $this->tip( 'This is the prefix given to all tables in the database.  If you are cloning the site on the same server AND the same database name then you will want to change this or else the imported database will overwrite the existing tables.', '', false ) . '</label> <input type="text" name="db_prefix" id="mysql_prefix" id="mysql_prefix" value="' . $this->_backupdata['db_prefix'] . '" size="50" />';
			echo '<br>';
			echo '<div style="font-size: 9px; margin-bottom: 7px;"><label>&nbsp;</label>&nbsp;<a target="_new" href="http://pluginbuddy.com/tutorial-create-database-in-cpanel/">Need help creating a database in cPanel? See this tutorial.</a></div>';
			echo '<label>&nbsp;</label> <input type="button" class="button-secondary" id="ithemes_mysql_test" value="Test database settings ..." /><br>
				<div style="display: none; background-color: #F1EDED; -moz-border-radius:4px 4px 4px 4px; border:1px solid #DFDFDF; margin:10px; padding:3px;" id="ithemes_loading">';
					echo ezimg::genImageTag( 'loading.gif' ) . ' Loading ...';
				echo '</div><br>';
			
			echo ezimg::genImageTag( 'bullet_error.png' ) . ' <b>WARNING:</b> Any existing WordPress installation with this prefix or in this directory may have data overwritten by the import. Make sure you are on the correct server with the correct settings.<br><br><br><br>';
			
			echo '<input type="hidden" name="file" value="' . htmlentities( $this->_options['file'] ) . '" />';
			echo '<input type="submit" name="submit" value="Next Step &raquo;" class="button" />';
			echo '</form>';
		}
		
		function view_step_5() {
			// Set up backup data from the backupbuddy_dat.php.
			$this->load_backup_dat();
			
			if ( isset( $_POST['siteurl'] ) ) { $this->_options['siteurl'] = $_POST['siteurl']; }
			if ( isset( $_POST['home'] ) ) {
				$this->_options['home'] = $_POST['home'];
			} else {
				$this->_options['home'] = $_POST['siteurl'];
			}
			
			if ( isset( $_POST['db_server'] ) ) { $this->_options['db_server'] = $_POST['db_server']; }
			if ( isset( $_POST['db_user'] ) ) { $this->_options['db_user'] = $_POST['db_user']; }
			if ( isset( $_POST['db_password'] ) ) { $this->_options['db_password'] = $_POST['db_password']; }
			if ( isset( $_POST['db_name'] ) ) { $this->_options['db_name'] = $_POST['db_name']; }
			if ( isset( $_POST['db_prefix'] ) ) { $this->_options['db_prefix'] = $_POST['db_prefix']; }
			
			$failed = false;
			
			// Migrate HTACCESS if not disabled.
			if ( $this->_options['skip_htaccess'] == false ) {
				$this->migrate_htaccess();
			}
			
			// Import database unless disabled.
			$db_continue = false;
			if ( false === $this->_options['skip_database_import'] ) {
				// Wipe database if option was selected.
				if ( $this->_options['wipe_database'] == true ) {
					if ( isset( $_GET['db_continue'] ) && ( is_numeric( $_GET['db_continue'] ) ) ) {
						// do nothing
					} else { // dont wipe on substeps of db import.
						echo 'Wiping existing database based on settings ... ';
						$failed = !$this->wipe_database();
						if ( false === $failed ) {
							echo 'Done.<br>';
						} else {
							echo 'Failed.<br>';
						}
					}
				}
				
				// Sanitize db continuation value if needed.
				if ( isset( $_GET['db_continue'] ) && ( is_numeric( $_GET['db_continue'] ) ) ) {
					// do nothing
				} else {
					$_GET['db_continue'] = 0;
				}
				$import_result = $this->restore_database( $_GET['db_continue'] );
				if ( true === $import_result ) {
					$failed = false;
					echo ' Done.<br>';
				} elseif ( $import_result === false ) { // Full failure.
					$failed = true;
					echo ' Failed.<br>';
				} else { // Needs to chunk up DB import and continue...
					$failed = false;
					$db_continue = true;
					// Continue on query $import_result...
					echo 'Next step will begin import on query ' . $import_result . '.<br>';
				}
			} else {
				$failed = false;
				echo 'Skipping database restore based on settings.<br>';
			}
			

			
			if ( false === $failed ) {
				echo '<br><br>';
				if ( $db_continue === true ) {
					echo '<div class="alert">';
					echo 'Your database was too large to import in one step and will be imported in chunks. Please continue the process until this step is finished.</div><br><br>';
					echo 'Please keep continuing until your database has fully imported. This may take a few steps.';
					echo '<form action="?step=5&db_continue=' . $import_result . '" method=post>';
					echo '<input type="hidden" name="options" value="' . htmlspecialchars( serialize( $this->_options ) ) . '" />';
					echo '<br><br><input type="submit" name="submit" class="button-secondary" value="Continue Database Restore &raquo" />';
					echo '</form>';
				} else {
					echo '<div class="alert">';
					echo 'Initial database import complete!</div><br><br>';
					echo 'Next the data in the database will be migrated to account for any file path or URL changes.';
					echo '<form action="?step=6" method=post>';
					echo '<input type="hidden" name="options" value="' . htmlspecialchars( serialize( $this->_options ) ) . '" />';
					echo '<br><br><input type="submit" name="submit" class="button-secondary" value="Finish Database Migration &raquo" />';
					echo '</form>';
				}
			} else {
				echo '<b>Database import failed. Please use your back button to correct any errors.</b>';
			}
		}
		
		function view_step_6() {
			// Set up backup data from the backupbuddy_dat.php.
			$this->load_backup_dat();
			$failed = false;
			
			echo 'Migrating database content ...';
			
			// Migrate DATABASE if not disabled.
			if ( false === $this->_options['skip_database_migration'] ) {
				$failed = !$this->migrate_database();
			} else {
				$failed = false;
				echo 'Skipping database migration based on settings.<br>';
			}
			
			if ( false === $failed ) {
				echo '<br><br>';
				
				echo '<div class="alert">';
				if ( $_POST['type'] == 'migrate' ) {
					echo 'Migration';
				} else {
					echo 'Import';
				}
				echo ' Complete!</div><br><br>';
				echo 'Verify site functionality then delete the backup ZIP file and importbuddy.php from your site root. Leaving these files is a security risk. Leaving the zip file and then subsequently running a BackupBuddy backup will result in excessively large backups as this zip file will be included.';

				echo '<form action="?step=7" method=post>';
				echo '<input type="hidden" name="options" value="' . htmlspecialchars( serialize( $this->_options ) ) . '" />';
				echo '<br><br><input type="submit" name="submit" class="button-secondary" value="Delete import & migration files &raquo" />';
				echo '</form>';
			} else {
				echo '<b>Restore failed. Please use your back button to correct any errors.</b>';
			}
		}

		
		function view_step_7() {
			$this->log( 'Beginning step 7 cleanup!' );
			
			echo 'This step handles cleanup of files. It is common to not be able to delete some files due to permission errors. You may manually delete them or ignore any errors if you wish.<br><br>';
			
			$this->remove_file( $this->_options['file'], 'backup .ZIP file (' . $this->_options['file'] . ')', true );
			
			$this->remove_file( 'importbuddy.php', 'importbuddy.php (this script)', true );
			
			$this->remove_file( 'importbuddy.txt', 'importbuddy.txt log file', true );
			
			// Full backup .sql file
			$this->remove_file( ABSPATH . '/wp-content/uploads/temp_'.$this->_options['zip_id'].'/db.sql', 'db.sql (backup database dump)', false );
			$this->remove_file( ABSPATH . '/wp-content/uploads/temp_'.$this->_options['zip_id'].'/db_1.sql', 'db_1.sql (backup database dump)', false );
			$this->remove_file( ABSPATH . '/wp-content/uploads/backupbuddy_temp/'.$this->_options['zip_id'].'/db_1.sql', 'db_1.sql (backup database dump)', false );
			// DB only sql file
			$this->remove_file( ABSPATH . '/db.sql', 'db.sql (backup database dump)', false );
			$this->remove_file( ABSPATH . '/db_1.sql', 'db_1.sql (backup database dump)', false );
			
			// Full backup dat file
			$this->remove_file( ABSPATH . '/wp-content/uploads/temp_' . $this->_options['zip_id'] . '/backupbuddy_dat.php', 'backupbuddy_dat.php (backup data file)', false );
			$this->remove_file( ABSPATH . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/backupbuddy_dat.php', 'backupbuddy_dat.php (backup data file)', false );
			// DB only dat file
			$this->remove_file( ABSPATH . '/backupbuddy_dat.php', 'backupbuddy_dat.php (backup data file)', false );
			
			$this->remove_file( ABSPATH . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/', 'Temporary backup directory.', false );
			
			echo '<br ><br>';
			echo '<div class="alert">Thank you for choosing BackupBuddy!</div>';
			
			$this->log( 'Finished step 7 cleanup! Kerfuffle!' );
		}
		
		
		function remove_file( $file, $description, $error_on_missing = false ) {
			$this->log( 'Deleting ' . $description . ' ... ' );

			if ( file_exists( $file ) ) {
				chmod( $file, 0755 ); // High permissions to delete.
				echo 'Deleting ' . $description . ' ... ';
				if ( @unlink( $file ) != 1 ) {
					$this->alert( 'File ' . $description . ' couldn\'t be deleted. File permissions (' . $this->get_fileperms( $file ) . ') did not allow it. You may manually delete this file if you wish.', true );
				} else {
					echo ' Done.<br>';
					$this->log( 'Finished deleting ' . $description . '.' );
				}
			} else {
				if ( $error_on_missing === true ) {
					$this->alert( 'File ' . $description . ' was not found to delete. Already deleted?', true );
				} else {
					$this->log( 'File ' . $description . ' was not found to delete. Already deleted?' );
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
		
		function notify_existing_wordpress() {
			if ( file_exists( ABSPATH . '/wp-config.php' ) ) {
				$this->log( 'Found existing WordPress installation.', 'warning' );
				echo '<br><br><div>';
				echo ezimg::genImageTag( 'bullet_error.png' ) . ' WARNING: There appears to already be a WordPress installation at this location. It is recommended that existing WordPress files and database be removed prior to migrating or restoring to avoid conflicts. You should not install WordPress prior to migrating.<br>';
				echo '</div>';
			}
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
				$this->_options['zip_id'] = $posb;
				$this->_options['zip_id'] = strrpos($file,'-')+1;
			} else {
				$this->_options['zip_id'] = $posa;
				$this->_options['zip_id'] = strrpos($file,'_')+1;
			}
			$this->_options['zip_id'] = substr( $this->_options['file'], $this->_options['zip_id'], strlen($this->_options['file'])-$this->_options['zip_id']-4 );
			
			return $this->_options['zip_id'];
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
			$this->log( 'STARTING Loading backup dat file....' );
			$backupdata_file = ABSPATH . '/wp-content/uploads/temp_'. $this->_options['zip_id'] .'/backupbuddy_dat.php'; // Full backup dat file location
			$backupdata_file_new = ABSPATH . '/wp-content/uploads/backupbuddy_temp/'. $this->_options['zip_id'] .'/backupbuddy_dat.php'; // Full backup dat file location
			
			if ( file_exists( $backupdata_file ) ) { // Full backup location.
				$backupdata = file_get_contents( $backupdata_file );
			} elseif ( file_exists( $backupdata_file_new ) ) { // Full backup location.
				$backupdata = file_get_contents( $backupdata_file_new );
			} elseif ( file_exists( ABSPATH . '/backupbuddy_dat.php' ) ) { // DB only location.
				$backupdata = file_get_contents( ABSPATH . '/backupbuddy_dat.php' );
			} else { // Missing.
				$this->alert( 'Error #9003: BackupBuddy data file (backupbuddy_dat.php) missing or unreadable. There may be a problem with the backup file, the files could not be extracted (you may manually extract the zip file in this directory to manually do this portion of restore), or the files were deleted before this portion of the restore was reached.  Start the import process over or try manually extracting (unzipping) the files then starting over. Restore will not continue to protect integrity of any existing data.', true, '9003' );
				die( ' Halted.' );
			}
			
			// Unserialize data; If it fails it then decodes the obscufated data then unserializes it. (new dat file method starting at 2.0).
			if ( false === ( $this->_backupdata = unserialize( $backupdata ) ) ) {
				// Skip first line.
				$second_line_pos = strpos( $backupdata, "\n" ) + 1;
				$backupdata = substr( $backupdata, $second_line_pos );
				
				// Decode back into an array.
				$this->_backupdata = unserialize( base64_decode( $backupdata ) );
			}
			
			$this->log( 'DONE Loading backup dat file.' );
		}
		
		
		/**
		 *	migrate_htaccess()
		 *
		 *	Migrates .htaccess file if it exists.
		 *
		 *	@return		boolean		True on success. Currently always true.
		 *
		 */
		function migrate_htaccess() {
			// If there is no .htaccess file then return.
			if ( !file_exists( ABSPATH . '/.htaccess' ) ) {
				$this->log( 'No htaccess file found. Skipping.' );
				return;
			}
			
			$this->log( 'Migrating htaccess file.' );
			
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
			
			echo 'Checking .htaccess file ... ';
			
			// If the URL (domain and/or URL subdirectory ) has changed, then need to update .htaccess file.
			if ( $newurl !== $oldurl ) {
				echo 'URL is different from source site. Updating .htaccess ... ';
				$this->log( 'HTAccess updating... Old URL: ' . $oldurl . '; New URL: ' . $newurl . '.' );
				
				$rewrite_lines = array();
				$got_rewrite = false;
				$rewrite_path = implode( '/', $newurl );
				$file_array = file( ABSPATH . '/.htaccess' );
				
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
					
				$handling = fopen( ABSPATH . '/.htaccess', 'w');
				fwrite( $handling, implode( $rewrite_lines ) );
				fclose( $handling );
				unset( $handling );
				echo 'Done.<br>';
				
				$this->log( 'Finished migrating htaccess file.' );
			} else {
				echo 'No changes needed. Done.<br>';
				
				$this->log( 'htaccess file did not need changed.' );
			}
			
			return true;
		}
		
		
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
				$this->alert( 'ERROR: Unable to select your specified database. Verify the database name and that you have set up proper permissions for your specified username to access it. Details: ' . mysql_error(), true, '9007' );
				return false;
			}
			
			// Set up character set. Important.
			mysql_query("SET NAMES 'utf8'");
		}
		
		
		/**
		 *	restore_database()
		 *
		 *	Preset the following variables within $this->_options before continuing:
		 *		db_server, db_user, db_password, db_name
		 *
		 *	@return			mixed		true: success, false: failure, integer: query to start next db import chunk on
		 */
		function restore_database( $query_start = 0 ) {
			$this->log( 'Beginning database restoration.' );
			
			$this->connect_database();
			
			// Require a table prefix.
			if ( $this->_options['db_prefix'] == '' ) {
				$this->alert( 'ERROR: A database prefix is required for importing. Details: ' . mysql_error(), true, '9008' );
			}
			
			if ( $query_start > 0 ) {
				echo 'Continuing to restore database dump starting at query ' . $query_start . '. ';
			} else {
				echo 'Restoring database dump ... ';
			}
			
			flush();
			
			// Check number of tables already existing with this prefix. Skips this check on substeps of DB import.
			if ( $query_start == 0 ) {
				$result = mysql_query( "SHOW TABLES LIKE '" . mysql_real_escape_string( $this->_options['db_prefix'] ) . "%'" );
				if ( mysql_num_rows( $result ) > 0 ) {
					//echo ezimg::genImageTag('bullet_error.png').' Found ' . mysql_num_rows( $result ) . ' existing tables with same prefix ... Restore stopped to prevent accidental overwrite of existing data.';
					$this->alert( 'Fatal error. The database already contains a WordPress installation with this prefix (' . mysql_num_rows( $result ) . ' tables). Restore has been stopped to prevent overwriting existing data. Please go back and enter a new database name and/or prefix OR clear our your database prior to restoring again.', true, '9014' );
					return false;
				}
				unset( $result );
			}
			
			
			// Import SQL dump onto new server. NOTE: This data has NOT been migrated. It is identical to the source server still at this point.
			$import_result = $this->import_sql_dump( $query_start );
			
			// CLEANUP
			mysql_close();
			
			//$this->log( 'Finished restoring database.' );
			
			return $import_result;
		}
		
		
		/**
		 *	import_sql_dump()
		 *
		 *	Directly inserts the source SQL dump into the new database.
		 *	Does NOT modify any data or do any migration.
		 *
		 *	@return		boolean		True: success, False: failed.
		 *
		 */
		function import_sql_dump( $query_start = 0 ) {
			// TODO: debugging
			//$query_start = 839;
			
			$this->log( 'Beginning importing of DB dump.' );
			
			$file_stream = false; // Default state.
			if ( file_exists ( ABSPATH . '/wp-content/uploads/temp_' . $this->_options['zip_id'] . '/db.sql' ) ) { // Full backup found.
				$file_stream = fopen( ABSPATH . '/wp-content/uploads/temp_'.$this->_options['zip_id'].'/db.sql', 'r' );
			} elseif ( file_exists ( ABSPATH . '/db.sql' ) ) { // DB-only backup found.
				$file_stream = fopen( ABSPATH . '/db.sql', 'r' );
			} elseif ( file_exists ( ABSPATH . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/db_1.sql' ) ) { // Full backup found. 2.0 method.
				$file_stream = fopen( ABSPATH . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/db_1.sql', 'r' );
			} elseif ( file_exists ( ABSPATH . '/db_1.sql' ) ) { // DB-only backup found. 2.0 method.
				$file_stream = fopen( ABSPATH . '/db_1.sql', 'r' );
			}
			
			if ( false === $file_stream ) {
				$this->alert( 'ERROR: Unable to find any database backup data in the selected backup.', true, '9009' );
				return false;
			}
			
			// Iterate through each full row action and import it one at a time.
			
			$query_count = 0;
			$file_data = '';
			
			while ( ! feof( $file_stream ) ) {
			
				while ( false === strpos( $file_data, "\n" ) ) {
					$file_data .= fread( $file_stream, 4096 );
				}
				
				$queries = explode( "\n", $file_data );
				
				if ( preg_match( "/\n$/", $file_data ) ) {
					$file_data = '';
				} else {
					$file_data = array_pop( $queries );
				}
				
				// TODO: DEBUGGING:
				//$this->_options['max_execution_time'] = 0.41;
				
				// Loops through each full query.
				foreach ( (array) $queries as $query ) {
					if ( $query_count < ( $query_start - 1 ) ) { // Handle skipping any queries up to the point we are at.
						$query_count++;
						continue; // Continue to next foreach iteration.
					} else {
						$query_count++;
					}
					
					$query = trim( $query );
					
					if ( empty( $query ) ) {
						continue;
					}
					
					$result = $this->import_sql_dump_line( $query );
					
					if ( false === $result ) { // Skipped query
						continue;
					}
					
					if ( 0 === ( $query_count % 100 ) ) {
						echo '.';
					}
					if ( 0 === ( $query_count % 6000 ) ) {
						echo "<br>\n";
					}
					
					// If we are within 1 second of reaching maximum PHP runtime then stop here so that it can be picked up in another PHP process...
					if ( ( ( microtime( true ) - $this->time_start ) + 1 ) >= $this->_options['max_execution_time'] ) {
					// TODO: Debugging:
					//if ( ( ( microtime( true ) - $this->time_start ) ) >= $this->_options['max_execution_time'] ) {
						echo ' Exhausted available PHP time to import for this page load... Stopped after query ' . $query_count . '. ';
						
						fclose( $file_stream );
						$this->log( 'Database too large to import in one pass. Breaking into chunks and continuing at query ' . ( $query_count + 1 ) );
						
						return ( $query_count + 1 );
						//break 2;
					}
				}
				
			}
			
			fclose( $file_stream );
			
			$this->log( 'Finished importing of DB dump.' );
			
			echo ' Took ' . round( microtime( true ) - $this->time_start, 3 ) . ' seconds on ' . $query_count . ' queries. ';
			
			return true;
		}
		
		
		/**
		 *	import_sql_dump_line()
		 *
		 *	Imports a line/query into the database.
		 *	Handles using the specified table prefix.
		 *
		 *	$query		string		Query string to run for importing.
		 *	@return		boolean		True=success, False=failed.
		 *
		 */
		function import_sql_dump_line( $query ) {
			$old_prefix = $this->_backupdata['db_prefix'];
			$new_prefix = $this->_options['db_prefix'];
			
			$query_operators = 'INSERT INTO|CREATE TABLE|REFERENCES|CONSTRAINT';
			
			// Replace database prefix in query.
			if ( $old_prefix !== $new_prefix ) {
				$query = preg_replace( "/^($query_operators)(\s+`?)$old_prefix/i", "\${1}\${2}$new_prefix", $query ); // 4-29-11
			}
			
			// Run the query
			// Disabled to prevent from running on EVERY line. Now just running before this. mysql_query("SET NAMES 'utf8'"); // Force UTF8
			$result = mysql_query( $query );
			
			if ( false === $result ) {
				$this->alert( 'ERROR: Unable to import SQL query: ' . mysql_error(), true, '9010' );
				return false;
			} else {
				return true;
			}
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
			$this->log( 'Beginning migration of DB.' );
			$this->log( '_options[] value: ' . serialize( $this->_options ) );
			
			$old_abspath = $this->_backupdata['abspath'];
			//$old_abspath = str_replace( '\\', '\\\\', $this->_backupdata['abspath'] ); // Remove escaping of windows paths. - Caused problems. REMOVE?
			$old_abspath = preg_replace( '|/+$|', '', $old_abspath ); // Remove escaping of windows paths.
			//$new_abspath = str_replace( '\\', '\\\\', ABSPATH ); Caused problems. REMOVE?
			$new_abspath = ABSPATH;
			$this->log( 'ABSPATH change for database ... Old Path: ' . $old_abspath . ', New Path: ' . $new_abspath . '.' );
			
			$old_url = $this->_backupdata['siteurl'];  // the value you want to search for
			
			// If http://www.blah.... then also we will replace http://blah... and vice versa.
			if ( stristr( $old_url, 'http://www.' ) || stristr( $old_url, 'https://www.' ) ) {
				$old_url_alt = str_ireplace( 'http://www.', 'http://', $old_url );
				$old_url_alt = str_ireplace( 'https://www.', 'https://', $old_url_alt );
			} else {
				$old_url_alt = str_ireplace( 'http://', 'http://www.', $old_url );
				$old_url_alt = str_ireplace( 'https://', 'https://www.', $old_url_alt );
			}
			
			$new_url = preg_replace( '|/*$|', '', $this->_options['siteurl'] );  // the value to replace it with
			$this->log( 'URL change for database ... Old URL: ' . $old_url . ', New URL: ' . $new_url . '.' );
			
			$count_tables_checked = 0;
			$count_items_checked = 0;
			$count_items_changed = 0;
			
			// Connect to database.
			$this->connect_database();
			
			flush();
			
			if ( $this->_options['siteurl'] != $this->_options['home'] ) { // Update home url value if it exists since it is custom.
				mysql_query( "UPDATE `" . $this->_options['db_prefix'] . "options` SET option_value='" . mysql_real_escape_string( $this->_options['home'] ) . "' WHERE option_name='home' LIMIT 1" );
			}
			
			$row_loop = 0;
			
			// Loop through the tables matching this prefix. Does NOT change data in other tables.
			// This changes actual data on a column by column basis for very row in every table.
			$main_result = mysql_query( "SHOW TABLES LIKE '" . $this->_options['db_prefix'] . "%'" );
			//echo 'Found ' . mysql_num_rows( $main_result ) . ' WordPress tables. ';
			while ( $table = mysql_fetch_row( $main_result ) ) {
				
				$count_tables_checked++;
				
				if ( $table[0] == $this->_options['db_prefix'] . 'posts' ) { // For posts table use SQL statement to do simple string replacement of URLs.
					echo 'Updating posts table Site URLs... ';
					mysql_query( "UPDATE `" . $table[0] . "` SET post_content = replace(post_content, '" . $old_url . "', '" . $new_url . "');" );
					echo 'Done.<br>';
					
					if ( $this->_options['siteurl'] != $this->_options['home'] ) { // Update home url value if it exists since it is custom.
						echo 'Updating posts table Home URLs... ';
						if ( empty( $this->_backupdata['homeurl'] ) ) {
							echo 'Failed. Your current backup does not include a home URL. Make a new backup with the latest BackupBuddy before migrating.';
						} else {
							mysql_query( "UPDATE `" . $table[0] . "` SET post_content = replace(post_content, '" . $this->_backupdata['homeurl'] . "', '" . $this->_options['home'] . "');" );
							echo 'Done.<br>';
						}
					}
				} else { // Must handle serialized data in any place for non-posts tables.
					$fields_list = mysql_query( "DESCRIBE `" . $table[0] . "`" );
					$index_fields = '';  // Reset fields for each table.
					$column_name = '';
					$table_index = '';
					$i = 0;
					
					while ( $field_rows = mysql_fetch_array( $fields_list ) ) {
						$column_name[$i++] = $field_rows['Field'];
						if ( $field_rows['Key'] == 'PRI' ) {
							$table_index[$i] = true;
						}
					}
					
					$data = mysql_query( "SELECT * FROM `" . $table[0] . "`" );
					if (!$data) {
						$this->alert( 'ERROR #44545343 ... SQL ERROR: ' . mysql_error(), true );
					}
					
					while ( $row = mysql_fetch_array( $data ) ) {
						$need_to_update = false;
						$UPDATE_SQL = 'UPDATE `' . $table[0] . '` SET ';
						$WHERE_SQL = ' WHERE ';
						
						$j = 0;
						foreach ( $column_name as $current_column ) {
							$j++;
							$count_items_checked++;
							$row_loop++;
							if ( $row_loop > 5000 ) {
								$row_loop = 0;
								echo '.';
								flush();
							}
							
							$data_to_fix = $row[$current_column];
							$edited_data = $data_to_fix;						// Set the same now - if they're different later we know we need to update.
							
							$unserialized = unserialize( $data_to_fix );		// unserialise - if false returned we don't try to process it as serialised.
							
							if ( $unserialized !== false ) {					// Serialized data so recursive array replace.
								$this->log ( 'Serialized ORIGINAL data: ' . $data_to_fix );
								$this->log( 'THIS IS ANGRY: ' . print_r( $unserialized, true ) );
								$this->recursive_array_replace( $old_url, $new_url, $unserialized );
								$this->recursive_array_replace( $old_url_alt, $new_url, $unserialized );
								$this->recursive_array_replace( $old_abspath, $new_abspath, $unserialized );
								$edited_data = serialize( $unserialized );
								$this->log ( 'Serialized EDITED data: ' . $edited_data );
							}	else {											// Not serialized data so just string replace.
								//if ( is_string( $data_to_fix ) ) {
								$this->log ( 'Not serialized ORIGINAL data: ' . $data_to_fix );
								$edited_data = str_replace( $old_url, $new_url, $data_to_fix );
								$edited_data = str_replace( $old_url_alt, $new_url, $edited_data );
								$edited_data = str_replace( $old_abspath, $new_abspath, $edited_data );
								$this->log ( 'Not serialized EDITED data: ' . $edited_data );
								//}
							}
								
							if ( $data_to_fix != $edited_data ) {				// If they're not the same, we need to add them to the update string.
								$this->log( 'Data changed!' );
								$count_items_changed++;
								if ( $need_to_update != false ) {				// If this isn't our first time here, we need to add a comma.
									$UPDATE_SQL = $UPDATE_SQL . ',';
								}
								$UPDATE_SQL = $UPDATE_SQL . ' ' . $current_column . ' = "' . mysql_real_escape_string( $edited_data ) . '"';
								$need_to_update = true;							// Only set if we need to update - avoids wasted UPDATE statements.
							}
							
							if ( $table_index[$j] ) {
								$WHERE_SQL = $WHERE_SQL . $current_column . ' = "' . $row[$current_column] . '" AND ';
							}
						}
						
						if ( $need_to_update ) {
							$this->log( 'Updated data.' );
							$WHERE_SQL = substr( $WHERE_SQL , 0, -4 );				// Strip off the excess AND - the easiest way to code this without extra flags, etc.
							$UPDATE_SQL = $UPDATE_SQL . $WHERE_SQL;
							$result = mysql_query( $UPDATE_SQL );
							if ( !$result ) {
								$this->alert( 'ERROR: mysql error updating db: ' . mysql_error() . '. SQL Query that failed on next line:', true );
								$this->alert( $UPDATE_SQL, true );
							} 
						}
						
					}
				} // end non-posts table updating.
			}
			
			unset( $main_result );
			
			// Update table prefixes in some WordPress meta data.
			$old_prefix = $this->_backupdata['db_prefix'];
			$new_prefix = mysql_real_escape_string( $this->_options['db_prefix'] );
			if ($old_prefix != $new_prefix ) {
				mysql_query("UPDATE `".$new_prefix."usermeta` SET meta_key = REPLACE(meta_key, '".$old_prefix."', '".$new_prefix."' );");
				mysql_query("UPDATE `".$new_prefix."options` SET option_name = '".$new_prefix."user_roles' WHERE option_name ='".$old_prefix."user_roles' AND blog_id='0';");
				echo ' Updated prefix META data. ';
			}
			
			/*
			$old_blogname = $this->_backupdata['blogname'];
			$new_blogname = mysql_real_escape_string( $_POST['blogname'] );
			if ($old_blogname != $new_blogname ) {
				mysql_query( "UPDATE ".$new_prefix."options SET option_value = '" . $new_blogname . "' WHERE option_name='blogname' LIMIT 1;" );
				echo 'Updated blog name.';
			}
			
			$old_blogdescription = $this->_backupdata['blogdescription'];
			$new_blogdescription = mysql_real_escape_string( $_POST['blogdescription'] );
			if ($old_blogdescription != $new_blogdescription ) {
				mysql_query( "UPDATE ".$new_prefix."options SET option_value = '" . $new_blogdescription . "' WHERE option_name='blogdescription' LIMIT 1;" );
				echo 'Updated blog description.';
			}
			*/
			
			echo '<br>Checked ' . $count_tables_checked . ' tables & ' . $count_items_checked . ' items. ' . $count_items_changed . ' items updated. Done.<br>';
			
			$this->log( 'Finished migration of DB.' );
			
			// Migrate WP config.
			$this->migrate_wp_config();
			
			return true;
		}
		
		
		function wipe_database() {
			$this->log( 'Beginning wipe of database.' );
			$this->log( '_options[] value: ' . serialize( $this->_options ) );
			
			// Connect to database.
			$this->connect_database();
			
			$result = mysql_query( 'SHOW TABLES' );
			echo 'Dropping ' . mysql_num_rows( $result ) . ' tables ... ';
			while( $row = mysql_fetch_row( $result ) ) {
				mysql_query( 'DROP TABLE `' . $row[0] . '`' );
			}
			mysql_free_result( $result ); // Free memory.
			
			return true;
		}
		
		
		/**
		 *	print_html_header()
		 *
		 *	Output HTML header wrapper to browser.
		 *
		 */
		function print_html_header() {
			?><html>
				<head>
					<title>BackupBuddy importbuddy.php by PluginBuddy.com</title>
				</head>
				<style type="text/css">
					body {
						background-color: #FFFFFF;
						font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
						font-size: 12px;
						color: #464646;
						padding: 30px;
						text-align: center;
					}
					h1 { color: #464646; font-size: 24px; font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif; font-style: italic; }
					h2 { color: #464646; font-size: 20px; font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif; font-style: italic; }
					.wrap {
						margin-top: 10px;
						min-height: 200px;
						width: 600px;
						position: relative;
						text-align: left;
						margin-left: auto;
						margin-right: auto;
					}
					.innerwrap {
						padding: 15px;
						width: 500px;
					}
					img {
						vertical-align: -2px;
					}
					.menu {
						padding: 8px;
						text-align: center;
						background-color: #CCCCCC;
						font-weight: bold;
					}
					input[type="text"] {
						margin: 3px;
						padding: 3px;
						-moz-border-radius:4px 4px 4px 4px;
						border-style:solid;
						border-width:1px;
						border-color: #DFDFDF;
					}
					.button {
						position: absolute;
						bottom: 15px;
						left: 37%;
						background:url("?ezimg=button-grad.png") repeat-x scroll left top #21759B;
						border-color:#298CBA;
						color:#FFFFFF;
						font-weight:bold;
						text-shadow:0 -1px 0 rgba(0, 0, 0, 0.3);
						-moz-border-radius:11px 11px 11px 11px;
						-moz-box-sizing:content-box;
						border-style:solid;
						border-width:1px;
						cursor:pointer;
						font-size:11px !important;
						line-height:14px;
						padding:2px 8px;
						text-decoration:none;
						font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
						font-size:13px;
					}
					.button:hover {
						border-color: #13455B;
					}
					.button-secondary {
						background:url("?ezimg=white-grad.png") repeat-x scroll left top #21759B;
						border-color:#BBBBBB;
						color:#464646;
						-moz-border-radius:11px 11px 11px 11px;
						-moz-box-sizing:content-box;
						border-style:solid;
						border-width:1px;
						cursor:pointer;
						font-size:11px !important;
						line-height:14px;
						padding:2px 8px;
						text-decoration:none;
						font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
						font-size:13px;
					}
					.button-secondary:hover {
						border-color: #13455B;
					}
					.alert {
						background-color:#FFFFE0;
						border: 1px solid #E6DB55;
						font-weight: bold;
						-moz-border-radius:3px 3px 3px 3px;
						padding: 5px;
					}
					label {
						float: left;
						width: 150px;
						margin-top: 7px;
					}
					.toggle {
						cursor: pointer;
					}
					.toggled {
						display: none;
						border: 1px solid #CCCCCC;
						margin: 4px;
						padding: 8px;
						-moz-border-radius:4px 4px 4px 4px;
					}
					
					#tooltip {
						position: absolute;
						z-index: 3000;
						border: 1px solid #111;
						background-color: #eee;
						padding: 5px;
						max-width: 300px;
						opacity: 0.85;
						border-radius: 5px;
						-webkit-border-radius: 5px;
						-moz-border-radius: 5px;
					}
					#tooltip h3, #tooltip div { margin: 0; }
					.light {
						color: #AFAFAF;
					}
				</style>
				<script type="text/javascript">
					(function(){var l=this,g,y=l.jQuery,p=l.$,o=l.jQuery=l.$=function(E,F){return new o.fn.init(E,F)},D=/^[^<]*(<(.|\s)+>)[^>]*$|^#([\w-]+)$/,f=/^.[^:#\[\.,]*$/;o.fn=o.prototype={init:function(E,H){E=E||document;if(E.nodeType){this[0]=E;this.length=1;this.context=E;return this}if(typeof E==="string"){var G=D.exec(E);if(G&&(G[1]||!H)){if(G[1]){E=o.clean([G[1]],H)}else{var I=document.getElementById(G[3]);if(I&&I.id!=G[3]){return o().find(E)}var F=o(I||[]);F.context=document;F.selector=E;return F}}else{return o(H).find(E)}}else{if(o.isFunction(E)){return o(document).ready(E)}}if(E.selector&&E.context){this.selector=E.selector;this.context=E.context}return this.setArray(o.isArray(E)?E:o.makeArray(E))},selector:"",jquery:"1.3.2",size:function(){return this.length},get:function(E){return E===g?Array.prototype.slice.call(this):this[E]},pushStack:function(F,H,E){var G=o(F);G.prevObject=this;G.context=this.context;if(H==="find"){G.selector=this.selector+(this.selector?" ":"")+E}else{if(H){G.selector=this.selector+"."+H+"("+E+")"}}return G},setArray:function(E){this.length=0;Array.prototype.push.apply(this,E);return this},each:function(F,E){return o.each(this,F,E)},index:function(E){return o.inArray(E&&E.jquery?E[0]:E,this)},attr:function(F,H,G){var E=F;if(typeof F==="string"){if(H===g){return this[0]&&o[G||"attr"](this[0],F)}else{E={};E[F]=H}}return this.each(function(I){for(F in E){o.attr(G?this.style:this,F,o.prop(this,E[F],G,I,F))}})},css:function(E,F){if((E=="width"||E=="height")&&parseFloat(F)<0){F=g}return this.attr(E,F,"curCSS")},text:function(F){if(typeof F!=="object"&&F!=null){return this.empty().append((this[0]&&this[0].ownerDocument||document).createTextNode(F))}var E="";o.each(F||this,function(){o.each(this.childNodes,function(){if(this.nodeType!=8){E+=this.nodeType!=1?this.nodeValue:o.fn.text([this])}})});return E},wrapAll:function(E){if(this[0]){var F=o(E,this[0].ownerDocument).clone();if(this[0].parentNode){F.insertBefore(this[0])}F.map(function(){var G=this;while(G.firstChild){G=G.firstChild}return G}).append(this)}return this},wrapInner:function(E){return this.each(function(){o(this).contents().wrapAll(E)})},wrap:function(E){return this.each(function(){o(this).wrapAll(E)})},append:function(){return this.domManip(arguments,true,function(E){if(this.nodeType==1){this.appendChild(E)}})},prepend:function(){return this.domManip(arguments,true,function(E){if(this.nodeType==1){this.insertBefore(E,this.firstChild)}})},before:function(){return this.domManip(arguments,false,function(E){this.parentNode.insertBefore(E,this)})},after:function(){return this.domManip(arguments,false,function(E){this.parentNode.insertBefore(E,this.nextSibling)})},end:function(){return this.prevObject||o([])},push:[].push,sort:[].sort,splice:[].splice,find:function(E){if(this.length===1){var F=this.pushStack([],"find",E);F.length=0;o.find(E,this[0],F);return F}else{return this.pushStack(o.unique(o.map(this,function(G){return o.find(E,G)})),"find",E)}},clone:function(G){var E=this.map(function(){if(!o.support.noCloneEvent&&!o.isXMLDoc(this)){var I=this.outerHTML;if(!I){var J=this.ownerDocument.createElement("div");J.appendChild(this.cloneNode(true));I=J.innerHTML}return o.clean([I.replace(/ jQuery\d+="(?:\d+|null)"/g,"").replace(/^\s*/,"")])[0]}else{return this.cloneNode(true)}});if(G===true){var H=this.find("*").andSelf(),F=0;E.find("*").andSelf().each(function(){if(this.nodeName!==H[F].nodeName){return}var I=o.data(H[F],"events");for(var K in I){for(var J in I[K]){o.event.add(this,K,I[K][J],I[K][J].data)}}F++})}return E},filter:function(E){return this.pushStack(o.isFunction(E)&&o.grep(this,function(G,F){return E.call(G,F)})||o.multiFilter(E,o.grep(this,function(F){return F.nodeType===1})),"filter",E)},closest:function(E){var G=o.expr.match.POS.test(E)?o(E):null,F=0;return this.map(function(){var H=this;while(H&&H.ownerDocument){if(G?G.index(H)>-1:o(H).is(E)){o.data(H,"closest",F);return H}H=H.parentNode;F++}})},not:function(E){if(typeof E==="string"){if(f.test(E)){return this.pushStack(o.multiFilter(E,this,true),"not",E)}else{E=o.multiFilter(E,this)}}var F=E.length&&E[E.length-1]!==g&&!E.nodeType;return this.filter(function(){return F?o.inArray(this,E)<0:this!=E})},add:function(E){return this.pushStack(o.unique(o.merge(this.get(),typeof E==="string"?o(E):o.makeArray(E))))},is:function(E){return !!E&&o.multiFilter(E,this).length>0},hasClass:function(E){return !!E&&this.is("."+E)},val:function(K){if(K===g){var E=this[0];if(E){if(o.nodeName(E,"option")){return(E.attributes.value||{}).specified?E.value:E.text}if(o.nodeName(E,"select")){var I=E.selectedIndex,L=[],M=E.options,H=E.type=="select-one";if(I<0){return null}for(var F=H?I:0,J=H?I+1:M.length;F<J;F++){var G=M[F];if(G.selected){K=o(G).val();if(H){return K}L.push(K)}}return L}return(E.value||"").replace(/\r/g,"")}return g}if(typeof K==="number"){K+=""}return this.each(function(){if(this.nodeType!=1){return}if(o.isArray(K)&&/radio|checkbox/.test(this.type)){this.checked=(o.inArray(this.value,K)>=0||o.inArray(this.name,K)>=0)}else{if(o.nodeName(this,"select")){var N=o.makeArray(K);o("option",this).each(function(){this.selected=(o.inArray(this.value,N)>=0||o.inArray(this.text,N)>=0)});if(!N.length){this.selectedIndex=-1}}else{this.value=K}}})},html:function(E){return E===g?(this[0]?this[0].innerHTML.replace(/ jQuery\d+="(?:\d+|null)"/g,""):null):this.empty().append(E)},replaceWith:function(E){return this.after(E).remove()},eq:function(E){return this.slice(E,+E+1)},slice:function(){return this.pushStack(Array.prototype.slice.apply(this,arguments),"slice",Array.prototype.slice.call(arguments).join(","))},map:function(E){return this.pushStack(o.map(this,function(G,F){return E.call(G,F,G)}))},andSelf:function(){return this.add(this.prevObject)},domManip:function(J,M,L){if(this[0]){var I=(this[0].ownerDocument||this[0]).createDocumentFragment(),F=o.clean(J,(this[0].ownerDocument||this[0]),I),H=I.firstChild;if(H){for(var G=0,E=this.length;G<E;G++){L.call(K(this[G],H),this.length>1||G>0?I.cloneNode(true):I)}}if(F){o.each(F,z)}}return this;function K(N,O){return M&&o.nodeName(N,"table")&&o.nodeName(O,"tr")?(N.getElementsByTagName("tbody")[0]||N.appendChild(N.ownerDocument.createElement("tbody"))):N}}};o.fn.init.prototype=o.fn;function z(E,F){if(F.src){o.ajax({url:F.src,async:false,dataType:"script"})}else{o.globalEval(F.text||F.textContent||F.innerHTML||"")}if(F.parentNode){F.parentNode.removeChild(F)}}function e(){return +new Date}o.extend=o.fn.extend=function(){var J=arguments[0]||{},H=1,I=arguments.length,E=false,G;if(typeof J==="boolean"){E=J;J=arguments[1]||{};H=2}if(typeof J!=="object"&&!o.isFunction(J)){J={}}if(I==H){J=this;--H}for(;H<I;H++){if((G=arguments[H])!=null){for(var F in G){var K=J[F],L=G[F];if(J===L){continue}if(E&&L&&typeof L==="object"&&!L.nodeType){J[F]=o.extend(E,K||(L.length!=null?[]:{}),L)}else{if(L!==g){J[F]=L}}}}}return J};var b=/z-?index|font-?weight|opacity|zoom|line-?height/i,q=document.defaultView||{},s=Object.prototype.toString;o.extend({noConflict:function(E){l.$=p;if(E){l.jQuery=y}return o},isFunction:function(E){return s.call(E)==="[object Function]"},isArray:function(E){return s.call(E)==="[object Array]"},isXMLDoc:function(E){return E.nodeType===9&&E.documentElement.nodeName!=="HTML"||!!E.ownerDocument&&o.isXMLDoc(E.ownerDocument)},globalEval:function(G){if(G&&/\S/.test(G)){var F=document.getElementsByTagName("head")[0]||document.documentElement,E=document.createElement("script");E.type="text/javascript";if(o.support.scriptEval){E.appendChild(document.createTextNode(G))}else{E.text=G}F.insertBefore(E,F.firstChild);F.removeChild(E)}},nodeName:function(F,E){return F.nodeName&&F.nodeName.toUpperCase()==E.toUpperCase()},each:function(G,K,F){var E,H=0,I=G.length;if(F){if(I===g){for(E in G){if(K.apply(G[E],F)===false){break}}}else{for(;H<I;){if(K.apply(G[H++],F)===false){break}}}}else{if(I===g){for(E in G){if(K.call(G[E],E,G[E])===false){break}}}else{for(var J=G[0];H<I&&K.call(J,H,J)!==false;J=G[++H]){}}}return G},prop:function(H,I,G,F,E){if(o.isFunction(I)){I=I.call(H,F)}return typeof I==="number"&&G=="curCSS"&&!b.test(E)?I+"px":I},className:{add:function(E,F){o.each((F||"").split(/\s+/),function(G,H){if(E.nodeType==1&&!o.className.has(E.className,H)){E.className+=(E.className?" ":"")+H}})},remove:function(E,F){if(E.nodeType==1){E.className=F!==g?o.grep(E.className.split(/\s+/),function(G){return !o.className.has(F,G)}).join(" "):""}},has:function(F,E){return F&&o.inArray(E,(F.className||F).toString().split(/\s+/))>-1}},swap:function(H,G,I){var E={};for(var F in G){E[F]=H.style[F];H.style[F]=G[F]}I.call(H);for(var F in G){H.style[F]=E[F]}},css:function(H,F,J,E){if(F=="width"||F=="height"){var L,G={position:"absolute",visibility:"hidden",display:"block"},K=F=="width"?["Left","Right"]:["Top","Bottom"];function I(){L=F=="width"?H.offsetWidth:H.offsetHeight;if(E==="border"){return}o.each(K,function(){if(!E){L-=parseFloat(o.curCSS(H,"padding"+this,true))||0}if(E==="margin"){L+=parseFloat(o.curCSS(H,"margin"+this,true))||0}else{L-=parseFloat(o.curCSS(H,"border"+this+"Width",true))||0}})}if(H.offsetWidth!==0){I()}else{o.swap(H,G,I)}return Math.max(0,Math.round(L))}return o.curCSS(H,F,J)},curCSS:function(I,F,G){var L,E=I.style;if(F=="opacity"&&!o.support.opacity){L=o.attr(E,"opacity");return L==""?"1":L}if(F.match(/float/i)){F=w}if(!G&&E&&E[F]){L=E[F]}else{if(q.getComputedStyle){if(F.match(/float/i)){F="float"}F=F.replace(/([A-Z])/g,"-$1").toLowerCase();var M=q.getComputedStyle(I,null);if(M){L=M.getPropertyValue(F)}if(F=="opacity"&&L==""){L="1"}}else{if(I.currentStyle){var J=F.replace(/\-(\w)/g,function(N,O){return O.toUpperCase()});L=I.currentStyle[F]||I.currentStyle[J];if(!/^\d+(px)?$/i.test(L)&&/^\d/.test(L)){var H=E.left,K=I.runtimeStyle.left;I.runtimeStyle.left=I.currentStyle.left;E.left=L||0;L=E.pixelLeft+"px";E.left=H;I.runtimeStyle.left=K}}}}return L},clean:function(F,K,I){K=K||document;if(typeof K.createElement==="undefined"){K=K.ownerDocument||K[0]&&K[0].ownerDocument||document}if(!I&&F.length===1&&typeof F[0]==="string"){var H=/^<(\w+)\s*\/?>$/.exec(F[0]);if(H){return[K.createElement(H[1])]}}var G=[],E=[],L=K.createElement("div");o.each(F,function(P,S){if(typeof S==="number"){S+=""}if(!S){return}if(typeof S==="string"){S=S.replace(/(<(\w+)[^>]*?)\/>/g,function(U,V,T){return T.match(/^(abbr|br|col|img|input|link|meta|param|hr|area|embed)$/i)?U:V+"></"+T+">"});var O=S.replace(/^\s+/,"").substring(0,10).toLowerCase();var Q=!O.indexOf("<opt")&&[1,"<select multiple='multiple'>","</select>"]||!O.indexOf("<leg")&&[1,"<fieldset>","</fieldset>"]||O.match(/^<(thead|tbody|tfoot|colg|cap)/)&&[1,"<table>","</table>"]||!O.indexOf("<tr")&&[2,"<table><tbody>","</tbody></table>"]||(!O.indexOf("<td")||!O.indexOf("<th"))&&[3,"<table><tbody><tr>","</tr></tbody></table>"]||!O.indexOf("<col")&&[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"]||!o.support.htmlSerialize&&[1,"div<div>","</div>"]||[0,"",""];L.innerHTML=Q[1]+S+Q[2];while(Q[0]--){L=L.lastChild}if(!o.support.tbody){var R=/<tbody/i.test(S),N=!O.indexOf("<table")&&!R?L.firstChild&&L.firstChild.childNodes:Q[1]=="<table>"&&!R?L.childNodes:[];for(var M=N.length-1;M>=0;--M){if(o.nodeName(N[M],"tbody")&&!N[M].childNodes.length){N[M].parentNode.removeChild(N[M])}}}if(!o.support.leadingWhitespace&&/^\s/.test(S)){L.insertBefore(K.createTextNode(S.match(/^\s*/)[0]),L.firstChild)}S=o.makeArray(L.childNodes)}if(S.nodeType){G.push(S)}else{G=o.merge(G,S)}});if(I){for(var J=0;G[J];J++){if(o.nodeName(G[J],"script")&&(!G[J].type||G[J].type.toLowerCase()==="text/javascript")){E.push(G[J].parentNode?G[J].parentNode.removeChild(G[J]):G[J])}else{if(G[J].nodeType===1){G.splice.apply(G,[J+1,0].concat(o.makeArray(G[J].getElementsByTagName("script"))))}I.appendChild(G[J])}}return E}return G},attr:function(J,G,K){if(!J||J.nodeType==3||J.nodeType==8){return g}var H=!o.isXMLDoc(J),L=K!==g;G=H&&o.props[G]||G;if(J.tagName){var F=/href|src|style/.test(G);if(G=="selected"&&J.parentNode){J.parentNode.selectedIndex}if(G in J&&H&&!F){if(L){if(G=="type"&&o.nodeName(J,"input")&&J.parentNode){throw"type property can't be changed"}J[G]=K}if(o.nodeName(J,"form")&&J.getAttributeNode(G)){return J.getAttributeNode(G).nodeValue}if(G=="tabIndex"){var I=J.getAttributeNode("tabIndex");return I&&I.specified?I.value:J.nodeName.match(/(button|input|object|select|textarea)/i)?0:J.nodeName.match(/^(a|area)$/i)&&J.href?0:g}return J[G]}if(!o.support.style&&H&&G=="style"){return o.attr(J.style,"cssText",K)}if(L){J.setAttribute(G,""+K)}var E=!o.support.hrefNormalized&&H&&F?J.getAttribute(G,2):J.getAttribute(G);return E===null?g:E}if(!o.support.opacity&&G=="opacity"){if(L){J.zoom=1;J.filter=(J.filter||"").replace(/alpha\([^)]*\)/,"")+(parseInt(K)+""=="NaN"?"":"alpha(opacity="+K*100+")")}return J.filter&&J.filter.indexOf("opacity=")>=0?(parseFloat(J.filter.match(/opacity=([^)]*)/)[1])/100)+"":""}G=G.replace(/-([a-z])/ig,function(M,N){return N.toUpperCase()});if(L){J[G]=K}return J[G]},trim:function(E){return(E||"").replace(/^\s+|\s+$/g,"")},makeArray:function(G){var E=[];if(G!=null){var F=G.length;if(F==null||typeof G==="string"||o.isFunction(G)||G.setInterval){E[0]=G}else{while(F){E[--F]=G[F]}}}return E},inArray:function(G,H){for(var E=0,F=H.length;E<F;E++){if(H[E]===G){return E}}return -1},merge:function(H,E){var F=0,G,I=H.length;if(!o.support.getAll){while((G=E[F++])!=null){if(G.nodeType!=8){H[I++]=G}}}else{while((G=E[F++])!=null){H[I++]=G}}return H},unique:function(K){var F=[],E={};try{for(var G=0,H=K.length;G<H;G++){var J=o.data(K[G]);if(!E[J]){E[J]=true;F.push(K[G])}}}catch(I){F=K}return F},grep:function(F,J,E){var G=[];for(var H=0,I=F.length;H<I;H++){if(!E!=!J(F[H],H)){G.push(F[H])}}return G},map:function(E,J){var F=[];for(var G=0,H=E.length;G<H;G++){var I=J(E[G],G);if(I!=null){F[F.length]=I}}return F.concat.apply([],F)}});var C=navigator.userAgent.toLowerCase();o.browser={version:(C.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/)||[0,"0"])[1],safari:/webkit/.test(C),opera:/opera/.test(C),msie:/msie/.test(C)&&!/opera/.test(C),mozilla:/mozilla/.test(C)&&!/(compatible|webkit)/.test(C)};o.each({parent:function(E){return E.parentNode},parents:function(E){return o.dir(E,"parentNode")},next:function(E){return o.nth(E,2,"nextSibling")},prev:function(E){return o.nth(E,2,"previousSibling")},nextAll:function(E){return o.dir(E,"nextSibling")},prevAll:function(E){return o.dir(E,"previousSibling")},siblings:function(E){return o.sibling(E.parentNode.firstChild,E)},children:function(E){return o.sibling(E.firstChild)},contents:function(E){return o.nodeName(E,"iframe")?E.contentDocument||E.contentWindow.document:o.makeArray(E.childNodes)}},function(E,F){o.fn[E]=function(G){var H=o.map(this,F);if(G&&typeof G=="string"){H=o.multiFilter(G,H)}return this.pushStack(o.unique(H),E,G)}});o.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(E,F){o.fn[E]=function(G){var J=[],L=o(G);for(var K=0,H=L.length;K<H;K++){var I=(K>0?this.clone(true):this).get();o.fn[F].apply(o(L[K]),I);J=J.concat(I)}return this.pushStack(J,E,G)}});o.each({removeAttr:function(E){o.attr(this,E,"");if(this.nodeType==1){this.removeAttribute(E)}},addClass:function(E){o.className.add(this,E)},removeClass:function(E){o.className.remove(this,E)},toggleClass:function(F,E){if(typeof E!=="boolean"){E=!o.className.has(this,F)}o.className[E?"add":"remove"](this,F)},remove:function(E){if(!E||o.filter(E,[this]).length){o("*",this).add([this]).each(function(){o.event.remove(this);o.removeData(this)});if(this.parentNode){this.parentNode.removeChild(this)}}},empty:function(){o(this).children().remove();while(this.firstChild){this.removeChild(this.firstChild)}}},function(E,F){o.fn[E]=function(){return this.each(F,arguments)}});function j(E,F){return E[0]&&parseInt(o.curCSS(E[0],F,true),10)||0}var h="jQuery"+e(),v=0,A={};o.extend({cache:{},data:function(F,E,G){F=F==l?A:F;var H=F[h];if(!H){H=F[h]=++v}if(E&&!o.cache[H]){o.cache[H]={}}if(G!==g){o.cache[H][E]=G}return E?o.cache[H][E]:H},removeData:function(F,E){F=F==l?A:F;var H=F[h];if(E){if(o.cache[H]){delete o.cache[H][E];E="";for(E in o.cache[H]){break}if(!E){o.removeData(F)}}}else{try{delete F[h]}catch(G){if(F.removeAttribute){F.removeAttribute(h)}}delete o.cache[H]}},queue:function(F,E,H){if(F){E=(E||"fx")+"queue";var G=o.data(F,E);if(!G||o.isArray(H)){G=o.data(F,E,o.makeArray(H))}else{if(H){G.push(H)}}}return G},dequeue:function(H,G){var E=o.queue(H,G),F=E.shift();if(!G||G==="fx"){F=E[0]}if(F!==g){F.call(H)}}});o.fn.extend({data:function(E,G){var H=E.split(".");H[1]=H[1]?"."+H[1]:"";if(G===g){var F=this.triggerHandler("getData"+H[1]+"!",[H[0]]);if(F===g&&this.length){F=o.data(this[0],E)}return F===g&&H[1]?this.data(H[0]):F}else{return this.trigger("setData"+H[1]+"!",[H[0],G]).each(function(){o.data(this,E,G)})}},removeData:function(E){return this.each(function(){o.removeData(this,E)})},queue:function(E,F){if(typeof E!=="string"){F=E;E="fx"}if(F===g){return o.queue(this[0],E)}return this.each(function(){var G=o.queue(this,E,F);if(E=="fx"&&G.length==1){G[0].call(this)}})},dequeue:function(E){return this.each(function(){o.dequeue(this,E)})}});
					
					(function(){var R=/((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^[\]]*\]|['"][^'"]*['"]|[^[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?/g,L=0,H=Object.prototype.toString;var F=function(Y,U,ab,ac){ab=ab||[];U=U||document;if(U.nodeType!==1&&U.nodeType!==9){return[]}if(!Y||typeof Y!=="string"){return ab}var Z=[],W,af,ai,T,ad,V,X=true;R.lastIndex=0;while((W=R.exec(Y))!==null){Z.push(W[1]);if(W[2]){V=RegExp.rightContext;break}}if(Z.length>1&&M.exec(Y)){if(Z.length===2&&I.relative[Z[0]]){af=J(Z[0]+Z[1],U)}else{af=I.relative[Z[0]]?[U]:F(Z.shift(),U);while(Z.length){Y=Z.shift();if(I.relative[Y]){Y+=Z.shift()}af=J(Y,af)}}}else{var ae=ac?{expr:Z.pop(),set:E(ac)}:F.find(Z.pop(),Z.length===1&&U.parentNode?U.parentNode:U,Q(U));af=F.filter(ae.expr,ae.set);if(Z.length>0){ai=E(af)}else{X=false}while(Z.length){var ah=Z.pop(),ag=ah;if(!I.relative[ah]){ah=""}else{ag=Z.pop()}if(ag==null){ag=U}I.relative[ah](ai,ag,Q(U))}}if(!ai){ai=af}if(!ai){throw"Syntax error, unrecognized expression: "+(ah||Y)}if(H.call(ai)==="[object Array]"){if(!X){ab.push.apply(ab,ai)}else{if(U.nodeType===1){for(var aa=0;ai[aa]!=null;aa++){if(ai[aa]&&(ai[aa]===true||ai[aa].nodeType===1&&K(U,ai[aa]))){ab.push(af[aa])}}}else{for(var aa=0;ai[aa]!=null;aa++){if(ai[aa]&&ai[aa].nodeType===1){ab.push(af[aa])}}}}}else{E(ai,ab)}if(V){F(V,U,ab,ac);if(G){hasDuplicate=false;ab.sort(G);if(hasDuplicate){for(var aa=1;aa<ab.length;aa++){if(ab[aa]===ab[aa-1]){ab.splice(aa--,1)}}}}}return ab};F.matches=function(T,U){return F(T,null,null,U)};F.find=function(aa,T,ab){var Z,X;if(!aa){return[]}for(var W=0,V=I.order.length;W<V;W++){var Y=I.order[W],X;if((X=I.match[Y].exec(aa))){var U=RegExp.leftContext;if(U.substr(U.length-1)!=="\\"){X[1]=(X[1]||"").replace(/\\/g,"");Z=I.find[Y](X,T,ab);if(Z!=null){aa=aa.replace(I.match[Y],"");break}}}}if(!Z){Z=T.getElementsByTagName("*")}return{set:Z,expr:aa}};F.filter=function(ad,ac,ag,W){var V=ad,ai=[],aa=ac,Y,T,Z=ac&&ac[0]&&Q(ac[0]);while(ad&&ac.length){for(var ab in I.filter){if((Y=I.match[ab].exec(ad))!=null){var U=I.filter[ab],ah,af;T=false;if(aa==ai){ai=[]}if(I.preFilter[ab]){Y=I.preFilter[ab](Y,aa,ag,ai,W,Z);if(!Y){T=ah=true}else{if(Y===true){continue}}}if(Y){for(var X=0;(af=aa[X])!=null;X++){if(af){ah=U(af,Y,X,aa);var ae=W^!!ah;if(ag&&ah!=null){if(ae){T=true}else{aa[X]=false}}else{if(ae){ai.push(af);T=true}}}}}if(ah!==g){if(!ag){aa=ai}ad=ad.replace(I.match[ab],"");if(!T){return[]}break}}}if(ad==V){if(T==null){throw"Syntax error, unrecognized expression: "+ad}else{break}}V=ad}return aa};var I=F.selectors={order:["ID","NAME","TAG"],match:{ID:/#((?:[\w\u00c0-\uFFFF_-]|\\.)+)/,CLASS:/\.((?:[\w\u00c0-\uFFFF_-]|\\.)+)/,NAME:/\[name=['"]*((?:[\w\u00c0-\uFFFF_-]|\\.)+)['"]*\]/,ATTR:/\[\s*((?:[\w\u00c0-\uFFFF_-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\]/,TAG:/^((?:[\w\u00c0-\uFFFF\*_-]|\\.)+)/,CHILD:/:(only|nth|last|first)-child(?:\((even|odd|[\dn+-]*)\))?/,POS:/:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^-]|$)/,PSEUDO:/:((?:[\w\u00c0-\uFFFF_-]|\\.)+)(?:\((['"]*)((?:\([^\)]+\)|[^\2\(\)]*)+)\2\))?/},attrMap:{"class":"className","for":"htmlFor"},attrHandle:{href:function(T){return T.getAttribute("href")}},relative:{"+":function(aa,T,Z){var X=typeof T==="string",ab=X&&!/\W/.test(T),Y=X&&!ab;if(ab&&!Z){T=T.toUpperCase()}for(var W=0,V=aa.length,U;W<V;W++){if((U=aa[W])){while((U=U.previousSibling)&&U.nodeType!==1){}aa[W]=Y||U&&U.nodeName===T?U||false:U===T}}if(Y){F.filter(T,aa,true)}},">":function(Z,U,aa){var X=typeof U==="string";if(X&&!/\W/.test(U)){U=aa?U:U.toUpperCase();for(var V=0,T=Z.length;V<T;V++){var Y=Z[V];if(Y){var W=Y.parentNode;Z[V]=W.nodeName===U?W:false}}}else{for(var V=0,T=Z.length;V<T;V++){var Y=Z[V];if(Y){Z[V]=X?Y.parentNode:Y.parentNode===U}}if(X){F.filter(U,Z,true)}}},"":function(W,U,Y){var V=L++,T=S;if(!U.match(/\W/)){var X=U=Y?U:U.toUpperCase();T=P}T("parentNode",U,V,W,X,Y)},"~":function(W,U,Y){var V=L++,T=S;if(typeof U==="string"&&!U.match(/\W/)){var X=U=Y?U:U.toUpperCase();T=P}T("previousSibling",U,V,W,X,Y)}},find:{ID:function(U,V,W){if(typeof V.getElementById!=="undefined"&&!W){var T=V.getElementById(U[1]);return T?[T]:[]}},NAME:function(V,Y,Z){if(typeof Y.getElementsByName!=="undefined"){var U=[],X=Y.getElementsByName(V[1]);for(var W=0,T=X.length;W<T;W++){if(X[W].getAttribute("name")===V[1]){U.push(X[W])}}return U.length===0?null:U}},TAG:function(T,U){return U.getElementsByTagName(T[1])}},preFilter:{CLASS:function(W,U,V,T,Z,aa){W=" "+W[1].replace(/\\/g,"")+" ";if(aa){return W}for(var X=0,Y;(Y=U[X])!=null;X++){if(Y){if(Z^(Y.className&&(" "+Y.className+" ").indexOf(W)>=0)){if(!V){T.push(Y)}}else{if(V){U[X]=false}}}}return false},ID:function(T){return T[1].replace(/\\/g,"")},TAG:function(U,T){for(var V=0;T[V]===false;V++){}return T[V]&&Q(T[V])?U[1]:U[1].toUpperCase()},CHILD:function(T){if(T[1]=="nth"){var U=/(-?)(\d*)n((?:\+|-)?\d*)/.exec(T[2]=="even"&&"2n"||T[2]=="odd"&&"2n+1"||!/\D/.test(T[2])&&"0n+"+T[2]||T[2]);T[2]=(U[1]+(U[2]||1))-0;T[3]=U[3]-0}T[0]=L++;return T},ATTR:function(X,U,V,T,Y,Z){var W=X[1].replace(/\\/g,"");if(!Z&&I.attrMap[W]){X[1]=I.attrMap[W]}if(X[2]==="~="){X[4]=" "+X[4]+" "}return X},PSEUDO:function(X,U,V,T,Y){if(X[1]==="not"){if(X[3].match(R).length>1||/^\w/.test(X[3])){X[3]=F(X[3],null,null,U)}else{var W=F.filter(X[3],U,V,true^Y);if(!V){T.push.apply(T,W)}return false}}else{if(I.match.POS.test(X[0])||I.match.CHILD.test(X[0])){return true}}return X},POS:function(T){T.unshift(true);return T}},filters:{enabled:function(T){return T.disabled===false&&T.type!=="hidden"},disabled:function(T){return T.disabled===true},checked:function(T){return T.checked===true},selected:function(T){T.parentNode.selectedIndex;return T.selected===true},parent:function(T){return !!T.firstChild},empty:function(T){return !T.firstChild},has:function(V,U,T){return !!F(T[3],V).length},header:function(T){return/h\d/i.test(T.nodeName)},text:function(T){return"text"===T.type},radio:function(T){return"radio"===T.type},checkbox:function(T){return"checkbox"===T.type},file:function(T){return"file"===T.type},password:function(T){return"password"===T.type},submit:function(T){return"submit"===T.type},image:function(T){return"image"===T.type},reset:function(T){return"reset"===T.type},button:function(T){return"button"===T.type||T.nodeName.toUpperCase()==="BUTTON"},input:function(T){return/input|select|textarea|button/i.test(T.nodeName)}},setFilters:{first:function(U,T){return T===0},last:function(V,U,T,W){return U===W.length-1},even:function(U,T){return T%2===0},odd:function(U,T){return T%2===1},lt:function(V,U,T){return U<T[3]-0},gt:function(V,U,T){return U>T[3]-0},nth:function(V,U,T){return T[3]-0==U},eq:function(V,U,T){return T[3]-0==U}},filter:{PSEUDO:function(Z,V,W,aa){var U=V[1],X=I.filters[U];if(X){return X(Z,W,V,aa)}else{if(U==="contains"){return(Z.textContent||Z.innerText||"").indexOf(V[3])>=0}else{if(U==="not"){var Y=V[3];for(var W=0,T=Y.length;W<T;W++){if(Y[W]===Z){return false}}return true}}}},CHILD:function(T,W){var Z=W[1],U=T;switch(Z){case"only":case"first":while(U=U.previousSibling){if(U.nodeType===1){return false}}if(Z=="first"){return true}U=T;case"last":while(U=U.nextSibling){if(U.nodeType===1){return false}}return true;case"nth":var V=W[2],ac=W[3];if(V==1&&ac==0){return true}var Y=W[0],ab=T.parentNode;if(ab&&(ab.sizcache!==Y||!T.nodeIndex)){var X=0;for(U=ab.firstChild;U;U=U.nextSibling){if(U.nodeType===1){U.nodeIndex=++X}}ab.sizcache=Y}var aa=T.nodeIndex-ac;if(V==0){return aa==0}else{return(aa%V==0&&aa/V>=0)}}},ID:function(U,T){return U.nodeType===1&&U.getAttribute("id")===T},TAG:function(U,T){return(T==="*"&&U.nodeType===1)||U.nodeName===T},CLASS:function(U,T){return(" "+(U.className||U.getAttribute("class"))+" ").indexOf(T)>-1},ATTR:function(Y,W){var V=W[1],T=I.attrHandle[V]?I.attrHandle[V](Y):Y[V]!=null?Y[V]:Y.getAttribute(V),Z=T+"",X=W[2],U=W[4];return T==null?X==="!=":X==="="?Z===U:X==="*="?Z.indexOf(U)>=0:X==="~="?(" "+Z+" ").indexOf(U)>=0:!U?Z&&T!==false:X==="!="?Z!=U:X==="^="?Z.indexOf(U)===0:X==="$="?Z.substr(Z.length-U.length)===U:X==="|="?Z===U||Z.substr(0,U.length+1)===U+"-":false},POS:function(X,U,V,Y){var T=U[2],W=I.setFilters[T];if(W){return W(X,V,U,Y)}}}};var M=I.match.POS;for(var O in I.match){I.match[O]=RegExp(I.match[O].source+/(?![^\[]*\])(?![^\(]*\))/.source)}var E=function(U,T){U=Array.prototype.slice.call(U);if(T){T.push.apply(T,U);return T}return U};try{Array.prototype.slice.call(document.documentElement.childNodes)}catch(N){E=function(X,W){var U=W||[];if(H.call(X)==="[object Array]"){Array.prototype.push.apply(U,X)}else{if(typeof X.length==="number"){for(var V=0,T=X.length;V<T;V++){U.push(X[V])}}else{for(var V=0;X[V];V++){U.push(X[V])}}}return U}}var G;if(document.documentElement.compareDocumentPosition){G=function(U,T){var V=U.compareDocumentPosition(T)&4?-1:U===T?0:1;if(V===0){hasDuplicate=true}return V}}else{if("sourceIndex" in document.documentElement){G=function(U,T){var V=U.sourceIndex-T.sourceIndex;if(V===0){hasDuplicate=true}return V}}else{if(document.createRange){G=function(W,U){var V=W.ownerDocument.createRange(),T=U.ownerDocument.createRange();V.selectNode(W);V.collapse(true);T.selectNode(U);T.collapse(true);var X=V.compareBoundaryPoints(Range.START_TO_END,T);if(X===0){hasDuplicate=true}return X}}}}(function(){var U=document.createElement("form"),V="script"+(new Date).getTime();U.innerHTML="<input name='"+V+"'/>";var T=document.documentElement;T.insertBefore(U,T.firstChild);if(!!document.getElementById(V)){I.find.ID=function(X,Y,Z){if(typeof Y.getElementById!=="undefined"&&!Z){var W=Y.getElementById(X[1]);return W?W.id===X[1]||typeof W.getAttributeNode!=="undefined"&&W.getAttributeNode("id").nodeValue===X[1]?[W]:g:[]}};I.filter.ID=function(Y,W){var X=typeof Y.getAttributeNode!=="undefined"&&Y.getAttributeNode("id");return Y.nodeType===1&&X&&X.nodeValue===W}}T.removeChild(U)})();(function(){var T=document.createElement("div");T.appendChild(document.createComment(""));if(T.getElementsByTagName("*").length>0){I.find.TAG=function(U,Y){var X=Y.getElementsByTagName(U[1]);if(U[1]==="*"){var W=[];for(var V=0;X[V];V++){if(X[V].nodeType===1){W.push(X[V])}}X=W}return X}}T.innerHTML="<a href='#'></a>";if(T.firstChild&&typeof T.firstChild.getAttribute!=="undefined"&&T.firstChild.getAttribute("href")!=="#"){I.attrHandle.href=function(U){return U.getAttribute("href",2)}}})();if(document.querySelectorAll){(function(){var T=F,U=document.createElement("div");U.innerHTML="<p class='TEST'></p>";if(U.querySelectorAll&&U.querySelectorAll(".TEST").length===0){return}F=function(Y,X,V,W){X=X||document;if(!W&&X.nodeType===9&&!Q(X)){try{return E(X.querySelectorAll(Y),V)}catch(Z){}}return T(Y,X,V,W)};F.find=T.find;F.filter=T.filter;F.selectors=T.selectors;F.matches=T.matches})()}if(document.getElementsByClassName&&document.documentElement.getElementsByClassName){(function(){var T=document.createElement("div");T.innerHTML="<div class='test e'></div><div class='test'></div>";if(T.getElementsByClassName("e").length===0){return}T.lastChild.className="e";if(T.getElementsByClassName("e").length===1){return}I.order.splice(1,0,"CLASS");I.find.CLASS=function(U,V,W){if(typeof V.getElementsByClassName!=="undefined"&&!W){return V.getElementsByClassName(U[1])}}})()}function P(U,Z,Y,ad,aa,ac){var ab=U=="previousSibling"&&!ac;for(var W=0,V=ad.length;W<V;W++){var T=ad[W];if(T){if(ab&&T.nodeType===1){T.sizcache=Y;T.sizset=W}T=T[U];var X=false;while(T){if(T.sizcache===Y){X=ad[T.sizset];break}if(T.nodeType===1&&!ac){T.sizcache=Y;T.sizset=W}if(T.nodeName===Z){X=T;break}T=T[U]}ad[W]=X}}}function S(U,Z,Y,ad,aa,ac){var ab=U=="previousSibling"&&!ac;for(var W=0,V=ad.length;W<V;W++){var T=ad[W];if(T){if(ab&&T.nodeType===1){T.sizcache=Y;T.sizset=W}T=T[U];var X=false;while(T){if(T.sizcache===Y){X=ad[T.sizset];break}if(T.nodeType===1){if(!ac){T.sizcache=Y;T.sizset=W}if(typeof Z!=="string"){if(T===Z){X=true;break}}else{if(F.filter(Z,[T]).length>0){X=T;break}}}T=T[U]}ad[W]=X}}}var K=document.compareDocumentPosition?function(U,T){return U.compareDocumentPosition(T)&16}:function(U,T){return U!==T&&(U.contains?U.contains(T):true)};var Q=function(T){return T.nodeType===9&&T.documentElement.nodeName!=="HTML"||!!T.ownerDocument&&Q(T.ownerDocument)};var J=function(T,aa){var W=[],X="",Y,V=aa.nodeType?[aa]:aa;while((Y=I.match.PSEUDO.exec(T))){X+=Y[0];T=T.replace(I.match.PSEUDO,"")}T=I.relative[T]?T+"*":T;for(var Z=0,U=V.length;Z<U;Z++){F(T,V[Z],W)}return F.filter(X,W)};o.find=F;o.filter=F.filter;o.expr=F.selectors;o.expr[":"]=o.expr.filters;F.selectors.filters.hidden=function(T){return T.offsetWidth===0||T.offsetHeight===0};F.selectors.filters.visible=function(T){return T.offsetWidth>0||T.offsetHeight>0};F.selectors.filters.animated=function(T){return o.grep(o.timers,function(U){return T===U.elem}).length};o.multiFilter=function(V,T,U){if(U){V=":not("+V+")"}return F.matches(V,T)};o.dir=function(V,U){var T=[],W=V[U];while(W&&W!=document){if(W.nodeType==1){T.push(W)}W=W[U]}return T};o.nth=function(X,T,V,W){T=T||1;var U=0;for(;X;X=X[V]){if(X.nodeType==1&&++U==T){break}}return X};o.sibling=function(V,U){var T=[];for(;V;V=V.nextSibling){if(V.nodeType==1&&V!=U){T.push(V)}}return T};return;l.Sizzle=F})();o.event={add:function(I,F,H,K){if(I.nodeType==3||I.nodeType==8){return}if(I.setInterval&&I!=l){I=l}if(!H.guid){H.guid=this.guid++}if(K!==g){var G=H;H=this.proxy(G);H.data=K}var E=o.data(I,"events")||o.data(I,"events",{}),J=o.data(I,"handle")||o.data(I,"handle",function(){return typeof o!=="undefined"&&!o.event.triggered?o.event.handle.apply(arguments.callee.elem,arguments):g});J.elem=I;o.each(F.split(/\s+/),function(M,N){var O=N.split(".");N=O.shift();H.type=O.slice().sort().join(".");var L=E[N];if(o.event.specialAll[N]){o.event.specialAll[N].setup.call(I,K,O)}if(!L){L=E[N]={};if(!o.event.special[N]||o.event.special[N].setup.call(I,K,O)===false){if(I.addEventListener){I.addEventListener(N,J,false)}else{if(I.attachEvent){I.attachEvent("on"+N,J)}}}}L[H.guid]=H;o.event.global[N]=true});I=null},guid:1,global:{},remove:function(K,H,J){if(K.nodeType==3||K.nodeType==8){return}var G=o.data(K,"events"),F,E;if(G){if(H===g||(typeof H==="string"&&H.charAt(0)==".")){for(var I in G){this.remove(K,I+(H||""))}}else{if(H.type){J=H.handler;H=H.type}o.each(H.split(/\s+/),function(M,O){var Q=O.split(".");O=Q.shift();var N=RegExp("(^|\\.)"+Q.slice().sort().join(".*\\.")+"(\\.|$)");if(G[O]){if(J){delete G[O][J.guid]}else{for(var P in G[O]){if(N.test(G[O][P].type)){delete G[O][P]}}}if(o.event.specialAll[O]){o.event.specialAll[O].teardown.call(K,Q)}for(F in G[O]){break}if(!F){if(!o.event.special[O]||o.event.special[O].teardown.call(K,Q)===false){if(K.removeEventListener){K.removeEventListener(O,o.data(K,"handle"),false)}else{if(K.detachEvent){K.detachEvent("on"+O,o.data(K,"handle"))}}}F=null;delete G[O]}}})}for(F in G){break}if(!F){var L=o.data(K,"handle");if(L){L.elem=null}o.removeData(K,"events");o.removeData(K,"handle")}}},trigger:function(I,K,H,E){var G=I.type||I;if(!E){I=typeof I==="object"?I[h]?I:o.extend(o.Event(G),I):o.Event(G);if(G.indexOf("!")>=0){I.type=G=G.slice(0,-1);I.exclusive=true}if(!H){I.stopPropagation();if(this.global[G]){o.each(o.cache,function(){if(this.events&&this.events[G]){o.event.trigger(I,K,this.handle.elem)}})}}if(!H||H.nodeType==3||H.nodeType==8){return g}I.result=g;I.target=H;K=o.makeArray(K);K.unshift(I)}I.currentTarget=H;var J=o.data(H,"handle");if(J){J.apply(H,K)}if((!H[G]||(o.nodeName(H,"a")&&G=="click"))&&H["on"+G]&&H["on"+G].apply(H,K)===false){I.result=false}if(!E&&H[G]&&!I.isDefaultPrevented()&&!(o.nodeName(H,"a")&&G=="click")){this.triggered=true;try{H[G]()}catch(L){}}this.triggered=false;if(!I.isPropagationStopped()){var F=H.parentNode||H.ownerDocument;if(F){o.event.trigger(I,K,F,true)}}},handle:function(K){var J,E;K=arguments[0]=o.event.fix(K||l.event);K.currentTarget=this;var L=K.type.split(".");K.type=L.shift();J=!L.length&&!K.exclusive;var I=RegExp("(^|\\.)"+L.slice().sort().join(".*\\.")+"(\\.|$)");E=(o.data(this,"events")||{})[K.type];for(var G in E){var H=E[G];if(J||I.test(H.type)){K.handler=H;K.data=H.data;var F=H.apply(this,arguments);if(F!==g){K.result=F;if(F===false){K.preventDefault();K.stopPropagation()}}if(K.isImmediatePropagationStopped()){break}}}},props:"altKey attrChange attrName bubbles button cancelable charCode clientX clientY ctrlKey currentTarget data detail eventPhase fromElement handler keyCode metaKey newValue originalTarget pageX pageY prevValue relatedNode relatedTarget screenX screenY shiftKey srcElement target toElement view wheelDelta which".split(" "),fix:function(H){if(H[h]){return H}var F=H;H=o.Event(F);for(var G=this.props.length,J;G;){J=this.props[--G];H[J]=F[J]}if(!H.target){H.target=H.srcElement||document}if(H.target.nodeType==3){H.target=H.target.parentNode}if(!H.relatedTarget&&H.fromElement){H.relatedTarget=H.fromElement==H.target?H.toElement:H.fromElement}if(H.pageX==null&&H.clientX!=null){var I=document.documentElement,E=document.body;H.pageX=H.clientX+(I&&I.scrollLeft||E&&E.scrollLeft||0)-(I.clientLeft||0);H.pageY=H.clientY+(I&&I.scrollTop||E&&E.scrollTop||0)-(I.clientTop||0)}if(!H.which&&((H.charCode||H.charCode===0)?H.charCode:H.keyCode)){H.which=H.charCode||H.keyCode}if(!H.metaKey&&H.ctrlKey){H.metaKey=H.ctrlKey}if(!H.which&&H.button){H.which=(H.button&1?1:(H.button&2?3:(H.button&4?2:0)))}return H},proxy:function(F,E){E=E||function(){return F.apply(this,arguments)};E.guid=F.guid=F.guid||E.guid||this.guid++;return E},special:{ready:{setup:B,teardown:function(){}}},specialAll:{live:{setup:function(E,F){o.event.add(this,F[0],c)},teardown:function(G){if(G.length){var E=0,F=RegExp("(^|\\.)"+G[0]+"(\\.|$)");o.each((o.data(this,"events").live||{}),function(){if(F.test(this.type)){E++}});if(E<1){o.event.remove(this,G[0],c)}}}}}};o.Event=function(E){if(!this.preventDefault){return new o.Event(E)}if(E&&E.type){this.originalEvent=E;this.type=E.type}else{this.type=E}this.timeStamp=e();this[h]=true};function k(){return false}function u(){return true}o.Event.prototype={preventDefault:function(){this.isDefaultPrevented=u;var E=this.originalEvent;if(!E){return}if(E.preventDefault){E.preventDefault()}E.returnValue=false},stopPropagation:function(){this.isPropagationStopped=u;var E=this.originalEvent;if(!E){return}if(E.stopPropagation){E.stopPropagation()}E.cancelBubble=true},stopImmediatePropagation:function(){this.isImmediatePropagationStopped=u;this.stopPropagation()},isDefaultPrevented:k,isPropagationStopped:k,isImmediatePropagationStopped:k};var a=function(F){var E=F.relatedTarget;while(E&&E!=this){try{E=E.parentNode}catch(G){E=this}}if(E!=this){F.type=F.data;o.event.handle.apply(this,arguments)}};o.each({mouseover:"mouseenter",mouseout:"mouseleave"},function(F,E){o.event.special[E]={setup:function(){o.event.add(this,F,a,E)},teardown:function(){o.event.remove(this,F,a)}}});o.fn.extend({bind:function(F,G,E){return F=="unload"?this.one(F,G,E):this.each(function(){o.event.add(this,F,E||G,E&&G)})},one:function(G,H,F){var E=o.event.proxy(F||H,function(I){o(this).unbind(I,E);return(F||H).apply(this,arguments)});return this.each(function(){o.event.add(this,G,E,F&&H)})},unbind:function(F,E){return this.each(function(){o.event.remove(this,F,E)})},trigger:function(E,F){return this.each(function(){o.event.trigger(E,F,this)})},triggerHandler:function(E,G){if(this[0]){var F=o.Event(E);F.preventDefault();F.stopPropagation();o.event.trigger(F,G,this[0]);return F.result}},toggle:function(G){var E=arguments,F=1;while(F<E.length){o.event.proxy(G,E[F++])}return this.click(o.event.proxy(G,function(H){this.lastToggle=(this.lastToggle||0)%F;H.preventDefault();return E[this.lastToggle++].apply(this,arguments)||false}))},hover:function(E,F){return this.mouseenter(E).mouseleave(F)},ready:function(E){B();if(o.isReady){E.call(document,o)}else{o.readyList.push(E)}return this},live:function(G,F){var E=o.event.proxy(F);E.guid+=this.selector+G;o(document).bind(i(G,this.selector),this.selector,E);return this},die:function(F,E){o(document).unbind(i(F,this.selector),E?{guid:E.guid+this.selector+F}:null);return this}});function c(H){var E=RegExp("(^|\\.)"+H.type+"(\\.|$)"),G=true,F=[];o.each(o.data(this,"events").live||[],function(I,J){if(E.test(J.type)){var K=o(H.target).closest(J.data)[0];if(K){F.push({elem:K,fn:J})}}});F.sort(function(J,I){return o.data(J.elem,"closest")-o.data(I.elem,"closest")});o.each(F,function(){if(this.fn.call(this.elem,H,this.fn.data)===false){return(G=false)}});return G}function i(F,E){return["live",F,E.replace(/\./g,"`").replace(/ /g,"|")].join(".")}o.extend({isReady:false,readyList:[],ready:function(){if(!o.isReady){o.isReady=true;if(o.readyList){o.each(o.readyList,function(){this.call(document,o)});o.readyList=null}o(document).triggerHandler("ready")}}});var x=false;function B(){if(x){return}x=true;if(document.addEventListener){document.addEventListener("DOMContentLoaded",function(){document.removeEventListener("DOMContentLoaded",arguments.callee,false);o.ready()},false)}else{if(document.attachEvent){document.attachEvent("onreadystatechange",function(){if(document.readyState==="complete"){document.detachEvent("onreadystatechange",arguments.callee);o.ready()}});if(document.documentElement.doScroll&&l==l.top){(function(){if(o.isReady){return}try{document.documentElement.doScroll("left")}catch(E){setTimeout(arguments.callee,0);return}o.ready()})()}}}o.event.add(l,"load",o.ready)}o.each(("blur,focus,load,resize,scroll,unload,click,dblclick,mousedown,mouseup,mousemove,mouseover,mouseout,mouseenter,mouseleave,change,select,submit,keydown,keypress,keyup,error").split(","),function(F,E){o.fn[E]=function(G){return G?this.bind(E,G):this.trigger(E)}});o(l).bind("unload",function(){for(var E in o.cache){if(E!=1&&o.cache[E].handle){o.event.remove(o.cache[E].handle.elem)}}});(function(){o.support={};var F=document.documentElement,G=document.createElement("script"),K=document.createElement("div"),J="script"+(new Date).getTime();K.style.display="none";K.innerHTML='   <link/><table></table><a href="/a" style="color:red;float:left;opacity:.5;">a</a><select><option>text</option></select><object><param/></object>';var H=K.getElementsByTagName("*"),E=K.getElementsByTagName("a")[0];if(!H||!H.length||!E){return}o.support={leadingWhitespace:K.firstChild.nodeType==3,tbody:!K.getElementsByTagName("tbody").length,objectAll:!!K.getElementsByTagName("object")[0].getElementsByTagName("*").length,htmlSerialize:!!K.getElementsByTagName("link").length,style:/red/.test(E.getAttribute("style")),hrefNormalized:E.getAttribute("href")==="/a",opacity:E.style.opacity==="0.5",cssFloat:!!E.style.cssFloat,scriptEval:false,noCloneEvent:true,boxModel:null};G.type="text/javascript";try{G.appendChild(document.createTextNode("window."+J+"=1;"))}catch(I){}F.insertBefore(G,F.firstChild);if(l[J]){o.support.scriptEval=true;delete l[J]}F.removeChild(G);if(K.attachEvent&&K.fireEvent){K.attachEvent("onclick",function(){o.support.noCloneEvent=false;K.detachEvent("onclick",arguments.callee)});K.cloneNode(true).fireEvent("onclick")}o(function(){var L=document.createElement("div");L.style.width=L.style.paddingLeft="1px";document.body.appendChild(L);o.boxModel=o.support.boxModel=L.offsetWidth===2;document.body.removeChild(L).style.display="none"})})();var w=o.support.cssFloat?"cssFloat":"styleFloat";o.props={"for":"htmlFor","class":"className","float":w,cssFloat:w,styleFloat:w,readonly:"readOnly",maxlength:"maxLength",cellspacing:"cellSpacing",rowspan:"rowSpan",tabindex:"tabIndex"};o.fn.extend({_load:o.fn.load,load:function(G,J,K){if(typeof G!=="string"){return this._load(G)}var I=G.indexOf(" ");if(I>=0){var E=G.slice(I,G.length);G=G.slice(0,I)}var H="GET";if(J){if(o.isFunction(J)){K=J;J=null}else{if(typeof J==="object"){J=o.param(J);H="POST"}}}var F=this;o.ajax({url:G,type:H,dataType:"html",data:J,complete:function(M,L){if(L=="success"||L=="notmodified"){F.html(E?o("<div/>").append(M.responseText.replace(/<script(.|\s)*?\/script>/g,"")).find(E):M.responseText)}if(K){F.each(K,[M.responseText,L,M])}}});return this},serialize:function(){return o.param(this.serializeArray())},serializeArray:function(){return this.map(function(){return this.elements?o.makeArray(this.elements):this}).filter(function(){return this.name&&!this.disabled&&(this.checked||/select|textarea/i.test(this.nodeName)||/text|hidden|password|search/i.test(this.type))}).map(function(E,F){var G=o(this).val();return G==null?null:o.isArray(G)?o.map(G,function(I,H){return{name:F.name,value:I}}):{name:F.name,value:G}}).get()}});o.each("ajaxStart,ajaxStop,ajaxComplete,ajaxError,ajaxSuccess,ajaxSend".split(","),function(E,F){o.fn[F]=function(G){return this.bind(F,G)}});var r=e();o.extend({get:function(E,G,H,F){if(o.isFunction(G)){H=G;G=null}return o.ajax({type:"GET",url:E,data:G,success:H,dataType:F})},getScript:function(E,F){return o.get(E,null,F,"script")},getJSON:function(E,F,G){return o.get(E,F,G,"json")},post:function(E,G,H,F){if(o.isFunction(G)){H=G;G={}}return o.ajax({type:"POST",url:E,data:G,success:H,dataType:F})},ajaxSetup:function(E){o.extend(o.ajaxSettings,E)},ajaxSettings:{url:location.href,global:true,type:"GET",contentType:"application/x-www-form-urlencoded",processData:true,async:true,xhr:function(){return l.ActiveXObject?new ActiveXObject("Microsoft.XMLHTTP"):new XMLHttpRequest()},accepts:{xml:"application/xml, text/xml",html:"text/html",script:"text/javascript, application/javascript",json:"application/json, text/javascript",text:"text/plain",_default:"*/*"}},lastModified:{},ajax:function(M){M=o.extend(true,M,o.extend(true,{},o.ajaxSettings,M));var W,F=/=\?(&|$)/g,R,V,G=M.type.toUpperCase();if(M.data&&M.processData&&typeof M.data!=="string"){M.data=o.param(M.data)}if(M.dataType=="jsonp"){if(G=="GET"){if(!M.url.match(F)){M.url+=(M.url.match(/\?/)?"&":"?")+(M.jsonp||"callback")+"=?"}}else{if(!M.data||!M.data.match(F)){M.data=(M.data?M.data+"&":"")+(M.jsonp||"callback")+"=?"}}M.dataType="json"}if(M.dataType=="json"&&(M.data&&M.data.match(F)||M.url.match(F))){W="jsonp"+r++;if(M.data){M.data=(M.data+"").replace(F,"="+W+"$1")}M.url=M.url.replace(F,"="+W+"$1");M.dataType="script";l[W]=function(X){V=X;I();L();l[W]=g;try{delete l[W]}catch(Y){}if(H){H.removeChild(T)}}}if(M.dataType=="script"&&M.cache==null){M.cache=false}if(M.cache===false&&G=="GET"){var E=e();var U=M.url.replace(/(\?|&)_=.*?(&|$)/,"$1_="+E+"$2");M.url=U+((U==M.url)?(M.url.match(/\?/)?"&":"?")+"_="+E:"")}if(M.data&&G=="GET"){M.url+=(M.url.match(/\?/)?"&":"?")+M.data;M.data=null}if(M.global&&!o.active++){o.event.trigger("ajaxStart")}var Q=/^(\w+:)?\/\/([^\/?#]+)/.exec(M.url);if(M.dataType=="script"&&G=="GET"&&Q&&(Q[1]&&Q[1]!=location.protocol||Q[2]!=location.host)){var H=document.getElementsByTagName("head")[0];var T=document.createElement("script");T.src=M.url;if(M.scriptCharset){T.charset=M.scriptCharset}if(!W){var O=false;T.onload=T.onreadystatechange=function(){if(!O&&(!this.readyState||this.readyState=="loaded"||this.readyState=="complete")){O=true;I();L();T.onload=T.onreadystatechange=null;H.removeChild(T)}}}H.appendChild(T);return g}var K=false;var J=M.xhr();if(M.username){J.open(G,M.url,M.async,M.username,M.password)}else{J.open(G,M.url,M.async)}try{if(M.data){J.setRequestHeader("Content-Type",M.contentType)}if(M.ifModified){J.setRequestHeader("If-Modified-Since",o.lastModified[M.url]||"Thu, 01 Jan 1970 00:00:00 GMT")}J.setRequestHeader("X-Requested-With","XMLHttpRequest");J.setRequestHeader("Accept",M.dataType&&M.accepts[M.dataType]?M.accepts[M.dataType]+", */*":M.accepts._default)}catch(S){}if(M.beforeSend&&M.beforeSend(J,M)===false){if(M.global&&!--o.active){o.event.trigger("ajaxStop")}J.abort();return false}if(M.global){o.event.trigger("ajaxSend",[J,M])}var N=function(X){if(J.readyState==0){if(P){clearInterval(P);P=null;if(M.global&&!--o.active){o.event.trigger("ajaxStop")}}}else{if(!K&&J&&(J.readyState==4||X=="timeout")){K=true;if(P){clearInterval(P);P=null}R=X=="timeout"?"timeout":!o.httpSuccess(J)?"error":M.ifModified&&o.httpNotModified(J,M.url)?"notmodified":"success";if(R=="success"){try{V=o.httpData(J,M.dataType,M)}catch(Z){R="parsererror"}}if(R=="success"){var Y;try{Y=J.getResponseHeader("Last-Modified")}catch(Z){}if(M.ifModified&&Y){o.lastModified[M.url]=Y}if(!W){I()}}else{o.handleError(M,J,R)}L();if(X){J.abort()}if(M.async){J=null}}}};if(M.async){var P=setInterval(N,13);if(M.timeout>0){setTimeout(function(){if(J&&!K){N("timeout")}},M.timeout)}}try{J.send(M.data)}catch(S){o.handleError(M,J,null,S)}if(!M.async){N()}function I(){if(M.success){M.success(V,R)}if(M.global){o.event.trigger("ajaxSuccess",[J,M])}}function L(){if(M.complete){M.complete(J,R)}if(M.global){o.event.trigger("ajaxComplete",[J,M])}if(M.global&&!--o.active){o.event.trigger("ajaxStop")}}return J},handleError:function(F,H,E,G){if(F.error){F.error(H,E,G)}if(F.global){o.event.trigger("ajaxError",[H,F,G])}},active:0,httpSuccess:function(F){try{return !F.status&&location.protocol=="file:"||(F.status>=200&&F.status<300)||F.status==304||F.status==1223}catch(E){}return false},httpNotModified:function(G,E){try{var H=G.getResponseHeader("Last-Modified");return G.status==304||H==o.lastModified[E]}catch(F){}return false},httpData:function(J,H,G){var F=J.getResponseHeader("content-type"),E=H=="xml"||!H&&F&&F.indexOf("xml")>=0,I=E?J.responseXML:J.responseText;if(E&&I.documentElement.tagName=="parsererror"){throw"parsererror"}if(G&&G.dataFilter){I=G.dataFilter(I,H)}if(typeof I==="string"){if(H=="script"){o.globalEval(I)}if(H=="json"){I=l["eval"]("("+I+")")}}return I},param:function(E){var G=[];function H(I,J){G[G.length]=encodeURIComponent(I)+"="+encodeURIComponent(J)}if(o.isArray(E)||E.jquery){o.each(E,function(){H(this.name,this.value)})}else{for(var F in E){if(o.isArray(E[F])){o.each(E[F],function(){H(F,this)})}else{H(F,o.isFunction(E[F])?E[F]():E[F])}}}return G.join("&").replace(/%20/g,"+")}});var m={},n,d=[["height","marginTop","marginBottom","paddingTop","paddingBottom"],["width","marginLeft","marginRight","paddingLeft","paddingRight"],["opacity"]];function t(F,E){var G={};o.each(d.concat.apply([],d.slice(0,E)),function(){G[this]=F});return G}o.fn.extend({show:function(J,L){if(J){return this.animate(t("show",3),J,L)}else{for(var H=0,F=this.length;H<F;H++){var E=o.data(this[H],"olddisplay");this[H].style.display=E||"";if(o.css(this[H],"display")==="none"){var G=this[H].tagName,K;if(m[G]){K=m[G]}else{var I=o("<"+G+" />").appendTo("body");K=I.css("display");if(K==="none"){K="block"}I.remove();m[G]=K}o.data(this[H],"olddisplay",K)}}for(var H=0,F=this.length;H<F;H++){this[H].style.display=o.data(this[H],"olddisplay")||""}return this}},hide:function(H,I){if(H){return this.animate(t("hide",3),H,I)}else{for(var G=0,F=this.length;G<F;G++){var E=o.data(this[G],"olddisplay");if(!E&&E!=="none"){o.data(this[G],"olddisplay",o.css(this[G],"display"))}}for(var G=0,F=this.length;G<F;G++){this[G].style.display="none"}return this}},_toggle:o.fn.toggle,toggle:function(G,F){var E=typeof G==="boolean";return o.isFunction(G)&&o.isFunction(F)?this._toggle.apply(this,arguments):G==null||E?this.each(function(){var H=E?G:o(this).is(":hidden");o(this)[H?"show":"hide"]()}):this.animate(t("toggle",3),G,F)},fadeTo:function(E,G,F){return this.animate({opacity:G},E,F)},animate:function(I,F,H,G){var E=o.speed(F,H,G);return this[E.queue===false?"each":"queue"](function(){var K=o.extend({},E),M,L=this.nodeType==1&&o(this).is(":hidden"),J=this;for(M in I){if(I[M]=="hide"&&L||I[M]=="show"&&!L){return K.complete.call(this)}if((M=="height"||M=="width")&&this.style){K.display=o.css(this,"display");K.overflow=this.style.overflow}}if(K.overflow!=null){this.style.overflow="hidden"}K.curAnim=o.extend({},I);o.each(I,function(O,S){var R=new o.fx(J,K,O);if(/toggle|show|hide/.test(S)){R[S=="toggle"?L?"show":"hide":S](I)}else{var Q=S.toString().match(/^([+-]=)?([\d+-.]+)(.*)$/),T=R.cur(true)||0;if(Q){var N=parseFloat(Q[2]),P=Q[3]||"px";if(P!="px"){J.style[O]=(N||1)+P;T=((N||1)/R.cur(true))*T;J.style[O]=T+P}if(Q[1]){N=((Q[1]=="-="?-1:1)*N)+T}R.custom(T,N,P)}else{R.custom(T,S,"")}}});return true})},stop:function(F,E){var G=o.timers;if(F){this.queue([])}this.each(function(){for(var H=G.length-1;H>=0;H--){if(G[H].elem==this){if(E){G[H](true)}G.splice(H,1)}}});if(!E){this.dequeue()}return this}});o.each({slideDown:t("show",1),slideUp:t("hide",1),slideToggle:t("toggle",1),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"}},function(E,F){o.fn[E]=function(G,H){return this.animate(F,G,H)}});o.extend({speed:function(G,H,F){var E=typeof G==="object"?G:{complete:F||!F&&H||o.isFunction(G)&&G,duration:G,easing:F&&H||H&&!o.isFunction(H)&&H};E.duration=o.fx.off?0:typeof E.duration==="number"?E.duration:o.fx.speeds[E.duration]||o.fx.speeds._default;E.old=E.complete;E.complete=function(){if(E.queue!==false){o(this).dequeue()}if(o.isFunction(E.old)){E.old.call(this)}};return E},easing:{linear:function(G,H,E,F){return E+F*G},swing:function(G,H,E,F){return((-Math.cos(G*Math.PI)/2)+0.5)*F+E}},timers:[],fx:function(F,E,G){this.options=E;this.elem=F;this.prop=G;if(!E.orig){E.orig={}}}});o.fx.prototype={update:function(){if(this.options.step){this.options.step.call(this.elem,this.now,this)}(o.fx.step[this.prop]||o.fx.step._default)(this);if((this.prop=="height"||this.prop=="width")&&this.elem.style){this.elem.style.display="block"}},cur:function(F){if(this.elem[this.prop]!=null&&(!this.elem.style||this.elem.style[this.prop]==null)){return this.elem[this.prop]}var E=parseFloat(o.css(this.elem,this.prop,F));return E&&E>-10000?E:parseFloat(o.curCSS(this.elem,this.prop))||0},custom:function(I,H,G){this.startTime=e();this.start=I;this.end=H;this.unit=G||this.unit||"px";this.now=this.start;this.pos=this.state=0;var E=this;function F(J){return E.step(J)}F.elem=this.elem;if(F()&&o.timers.push(F)&&!n){n=setInterval(function(){var K=o.timers;for(var J=0;J<K.length;J++){if(!K[J]()){K.splice(J--,1)}}if(!K.length){clearInterval(n);n=g}},13)}},show:function(){this.options.orig[this.prop]=o.attr(this.elem.style,this.prop);this.options.show=true;this.custom(this.prop=="width"||this.prop=="height"?1:0,this.cur());o(this.elem).show()},hide:function(){this.options.orig[this.prop]=o.attr(this.elem.style,this.prop);this.options.hide=true;this.custom(this.cur(),0)},step:function(H){var G=e();if(H||G>=this.options.duration+this.startTime){this.now=this.end;this.pos=this.state=1;this.update();this.options.curAnim[this.prop]=true;var E=true;for(var F in this.options.curAnim){if(this.options.curAnim[F]!==true){E=false}}if(E){if(this.options.display!=null){this.elem.style.overflow=this.options.overflow;this.elem.style.display=this.options.display;if(o.css(this.elem,"display")=="none"){this.elem.style.display="block"}}if(this.options.hide){o(this.elem).hide()}if(this.options.hide||this.options.show){for(var I in this.options.curAnim){o.attr(this.elem.style,I,this.options.orig[I])}}this.options.complete.call(this.elem)}return false}else{var J=G-this.startTime;this.state=J/this.options.duration;this.pos=o.easing[this.options.easing||(o.easing.swing?"swing":"linear")](this.state,J,0,1,this.options.duration);this.now=this.start+((this.end-this.start)*this.pos);this.update()}return true}};o.extend(o.fx,{speeds:{slow:600,fast:200,_default:400},step:{opacity:function(E){o.attr(E.elem.style,"opacity",E.now)},_default:function(E){if(E.elem.style&&E.elem.style[E.prop]!=null){E.elem.style[E.prop]=E.now+E.unit}else{E.elem[E.prop]=E.now}}}});if(document.documentElement.getBoundingClientRect){o.fn.offset=function(){if(!this[0]){return{top:0,left:0}}if(this[0]===this[0].ownerDocument.body){return o.offset.bodyOffset(this[0])}var G=this[0].getBoundingClientRect(),J=this[0].ownerDocument,F=J.body,E=J.documentElement,L=E.clientTop||F.clientTop||0,K=E.clientLeft||F.clientLeft||0,I=G.top+(self.pageYOffset||o.boxModel&&E.scrollTop||F.scrollTop)-L,H=G.left+(self.pageXOffset||o.boxModel&&E.scrollLeft||F.scrollLeft)-K;return{top:I,left:H}}}else{o.fn.offset=function(){if(!this[0]){return{top:0,left:0}}if(this[0]===this[0].ownerDocument.body){return o.offset.bodyOffset(this[0])}o.offset.initialized||o.offset.initialize();var J=this[0],G=J.offsetParent,F=J,O=J.ownerDocument,M,H=O.documentElement,K=O.body,L=O.defaultView,E=L.getComputedStyle(J,null),N=J.offsetTop,I=J.offsetLeft;while((J=J.parentNode)&&J!==K&&J!==H){M=L.getComputedStyle(J,null);N-=J.scrollTop,I-=J.scrollLeft;if(J===G){N+=J.offsetTop,I+=J.offsetLeft;if(o.offset.doesNotAddBorder&&!(o.offset.doesAddBorderForTableAndCells&&/^t(able|d|h)$/i.test(J.tagName))){N+=parseInt(M.borderTopWidth,10)||0,I+=parseInt(M.borderLeftWidth,10)||0}F=G,G=J.offsetParent}if(o.offset.subtractsBorderForOverflowNotVisible&&M.overflow!=="visible"){N+=parseInt(M.borderTopWidth,10)||0,I+=parseInt(M.borderLeftWidth,10)||0}E=M}if(E.position==="relative"||E.position==="static"){N+=K.offsetTop,I+=K.offsetLeft}if(E.position==="fixed"){N+=Math.max(H.scrollTop,K.scrollTop),I+=Math.max(H.scrollLeft,K.scrollLeft)}return{top:N,left:I}}}o.offset={initialize:function(){if(this.initialized){return}var L=document.body,F=document.createElement("div"),H,G,N,I,M,E,J=L.style.marginTop,K='<div style="position:absolute;top:0;left:0;margin:0;border:5px solid #000;padding:0;width:1px;height:1px;"><div></div></div><table style="position:absolute;top:0;left:0;margin:0;border:5px solid #000;padding:0;width:1px;height:1px;" cellpadding="0" cellspacing="0"><tr><td></td></tr></table>';M={position:"absolute",top:0,left:0,margin:0,border:0,width:"1px",height:"1px",visibility:"hidden"};for(E in M){F.style[E]=M[E]}F.innerHTML=K;L.insertBefore(F,L.firstChild);H=F.firstChild,G=H.firstChild,I=H.nextSibling.firstChild.firstChild;this.doesNotAddBorder=(G.offsetTop!==5);this.doesAddBorderForTableAndCells=(I.offsetTop===5);H.style.overflow="hidden",H.style.position="relative";this.subtractsBorderForOverflowNotVisible=(G.offsetTop===-5);L.style.marginTop="1px";this.doesNotIncludeMarginInBodyOffset=(L.offsetTop===0);L.style.marginTop=J;L.removeChild(F);this.initialized=true},bodyOffset:function(E){o.offset.initialized||o.offset.initialize();var G=E.offsetTop,F=E.offsetLeft;if(o.offset.doesNotIncludeMarginInBodyOffset){G+=parseInt(o.curCSS(E,"marginTop",true),10)||0,F+=parseInt(o.curCSS(E,"marginLeft",true),10)||0}return{top:G,left:F}}};o.fn.extend({position:function(){var I=0,H=0,F;if(this[0]){var G=this.offsetParent(),J=this.offset(),E=/^body|html$/i.test(G[0].tagName)?{top:0,left:0}:G.offset();J.top-=j(this,"marginTop");J.left-=j(this,"marginLeft");E.top+=j(G,"borderTopWidth");E.left+=j(G,"borderLeftWidth");F={top:J.top-E.top,left:J.left-E.left}}return F},offsetParent:function(){var E=this[0].offsetParent||document.body;while(E&&(!/^body|html$/i.test(E.tagName)&&o.css(E,"position")=="static")){E=E.offsetParent}return o(E)}});o.each(["Left","Top"],function(F,E){var G="scroll"+E;o.fn[G]=function(H){if(!this[0]){return null}return H!==g?this.each(function(){this==l||this==document?l.scrollTo(!F?H:o(l).scrollLeft(),F?H:o(l).scrollTop()):this[G]=H}):this[0]==l||this[0]==document?self[F?"pageYOffset":"pageXOffset"]||o.boxModel&&document.documentElement[G]||document.body[G]:this[0][G]}});o.each(["Height","Width"],function(I,G){var E=I?"Left":"Top",H=I?"Right":"Bottom",F=G.toLowerCase();o.fn["inner"+G]=function(){return this[0]?o.css(this[0],F,false,"padding"):null};o.fn["outer"+G]=function(K){return this[0]?o.css(this[0],F,false,K?"margin":"border"):null};var J=G.toLowerCase();o.fn[J]=function(K){return this[0]==l?document.compatMode=="CSS1Compat"&&document.documentElement["client"+G]||document.body["client"+G]:this[0]==document?Math.max(document.documentElement["client"+G],document.body["scroll"+G],document.documentElement["scroll"+G],document.body["offset"+G],document.documentElement["offset"+G]):K===g?(this.length?o.css(this[0],J):null):this.css(J,typeof K==="string"?K:K+"px")}})})();
					
					(function($){var helper={},current,title,tID,IE=$.browser.msie&&/MSIE\s(5\.5|6\.)/.test(navigator.userAgent),track=false;$.tooltip={blocked:false,defaults:{delay:200,fade:false,showURL:true,extraClass:"",top:15,left:15,id:"tooltip"},block:function(){$.tooltip.blocked=!$.tooltip.blocked;}};$.fn.extend({tooltip:function(settings){settings=$.extend({},$.tooltip.defaults,settings);createHelper(settings);return this.each(function(){$.data(this,"tooltip",settings);this.tOpacity=helper.parent.css("opacity");this.tooltipText=this.title;$(this).removeAttr("title");this.alt="";}).mouseover(save).mouseout(hide).click(hide);},fixPNG:IE?function(){return this.each(function(){var image=$(this).css('backgroundImage');if(image.match(/^url\(["']?(.*\.png)["']?\)$/i)){image=RegExp.$1;$(this).css({'backgroundImage':'none','filter':"progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='"+image+"')"}).each(function(){var position=$(this).css('position');if(position!='absolute'&&position!='relative')$(this).css('position','relative');});}});}:function(){return this;},unfixPNG:IE?function(){return this.each(function(){$(this).css({'filter':'',backgroundImage:''});});}:function(){return this;},hideWhenEmpty:function(){return this.each(function(){$(this)[$(this).html()?"show":"hide"]();});},url:function(){return this.attr('href')||this.attr('src');}});function createHelper(settings){if(helper.parent)return;helper.parent=$('<div id="'+settings.id+'"><h3></h3><div class="body"></div><div class="url"></div></div>').appendTo(document.body).hide();if($.fn.bgiframe)helper.parent.bgiframe();helper.title=$('h3',helper.parent);helper.body=$('div.body',helper.parent);helper.url=$('div.url',helper.parent);}function settings(element){return $.data(element,"tooltip");}function handle(event){if(settings(this).delay)tID=setTimeout(show,settings(this).delay);else
					show();track=!!settings(this).track;$(document.body).bind('mousemove',update);update(event);}function save(){if($.tooltip.blocked||this==current||(!this.tooltipText&&!settings(this).bodyHandler))return;current=this;title=this.tooltipText;if(settings(this).bodyHandler){helper.title.hide();var bodyContent=settings(this).bodyHandler.call(this);if(bodyContent.nodeType||bodyContent.jquery){helper.body.empty().append(bodyContent)}else{helper.body.html(bodyContent);}helper.body.show();}else if(settings(this).showBody){var parts=title.split(settings(this).showBody);helper.title.html(parts.shift()).show();helper.body.empty();for(var i=0,part;(part=parts[i]);i++){if(i>0)helper.body.append("<br/>");helper.body.append(part);}helper.body.hideWhenEmpty();}else{helper.title.html(title).show();helper.body.hide();}if(settings(this).showURL&&$(this).url())helper.url.html($(this).url().replace('http://','')).show();else
					helper.url.hide();helper.parent.addClass(settings(this).extraClass);if(settings(this).fixPNG)helper.parent.fixPNG();handle.apply(this,arguments);}function show(){tID=null;if((!IE||!$.fn.bgiframe)&&settings(current).fade){if(helper.parent.is(":animated"))helper.parent.stop().show().fadeTo(settings(current).fade,current.tOpacity);else
					helper.parent.is(':visible')?helper.parent.fadeTo(settings(current).fade,current.tOpacity):helper.parent.fadeIn(settings(current).fade);}else{helper.parent.show();}update();}function update(event){if($.tooltip.blocked)return;if(event&&event.target.tagName=="OPTION"){return;}if(!track&&helper.parent.is(":visible")){$(document.body).unbind('mousemove',update)}if(current==null){$(document.body).unbind('mousemove',update);return;}helper.parent.removeClass("viewport-right").removeClass("viewport-bottom");var left=helper.parent[0].offsetLeft;var top=helper.parent[0].offsetTop;if(event){left=event.pageX+settings(current).left;top=event.pageY+settings(current).top;var right='auto';if(settings(current).positionLeft){right=$(window).width()-left;left='auto';}helper.parent.css({left:left,right:right,top:top});}var v=viewport(),h=helper.parent[0];if(v.x+v.cx<h.offsetLeft+h.offsetWidth){left-=h.offsetWidth+20+settings(current).left;helper.parent.css({left:left+'px'}).addClass("viewport-right");}if(v.y+v.cy<h.offsetTop+h.offsetHeight){top-=h.offsetHeight+20+settings(current).top;helper.parent.css({top:top+'px'}).addClass("viewport-bottom");}}function viewport(){return{x:$(window).scrollLeft(),y:$(window).scrollTop(),cx:$(window).width(),cy:$(window).height()};}function hide(event){if($.tooltip.blocked)return;if(tID)clearTimeout(tID);current=null;var tsettings=settings(this);function complete(){helper.parent.removeClass(tsettings.extraClass).hide().css("opacity","");}if((!IE||!$.fn.bgiframe)&&tsettings.fade){if(helper.parent.is(':animated'))helper.parent.stop().fadeTo(tsettings.fade,0,complete);else
					helper.parent.stop().fadeOut(tsettings.fade,complete);}else
					complete();if(settings(this).fixPNG)helper.parent.unfixPNG();}})(jQuery);
					
					jQuery(document).ready(function() {
						jQuery('.pluginbuddy_tip').tooltip({
							track: true,
							delay: 0,
							showURL: false,
							showBody: " - ",
							fade: 250
						});
						jQuery('.toggle').click(function(e) {
							jQuery( '#toggle-' + jQuery(this).attr('id') ).slideToggle();
						});
						jQuery('.option_toggle').change(function(e) {
							if (jQuery(this).attr('checked')) {
								jQuery('.' + jQuery(this).attr('id') + '_toggle' ).slideToggle();
							} else {
								jQuery('.' + jQuery(this).attr('id') + '_toggle' ).slideToggle();
							}
						});
						jQuery('#ithemes_mysql_test').click(function(e) {
							jQuery('#ithemes_loading').show();
							jQuery.post('importbuddy.php', { action: "mysql_test", server: jQuery('#mysql_server').val(), name: jQuery('#mysql_name').val(), user: jQuery('#mysql_user').val(), pass: jQuery('#mysql_password').val(), prefix: jQuery('#mysql_prefix').val() }, 
								function(data) {
									//alert(data);
									jQuery('#ithemes_loading').html( data );
									/*
									if (data.status!='1') {
										alert('Error Saving Changes: '+data.msg);
									}
									jQuery('#ithemes_loading').html( data );
									*/
								}
							); //,"json");
							return false;
						});
					});
					
				</script>
				<body>
					
					<h1>BackupBuddy Restoration & Migration Tool</h1>
					
					<?php
					if ( $this->_options['skip_files'] != false ) {
						echo 'WARNING: Debug option to skip files is set to true. Files will not be extracted.<br>';
					}
					if ( $this->_options['wipe_database'] != false ) {
						echo 'WARNING: Debug option to wipe database is set to true. All existing data will be erased.<br>';
					}
					if ( $this->_options['skip_database_import'] != false ) {
						echo 'WARNING: Debug option to skip database import set to true. The database will not be imported.<br>';
					}
					if ( $this->_options['skip_database_migration'] != false ) {
						echo 'WARNING: Debug option to skip database import set to true. The database will not be migrated.<br>';
					}
					if ( $this->_options['skip_htaccess'] != false ) {
						echo 'WARNING: Debug option to skip migrating the htaccess file is set to true. The file will not be migrated if needed.<br>';
					}
					if ( $this->_options['force_compatibility_medium'] != false ) {
						echo 'WARNING: Debug option to force medium compatibility mode. This may result in slower, less reliable operation.<br>';
					}
					if ( $this->_options['force_compatibility_slow'] != false ) {
						echo 'WARNING: Debug option to force slow compatibility mode. This may result in slower, less reliable operation.<br>';
					}
					if ( $this->_options['show_php_warnings'] != false ) {
						echo 'WARNING: Debug option to strictly report all errors & warnings from PHP is set to true. This may cause operation problems.<br>';
					}
					echo '<br>';
					?>
					
					<div style="width: 600px; margin-left: auto; margin-right: auto;">
					<div style="max-width: 600px; margin:10px; padding: 20px; border:1px solid #ccc; -moz-border-radius: 10px; -webkit-border-radius: 10px; background: #F9F9F9; -moz-box-shadow: 10px 10px 15px -12px #356D8F; -webkit-box-shadow: 10px 10px 15px -12px #356D8F; padding-right: 20px;">
						<div class="wrap">
							<!-- div class="menu" -->
								<h2><?php echo 'Step ' . $this->_step . ' of ' . $this->_total_steps; ?></h2>
							<!-- /div -->
							<div class="innerwrap">
			<?php
		}
		
		
		/**
		 *	print_html_footer()
		 *
		 *	Output HTML footer wrapper to browser.
		 *
		 */
		function print_html_footer() {
			echo '</div>';
			echo '</div>';
			echo '</div></div>';
			echo '<div style="clear: both;"><br><br><a href="http://pluginbuddy.com" style="text-decoration: none; vertical-align: -2px;">';
			if ( $this->_step != '6' ) { // after importbuddy deleted on step 6, this image cant load so dont put it in...
				echo ezimg::genImageTag( 'pluginbuddy.png' );
			}
			echo ' PluginBuddy.com</a></div>';
			if ( $this->_version == '#VERSION#') {
				//echo '<i>Version Unknown</i>';
			} else {
				echo '<br><i>This script last updated at BackupBuddy v' . $this->_version . '</i>';
			}
			echo '</body>';
			echo '</html>';
		}
		
		
		/*	extract_files()
		 *
		 *	Extracts files trying the following methods:
		 *
		 *	1) Command line UNZIP executable.
		 *	2) PHP ZipArchive Class compiled in PHP.
		 *	3) PCLZip PHP-based zip emulation.
		 *
		 */
		function extract_files() {
			$this->log( 'Starting to unzip.' );
			
			$failed = true;
			
			// HIGHSPEED
			if ( ( $this->_options['force_compatibility_medium'] === false ) && ( $this->_options['force_compatibility_slow'] === false ) ) {
				echo 'Attempting high speed extraction (normal mode) ... ';
				if ( $this->extract_files_highspeed() === true ) {
					$failed = false;
					echo 'Done.<br><br>';
					$this->log( 'Highspeed Native command line unzip success!' );
					
					return true;
				} else {
					$failed = true;
					echo 'Failed.<br><br>Falling back to slower method.<br><br>';
					$this->log( 'High speed extraction FAILED! Falling back to slower method.', 'error' );
				}
			} else {
				echo 'Skipping high speed extraction based on advanced settings.<br><br>';
			}
			
			// MEDIUMSPEED
			if ( $this->_options['force_compatibility_slow'] === false ) {
				if ( $failed === true ) {
					echo 'Attempting medium speed extraction (compatibility mode: ZipArchive) ... ';
					if ( $this->extract_files_mediumspeed() === true ) {
						$failed = false;
						echo 'Done.<br><br>';
						$this->log( 'Mediumspeed ZipArchive class unzip success!' );
						
						return true;
					} else {
						$failed = true;
						echo 'Failed.<br><br>Falling back to slower method.<br><br>';
						$this->log( 'Medium speed extraction FAILED! Falling back to slower method.', 'error' );
					}
				}
			} else {
				echo 'Skipping medium speed extraction based on advanced settings.<br><br>';
			}
			
			if ( $failed === true ) {
				// LOWSPEED
				echo 'Attempting low speed extraction (compatibility mode: PCLZip) ... ';
				if ( $this->extract_files_lowspeed() === true ) {
					$failed = false;
					echo 'Done.<br><br>';
					$this->log( 'Lowspeed PCLZIP unzip success!' );
					
					return true;
				} else {
					$failed = true;
					echo 'Failed. Final compatibility unzip method has failed.<br><br>';
					$this->log( 'Low speed extraction FAILED! No more fallbacks.', 'error' );
				}
			}
			
			unset( $failed );
			
			return false; // If we got this far, nothing succeeded!
		}
		
		
		function extract_files_highspeed() {
			$this->log( 'Starting highspeed extraction.' );
			
			$file = ABSPATH . '/' . $this->_options['file'];
			$directory = ABSPATH;
			$options = '';
		
			$phpinfo = $this->phpinfo_array();
			if ( strpos( $phpinfo['PHP Core']['disable_functions'], 'exec') != true ) { // If exec is not explicitly disabled in PHP...
				$command = 'unzip -qo'; // q = quiet, o = overwrite without prompt.
				$command .= " '$file' -d '$directory' -x 'importbuddy.php'"; // x excludes importbuddy script to prevent overwriting newer importbuddy on extract step.

				if ( file_exists( ABSPATH . '\unzip.exe' ) ) {
					$this->alert( 'Attempting to use provided unzip.exe for Windows zip functionality.' );
					
					$command = str_replace( '\'', '"', $command ); // Windows wants double quotes
					$command = ABSPATH . '\\' . $command;
				}
				
				exec( $command, $exec_return_a, $exec_return_b);
				
				if ( $exec_return_b != '' ) { // UNZIP FAILED!
					// ERROR LIST: http://www.mkssoftware.com/docs/man1/unzip.1.asp
					if ( $exec_return_b == '50' ) {
						$this->alert( 'The disk is (or was) full during extraction <b>OR</b> the zip/unzip command does not have write permission to your directory.  Try increasing permissions for the directory.', true );
					}
					
					return false;
				}
			} else {
				echo '<br><br><br>';
				$this->alert( 'Highspeed extraction failed. Exec() function is explicitly disabled in PHP. Falling back to slower mode to try again.', true );
				
				return false;
			}
			
			// Unzip reported success so make sure key files exist!
			if ( 
			  ( !file_exists ( ABSPATH . '/wp-content/uploads/temp_'.$this->_options['zip_id'].'/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( ABSPATH . '/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( ABSPATH . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/backupbuddy_dat.php' ) )
			  ) {
				$this->alert( 'Highspeed extraction reported success; HOWEVER, key files are missing. Falling back to slower mode to try again.', true );
				return false;
			}
			
			return true;
		}
		
		
		function extract_files_mediumspeed() {
			$this->log( 'Starting mediumspeed extraction.' );
			
			$file = ABSPATH . '/' . $this->_options['file'];
			$directory = ABSPATH;
			
			if ( class_exists( 'ZipArchive' ) ) {
				$this->log( 'ZipArchive class exists.' );
				$zip = new ZipArchive;
				if ( $zip->open( $file ) === true ) {
					if ( $zip->extractTo( $directory ) === true ) {
						$this->log( 'ZipArchive extraction success.' );
						return true;
					} else {
						$this->alert( 'ZipArchive was available but failed extracting files.', true );
						return false;
					}
				} else {
					$this->alert( 'Unable to open ZipArchive ZIP: ' . $file, true );
					return false;
				}
			} else {
				$this->log( 'ZipArchive class unavailable.' );
				if ( version_compare( phpversion(), '5.0.0', '<' ) ) {
					$reason = 'as this server runs PHP 4. See if your host can upgrade to PHP 5.2+.';
				} elseif ( version_compare( phpversion(), '5.2.0', '<' ) ) {
					$reason = 'as this server runs PHP ' . phpversion() . '. See if your host can upgrade to PHP 5.2+.';
				} else {
					$reason = 'as this server\'s PHP wasn\'t compiled with the --enable-zip. See if your host is able to update PHP with this option.';
				}
				$this->alert( 'Mediumspeed extraction failed. ZipArchive is not available ' . $reason . '. Falling back to slower mode to try again.', true );
				return false;
			}
			
			// Unzip reported success so make sure key files exist!
			if ( 
			  ( !file_exists ( ABSPATH . '/wp-content/uploads/temp_'.$this->_options['zip_id'].'/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( ABSPATH . '/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( ABSPATH . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/backupbuddy_dat.php' ) )
			  ) {
				$this->alert( 'Highspeed extraction reported success; HOWEVER, key files are missing. Falling back to slower mode to try again.', true );
				return false;
			}
			
			return true;
		}
		
		
		function extract_files_lowspeed() {
			$this->log( 'Starting lowspeed extraction.' );
			
			$file = ABSPATH . '/' . $this->_options['file'];
			
			$archive = new PclZip( $file );
			
			if ( file_exists( $file ) ) {
				$result = $archive->extract(); // Extract to current directory. Explicity using PCLZIP_OPT_PATH results in extraction to a PCLZIP_OPT_PATH subfolder.
			} else {
				$this->alert( 'Unable to open ZIP for PCLZIP. Error #54556565834.', true );
			}
			
			if ( 0 == $result ) {
				echo 'Error: '.$archive->errorInfo(true);
				$this->alert( 'Lowspeed extraction failed. Message: ' . $archive->errorInfo(true), true );
				return false;
			}
			
			// Unzip reported success so make sure key files exist!
			if ( 
			  ( !file_exists ( ABSPATH . '/wp-content/uploads/temp_'.$this->_options['zip_id'].'/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( ABSPATH . '/backupbuddy_dat.php' ) ) &&
			  ( !file_exists ( ABSPATH . '/wp-content/uploads/backupbuddy_temp/' . $this->_options['zip_id'] . '/backupbuddy_dat.php' ) )
			  ) {
				$this->alert( 'Highspeed extraction reported success; HOWEVER, key files are missing. Falling back to slower mode to try again.', true );
				return false;
			}
			
			return true;
		}
		
		
		// Used for database serialization replacing.
		function recursive_array_replace( $find, $replace, &$data ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					if ( is_array( $value ) ) {
						$this->recursive_array_replace( $find, $replace, $data[$key] );
					} else {
						// Have to check if it's string to ensure no switching to string for booleans/numbers/nulls - don't need any nasty conversions.
						if ( is_string( $value ) ) $data[$key] = str_replace( $find, $replace, $value );
					}
				}
			} else {
				if ( is_string( $data ) ) $data = str_replace( $find, $replace, $data );
			}
		}
		
		
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
				if ( !remove_dir( $dir . "/" . $item ) ) {
					chmod( $dir . "/" . $item, 0777 );
					if ( !remove_dir( $dir . "/" . $item ) ) {
						return false;
					}
				}
			}
			return rmdir($dir);
		}
		
		
		// Get phpinfo() data as an array.
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
		
		
		function rand_string( $length = 32, $chars = 'abcdefghijkmnopqrstuvwxyz1234567890' ) {
			$chars_length = ( strlen( $chars ) - 1 );
			$string = $chars{rand(0, $chars_length)};
			for ( $i = 1; $i < $length; $i = strlen( $string ) ) {
				$r = $chars{rand(0, $chars_length)};
				if ($r != $string{$i - 1}) $string .=  $r;
			return $string;
			}
		}
		
		
		function migrate_wp_config() {
			$this->log( 'Beginning migrating wp-config' );
			
			echo 'Migrating wp-config.php ... ';
			
			flush();
			
			if (file_exists(ABSPATH.'/wp-config.php')) {
				$updated_home_url = false;
				$wp_config = array();
				$lines = file( ABSPATH . '/wp-config.php' );
				foreach($lines as $line) {
					if (strstr($line,'DB_NAME')) {
						$wp_config[] = "define('DB_NAME', '".$this->_options['db_name']."');\r\n";
					} elseif (strstr($line,'DB_USER')) {
						$wp_config[] = "define('DB_USER', '".$this->_options['db_user']."');\r\n";
					} elseif (strstr($line,'DB_PASSWORD')) {
						$wp_config[] = "define('DB_PASSWORD', '".$this->_options['db_password']."');\r\n";
					} elseif (strstr($line,'DB_HOST')) {
						$wp_config[] = "define('DB_HOST', '".$this->_options['db_server']."');\r\n";
					} elseif (strstr($line,'$table_prefix')) {
						$wp_config[] = '$table_prefix = '."'". $this->_options['db_prefix'] ."';\r\n";
					} elseif ( strstr( $line, 'WP_SITEURL' ) ) { // Update site URL if defined.
						$wp_config[] = "define('WP_SITEURL', '" . trim( $this->_options['siteurl'], '/' ) . "');\r\n";
					} elseif ( strstr( $line, 'WP_HOME' ) ) { // Update home URL if defined.
						$wp_config[] = "define('WP_HOME', '" . trim( $this->_options['home'], '/' ) . "');\r\n";
						$updated_home_url = true;
					} else {
						$wp_config[] = $line;
					}
				}
				unset($lines);
				unset($line);
				
				// If a custom home URL was defined but never written then append this line at the end of the wp-config.php.
				/*
				if ( ( $this->_options['siteurl'] != $this->_options['home'] ) && ( $updated_home_url === false ) ) {
					$wp_config[] = "define('WP_HOME', '" . $this->_options['home'] . "');\r\n";
				}
				*/
				
				
				if ( chmod( ABSPATH.'/wp-config.php', 0755 ) ) {
					$this->log( 'Changed wp-config permissions to 755.' );
				}
				
				// Write changes to config file.
				if ( false === ( $fh = fopen( ABSPATH . '/wp-config.php', 'w' ) ) ) {
					$this->alert( 'ERROR: Unable to save changes to wp-config.php. Make sure this file has write permissions!', true );
					return false;
				}
				
				foreach ($wp_config as $key => $value) {
					fwrite($fh, $value);
				}
				unset($value);
				unset($key);
				fclose($fh);
				unset($fh);
			} else {
				echo '<br>' . ezimg::genImageTag( 'bullet_error.png' ) . 'Note: wp-config.php not found.  This is only a database restoration.<br>';
			}
			
			echo 'Done.<br>';
			
			$this->log( 'Finished migrating wp-config' );
			
			return true;
		}
		
		function delete_directory($dir) {
			if ($handle = opendir($dir)) {
				$array = array();
				
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
				
						if(is_dir($dir.$file))
						{
							if(!@rmdir($dir.$file)) // Empty directory? Remove it
							{
							//deleteDir($dir.$file.'/'); // Not empty? Delete the files inside it
							}
						}
						else
						{
						   @unlink($dir.$file);
						}
					}
				}
				closedir($handle);
				
				@rmdir($dir);
			}
		}

	}
}
$PluginBuddyImportBuddy = new PluginBuddyImportBuddy();

































































//*********** Example of Use *********//
// http://ezinedesigner.com/embed-images-php.html
// echo ezimg::genImageTag('bullet_go.png');
// generates:  <img src='?ezimg=bullet_go.png' alt='' width='16' height='16' />  
//*********** End of Example *********//

class ezimg {

	 function genImageTag($name){
	  $image = ezimg::getImgData($name);     
	  $result = "<img src='?ezimg={$name}' alt='' width='{$image['width']}' height='{$image['height']}' border='0' />";
	  return $result;
	 }
	 
	 function showImg($name){ 
	  $image = ezimg::getImgData($name); 
	  header("Content-type: image/{$image['type']}");
	  echo gzuncompress(base64_decode(str_replace(' ', '', $image['code'])));
	  exit;	  
	 }

	 function getImgData($name){
	  $images = array(
	   'blank.gif' => array( 
		 'type'=>'gif', 'width'=>'1', 'height'=>'1',
		 'code'=>"eJxz93SzsExkZGBkaGCAAsWfLIwgWgdEgGQYmJhcGBmsAXIBA/w="
		),
		'pluginbuddy_tip.png' => array( 
			'type'=>'png', 'width'=>'15', 'height'=>'15',
			'code'=>"eJwBNQLK/YlQTkcNChoKAAAADUlIRFIAAAAPAAAADwgGAAAAO9aVSgAAAfxJREFUeNp9kT2IGkEY
			hufOa3JRi4SkNoGDlIqY4sgRgk0IaTRFbK4QIoLFoZBCsEhEEIwga6EgYmEKRRAb/wp/lwi7BJTj
			Agb1GhWFgEaF4CknN3lZUPY2Py88MMx8z8w3MySTyQjk83lSKpUEarXaY/ASvAZPwH6xWCTpdJqI
			I5VfAOYr0ul0vvd6vU4TKZfLdchv/idbWJbl5vP5jEqyXC5/8TzfhPweyr5Ufl6pVPjNZnNN/5Eb
			BBucRyKRd9BkghyLxUgikQhMp9Of28LBYECz2SyNx+MUNxB3cJVKpViVSvVgKz9EOxwVJZfLCUwm
			ExqNRul4PN6t1ev1C4vF8hbqAQmFQifVavWbWF6tVnQ4HAobBINBulgsdmutVuvSarV+gKwgfr//
			uFAoQL4VoV1sTPv9PhWH47hLk8nkhXyfeDweRSAQaNwgohqhVXREpUkmky2dTne2lYnT6fzY7XZ/
			iIvwbdTlct0SR6PRzGw25yHqwV1it9sJULvd7gbudiUuxg/sxuv1+trn83E41Q/xCMi2MsELnuKk
			Rrvd3nUg+rqZ1+vl9Xr9Z0jPgJxIsmc0Go/xGIzNZmMZhmmGw+Fzh8PxxWAwZNVq9SfUnIB7YA9I
			I0weyuVylVarNWo0GrNSqXyFuafgEVBIxD8DmSAycAccgoO/Sb8BZ/P7CEyp2F0AAAAASUVORK5C
			YII2SgQp"
		),
	   'bullet_go.png' => array( 
		 'type'=>'png', 'width'=>'16', 'height'=>'16',
		 'code'=>"eJwBmgFl/olQTkcNChoKAAAADUlIRFIAAAAQAAAAEAgGAAAAH/P/YQAAAARnQU1BAACvyDcFiukA
				  AAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAABLElEQVQ4y2P4//8/AyWYYZgb
				  kL3Y/GvqfMP/8XN1OckyIHm+4dfGzVH/w2do/PefKs9J0ID8pbb/cxeb/01faPw3ca7+35r1of9X
				  nZ74v2S1/3/XfvG/tt2CPHgNADr5/4Zz0/6vPTsFrHHF6Qn/J+wp+b/weNf/jKVu/03b2f/qNjPy
				  4zQA6GSw5r5d+f87d2T/b92W9r9hc+L/pq3p/2ccav4fs8Dmv2o9wx+cBkTP1vy/8tSE/0tP9P5f
				  eKzr/7yjHUBDsv5PP9T0P22px3/FWoZX0pUMBjgNCJyu+M9zovQ/537Rf9bd/P/i5lv9n3aw4X/S
				  Yrf/8rUMzyUrGbRIigXtZsav8Qud/8tXMzwBalYnORqBTv4qV838X7SSQZHslChczsA5uPMCAIeV
				  x/oO3azsAAAAAElFTkSuQmCCHZbPdQ=="
		),
		'bullet_error.png' => array( 
		 'type'=>'png', 'width'=>'16', 'height'=>'16',
		 'code'=>"eJwBxgE5/olQTkcNChoKAAAADUlIRFIAAAAQAAAAEAgGAAAAH/P/YQAAAARnQU1BAACvyDcFiukA
				  AAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAABWElEQVQ4y2P8//8/AyWAiYFC
				  QLEBLLgkHhyID/3/71/x/3///ZRclrwiyQX39sTw/fv9u5Ffzt8cSJeR7AWgpgI+WW9NPklDhj8/
				  fydfXxdgSrQBtzaFaAE15fOKCDD8/bSJQVwvReDvz1+1l5d4sBI0AGgT05+fvypFtaKFGH6cYzi1
				  aCkDj8B3hj8/fvkAsR9BA4A2uXEJ68XwCn5j+PfzPgPD/38Mf7+cZlB0KGb88/Nn86lJlrw4Dbi4
				  0I0NaEuLgJwBw79vl4B6vzMYB6sz/Pv1jIGD4xaDuG6EJtB1hTgNANqeKqTibczF+4bh/5+3DAyM
				  zAxn190CBSnDvx+3GETV5IFqfmcfatLRwpoO/vz4CfS7P9DIBwxMfKJAkf8MZkkBcHuYOZUZlFzL
				  xW5tbmoECoRiGvDzF8e52cHfgQkH6Px/SPg/nGYA0f//SyC7gHHoZyYATLOoGeLYqQwAAAAASUVO
				  RK5CYIKy1slr"
		),
		 'pluginbuddy.png' => array( 
		 'type'=>'png', 'width'=>'16', 'height'=>'16',
		 'code'=>"eJwBIA3f8olQTkcNChoKAAAADUlIRFIAAAAQAAAAEAgGAAAAH/P/YQAAAAlwSFlzAAALEwAACxMB
				  AJqcGAAACk9pQ0NQUGhvdG9zaG9wIElDQyBwcm9maWxlAAB42p1TZ1RT6RY99970QkuIgJRLb1IV
				  CCBSQouAFJEmKiEJEEqIIaHZFVHBEUVFBBvIoIgDjo6AjBVRLAyKCtgH5CGijoOjiIrK++F7o2vW
				  vPfmzf611z7nrPOds88HwAgMlkgzUTWADKlCHhHgg8fExuHkLkCBCiRwABAIs2Qhc/0jAQD4fjw8
				  KyLAB74AAXjTCwgAwE2bwDAch/8P6kKZXAGAhAHAdJE4SwiAFABAeo5CpgBARgGAnZgmUwCgBABg
				  y2Ni4wBQLQBgJ3/m0wCAnfiZewEAW5QhFQGgkQAgE2WIRABoOwCsz1aKRQBYMAAUZkvEOQDYLQAw
				  SVdmSACwtwDAzhALsgAIDAAwUYiFKQAEewBgyCMjeACEmQAURvJXPPErrhDnKgAAeJmyPLkkOUWB
				  WwgtcQdXVy4eKM5JFysUNmECYZpALsJ5mRkygTQP4PPMAACgkRUR4IPz/XjODq7OzjaOtg5fLeq/
				  Bv8iYmLj/uXPq3BAAADhdH7R/iwvsxqAOwaAbf6iJe4EaF4LoHX3i2ayD0C1AKDp2lfzcPh+PDxF
				  oZC52dnl5OTYSsRCW2HKV33+Z8JfwFf9bPl+PPz39eC+4iSBMl2BRwT44MLM9EylHM+SCYRi3OaP
				  R/y3C//8HdMixEliuVgqFONREnGORJqM8zKlIolCkinFJdL/ZOLfLPsDPt81ALBqPgF7kS2oXWMD
				  9ksnEFh0wOL3AADyu2/B1CgIA4Bog+HPd//vP/1HoCUAgGZJknEAAF5EJC5UyrM/xwgAAESggSqw
				  QRv0wRgswAYcwQXcwQv8YDaEQiTEwkIQQgpkgBxyYCmsgkIohs2wHSpgL9RAHTTAUWiGk3AOLsJV
				  uA49cA/6YQiewSi8gQkEQcgIE2Eh2ogBYopYI44IF5mF+CHBSAQSiyQgyYgUUSJLkTVIMVKKVCBV
				  SB3yPXICOYdcRrqRO8gAMoL8hrxHMZSBslE91Ay1Q7moNxqERqIL0GR0MZqPFqCb0HK0Gj2MNqHn
				  0KtoD9qPPkPHMMDoGAczxGwwLsbDQrE4LAmTY8uxIqwMq8YasFasA7uJ9WPPsXcEEoFFwAk2BHdC
				  IGEeQUhYTFhO2EioIBwkNBHaCTcJA4RRwicik6hLtCa6EfnEGGIyMYdYSCwj1hKPEy8Qe4hDxDck
				  EolDMie5kAJJsaRU0hLSRtJuUiPpLKmbNEgaI5PJ2mRrsgc5lCwgK8iF5J3kw+Qz5BvkIfJbCp1i
				  QHGk+FPiKFLKakoZ5RDlNOUGZZgyQVWjmlLdqKFUETWPWkKtobZSr1GHqBM0dZo5zYMWSUulraKV
				  0xpoF2j3aa/odLoR3ZUeTpfQV9LL6Ufol+gD9HcMDYYVg8eIZygZmxgHGGcZdxivmEymGdOLGcdU
				  MDcx65jnmQ+Zb1VYKrYqfBWRygqVSpUmlRsqL1Spqqaq3qoLVfNVy1SPqV5Tfa5GVTNT46kJ1Jar
				  VaqdUOtTG1NnqTuoh6pnqG9UP6R+Wf2JBlnDTMNPQ6RRoLFf47zGIAtjGbN4LCFrDauGdYE1xCax
				  zdl8diq7mP0du4s9qqmhOUMzSjNXs1LzlGY/B+OYcficdE4J5yinl/N+it4U7yniKRumNEy5MWVc
				  a6qWl5ZYq0irUatH6702ru2nnaa9RbtZ+4EOQcdKJ1wnR2ePzgWd51PZU92nCqcWTT069a4uqmul
				  G6G7RHe/bqfumJ6+XoCeTG+n3nm95/ocfS/9VP1t+qf1RwxYBrMMJAbbDM4YPMU1cW88HS/H2/FR
				  Q13DQEOlYZVhl+GEkbnRPKPVRo1GD4xpxlzjJONtxm3GoyYGJiEmS03qTe6aUk25pimmO0w7TMfN
				  zM2izdaZNZs9Mdcy55vnm9eb37dgWnhaLLaotrhlSbLkWqZZ7ra8boVaOVmlWFVaXbNGrZ2tJda7
				  rbunEae5TpNOq57WZ8Ow8bbJtqm3GbDl2AbbrrZttn1hZ2IXZ7fFrsPuk72Tfbp9jf09Bw2H2Q6r
				  HVodfnO0chQ6Vjrems6c7j99xfSW6S9nWM8Qz9gz47YTyynEaZ1Tm9NHZxdnuXOD84iLiUuCyy6X
				  Pi6bG8bdyL3kSnT1cV3hetL1nZuzm8LtqNuv7jbuae6H3J/MNJ8pnlkzc9DDyEPgUeXRPwuflTBr
				  36x+T0NPgWe15yMvYy+RV63XsLeld6r3Ye8XPvY+cp/jPuM8N94y3llfzDfAt8i3y0/Db55fhd9D
				  fyP/ZP96/9EAp4AlAWcDiYFBgVsC+/h6fCG/jj8622X2stntQYyguUEVQY+CrYLlwa0haMjskK0h
				  9+eYzpHOaQ6FUH7o1tAHYeZhi8N+DCeFh4VXhj+OcIhYGtExlzV30dxDc99E+kSWRN6bZzFPOa8t
				  SjUqPqouajzaN7o0uj/GLmZZzNVYnVhJbEscOS4qrjZubL7f/O3zh+Kd4gvjexeYL8hdcHmhzsL0
				  hacWqS4SLDqWQEyITjiU8EEQKqgWjCXyE3cljgp5wh3CZyIv0TbRiNhDXCoeTvJIKk16kuyRvDV5
				  JMUzpSzluYQnqZC8TA1M3Zs6nhaadiBtMj06vTGDkpGQcUKqIU2TtmfqZ+ZmdsusZYWy/sVui7cv
				  HpUHyWuzkKwFWS0KtkKm6FRaKNcqB7JnZVdmv82JyjmWq54rze3Ms8rbkDec75//7RLCEuGStqWG
				  S1ctHVjmvaxqObI8cXnbCuMVBSuGVgasPLiKtipt1U+r7VeXrn69JnpNa4FewcqCwbUBa+sLVQrl
				  hX3r3NftXU9YL1nftWH6hp0bPhWJiq4U2xeXFX/YKNx45RuHb8q/mdyUtKmrxLlkz2bSZunm3i2e
				  Ww6Wqpfmlw5uDdnatA3fVrTt9fZF2y+XzSjbu4O2Q7mjvzy4vGWnyc7NOz9UpFT0VPpUNu7S3bVh
				  1/hu0e4be7z2NOzV21u89/0+yb7bVQFVTdVm1WX7Sfuz9z+uiarp+Jb7bV2tTm1x7ccD0gP9ByMO
				  tte51NUd0j1UUo/WK+tHDscfvv6d73ctDTYNVY2cxuIjcER55On3Cd/3Hg062naMe6zhB9Mfdh1n
				  HS9qQprymkabU5r7W2Jbuk/MPtHW6t56/EfbHw+cNDxZeUrzVMlp2umC05Nn8s+MnZWdfX4u+dxg
				  26K2e+djzt9qD2/vuhB04dJF/4vnO7w7zlzyuHTystvlE1e4V5qvOl9t6nTqPP6T00/Hu5y7mq65
				  XGu57nq9tXtm9+kbnjfO3fS9efEW/9bVnjk93b3zem/3xff13xbdfnIn/c7Lu9l3J+6tvE+8X/RA
				  7UHZQ92H1T9b/tzY79x/asB3oPPR3Ef3BoWDz/6R9Y8PQwWPmY/Lhg2G6544Pjk54j9y/en8p0PP
				  ZM8mnhf+ov7LrhcWL3741evXztGY0aGX8peTv218pf3qwOsZr9vGwsYevsl4MzFe9Fb77cF33Hcd
				  76PfD0/kfCB/KP9o+bH1U9Cn+5MZk5P/BAOY8/xjMy3bAAAAIGNIUk0AAHolAACAgwAA+f8AAIDp
				  AAB1MAAA6mAAADqYAAAXb5JfxUYAAAJLSURBVHjadJNNSFRRFMd/97375s04ypijadgEYoqBuAvC
				  FiVERORGF4Mbt1KLalXLwLZRtHFRpNBSwaBN0EraVAstkgoMIlCTcT7MGXW+3nunxbwZncn+cOGe
				  ez74n/+5R8XjcRtoBcKAwSGEeqgjdw84AHY0EJm5+2opHCwNKCsmIAiCbStENQEKJXmKRReFAhRS
				  XleFkt6Yejx+XgOhgHa7zY47mO23FAhKCbtOJ6ubPxGEc10x2u2dSrIo3PRzgtuPTgNN2qetRPe4
				  yu43qxxnl1PMLkcRgbFBj+mRgVofYvW6gAkYutqvSLGu4Scff7OeywOQWglw/2IX4YAvg1eoxdVE
				  02a9YpHQ4d22IGQdamgeiTUaNf71p4wAs6O9TAxGudHXyvx4H0pVfI3z0A2j4ubrLVYzaYZjzfS3
				  VWg8W9lmbOEHQ21R3kyeqYvXjWRGegzebZZZ+LaDoTK+QIqwVfFVHoy6LAFwy1kTJ8m94SBz122a
				  tYUn4AkEjQAzV0PcGw6Bk8RzstUKooFSoaS3rcTDlmJmDosC12KLztlWV39OhgGhu2WfC+1511u7
				  Ypa9AJQ3VL5oJYCCBnKbqcjcqWh2QiRtKcR9/2Fqfi/34Db0ngCFKmzsfH07/TQykI0LylQqWN5K
				  tywCeyoej5tAk78LJuAAHUtDk0sJpyMK0KmT6ctfXl4CUr5uLrAPHFSNnH9qo0+WOm1Mp/KRSicD
				  wB6QaJyawfE4CHqZT1UjILvf/e37B/o/BbKjay/GgWbf3gd2jwv8OwCOm9VeSiwMvwAAAABJRU5E
				  rkJggr35OwY="
		),
		'button-grad.png' => array( 
		 'type'=>'png', 'width'=>'5', 'height'=>'30',
		 'code'=>"eJzrDPBz5+WS4mJgYOD19HAJAtKsQCzHwQYkn9Rd2QukWIqdPEM4gKCGI6UDyOcs8IgsZmDgFgJh
				  xkt36/4ABRVLXCNKgvPTSsoTi1IZfBOTi/JzU1MyExXcMotSy/OLsosVTPQMXqmplwIVi4EUOxel
				  JpZk5ucphGTmpjIYGuobmusbWNzKKS0EqnDzdHEMqZiTfOXNza/zGXk3GPw5LpCc8PP/f/llCoft
				  c5puhPw7nqYW9nZKlEvT5A1RLW/O/W8+7xmy5YX9Li+hGpbLv+cv8PJxZHjV5F0eXGHUDzSRwdPV
				  z2WdU0ITAKUwUss="
		),
		'white-grad.png' => array( 
		 'type'=>'png', 'width'=>'5', 'height'=>'30',
		 'code'=>"eJzrDPBz5+WS4mJgYOD19HAJAtKsQCzHwQYkn9Rd2QukWIqdPEM4gKCGI6UDyOcs8IgsZmDgFgJh
              xkt36/4ABRVLXCNKgvPTSsoTi1IZfBOTi/JzU1MyExXcMotSy/OLsosVTPQMXqmplwIVi4EUOxel
              JpZk5ucphGTmpjIYGuobmusbWNzKKS0EqlD1dHEMqZiT/Of////1bCYHWvQOgECTq2LZwYWLalfs
              KP7RKsTQzGOlds/bxBGogcHT1c9lnVNCEwBxhkCT"
		),
		'loading.gif' => array(
		 'type'=>'gif', 'width'=>'16', 'height'=>'11',
		 'code'=>"eJxz93SzsEwUYOBm+MLA8P//AwYGhlu3Dly4sP3Vq3NsbKxArp6eRlNTUUJCyK5dS5SUZL28HLu6
				  KlNSIvbtW66mpsjCwuzn5/Ls2akbN/Z9+XLdwsLwzp1Dnz5d3bZtwYIFvadObXr37iIDDCj+5/Zz
				  DQl2dgxwNdIzYGYECf2Tci5KTSxJTVEozyzJUEjMSqzIyU9M0cvMS8tnUPzJwskNVKUD0g1yJAOr
				  roJCX0rmvCULuB6dYlrnINBscca04HL1vds75vOxPxFs3/LI4QSPXvG6a1yua6c5YTNBJSFRgjVh
				  3opMmfaJ+w7cOrTyzaXlvc/f7H29O0JfYw5ro7VgpwQTNo1mQKu7E3Ued6hwKknxJC9JWqRxUvr8
				  lt7XRlIHpjMfaNob/fGEt2Dj2YuXdwj7zTwUyq0lMs3vkAA2o0yBRi3hcBUMYep9bJKk4nnxlU2/
				  8PnSyufWs3iXM59osUva4P5LtP/MxGJLFqnK1de6d2nNDVLEZpIRJDyubYp45hI4eYfHpaSgUNeT
				  W2IfWcW9nXabMe1wfOALZcbeE89OsCbP81p59asUVr+ZQ4xRKYxtWbEpaVnmzdbE6RmPF23LnRXL
				  p/Dmb9P2RMEHzHrTfVX6Z5gwxesymJmdXrDvQRAHNrP0IWY9AbpnxaZ3q4pkVIO28D4wXf97u0zB
				  dO5vXUdmFbbK/IrTZTBhc45cvwRrAJFshjU8lQEAXE/9dg=="
		)
	  );
	  if (isset($images[$name])) {
		return $images[$name];
	  } else {
		return $images['blank.gif'];
	 }
	}
}














// --------------------------------------------------------------------------------
// PhpConcept Library - Zip Module 2.8.2
// --------------------------------------------------------------------------------
// License GNU/LGPL - Vincent Blavet - August 2009
// http://www.phpconcept.net
// --------------------------------------------------------------------------------
//
// Presentation :
//   PclZip is a PHP library that manage ZIP archives.
//   So far tests show that archives generated by PclZip are readable by
//   WinZip application and other tools.
//
// Description :
//   See readme.txt and http://www.phpconcept.net
//
// Warning :
//   This library and the associated files are non commercial, non professional
//   work.
//   It should not have unexpected results. However if any damage is caused by
//   this software the author can not be responsible.
//   The use of this software is at the risk of the user.
//
// --------------------------------------------------------------------------------
// $Id: pclzip.lib.php,v 1.60 2009/09/30 21:01:04 vblavet Exp $
// --------------------------------------------------------------------------------

  // ----- Constants
  if (!defined('PCLZIP_READ_BLOCK_SIZE')) {
    define( 'PCLZIP_READ_BLOCK_SIZE', 2048 );
  }
  
  // ----- File list separator
  // In version 1.x of PclZip, the separator for file list is a space
  // (which is not a very smart choice, specifically for windows paths !).
  // A better separator should be a comma (,). This constant gives you the
  // abilty to change that.
  // However notice that changing this value, may have impact on existing
  // scripts, using space separated filenames.
  // Recommanded values for compatibility with older versions :
  //define( 'PCLZIP_SEPARATOR', ' ' );
  // Recommanded values for smart separation of filenames.
  if (!defined('PCLZIP_SEPARATOR')) {
    define( 'PCLZIP_SEPARATOR', ',' );
  }

  // ----- Error configuration
  // 0 : PclZip Class integrated error handling
  // 1 : PclError external library error handling. By enabling this
  //     you must ensure that you have included PclError library.
  // [2,...] : reserved for futur use
  if (!defined('PCLZIP_ERROR_EXTERNAL')) {
    define( 'PCLZIP_ERROR_EXTERNAL', 0 );
  }

  // ----- Optional static temporary directory
  //       By default temporary files are generated in the script current
  //       path.
  //       If defined :
  //       - MUST BE terminated by a '/'.
  //       - MUST be a valid, already created directory
  //       Samples :
  // define( 'PCLZIP_TEMPORARY_DIR', '/temp/' );
  // define( 'PCLZIP_TEMPORARY_DIR', 'C:/Temp/' );
  if (!defined('PCLZIP_TEMPORARY_DIR')) {
    define( 'PCLZIP_TEMPORARY_DIR', '' );
  }

  // ----- Optional threshold ratio for use of temporary files
  //       Pclzip sense the size of the file to add/extract and decide to
  //       use or not temporary file. The algorythm is looking for 
  //       memory_limit of PHP and apply a ratio.
  //       threshold = memory_limit * ratio.
  //       Recommended values are under 0.5. Default 0.47.
  //       Samples :
  // define( 'PCLZIP_TEMPORARY_FILE_RATIO', 0.5 );
  if (!defined('PCLZIP_TEMPORARY_FILE_RATIO')) {
    define( 'PCLZIP_TEMPORARY_FILE_RATIO', 0.47 );
  }

// --------------------------------------------------------------------------------
// ***** UNDER THIS LINE NOTHING NEEDS TO BE MODIFIED *****
// --------------------------------------------------------------------------------

  // ----- Global variables
  $g_pclzip_version = "2.8.2";

  // ----- Error codes
  //   -1 : Unable to open file in binary write mode
  //   -2 : Unable to open file in binary read mode
  //   -3 : Invalid parameters
  //   -4 : File does not exist
  //   -5 : Filename is too long (max. 255)
  //   -6 : Not a valid zip file
  //   -7 : Invalid extracted file size
  //   -8 : Unable to create directory
  //   -9 : Invalid archive extension
  //  -10 : Invalid archive format
  //  -11 : Unable to delete file (unlink)
  //  -12 : Unable to rename file (rename)
  //  -13 : Invalid header checksum
  //  -14 : Invalid archive size
  define( 'PCLZIP_ERR_USER_ABORTED', 2 );
  define( 'PCLZIP_ERR_NO_ERROR', 0 );
  define( 'PCLZIP_ERR_WRITE_OPEN_FAIL', -1 );
  define( 'PCLZIP_ERR_READ_OPEN_FAIL', -2 );
  define( 'PCLZIP_ERR_INVALID_PARAMETER', -3 );
  define( 'PCLZIP_ERR_MISSING_FILE', -4 );
  define( 'PCLZIP_ERR_FILENAME_TOO_LONG', -5 );
  define( 'PCLZIP_ERR_INVALID_ZIP', -6 );
  define( 'PCLZIP_ERR_BAD_EXTRACTED_FILE', -7 );
  define( 'PCLZIP_ERR_DIR_CREATE_FAIL', -8 );
  define( 'PCLZIP_ERR_BAD_EXTENSION', -9 );
  define( 'PCLZIP_ERR_BAD_FORMAT', -10 );
  define( 'PCLZIP_ERR_DELETE_FILE_FAIL', -11 );
  define( 'PCLZIP_ERR_RENAME_FILE_FAIL', -12 );
  define( 'PCLZIP_ERR_BAD_CHECKSUM', -13 );
  define( 'PCLZIP_ERR_INVALID_ARCHIVE_ZIP', -14 );
  define( 'PCLZIP_ERR_MISSING_OPTION_VALUE', -15 );
  define( 'PCLZIP_ERR_INVALID_OPTION_VALUE', -16 );
  define( 'PCLZIP_ERR_ALREADY_A_DIRECTORY', -17 );
  define( 'PCLZIP_ERR_UNSUPPORTED_COMPRESSION', -18 );
  define( 'PCLZIP_ERR_UNSUPPORTED_ENCRYPTION', -19 );
  define( 'PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE', -20 );
  define( 'PCLZIP_ERR_DIRECTORY_RESTRICTION', -21 );

  // ----- Options values
  define( 'PCLZIP_OPT_PATH', 77001 );
  define( 'PCLZIP_OPT_ADD_PATH', 77002 );
  define( 'PCLZIP_OPT_REMOVE_PATH', 77003 );
  define( 'PCLZIP_OPT_REMOVE_ALL_PATH', 77004 );
  define( 'PCLZIP_OPT_SET_CHMOD', 77005 );
  define( 'PCLZIP_OPT_EXTRACT_AS_STRING', 77006 );
  define( 'PCLZIP_OPT_NO_COMPRESSION', 77007 );
  define( 'PCLZIP_OPT_BY_NAME', 77008 );
  define( 'PCLZIP_OPT_BY_INDEX', 77009 );
  define( 'PCLZIP_OPT_BY_EREG', 77010 );
  define( 'PCLZIP_OPT_BY_PREG', 77011 );
  define( 'PCLZIP_OPT_COMMENT', 77012 );
  define( 'PCLZIP_OPT_ADD_COMMENT', 77013 );
  define( 'PCLZIP_OPT_PREPEND_COMMENT', 77014 );
  define( 'PCLZIP_OPT_EXTRACT_IN_OUTPUT', 77015 );
  define( 'PCLZIP_OPT_REPLACE_NEWER', 77016 );
  define( 'PCLZIP_OPT_STOP_ON_ERROR', 77017 );
  // Having big trouble with crypt. Need to multiply 2 long int
  // which is not correctly supported by PHP ...
  //define( 'PCLZIP_OPT_CRYPT', 77018 );
  define( 'PCLZIP_OPT_EXTRACT_DIR_RESTRICTION', 77019 );
  define( 'PCLZIP_OPT_TEMP_FILE_THRESHOLD', 77020 );
  define( 'PCLZIP_OPT_ADD_TEMP_FILE_THRESHOLD', 77020 ); // alias
  define( 'PCLZIP_OPT_TEMP_FILE_ON', 77021 );
  define( 'PCLZIP_OPT_ADD_TEMP_FILE_ON', 77021 ); // alias
  define( 'PCLZIP_OPT_TEMP_FILE_OFF', 77022 );
  define( 'PCLZIP_OPT_ADD_TEMP_FILE_OFF', 77022 ); // alias
  
  // ----- File description attributes
  define( 'PCLZIP_ATT_FILE_NAME', 79001 );
  define( 'PCLZIP_ATT_FILE_NEW_SHORT_NAME', 79002 );
  define( 'PCLZIP_ATT_FILE_NEW_FULL_NAME', 79003 );
  define( 'PCLZIP_ATT_FILE_MTIME', 79004 );
  define( 'PCLZIP_ATT_FILE_CONTENT', 79005 );
  define( 'PCLZIP_ATT_FILE_COMMENT', 79006 );

  // ----- Call backs values
  define( 'PCLZIP_CB_PRE_EXTRACT', 78001 );
  define( 'PCLZIP_CB_POST_EXTRACT', 78002 );
  define( 'PCLZIP_CB_PRE_ADD', 78003 );
  define( 'PCLZIP_CB_POST_ADD', 78004 );
  /* For futur use
  define( 'PCLZIP_CB_PRE_LIST', 78005 );
  define( 'PCLZIP_CB_POST_LIST', 78006 );
  define( 'PCLZIP_CB_PRE_DELETE', 78007 );
  define( 'PCLZIP_CB_POST_DELETE', 78008 );
  */

  // --------------------------------------------------------------------------------
  // Class : PclZip
  // Description :
  //   PclZip is the class that represent a Zip archive.
  //   The public methods allow the manipulation of the archive.
  // Attributes :
  //   Attributes must not be accessed directly.
  // Methods :
  //   PclZip() : Object creator
  //   create() : Creates the Zip archive
  //   listContent() : List the content of the Zip archive
  //   extract() : Extract the content of the archive
  //   properties() : List the properties of the archive
  // --------------------------------------------------------------------------------
  class PclZip
  {
    // ----- Filename of the zip file
    var $zipname = '';

    // ----- File descriptor of the zip file
    var $zip_fd = 0;

    // ----- Internal error handling
    var $error_code = 1;
    var $error_string = '';
    
    // ----- Current status of the magic_quotes_runtime
    // This value store the php configuration for magic_quotes
    // The class can then disable the magic_quotes and reset it after
    var $magic_quotes_status;

  // --------------------------------------------------------------------------------
  // Function : PclZip()
  // Description :
  //   Creates a PclZip object and set the name of the associated Zip archive
  //   filename.
  //   Note that no real action is taken, if the archive does not exist it is not
  //   created. Use create() for that.
  // --------------------------------------------------------------------------------
  function PclZip($p_zipname)
  {

    // ----- Tests the zlib
    if (!function_exists('gzopen'))
    {
      die('Abort '.basename(__FILE__).' : Missing zlib extensions');
    }

    // ----- Set the attributes
    $this->zipname = $p_zipname;
    $this->zip_fd = 0;
    $this->magic_quotes_status = -1;

    // ----- Return
    return;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function :
  //   create($p_filelist, $p_add_dir="", $p_remove_dir="")
  //   create($p_filelist, $p_option, $p_option_value, ...)
  // Description :
  //   This method supports two different synopsis. The first one is historical.
  //   This method creates a Zip Archive. The Zip file is created in the
  //   filesystem. The files and directories indicated in $p_filelist
  //   are added in the archive. See the parameters description for the
  //   supported format of $p_filelist.
  //   When a directory is in the list, the directory and its content is added
  //   in the archive.
  //   In this synopsis, the function takes an optional variable list of
  //   options. See bellow the supported options.
  // Parameters :
  //   $p_filelist : An array containing file or directory names, or
  //                 a string containing one filename or one directory name, or
  //                 a string containing a list of filenames and/or directory
  //                 names separated by spaces.
  //   $p_add_dir : A path to add before the real path of the archived file,
  //                in order to have it memorized in the archive.
  //   $p_remove_dir : A path to remove from the real path of the file to archive,
  //                   in order to have a shorter path memorized in the archive.
  //                   When $p_add_dir and $p_remove_dir are set, $p_remove_dir
  //                   is removed first, before $p_add_dir is added.
  // Options :
  //   PCLZIP_OPT_ADD_PATH :
  //   PCLZIP_OPT_REMOVE_PATH :
  //   PCLZIP_OPT_REMOVE_ALL_PATH :
  //   PCLZIP_OPT_COMMENT :
  //   PCLZIP_CB_PRE_ADD :
  //   PCLZIP_CB_POST_ADD :
  // Return Values :
  //   0 on failure,
  //   The list of the added files, with a status of the add action.
  //   (see PclZip::listContent() for list entry format)
  // --------------------------------------------------------------------------------
  function create($p_filelist)
  {
    $v_result=1;

    // ----- Reset the error handler
    $this->privErrorReset();

    // ----- Set default values
    $v_options = array();
    $v_options[PCLZIP_OPT_NO_COMPRESSION] = FALSE;

    // ----- Look for variable options arguments
    $v_size = func_num_args();

    // ----- Look for arguments
    if ($v_size > 1) {
      // ----- Get the arguments
      $v_arg_list = func_get_args();

      // ----- Remove from the options list the first argument
      array_shift($v_arg_list);
      $v_size--;

      // ----- Look for first arg
      if ((is_integer($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {

        // ----- Parse the options
        $v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options,
                                            array (PCLZIP_OPT_REMOVE_PATH => 'optional',
                                                   PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                                                   PCLZIP_OPT_ADD_PATH => 'optional',
                                                   PCLZIP_CB_PRE_ADD => 'optional',
                                                   PCLZIP_CB_POST_ADD => 'optional',
                                                   PCLZIP_OPT_NO_COMPRESSION => 'optional',
                                                   PCLZIP_OPT_COMMENT => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
                                                   //, PCLZIP_OPT_CRYPT => 'optional'
                                             ));
        if ($v_result != 1) {
          return 0;
        }
      }

      // ----- Look for 2 args
      // Here we need to support the first historic synopsis of the
      // method.
      else {

        // ----- Get the first argument
        $v_options[PCLZIP_OPT_ADD_PATH] = $v_arg_list[0];

        // ----- Look for the optional second argument
        if ($v_size == 2) {
          $v_options[PCLZIP_OPT_REMOVE_PATH] = $v_arg_list[1];
        }
        else if ($v_size > 2) {
          PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER,
		                       "Invalid number / type of arguments");
          return 0;
        }
      }
    }
    
    // ----- Look for default option values
    $this->privOptionDefaultThreshold($v_options);

    // ----- Init
    $v_string_list = array();
    $v_att_list = array();
    $v_filedescr_list = array();
    $p_result_list = array();
    
    // ----- Look if the $p_filelist is really an array
    if (is_array($p_filelist)) {
    
      // ----- Look if the first element is also an array
      //       This will mean that this is a file description entry
      if (isset($p_filelist[0]) && is_array($p_filelist[0])) {
        $v_att_list = $p_filelist;
      }
      
      // ----- The list is a list of string names
      else {
        $v_string_list = $p_filelist;
      }
    }

    // ----- Look if the $p_filelist is a string
    else if (is_string($p_filelist)) {
      // ----- Create a list from the string
      $v_string_list = explode(PCLZIP_SEPARATOR, $p_filelist);
    }

    // ----- Invalid variable type for $p_filelist
    else {
      PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid variable type p_filelist");
      return 0;
    }
    
    // ----- Reformat the string list
    if (sizeof($v_string_list) != 0) {
      foreach ($v_string_list as $v_string) {
        if ($v_string != '') {
          $v_att_list[][PCLZIP_ATT_FILE_NAME] = $v_string;
        }
        else {
        }
      }
    }
    
    // ----- For each file in the list check the attributes
    $v_supported_attributes
    = array ( PCLZIP_ATT_FILE_NAME => 'mandatory'
             ,PCLZIP_ATT_FILE_NEW_SHORT_NAME => 'optional'
             ,PCLZIP_ATT_FILE_NEW_FULL_NAME => 'optional'
             ,PCLZIP_ATT_FILE_MTIME => 'optional'
             ,PCLZIP_ATT_FILE_CONTENT => 'optional'
             ,PCLZIP_ATT_FILE_COMMENT => 'optional'
						);
    foreach ($v_att_list as $v_entry) {
      $v_result = $this->privFileDescrParseAtt($v_entry,
                                               $v_filedescr_list[],
                                               $v_options,
                                               $v_supported_attributes);
      if ($v_result != 1) {
        return 0;
      }
    }

    // ----- Expand the filelist (expand directories)
    $v_result = $this->privFileDescrExpand($v_filedescr_list, $v_options);
    if ($v_result != 1) {
      return 0;
    }

    // ----- Call the create fct
    $v_result = $this->privCreate($v_filedescr_list, $p_result_list, $v_options);
    if ($v_result != 1) {
      return 0;
    }

    // ----- Return
    return $p_result_list;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function :
  //   add($p_filelist, $p_add_dir="", $p_remove_dir="")
  //   add($p_filelist, $p_option, $p_option_value, ...)
  // Description :
  //   This method supports two synopsis. The first one is historical.
  //   This methods add the list of files in an existing archive.
  //   If a file with the same name already exists, it is added at the end of the
  //   archive, the first one is still present.
  //   If the archive does not exist, it is created.
  // Parameters :
  //   $p_filelist : An array containing file or directory names, or
  //                 a string containing one filename or one directory name, or
  //                 a string containing a list of filenames and/or directory
  //                 names separated by spaces.
  //   $p_add_dir : A path to add before the real path of the archived file,
  //                in order to have it memorized in the archive.
  //   $p_remove_dir : A path to remove from the real path of the file to archive,
  //                   in order to have a shorter path memorized in the archive.
  //                   When $p_add_dir and $p_remove_dir are set, $p_remove_dir
  //                   is removed first, before $p_add_dir is added.
  // Options :
  //   PCLZIP_OPT_ADD_PATH :
  //   PCLZIP_OPT_REMOVE_PATH :
  //   PCLZIP_OPT_REMOVE_ALL_PATH :
  //   PCLZIP_OPT_COMMENT :
  //   PCLZIP_OPT_ADD_COMMENT :
  //   PCLZIP_OPT_PREPEND_COMMENT :
  //   PCLZIP_CB_PRE_ADD :
  //   PCLZIP_CB_POST_ADD :
  // Return Values :
  //   0 on failure,
  //   The list of the added files, with a status of the add action.
  //   (see PclZip::listContent() for list entry format)
  // --------------------------------------------------------------------------------
  function add($p_filelist)
  {
    $v_result=1;

    // ----- Reset the error handler
    $this->privErrorReset();

    // ----- Set default values
    $v_options = array();
    $v_options[PCLZIP_OPT_NO_COMPRESSION] = FALSE;

    // ----- Look for variable options arguments
    $v_size = func_num_args();

    // ----- Look for arguments
    if ($v_size > 1) {
      // ----- Get the arguments
      $v_arg_list = func_get_args();

      // ----- Remove form the options list the first argument
      array_shift($v_arg_list);
      $v_size--;

      // ----- Look for first arg
      if ((is_integer($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {

        // ----- Parse the options
        $v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options,
                                            array (PCLZIP_OPT_REMOVE_PATH => 'optional',
                                                   PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                                                   PCLZIP_OPT_ADD_PATH => 'optional',
                                                   PCLZIP_CB_PRE_ADD => 'optional',
                                                   PCLZIP_CB_POST_ADD => 'optional',
                                                   PCLZIP_OPT_NO_COMPRESSION => 'optional',
                                                   PCLZIP_OPT_COMMENT => 'optional',
                                                   PCLZIP_OPT_ADD_COMMENT => 'optional',
                                                   PCLZIP_OPT_PREPEND_COMMENT => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
                                                   //, PCLZIP_OPT_CRYPT => 'optional'
												   ));
        if ($v_result != 1) {
          return 0;
        }
      }

      // ----- Look for 2 args
      // Here we need to support the first historic synopsis of the
      // method.
      else {

        // ----- Get the first argument
        $v_options[PCLZIP_OPT_ADD_PATH] = $v_add_path = $v_arg_list[0];

        // ----- Look for the optional second argument
        if ($v_size == 2) {
          $v_options[PCLZIP_OPT_REMOVE_PATH] = $v_arg_list[1];
        }
        else if ($v_size > 2) {
          // ----- Error log
          PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid number / type of arguments");

          // ----- Return
          return 0;
        }
      }
    }

    // ----- Look for default option values
    $this->privOptionDefaultThreshold($v_options);

    // ----- Init
    $v_string_list = array();
    $v_att_list = array();
    $v_filedescr_list = array();
    $p_result_list = array();
    
    // ----- Look if the $p_filelist is really an array
    if (is_array($p_filelist)) {
    
      // ----- Look if the first element is also an array
      //       This will mean that this is a file description entry
      if (isset($p_filelist[0]) && is_array($p_filelist[0])) {
        $v_att_list = $p_filelist;
      }
      
      // ----- The list is a list of string names
      else {
        $v_string_list = $p_filelist;
      }
    }

    // ----- Look if the $p_filelist is a string
    else if (is_string($p_filelist)) {
      // ----- Create a list from the string
      $v_string_list = explode(PCLZIP_SEPARATOR, $p_filelist);
    }

    // ----- Invalid variable type for $p_filelist
    else {
      PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid variable type '".gettype($p_filelist)."' for p_filelist");
      return 0;
    }
    
    // ----- Reformat the string list
    if (sizeof($v_string_list) != 0) {
      foreach ($v_string_list as $v_string) {
        $v_att_list[][PCLZIP_ATT_FILE_NAME] = $v_string;
      }
    }
    
    // ----- For each file in the list check the attributes
    $v_supported_attributes
    = array ( PCLZIP_ATT_FILE_NAME => 'mandatory'
             ,PCLZIP_ATT_FILE_NEW_SHORT_NAME => 'optional'
             ,PCLZIP_ATT_FILE_NEW_FULL_NAME => 'optional'
             ,PCLZIP_ATT_FILE_MTIME => 'optional'
             ,PCLZIP_ATT_FILE_CONTENT => 'optional'
             ,PCLZIP_ATT_FILE_COMMENT => 'optional'
						);
    foreach ($v_att_list as $v_entry) {
      $v_result = $this->privFileDescrParseAtt($v_entry,
                                               $v_filedescr_list[],
                                               $v_options,
                                               $v_supported_attributes);
      if ($v_result != 1) {
        return 0;
      }
    }

    // ----- Expand the filelist (expand directories)
    $v_result = $this->privFileDescrExpand($v_filedescr_list, $v_options);
    if ($v_result != 1) {
      return 0;
    }

    // ----- Call the create fct
    $v_result = $this->privAdd($v_filedescr_list, $p_result_list, $v_options);
    if ($v_result != 1) {
      return 0;
    }

    // ----- Return
    return $p_result_list;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : listContent()
  // Description :
  //   This public method, gives the list of the files and directories, with their
  //   properties.
  //   The properties of each entries in the list are (used also in other functions) :
  //     filename : Name of the file. For a create or add action it is the filename
  //                given by the user. For an extract function it is the filename
  //                of the extracted file.
  //     stored_filename : Name of the file / directory stored in the archive.
  //     size : Size of the stored file.
  //     compressed_size : Size of the file's data compressed in the archive
  //                       (without the headers overhead)
  //     mtime : Last known modification date of the file (UNIX timestamp)
  //     comment : Comment associated with the file
  //     folder : true | false
  //     index : index of the file in the archive
  //     status : status of the action (depending of the action) :
  //              Values are :
  //                ok : OK !
  //                filtered : the file / dir is not extracted (filtered by user)
  //                already_a_directory : the file can not be extracted because a
  //                                      directory with the same name already exists
  //                write_protected : the file can not be extracted because a file
  //                                  with the same name already exists and is
  //                                  write protected
  //                newer_exist : the file was not extracted because a newer file exists
  //                path_creation_fail : the file is not extracted because the folder
  //                                     does not exist and can not be created
  //                write_error : the file was not extracted because there was a
  //                              error while writing the file
  //                read_error : the file was not extracted because there was a error
  //                             while reading the file
  //                invalid_header : the file was not extracted because of an archive
  //                                 format error (bad file header)
  //   Note that each time a method can continue operating when there
  //   is an action error on a file, the error is only logged in the file status.
  // Return Values :
  //   0 on an unrecoverable failure,
  //   The list of the files in the archive.
  // --------------------------------------------------------------------------------
  function listContent()
  {
    $v_result=1;

    // ----- Reset the error handler
    $this->privErrorReset();

    // ----- Check archive
    if (!$this->privCheckFormat()) {
      return(0);
    }

    // ----- Call the extracting fct
    $p_list = array();
    if (($v_result = $this->privList($p_list)) != 1)
    {
      unset($p_list);
      return(0);
    }

    // ----- Return
    return $p_list;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function :
  //   extract($p_path="./", $p_remove_path="")
  //   extract([$p_option, $p_option_value, ...])
  // Description :
  //   This method supports two synopsis. The first one is historical.
  //   This method extract all the files / directories from the archive to the
  //   folder indicated in $p_path.
  //   If you want to ignore the 'root' part of path of the memorized files
  //   you can indicate this in the optional $p_remove_path parameter.
  //   By default, if a newer file with the same name already exists, the
  //   file is not extracted.
  //
  //   If both PCLZIP_OPT_PATH and PCLZIP_OPT_ADD_PATH aoptions
  //   are used, the path indicated in PCLZIP_OPT_ADD_PATH is append
  //   at the end of the path value of PCLZIP_OPT_PATH.
  // Parameters :
  //   $p_path : Path where the files and directories are to be extracted
  //   $p_remove_path : First part ('root' part) of the memorized path
  //                    (if any similar) to remove while extracting.
  // Options :
  //   PCLZIP_OPT_PATH :
  //   PCLZIP_OPT_ADD_PATH :
  //   PCLZIP_OPT_REMOVE_PATH :
  //   PCLZIP_OPT_REMOVE_ALL_PATH :
  //   PCLZIP_CB_PRE_EXTRACT :
  //   PCLZIP_CB_POST_EXTRACT :
  // Return Values :
  //   0 or a negative value on failure,
  //   The list of the extracted files, with a status of the action.
  //   (see PclZip::listContent() for list entry format)
  // --------------------------------------------------------------------------------
  function extract()
  {
    $v_result=1;

    // ----- Reset the error handler
    $this->privErrorReset();

    // ----- Check archive
    if (!$this->privCheckFormat()) {
      return(0);
    }

    // ----- Set default values
    $v_options = array();
//    $v_path = "./";
    $v_path = '';
    $v_remove_path = "";
    $v_remove_all_path = false;

    // ----- Look for variable options arguments
    $v_size = func_num_args();

    // ----- Default values for option
    $v_options[PCLZIP_OPT_EXTRACT_AS_STRING] = FALSE;

    // ----- Look for arguments
    if ($v_size > 0) {
      // ----- Get the arguments
      $v_arg_list = func_get_args();

      // ----- Look for first arg
      if ((is_integer($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {

        // ----- Parse the options
        $v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options,
                                            array (PCLZIP_OPT_PATH => 'optional',
                                                   PCLZIP_OPT_REMOVE_PATH => 'optional',
                                                   PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                                                   PCLZIP_OPT_ADD_PATH => 'optional',
                                                   PCLZIP_CB_PRE_EXTRACT => 'optional',
                                                   PCLZIP_CB_POST_EXTRACT => 'optional',
                                                   PCLZIP_OPT_SET_CHMOD => 'optional',
                                                   PCLZIP_OPT_BY_NAME => 'optional',
                                                   PCLZIP_OPT_BY_EREG => 'optional',
                                                   PCLZIP_OPT_BY_PREG => 'optional',
                                                   PCLZIP_OPT_BY_INDEX => 'optional',
                                                   PCLZIP_OPT_EXTRACT_AS_STRING => 'optional',
                                                   PCLZIP_OPT_EXTRACT_IN_OUTPUT => 'optional',
                                                   PCLZIP_OPT_REPLACE_NEWER => 'optional'
                                                   ,PCLZIP_OPT_STOP_ON_ERROR => 'optional'
                                                   ,PCLZIP_OPT_EXTRACT_DIR_RESTRICTION => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
												    ));
        if ($v_result != 1) {
          return 0;
        }

        // ----- Set the arguments
        if (isset($v_options[PCLZIP_OPT_PATH])) {
          $v_path = $v_options[PCLZIP_OPT_PATH];
        }
        if (isset($v_options[PCLZIP_OPT_REMOVE_PATH])) {
          $v_remove_path = $v_options[PCLZIP_OPT_REMOVE_PATH];
        }
        if (isset($v_options[PCLZIP_OPT_REMOVE_ALL_PATH])) {
          $v_remove_all_path = $v_options[PCLZIP_OPT_REMOVE_ALL_PATH];
        }
        if (isset($v_options[PCLZIP_OPT_ADD_PATH])) {
          // ----- Check for '/' in last path char
          if ((strlen($v_path) > 0) && (substr($v_path, -1) != '/')) {
            $v_path .= '/';
          }
          $v_path .= $v_options[PCLZIP_OPT_ADD_PATH];
        }
      }

      // ----- Look for 2 args
      // Here we need to support the first historic synopsis of the
      // method.
      else {

        // ----- Get the first argument
        $v_path = $v_arg_list[0];

        // ----- Look for the optional second argument
        if ($v_size == 2) {
          $v_remove_path = $v_arg_list[1];
        }
        else if ($v_size > 2) {
          // ----- Error log
          PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid number / type of arguments");

          // ----- Return
          return 0;
        }
      }
    }

    // ----- Look for default option values
    $this->privOptionDefaultThreshold($v_options);

    // ----- Trace

    // ----- Call the extracting fct
    $p_list = array();
    $v_result = $this->privExtractByRule($p_list, $v_path, $v_remove_path,
	                                     $v_remove_all_path, $v_options);
    if ($v_result < 1) {
      unset($p_list);
      return(0);
    }

    // ----- Return
    return $p_list;
  }
  // --------------------------------------------------------------------------------


  // --------------------------------------------------------------------------------
  // Function :
  //   extractByIndex($p_index, $p_path="./", $p_remove_path="")
  //   extractByIndex($p_index, [$p_option, $p_option_value, ...])
  // Description :
  //   This method supports two synopsis. The first one is historical.
  //   This method is doing a partial extract of the archive.
  //   The extracted files or folders are identified by their index in the
  //   archive (from 0 to n).
  //   Note that if the index identify a folder, only the folder entry is
  //   extracted, not all the files included in the archive.
  // Parameters :
  //   $p_index : A single index (integer) or a string of indexes of files to
  //              extract. The form of the string is "0,4-6,8-12" with only numbers
  //              and '-' for range or ',' to separate ranges. No spaces or ';'
  //              are allowed.
  //   $p_path : Path where the files and directories are to be extracted
  //   $p_remove_path : First part ('root' part) of the memorized path
  //                    (if any similar) to remove while extracting.
  // Options :
  //   PCLZIP_OPT_PATH :
  //   PCLZIP_OPT_ADD_PATH :
  //   PCLZIP_OPT_REMOVE_PATH :
  //   PCLZIP_OPT_REMOVE_ALL_PATH :
  //   PCLZIP_OPT_EXTRACT_AS_STRING : The files are extracted as strings and
  //     not as files.
  //     The resulting content is in a new field 'content' in the file
  //     structure.
  //     This option must be used alone (any other options are ignored).
  //   PCLZIP_CB_PRE_EXTRACT :
  //   PCLZIP_CB_POST_EXTRACT :
  // Return Values :
  //   0 on failure,
  //   The list of the extracted files, with a status of the action.
  //   (see PclZip::listContent() for list entry format)
  // --------------------------------------------------------------------------------
  //function extractByIndex($p_index, options...)
  function extractByIndex($p_index)
  {
    $v_result=1;

    // ----- Reset the error handler
    $this->privErrorReset();

    // ----- Check archive
    if (!$this->privCheckFormat()) {
      return(0);
    }

    // ----- Set default values
    $v_options = array();
//    $v_path = "./";
    $v_path = '';
    $v_remove_path = "";
    $v_remove_all_path = false;

    // ----- Look for variable options arguments
    $v_size = func_num_args();

    // ----- Default values for option
    $v_options[PCLZIP_OPT_EXTRACT_AS_STRING] = FALSE;

    // ----- Look for arguments
    if ($v_size > 1) {
      // ----- Get the arguments
      $v_arg_list = func_get_args();

      // ----- Remove form the options list the first argument
      array_shift($v_arg_list);
      $v_size--;

      // ----- Look for first arg
      if ((is_integer($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {

        // ----- Parse the options
        $v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options,
                                            array (PCLZIP_OPT_PATH => 'optional',
                                                   PCLZIP_OPT_REMOVE_PATH => 'optional',
                                                   PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
                                                   PCLZIP_OPT_EXTRACT_AS_STRING => 'optional',
                                                   PCLZIP_OPT_ADD_PATH => 'optional',
                                                   PCLZIP_CB_PRE_EXTRACT => 'optional',
                                                   PCLZIP_CB_POST_EXTRACT => 'optional',
                                                   PCLZIP_OPT_SET_CHMOD => 'optional',
                                                   PCLZIP_OPT_REPLACE_NEWER => 'optional'
                                                   ,PCLZIP_OPT_STOP_ON_ERROR => 'optional'
                                                   ,PCLZIP_OPT_EXTRACT_DIR_RESTRICTION => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_THRESHOLD => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_ON => 'optional',
                                                   PCLZIP_OPT_TEMP_FILE_OFF => 'optional'
												   ));
        if ($v_result != 1) {
          return 0;
        }

        // ----- Set the arguments
        if (isset($v_options[PCLZIP_OPT_PATH])) {
          $v_path = $v_options[PCLZIP_OPT_PATH];
        }
        if (isset($v_options[PCLZIP_OPT_REMOVE_PATH])) {
          $v_remove_path = $v_options[PCLZIP_OPT_REMOVE_PATH];
        }
        if (isset($v_options[PCLZIP_OPT_REMOVE_ALL_PATH])) {
          $v_remove_all_path = $v_options[PCLZIP_OPT_REMOVE_ALL_PATH];
        }
        if (isset($v_options[PCLZIP_OPT_ADD_PATH])) {
          // ----- Check for '/' in last path char
          if ((strlen($v_path) > 0) && (substr($v_path, -1) != '/')) {
            $v_path .= '/';
          }
          $v_path .= $v_options[PCLZIP_OPT_ADD_PATH];
        }
        if (!isset($v_options[PCLZIP_OPT_EXTRACT_AS_STRING])) {
          $v_options[PCLZIP_OPT_EXTRACT_AS_STRING] = FALSE;
        }
        else {
        }
      }

      // ----- Look for 2 args
      // Here we need to support the first historic synopsis of the
      // method.
      else {

        // ----- Get the first argument
        $v_path = $v_arg_list[0];

        // ----- Look for the optional second argument
        if ($v_size == 2) {
          $v_remove_path = $v_arg_list[1];
        }
        else if ($v_size > 2) {
          // ----- Error log
          PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid number / type of arguments");

          // ----- Return
          return 0;
        }
      }
    }

    // ----- Trace

    // ----- Trick
    // Here I want to reuse extractByRule(), so I need to parse the $p_index
    // with privParseOptions()
    $v_arg_trick = array (PCLZIP_OPT_BY_INDEX, $p_index);
    $v_options_trick = array();
    $v_result = $this->privParseOptions($v_arg_trick, sizeof($v_arg_trick), $v_options_trick,
                                        array (PCLZIP_OPT_BY_INDEX => 'optional' ));
    if ($v_result != 1) {
        return 0;
    }
    $v_options[PCLZIP_OPT_BY_INDEX] = $v_options_trick[PCLZIP_OPT_BY_INDEX];

    // ----- Look for default option values
    $this->privOptionDefaultThreshold($v_options);

    // ----- Call the extracting fct
    if (($v_result = $this->privExtractByRule($p_list, $v_path, $v_remove_path, $v_remove_all_path, $v_options)) < 1) {
        return(0);
    }

    // ----- Return
    return $p_list;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function :
  //   delete([$p_option, $p_option_value, ...])
  // Description :
  //   This method removes files from the archive.
  //   If no parameters are given, then all the archive is emptied.
  // Parameters :
  //   None or optional arguments.
  // Options :
  //   PCLZIP_OPT_BY_INDEX :
  //   PCLZIP_OPT_BY_NAME :
  //   PCLZIP_OPT_BY_EREG : 
  //   PCLZIP_OPT_BY_PREG :
  // Return Values :
  //   0 on failure,
  //   The list of the files which are still present in the archive.
  //   (see PclZip::listContent() for list entry format)
  // --------------------------------------------------------------------------------
  function delete()
  {
    $v_result=1;

    // ----- Reset the error handler
    $this->privErrorReset();

    // ----- Check archive
    if (!$this->privCheckFormat()) {
      return(0);
    }

    // ----- Set default values
    $v_options = array();

    // ----- Look for variable options arguments
    $v_size = func_num_args();

    // ----- Look for arguments
    if ($v_size > 0) {
      // ----- Get the arguments
      $v_arg_list = func_get_args();

      // ----- Parse the options
      $v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options,
                                        array (PCLZIP_OPT_BY_NAME => 'optional',
                                               PCLZIP_OPT_BY_EREG => 'optional',
                                               PCLZIP_OPT_BY_PREG => 'optional',
                                               PCLZIP_OPT_BY_INDEX => 'optional' ));
      if ($v_result != 1) {
          return 0;
      }
    }

    // ----- Magic quotes trick
    $this->privDisableMagicQuotes();

    // ----- Call the delete fct
    $v_list = array();
    if (($v_result = $this->privDeleteByRule($v_list, $v_options)) != 1) {
      $this->privSwapBackMagicQuotes();
      unset($v_list);
      return(0);
    }

    // ----- Magic quotes trick
    $this->privSwapBackMagicQuotes();

    // ----- Return
    return $v_list;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : deleteByIndex()
  // Description :
  //   ***** Deprecated *****
  //   delete(PCLZIP_OPT_BY_INDEX, $p_index) should be prefered.
  // --------------------------------------------------------------------------------
  function deleteByIndex($p_index)
  {
    
    $p_list = $this->delete(PCLZIP_OPT_BY_INDEX, $p_index);

    // ----- Return
    return $p_list;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : properties()
  // Description :
  //   This method gives the properties of the archive.
  //   The properties are :
  //     nb : Number of files in the archive
  //     comment : Comment associated with the archive file
  //     status : not_exist, ok
  // Parameters :
  //   None
  // Return Values :
  //   0 on failure,
  //   An array with the archive properties.
  // --------------------------------------------------------------------------------
  function properties()
  {

    // ----- Reset the error handler
    $this->privErrorReset();

    // ----- Magic quotes trick
    $this->privDisableMagicQuotes();

    // ----- Check archive
    if (!$this->privCheckFormat()) {
      $this->privSwapBackMagicQuotes();
      return(0);
    }

    // ----- Default properties
    $v_prop = array();
    $v_prop['comment'] = '';
    $v_prop['nb'] = 0;
    $v_prop['status'] = 'not_exist';

    // ----- Look if file exists
    if (@is_file($this->zipname))
    {
      // ----- Open the zip file
      if (($this->zip_fd = @fopen($this->zipname, 'rb')) == 0)
      {
        $this->privSwapBackMagicQuotes();
        
        // ----- Error log
        PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open archive \''.$this->zipname.'\' in binary read mode');

        // ----- Return
        return 0;
      }

      // ----- Read the central directory informations
      $v_central_dir = array();
      if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1)
      {
        $this->privSwapBackMagicQuotes();
        return 0;
      }

      // ----- Close the zip file
      $this->privCloseFd();

      // ----- Set the user attributes
      $v_prop['comment'] = $v_central_dir['comment'];
      $v_prop['nb'] = $v_central_dir['entries'];
      $v_prop['status'] = 'ok';
    }

    // ----- Magic quotes trick
    $this->privSwapBackMagicQuotes();

    // ----- Return
    return $v_prop;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : duplicate()
  // Description :
  //   This method creates an archive by copying the content of an other one. If
  //   the archive already exist, it is replaced by the new one without any warning.
  // Parameters :
  //   $p_archive : The filename of a valid archive, or
  //                a valid PclZip object.
  // Return Values :
  //   1 on success.
  //   0 or a negative value on error (error code).
  // --------------------------------------------------------------------------------
  function duplicate($p_archive)
  {
    $v_result = 1;

    // ----- Reset the error handler
    $this->privErrorReset();

    // ----- Look if the $p_archive is a PclZip object
    if ((is_object($p_archive)) && (get_class($p_archive) == 'pclzip'))
    {

      // ----- Duplicate the archive
      $v_result = $this->privDuplicate($p_archive->zipname);
    }

    // ----- Look if the $p_archive is a string (so a filename)
    else if (is_string($p_archive))
    {

      // ----- Check that $p_archive is a valid zip file
      // TBC : Should also check the archive format
      if (!is_file($p_archive)) {
        // ----- Error log
        PclZip::privErrorLog(PCLZIP_ERR_MISSING_FILE, "No file with filename '".$p_archive."'");
        $v_result = PCLZIP_ERR_MISSING_FILE;
      }
      else {
        // ----- Duplicate the archive
        $v_result = $this->privDuplicate($p_archive);
      }
    }

    // ----- Invalid variable
    else
    {
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid variable type p_archive_to_add");
      $v_result = PCLZIP_ERR_INVALID_PARAMETER;
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : merge()
  // Description :
  //   This method merge the $p_archive_to_add archive at the end of the current
  //   one ($this).
  //   If the archive ($this) does not exist, the merge becomes a duplicate.
  //   If the $p_archive_to_add archive does not exist, the merge is a success.
  // Parameters :
  //   $p_archive_to_add : It can be directly the filename of a valid zip archive,
  //                       or a PclZip object archive.
  // Return Values :
  //   1 on success,
  //   0 or negative values on error (see below).
  // --------------------------------------------------------------------------------
  function merge($p_archive_to_add)
  {
    $v_result = 1;

    // ----- Reset the error handler
    $this->privErrorReset();

    // ----- Check archive
    if (!$this->privCheckFormat()) {
      return(0);
    }

    // ----- Look if the $p_archive_to_add is a PclZip object
    if ((is_object($p_archive_to_add)) && (get_class($p_archive_to_add) == 'pclzip'))
    {

      // ----- Merge the archive
      $v_result = $this->privMerge($p_archive_to_add);
    }

    // ----- Look if the $p_archive_to_add is a string (so a filename)
    else if (is_string($p_archive_to_add))
    {

      // ----- Create a temporary archive
      $v_object_archive = new PclZip($p_archive_to_add);

      // ----- Merge the archive
      $v_result = $this->privMerge($v_object_archive);
    }

    // ----- Invalid variable
    else
    {
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid variable type p_archive_to_add");
      $v_result = PCLZIP_ERR_INVALID_PARAMETER;
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------



  // --------------------------------------------------------------------------------
  // Function : errorCode()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function errorCode()
  {
    if (PCLZIP_ERROR_EXTERNAL == 1) {
      return(PclErrorCode());
    }
    else {
      return($this->error_code);
    }
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : errorName()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function errorName($p_with_code=false)
  {
    $v_name = array ( PCLZIP_ERR_NO_ERROR => 'PCLZIP_ERR_NO_ERROR',
                      PCLZIP_ERR_WRITE_OPEN_FAIL => 'PCLZIP_ERR_WRITE_OPEN_FAIL',
                      PCLZIP_ERR_READ_OPEN_FAIL => 'PCLZIP_ERR_READ_OPEN_FAIL',
                      PCLZIP_ERR_INVALID_PARAMETER => 'PCLZIP_ERR_INVALID_PARAMETER',
                      PCLZIP_ERR_MISSING_FILE => 'PCLZIP_ERR_MISSING_FILE',
                      PCLZIP_ERR_FILENAME_TOO_LONG => 'PCLZIP_ERR_FILENAME_TOO_LONG',
                      PCLZIP_ERR_INVALID_ZIP => 'PCLZIP_ERR_INVALID_ZIP',
                      PCLZIP_ERR_BAD_EXTRACTED_FILE => 'PCLZIP_ERR_BAD_EXTRACTED_FILE',
                      PCLZIP_ERR_DIR_CREATE_FAIL => 'PCLZIP_ERR_DIR_CREATE_FAIL',
                      PCLZIP_ERR_BAD_EXTENSION => 'PCLZIP_ERR_BAD_EXTENSION',
                      PCLZIP_ERR_BAD_FORMAT => 'PCLZIP_ERR_BAD_FORMAT',
                      PCLZIP_ERR_DELETE_FILE_FAIL => 'PCLZIP_ERR_DELETE_FILE_FAIL',
                      PCLZIP_ERR_RENAME_FILE_FAIL => 'PCLZIP_ERR_RENAME_FILE_FAIL',
                      PCLZIP_ERR_BAD_CHECKSUM => 'PCLZIP_ERR_BAD_CHECKSUM',
                      PCLZIP_ERR_INVALID_ARCHIVE_ZIP => 'PCLZIP_ERR_INVALID_ARCHIVE_ZIP',
                      PCLZIP_ERR_MISSING_OPTION_VALUE => 'PCLZIP_ERR_MISSING_OPTION_VALUE',
                      PCLZIP_ERR_INVALID_OPTION_VALUE => 'PCLZIP_ERR_INVALID_OPTION_VALUE',
                      PCLZIP_ERR_UNSUPPORTED_COMPRESSION => 'PCLZIP_ERR_UNSUPPORTED_COMPRESSION',
                      PCLZIP_ERR_UNSUPPORTED_ENCRYPTION => 'PCLZIP_ERR_UNSUPPORTED_ENCRYPTION'
                      ,PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE => 'PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE'
                      ,PCLZIP_ERR_DIRECTORY_RESTRICTION => 'PCLZIP_ERR_DIRECTORY_RESTRICTION'
                    );

    if (isset($v_name[$this->error_code])) {
      $v_value = $v_name[$this->error_code];
    }
    else {
      $v_value = 'NoName';
    }

    if ($p_with_code) {
      return($v_value.' ('.$this->error_code.')');
    }
    else {
      return($v_value);
    }
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : errorInfo()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function errorInfo($p_full=false)
  {
    if (PCLZIP_ERROR_EXTERNAL == 1) {
      return(PclErrorString());
    }
    else {
      if ($p_full) {
        return($this->errorName(true)." : ".$this->error_string);
      }
      else {
        return($this->error_string." [code ".$this->error_code."]");
      }
    }
  }
  // --------------------------------------------------------------------------------


// --------------------------------------------------------------------------------
// ***** UNDER THIS LINE ARE DEFINED PRIVATE INTERNAL FUNCTIONS *****
// *****                                                        *****
// *****       THESES FUNCTIONS MUST NOT BE USED DIRECTLY       *****
// --------------------------------------------------------------------------------



  // --------------------------------------------------------------------------------
  // Function : privCheckFormat()
  // Description :
  //   This method check that the archive exists and is a valid zip archive.
  //   Several level of check exists. (futur)
  // Parameters :
  //   $p_level : Level of check. Default 0.
  //              0 : Check the first bytes (magic codes) (default value))
  //              1 : 0 + Check the central directory (futur)
  //              2 : 1 + Check each file header (futur)
  // Return Values :
  //   true on success,
  //   false on error, the error code is set.
  // --------------------------------------------------------------------------------
  function privCheckFormat($p_level=0)
  {
    $v_result = true;

	// ----- Reset the file system cache
    clearstatcache();

    // ----- Reset the error handler
    $this->privErrorReset();

    // ----- Look if the file exits
    if (!is_file($this->zipname)) {
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_MISSING_FILE, "Missing archive file '".$this->zipname."'");
      return(false);
    }

    // ----- Check that the file is readeable
    if (!is_readable($this->zipname)) {
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to read archive '".$this->zipname."'");
      return(false);
    }

    // ----- Check the magic code
    // TBC

    // ----- Check the central header
    // TBC

    // ----- Check each file header
    // TBC

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privParseOptions()
  // Description :
  //   This internal methods reads the variable list of arguments ($p_options_list,
  //   $p_size) and generate an array with the options and values ($v_result_list).
  //   $v_requested_options contains the options that can be present and those that
  //   must be present.
  //   $v_requested_options is an array, with the option value as key, and 'optional',
  //   or 'mandatory' as value.
  // Parameters :
  //   See above.
  // Return Values :
  //   1 on success.
  //   0 on failure.
  // --------------------------------------------------------------------------------
  function privParseOptions(&$p_options_list, $p_size, &$v_result_list, $v_requested_options=false)
  {
    $v_result=1;
    
    // ----- Read the options
    $i=0;
    while ($i<$p_size) {

      // ----- Check if the option is supported
      if (!isset($v_requested_options[$p_options_list[$i]])) {
        // ----- Error log
        PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid optional parameter '".$p_options_list[$i]."' for this method");

        // ----- Return
        return PclZip::errorCode();
      }

      // ----- Look for next option
      switch ($p_options_list[$i]) {
        // ----- Look for options that request a path value
        case PCLZIP_OPT_PATH :
        case PCLZIP_OPT_REMOVE_PATH :
        case PCLZIP_OPT_ADD_PATH :
          // ----- Check the number of parameters
          if (($i+1) >= $p_size) {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }

          // ----- Get the value
          $v_result_list[$p_options_list[$i]] = PclZipUtilTranslateWinPath($p_options_list[$i+1], FALSE);
          $i++;
        break;

        case PCLZIP_OPT_TEMP_FILE_THRESHOLD :
          // ----- Check the number of parameters
          if (($i+1) >= $p_size) {
            PclZip::privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");
            return PclZip::errorCode();
          }
          
          // ----- Check for incompatible options
          if (isset($v_result_list[PCLZIP_OPT_TEMP_FILE_OFF])) {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Option '".PclZipUtilOptionText($p_options_list[$i])."' can not be used with option 'PCLZIP_OPT_TEMP_FILE_OFF'");
            return PclZip::errorCode();
          }
          
          // ----- Check the value
          $v_value = $p_options_list[$i+1];
          if ((!is_integer($v_value)) || ($v_value<0)) {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Integer expected for option '".PclZipUtilOptionText($p_options_list[$i])."'");
            return PclZip::errorCode();
          }

          // ----- Get the value (and convert it in bytes)
          $v_result_list[$p_options_list[$i]] = $v_value*1048576;
          $i++;
        break;

        case PCLZIP_OPT_TEMP_FILE_ON :
          // ----- Check for incompatible options
          if (isset($v_result_list[PCLZIP_OPT_TEMP_FILE_OFF])) {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Option '".PclZipUtilOptionText($p_options_list[$i])."' can not be used with option 'PCLZIP_OPT_TEMP_FILE_OFF'");
            return PclZip::errorCode();
          }
          
          $v_result_list[$p_options_list[$i]] = true;
        break;

        case PCLZIP_OPT_TEMP_FILE_OFF :
          // ----- Check for incompatible options
          if (isset($v_result_list[PCLZIP_OPT_TEMP_FILE_ON])) {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Option '".PclZipUtilOptionText($p_options_list[$i])."' can not be used with option 'PCLZIP_OPT_TEMP_FILE_ON'");
            return PclZip::errorCode();
          }
          // ----- Check for incompatible options
          if (isset($v_result_list[PCLZIP_OPT_TEMP_FILE_THRESHOLD])) {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Option '".PclZipUtilOptionText($p_options_list[$i])."' can not be used with option 'PCLZIP_OPT_TEMP_FILE_THRESHOLD'");
            return PclZip::errorCode();
          }
          
          $v_result_list[$p_options_list[$i]] = true;
        break;

        case PCLZIP_OPT_EXTRACT_DIR_RESTRICTION :
          // ----- Check the number of parameters
          if (($i+1) >= $p_size) {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }

          // ----- Get the value
          if (   is_string($p_options_list[$i+1])
              && ($p_options_list[$i+1] != '')) {
            $v_result_list[$p_options_list[$i]] = PclZipUtilTranslateWinPath($p_options_list[$i+1], FALSE);
            $i++;
          }
          else {
          }
        break;

        // ----- Look for options that request an array of string for value
        case PCLZIP_OPT_BY_NAME :
          // ----- Check the number of parameters
          if (($i+1) >= $p_size) {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }

          // ----- Get the value
          if (is_string($p_options_list[$i+1])) {
              $v_result_list[$p_options_list[$i]][0] = $p_options_list[$i+1];
          }
          else if (is_array($p_options_list[$i+1])) {
              $v_result_list[$p_options_list[$i]] = $p_options_list[$i+1];
          }
          else {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Wrong parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }
          $i++;
        break;

        // ----- Look for options that request an EREG or PREG expression
        case PCLZIP_OPT_BY_EREG :
          // ereg() is deprecated starting with PHP 5.3. Move PCLZIP_OPT_BY_EREG
          // to PCLZIP_OPT_BY_PREG
          $p_options_list[$i] = PCLZIP_OPT_BY_PREG;
        case PCLZIP_OPT_BY_PREG :
        //case PCLZIP_OPT_CRYPT :
          // ----- Check the number of parameters
          if (($i+1) >= $p_size) {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }

          // ----- Get the value
          if (is_string($p_options_list[$i+1])) {
              $v_result_list[$p_options_list[$i]] = $p_options_list[$i+1];
          }
          else {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Wrong parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }
          $i++;
        break;

        // ----- Look for options that takes a string
        case PCLZIP_OPT_COMMENT :
        case PCLZIP_OPT_ADD_COMMENT :
        case PCLZIP_OPT_PREPEND_COMMENT :
          // ----- Check the number of parameters
          if (($i+1) >= $p_size) {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE,
			                     "Missing parameter value for option '"
								 .PclZipUtilOptionText($p_options_list[$i])
								 ."'");

            // ----- Return
            return PclZip::errorCode();
          }

          // ----- Get the value
          if (is_string($p_options_list[$i+1])) {
              $v_result_list[$p_options_list[$i]] = $p_options_list[$i+1];
          }
          else {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE,
			                     "Wrong parameter value for option '"
								 .PclZipUtilOptionText($p_options_list[$i])
								 ."'");

            // ----- Return
            return PclZip::errorCode();
          }
          $i++;
        break;

        // ----- Look for options that request an array of index
        case PCLZIP_OPT_BY_INDEX :
          // ----- Check the number of parameters
          if (($i+1) >= $p_size) {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }

          // ----- Get the value
          $v_work_list = array();
          if (is_string($p_options_list[$i+1])) {

              // ----- Remove spaces
              $p_options_list[$i+1] = strtr($p_options_list[$i+1], ' ', '');

              // ----- Parse items
              $v_work_list = explode(",", $p_options_list[$i+1]);
          }
          else if (is_integer($p_options_list[$i+1])) {
              $v_work_list[0] = $p_options_list[$i+1].'-'.$p_options_list[$i+1];
          }
          else if (is_array($p_options_list[$i+1])) {
              $v_work_list = $p_options_list[$i+1];
          }
          else {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Value must be integer, string or array for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }
          
          // ----- Reduce the index list
          // each index item in the list must be a couple with a start and
          // an end value : [0,3], [5-5], [8-10], ...
          // ----- Check the format of each item
          $v_sort_flag=false;
          $v_sort_value=0;
          for ($j=0; $j<sizeof($v_work_list); $j++) {
              // ----- Explode the item
              $v_item_list = explode("-", $v_work_list[$j]);
              $v_size_item_list = sizeof($v_item_list);
              
              // ----- TBC : Here we might check that each item is a
              // real integer ...
              
              // ----- Look for single value
              if ($v_size_item_list == 1) {
                  // ----- Set the option value
                  $v_result_list[$p_options_list[$i]][$j]['start'] = $v_item_list[0];
                  $v_result_list[$p_options_list[$i]][$j]['end'] = $v_item_list[0];
              }
              elseif ($v_size_item_list == 2) {
                  // ----- Set the option value
                  $v_result_list[$p_options_list[$i]][$j]['start'] = $v_item_list[0];
                  $v_result_list[$p_options_list[$i]][$j]['end'] = $v_item_list[1];
              }
              else {
                  // ----- Error log
                  PclZip::privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Too many values in index range for option '".PclZipUtilOptionText($p_options_list[$i])."'");

                  // ----- Return
                  return PclZip::errorCode();
              }


              // ----- Look for list sort
              if ($v_result_list[$p_options_list[$i]][$j]['start'] < $v_sort_value) {
                  $v_sort_flag=true;

                  // ----- TBC : An automatic sort should be writen ...
                  // ----- Error log
                  PclZip::privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Invalid order of index range for option '".PclZipUtilOptionText($p_options_list[$i])."'");

                  // ----- Return
                  return PclZip::errorCode();
              }
              $v_sort_value = $v_result_list[$p_options_list[$i]][$j]['start'];
          }
          
          // ----- Sort the items
          if ($v_sort_flag) {
              // TBC : To Be Completed
          }

          // ----- Next option
          $i++;
        break;

        // ----- Look for options that request no value
        case PCLZIP_OPT_REMOVE_ALL_PATH :
        case PCLZIP_OPT_EXTRACT_AS_STRING :
        case PCLZIP_OPT_NO_COMPRESSION :
        case PCLZIP_OPT_EXTRACT_IN_OUTPUT :
        case PCLZIP_OPT_REPLACE_NEWER :
        case PCLZIP_OPT_STOP_ON_ERROR :
          $v_result_list[$p_options_list[$i]] = true;
        break;

        // ----- Look for options that request an octal value
        case PCLZIP_OPT_SET_CHMOD :
          // ----- Check the number of parameters
          if (($i+1) >= $p_size) {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }

          // ----- Get the value
          $v_result_list[$p_options_list[$i]] = $p_options_list[$i+1];
          $i++;
        break;

        // ----- Look for options that request a call-back
        case PCLZIP_CB_PRE_EXTRACT :
        case PCLZIP_CB_POST_EXTRACT :
        case PCLZIP_CB_PRE_ADD :
        case PCLZIP_CB_POST_ADD :
        /* for futur use
        case PCLZIP_CB_PRE_DELETE :
        case PCLZIP_CB_POST_DELETE :
        case PCLZIP_CB_PRE_LIST :
        case PCLZIP_CB_POST_LIST :
        */
          // ----- Check the number of parameters
          if (($i+1) >= $p_size) {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }

          // ----- Get the value
          $v_function_name = $p_options_list[$i+1];

          // ----- Check that the value is a valid existing function
          if (!function_exists($v_function_name)) {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_OPTION_VALUE, "Function '".$v_function_name."()' is not an existing function for option '".PclZipUtilOptionText($p_options_list[$i])."'");

            // ----- Return
            return PclZip::errorCode();
          }

          // ----- Set the attribute
          $v_result_list[$p_options_list[$i]] = $v_function_name;
          $i++;
        break;

        default :
          // ----- Error log
          PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER,
		                       "Unknown parameter '"
							   .$p_options_list[$i]."'");

          // ----- Return
          return PclZip::errorCode();
      }

      // ----- Next options
      $i++;
    }

    // ----- Look for mandatory options
    if ($v_requested_options !== false) {
      for ($key=reset($v_requested_options); $key=key($v_requested_options); $key=next($v_requested_options)) {
        // ----- Look for mandatory option
        if ($v_requested_options[$key] == 'mandatory') {
          // ----- Look if present
          if (!isset($v_result_list[$key])) {
            // ----- Error log
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Missing mandatory parameter ".PclZipUtilOptionText($key)."(".$key.")");

            // ----- Return
            return PclZip::errorCode();
          }
        }
      }
    }
    
    // ----- Look for default values
    if (!isset($v_result_list[PCLZIP_OPT_TEMP_FILE_THRESHOLD])) {
      
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privOptionDefaultThreshold()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privOptionDefaultThreshold(&$p_options)
  {
    $v_result=1;
    
    if (isset($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD])
        || isset($p_options[PCLZIP_OPT_TEMP_FILE_OFF])) {
      return $v_result;
    }
    
    // ----- Get 'memory_limit' configuration value
    $v_memory_limit = ini_get('memory_limit');
    $v_memory_limit = trim($v_memory_limit);
    $last = strtolower(substr($v_memory_limit, -1));
 
    if($last == 'g')
        //$v_memory_limit = $v_memory_limit*1024*1024*1024;
        $v_memory_limit = $v_memory_limit*1073741824;
    if($last == 'm')
        //$v_memory_limit = $v_memory_limit*1024*1024;
        $v_memory_limit = $v_memory_limit*1048576;
    if($last == 'k')
        $v_memory_limit = $v_memory_limit*1024;
            
    $p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD] = floor($v_memory_limit*PCLZIP_TEMPORARY_FILE_RATIO);
    

    // ----- Sanity check : No threshold if value lower than 1M
    if ($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD] < 1048576) {
      unset($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD]);
    }
          
    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privFileDescrParseAtt()
  // Description :
  // Parameters :
  // Return Values :
  //   1 on success.
  //   0 on failure.
  // --------------------------------------------------------------------------------
  function privFileDescrParseAtt(&$p_file_list, &$p_filedescr, $v_options, $v_requested_options=false)
  {
    $v_result=1;
    
    // ----- For each file in the list check the attributes
    foreach ($p_file_list as $v_key => $v_value) {
    
      // ----- Check if the option is supported
      if (!isset($v_requested_options[$v_key])) {
        // ----- Error log
        PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid file attribute '".$v_key."' for this file");

        // ----- Return
        return PclZip::errorCode();
      }

      // ----- Look for attribute
      switch ($v_key) {
        case PCLZIP_ATT_FILE_NAME :
          if (!is_string($v_value)) {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid type ".gettype($v_value).". String expected for attribute '".PclZipUtilOptionText($v_key)."'");
            return PclZip::errorCode();
          }

          $p_filedescr['filename'] = PclZipUtilPathReduction($v_value);
          
          if ($p_filedescr['filename'] == '') {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid empty filename for attribute '".PclZipUtilOptionText($v_key)."'");
            return PclZip::errorCode();
          }

        break;

        case PCLZIP_ATT_FILE_NEW_SHORT_NAME :
          if (!is_string($v_value)) {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid type ".gettype($v_value).". String expected for attribute '".PclZipUtilOptionText($v_key)."'");
            return PclZip::errorCode();
          }

          $p_filedescr['new_short_name'] = PclZipUtilPathReduction($v_value);

          if ($p_filedescr['new_short_name'] == '') {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid empty short filename for attribute '".PclZipUtilOptionText($v_key)."'");
            return PclZip::errorCode();
          }
        break;

        case PCLZIP_ATT_FILE_NEW_FULL_NAME :
          if (!is_string($v_value)) {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid type ".gettype($v_value).". String expected for attribute '".PclZipUtilOptionText($v_key)."'");
            return PclZip::errorCode();
          }

          $p_filedescr['new_full_name'] = PclZipUtilPathReduction($v_value);

          if ($p_filedescr['new_full_name'] == '') {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid empty full filename for attribute '".PclZipUtilOptionText($v_key)."'");
            return PclZip::errorCode();
          }
        break;

        // ----- Look for options that takes a string
        case PCLZIP_ATT_FILE_COMMENT :
          if (!is_string($v_value)) {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid type ".gettype($v_value).". String expected for attribute '".PclZipUtilOptionText($v_key)."'");
            return PclZip::errorCode();
          }

          $p_filedescr['comment'] = $v_value;
        break;

        case PCLZIP_ATT_FILE_MTIME :
          if (!is_integer($v_value)) {
            PclZip::privErrorLog(PCLZIP_ERR_INVALID_ATTRIBUTE_VALUE, "Invalid type ".gettype($v_value).". Integer expected for attribute '".PclZipUtilOptionText($v_key)."'");
            return PclZip::errorCode();
          }

          $p_filedescr['mtime'] = $v_value;
        break;

        case PCLZIP_ATT_FILE_CONTENT :
          $p_filedescr['content'] = $v_value;
        break;

        default :
          // ----- Error log
          PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER,
		                           "Unknown parameter '".$v_key."'");

          // ----- Return
          return PclZip::errorCode();
      }

      // ----- Look for mandatory options
      if ($v_requested_options !== false) {
        for ($key=reset($v_requested_options); $key=key($v_requested_options); $key=next($v_requested_options)) {
          // ----- Look for mandatory option
          if ($v_requested_options[$key] == 'mandatory') {
            // ----- Look if present
            if (!isset($p_file_list[$key])) {
              PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Missing mandatory parameter ".PclZipUtilOptionText($key)."(".$key.")");
              return PclZip::errorCode();
            }
          }
        }
      }
    
    // end foreach
    }
    
    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privFileDescrExpand()
  // Description :
  //   This method look for each item of the list to see if its a file, a folder
  //   or a string to be added as file. For any other type of files (link, other)
  //   just ignore the item.
  //   Then prepare the information that will be stored for that file.
  //   When its a folder, expand the folder with all the files that are in that 
  //   folder (recursively).
  // Parameters :
  // Return Values :
  //   1 on success.
  //   0 on failure.
  // --------------------------------------------------------------------------------
  function privFileDescrExpand(&$p_filedescr_list, &$p_options)
  {
    $v_result=1;
    
    // ----- Create a result list
    $v_result_list = array();
    
    // ----- Look each entry
    for ($i=0; $i<sizeof($p_filedescr_list); $i++) {
      
      // ----- Get filedescr
      $v_descr = $p_filedescr_list[$i];
      
      // ----- Reduce the filename
      $v_descr['filename'] = PclZipUtilTranslateWinPath($v_descr['filename'], false);
      $v_descr['filename'] = PclZipUtilPathReduction($v_descr['filename']);
      
      // ----- Look for real file or folder
      if (file_exists($v_descr['filename'])) {
        if (@is_file($v_descr['filename'])) {
          $v_descr['type'] = 'file';
        }
        else if (@is_dir($v_descr['filename'])) {
          $v_descr['type'] = 'folder';
        }
        else if (@is_link($v_descr['filename'])) {
          // skip
          continue;
        }
        else {
          // skip
          continue;
        }
      }
      
      // ----- Look for string added as file
      else if (isset($v_descr['content'])) {
        $v_descr['type'] = 'virtual_file';
      }
      
      // ----- Missing file
      else {
        // ----- Error log
        PclZip::privErrorLog(PCLZIP_ERR_MISSING_FILE, "File '".$v_descr['filename']."' does not exist");

        // ----- Return
        return PclZip::errorCode();
      }
      
      // ----- Calculate the stored filename
      $this->privCalculateStoredFilename($v_descr, $p_options);
      
      // ----- Add the descriptor in result list
      $v_result_list[sizeof($v_result_list)] = $v_descr;
      
      // ----- Look for folder
      if ($v_descr['type'] == 'folder') {
        // ----- List of items in folder
        $v_dirlist_descr = array();
        $v_dirlist_nb = 0;
        if ($v_folder_handler = @opendir($v_descr['filename'])) {
          while (($v_item_handler = @readdir($v_folder_handler)) !== false) {

            // ----- Skip '.' and '..'
            if (($v_item_handler == '.') || ($v_item_handler == '..')) {
                continue;
            }
            
            // ----- Compose the full filename
            $v_dirlist_descr[$v_dirlist_nb]['filename'] = $v_descr['filename'].'/'.$v_item_handler;
            
            // ----- Look for different stored filename
            // Because the name of the folder was changed, the name of the
            // files/sub-folders also change
            if (($v_descr['stored_filename'] != $v_descr['filename'])
                 && (!isset($p_options[PCLZIP_OPT_REMOVE_ALL_PATH]))) {
              if ($v_descr['stored_filename'] != '') {
                $v_dirlist_descr[$v_dirlist_nb]['new_full_name'] = $v_descr['stored_filename'].'/'.$v_item_handler;
              }
              else {
                $v_dirlist_descr[$v_dirlist_nb]['new_full_name'] = $v_item_handler;
              }
            }
      
            $v_dirlist_nb++;
          }
          
          @closedir($v_folder_handler);
        }
        else {
          // TBC : unable to open folder in read mode
        }
        
        // ----- Expand each element of the list
        if ($v_dirlist_nb != 0) {
          // ----- Expand
          if (($v_result = $this->privFileDescrExpand($v_dirlist_descr, $p_options)) != 1) {
            return $v_result;
          }
          
          // ----- Concat the resulting list
          $v_result_list = array_merge($v_result_list, $v_dirlist_descr);
        }
        else {
        }
          
        // ----- Free local array
        unset($v_dirlist_descr);
      }
    }
    
    // ----- Get the result list
    $p_filedescr_list = $v_result_list;

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privCreate()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privCreate($p_filedescr_list, &$p_result_list, &$p_options)
  {
    $v_result=1;
    $v_list_detail = array();
    
    // ----- Magic quotes trick
    $this->privDisableMagicQuotes();

    // ----- Open the file in write mode
    if (($v_result = $this->privOpenFd('wb')) != 1)
    {
      // ----- Return
      return $v_result;
    }

    // ----- Add the list of files
    $v_result = $this->privAddList($p_filedescr_list, $p_result_list, $p_options);

    // ----- Close
    $this->privCloseFd();

    // ----- Magic quotes trick
    $this->privSwapBackMagicQuotes();

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privAdd()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privAdd($p_filedescr_list, &$p_result_list, &$p_options)
  {
    $v_result=1;
    $v_list_detail = array();

    // ----- Look if the archive exists or is empty
    if ((!is_file($this->zipname)) || (filesize($this->zipname) == 0))
    {

      // ----- Do a create
      $v_result = $this->privCreate($p_filedescr_list, $p_result_list, $p_options);

      // ----- Return
      return $v_result;
    }
    // ----- Magic quotes trick
    $this->privDisableMagicQuotes();

    // ----- Open the zip file
    if (($v_result=$this->privOpenFd('rb')) != 1)
    {
      // ----- Magic quotes trick
      $this->privSwapBackMagicQuotes();

      // ----- Return
      return $v_result;
    }

    // ----- Read the central directory informations
    $v_central_dir = array();
    if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1)
    {
      $this->privCloseFd();
      $this->privSwapBackMagicQuotes();
      return $v_result;
    }

    // ----- Go to beginning of File
    @rewind($this->zip_fd);

    // ----- Creates a temporay file
    $v_zip_temp_name = PCLZIP_TEMPORARY_DIR.uniqid('pclzip-').'.tmp';

    // ----- Open the temporary file in write mode
    if (($v_zip_temp_fd = @fopen($v_zip_temp_name, 'wb')) == 0)
    {
      $this->privCloseFd();
      $this->privSwapBackMagicQuotes();

      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open temporary file \''.$v_zip_temp_name.'\' in binary write mode');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Copy the files from the archive to the temporary file
    // TBC : Here I should better append the file and go back to erase the central dir
    $v_size = $v_central_dir['offset'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($this->zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Swap the file descriptor
    // Here is a trick : I swap the temporary fd with the zip fd, in order to use
    // the following methods on the temporary fil and not the real archive
    $v_swap = $this->zip_fd;
    $this->zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;

    // ----- Add the files
    $v_header_list = array();
    if (($v_result = $this->privAddFileList($p_filedescr_list, $v_header_list, $p_options)) != 1)
    {
      fclose($v_zip_temp_fd);
      $this->privCloseFd();
      @unlink($v_zip_temp_name);
      $this->privSwapBackMagicQuotes();

      // ----- Return
      return $v_result;
    }

    // ----- Store the offset of the central dir
    $v_offset = @ftell($this->zip_fd);

    // ----- Copy the block of file headers from the old archive
    $v_size = $v_central_dir['size'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($v_zip_temp_fd, $v_read_size);
      @fwrite($this->zip_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Create the Central Dir files header
    for ($i=0, $v_count=0; $i<sizeof($v_header_list); $i++)
    {
      // ----- Create the file header
      if ($v_header_list[$i]['status'] == 'ok') {
        if (($v_result = $this->privWriteCentralFileHeader($v_header_list[$i])) != 1) {
          fclose($v_zip_temp_fd);
          $this->privCloseFd();
          @unlink($v_zip_temp_name);
          $this->privSwapBackMagicQuotes();

          // ----- Return
          return $v_result;
        }
        $v_count++;
      }

      // ----- Transform the header to a 'usable' info
      $this->privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
    }

    // ----- Zip file comment
    $v_comment = $v_central_dir['comment'];
    if (isset($p_options[PCLZIP_OPT_COMMENT])) {
      $v_comment = $p_options[PCLZIP_OPT_COMMENT];
    }
    if (isset($p_options[PCLZIP_OPT_ADD_COMMENT])) {
      $v_comment = $v_comment.$p_options[PCLZIP_OPT_ADD_COMMENT];
    }
    if (isset($p_options[PCLZIP_OPT_PREPEND_COMMENT])) {
      $v_comment = $p_options[PCLZIP_OPT_PREPEND_COMMENT].$v_comment;
    }

    // ----- Calculate the size of the central header
    $v_size = @ftell($this->zip_fd)-$v_offset;

    // ----- Create the central dir footer
    if (($v_result = $this->privWriteCentralHeader($v_count+$v_central_dir['entries'], $v_size, $v_offset, $v_comment)) != 1)
    {
      // ----- Reset the file list
      unset($v_header_list);
      $this->privSwapBackMagicQuotes();

      // ----- Return
      return $v_result;
    }

    // ----- Swap back the file descriptor
    $v_swap = $this->zip_fd;
    $this->zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;

    // ----- Close
    $this->privCloseFd();

    // ----- Close the temporary file
    @fclose($v_zip_temp_fd);

    // ----- Magic quotes trick
    $this->privSwapBackMagicQuotes();

    // ----- Delete the zip file
    // TBC : I should test the result ...
    @unlink($this->zipname);

    // ----- Rename the temporary file
    // TBC : I should test the result ...
    //@rename($v_zip_temp_name, $this->zipname);
    PclZipUtilRename($v_zip_temp_name, $this->zipname);

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privOpenFd()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function privOpenFd($p_mode)
  {
    $v_result=1;

    // ----- Look if already open
    if ($this->zip_fd != 0)
    {
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Zip file \''.$this->zipname.'\' already open');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Open the zip file
    if (($this->zip_fd = @fopen($this->zipname, $p_mode)) == 0)
    {
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open archive \''.$this->zipname.'\' in '.$p_mode.' mode');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privCloseFd()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function privCloseFd()
  {
    $v_result=1;

    if ($this->zip_fd != 0)
      @fclose($this->zip_fd);
    $this->zip_fd = 0;

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privAddList()
  // Description :
  //   $p_add_dir and $p_remove_dir will give the ability to memorize a path which is
  //   different from the real path of the file. This is usefull if you want to have PclTar
  //   running in any directory, and memorize relative path from an other directory.
  // Parameters :
  //   $p_list : An array containing the file or directory names to add in the tar
  //   $p_result_list : list of added files with their properties (specially the status field)
  //   $p_add_dir : Path to add in the filename path archived
  //   $p_remove_dir : Path to remove in the filename path archived
  // Return Values :
  // --------------------------------------------------------------------------------
//  function privAddList($p_list, &$p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, &$p_options)
  function privAddList($p_filedescr_list, &$p_result_list, &$p_options)
  {
    $v_result=1;

    // ----- Add the files
    $v_header_list = array();
    if (($v_result = $this->privAddFileList($p_filedescr_list, $v_header_list, $p_options)) != 1)
    {
      // ----- Return
      return $v_result;
    }

    // ----- Store the offset of the central dir
    $v_offset = @ftell($this->zip_fd);

    // ----- Create the Central Dir files header
    for ($i=0,$v_count=0; $i<sizeof($v_header_list); $i++)
    {
      // ----- Create the file header
      if ($v_header_list[$i]['status'] == 'ok') {
        if (($v_result = $this->privWriteCentralFileHeader($v_header_list[$i])) != 1) {
          // ----- Return
          return $v_result;
        }
        $v_count++;
      }

      // ----- Transform the header to a 'usable' info
      $this->privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
    }

    // ----- Zip file comment
    $v_comment = '';
    if (isset($p_options[PCLZIP_OPT_COMMENT])) {
      $v_comment = $p_options[PCLZIP_OPT_COMMENT];
    }

    // ----- Calculate the size of the central header
    $v_size = @ftell($this->zip_fd)-$v_offset;

    // ----- Create the central dir footer
    if (($v_result = $this->privWriteCentralHeader($v_count, $v_size, $v_offset, $v_comment)) != 1)
    {
      // ----- Reset the file list
      unset($v_header_list);

      // ----- Return
      return $v_result;
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privAddFileList()
  // Description :
  // Parameters :
  //   $p_filedescr_list : An array containing the file description 
  //                      or directory names to add in the zip
  //   $p_result_list : list of added files with their properties (specially the status field)
  // Return Values :
  // --------------------------------------------------------------------------------
  function privAddFileList($p_filedescr_list, &$p_result_list, &$p_options)
  {
    $v_result=1;
    $v_header = array();

    // ----- Recuperate the current number of elt in list
    $v_nb = sizeof($p_result_list);

    // ----- Loop on the files
    for ($j=0; ($j<sizeof($p_filedescr_list)) && ($v_result==1); $j++) {
      // ----- Format the filename
      $p_filedescr_list[$j]['filename']
      = PclZipUtilTranslateWinPath($p_filedescr_list[$j]['filename'], false);
      

      // ----- Skip empty file names
      // TBC : Can this be possible ? not checked in DescrParseAtt ?
      if ($p_filedescr_list[$j]['filename'] == "") {
        continue;
      }

      // ----- Check the filename
      if (   ($p_filedescr_list[$j]['type'] != 'virtual_file')
          && (!file_exists($p_filedescr_list[$j]['filename']))) {
        PclZip::privErrorLog(PCLZIP_ERR_MISSING_FILE, "File '".$p_filedescr_list[$j]['filename']."' does not exist");
        return PclZip::errorCode();
      }

      // ----- Look if it is a file or a dir with no all path remove option
      // or a dir with all its path removed
//      if (   (is_file($p_filedescr_list[$j]['filename']))
//          || (   is_dir($p_filedescr_list[$j]['filename'])
      if (   ($p_filedescr_list[$j]['type'] == 'file')
          || ($p_filedescr_list[$j]['type'] == 'virtual_file')
          || (   ($p_filedescr_list[$j]['type'] == 'folder')
              && (   !isset($p_options[PCLZIP_OPT_REMOVE_ALL_PATH])
                  || !$p_options[PCLZIP_OPT_REMOVE_ALL_PATH]))
          ) {

        // ----- Add the file
        $v_result = $this->privAddFile($p_filedescr_list[$j], $v_header,
                                       $p_options);
        if ($v_result != 1) {
          return $v_result;
        }

        // ----- Store the file infos
        $p_result_list[$v_nb++] = $v_header;
      }
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privAddFile()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privAddFile($p_filedescr, &$p_header, &$p_options)
  {
    $v_result=1;
    
    // ----- Working variable
    $p_filename = $p_filedescr['filename'];

    // TBC : Already done in the fileAtt check ... ?
    if ($p_filename == "") {
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid file list parameter (invalid or empty list)");

      // ----- Return
      return PclZip::errorCode();
    }
  
    // ----- Look for a stored different filename 
    /* TBC : Removed
    if (isset($p_filedescr['stored_filename'])) {
      $v_stored_filename = $p_filedescr['stored_filename'];
    }
    else {
      $v_stored_filename = $p_filedescr['stored_filename'];
    }
    */

    // ----- Set the file properties
    clearstatcache();
    $p_header['version'] = 20;
    $p_header['version_extracted'] = 10;
    $p_header['flag'] = 0;
    $p_header['compression'] = 0;
    $p_header['crc'] = 0;
    $p_header['compressed_size'] = 0;
    $p_header['filename_len'] = strlen($p_filename);
    $p_header['extra_len'] = 0;
    $p_header['disk'] = 0;
    $p_header['internal'] = 0;
    $p_header['offset'] = 0;
    $p_header['filename'] = $p_filename;
// TBC : Removed    $p_header['stored_filename'] = $v_stored_filename;
    $p_header['stored_filename'] = $p_filedescr['stored_filename'];
    $p_header['extra'] = '';
    $p_header['status'] = 'ok';
    $p_header['index'] = -1;

    // ----- Look for regular file
    if ($p_filedescr['type']=='file') {
      $p_header['external'] = 0x00000000;
      $p_header['size'] = filesize($p_filename);
    }
    
    // ----- Look for regular folder
    else if ($p_filedescr['type']=='folder') {
      $p_header['external'] = 0x00000010;
      $p_header['mtime'] = filemtime($p_filename);
      $p_header['size'] = filesize($p_filename);
    }
    
    // ----- Look for virtual file
    else if ($p_filedescr['type'] == 'virtual_file') {
      $p_header['external'] = 0x00000000;
      $p_header['size'] = strlen($p_filedescr['content']);
    }
    

    // ----- Look for filetime
    if (isset($p_filedescr['mtime'])) {
      $p_header['mtime'] = $p_filedescr['mtime'];
    }
    else if ($p_filedescr['type'] == 'virtual_file') {
      $p_header['mtime'] = time();
    }
    else {
      $p_header['mtime'] = filemtime($p_filename);
    }

    // ------ Look for file comment
    if (isset($p_filedescr['comment'])) {
      $p_header['comment_len'] = strlen($p_filedescr['comment']);
      $p_header['comment'] = $p_filedescr['comment'];
    }
    else {
      $p_header['comment_len'] = 0;
      $p_header['comment'] = '';
    }

    // ----- Look for pre-add callback
    if (isset($p_options[PCLZIP_CB_PRE_ADD])) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->privConvertHeader2FileInfo($p_header, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
//      eval('$v_result = '.$p_options[PCLZIP_CB_PRE_ADD].'(PCLZIP_CB_PRE_ADD, $v_local_header);');
      $v_result = $p_options[PCLZIP_CB_PRE_ADD](PCLZIP_CB_PRE_ADD, $v_local_header);
      if ($v_result == 0) {
        // ----- Change the file status
        $p_header['status'] = "skipped";
        $v_result = 1;
      }

      // ----- Update the informations
      // Only some fields can be modified
      if ($p_header['stored_filename'] != $v_local_header['stored_filename']) {
        $p_header['stored_filename'] = PclZipUtilPathReduction($v_local_header['stored_filename']);
      }
    }

    // ----- Look for empty stored filename
    if ($p_header['stored_filename'] == "") {
      $p_header['status'] = "filtered";
    }
    
    // ----- Check the path length
    if (strlen($p_header['stored_filename']) > 0xFF) {
      $p_header['status'] = 'filename_too_long';
    }

    // ----- Look if no error, or file not skipped
    if ($p_header['status'] == 'ok') {

      // ----- Look for a file
      if ($p_filedescr['type'] == 'file') {
        // ----- Look for using temporary file to zip
        if ( (!isset($p_options[PCLZIP_OPT_TEMP_FILE_OFF])) 
            && (isset($p_options[PCLZIP_OPT_TEMP_FILE_ON])
                || (isset($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD])
                    && ($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD] <= $p_header['size'])) ) ) {
          $v_result = $this->privAddFileUsingTempFile($p_filedescr, $p_header, $p_options);
          if ($v_result < PCLZIP_ERR_NO_ERROR) {
            return $v_result;
          }
        }
        
        // ----- Use "in memory" zip algo
        else {

        // ----- Open the source file
        if (($v_file = @fopen($p_filename, "rb")) == 0) {
          PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to open file '$p_filename' in binary read mode");
          return PclZip::errorCode();
        }

        // ----- Read the file content
        $v_content = @fread($v_file, $p_header['size']);

        // ----- Close the file
        @fclose($v_file);

        // ----- Calculate the CRC
        $p_header['crc'] = @crc32($v_content);
        
        // ----- Look for no compression
        if ($p_options[PCLZIP_OPT_NO_COMPRESSION]) {
          // ----- Set header parameters
          $p_header['compressed_size'] = $p_header['size'];
          $p_header['compression'] = 0;
        }
        
        // ----- Look for normal compression
        else {
          // ----- Compress the content
          $v_content = @gzdeflate($v_content);

          // ----- Set header parameters
          $p_header['compressed_size'] = strlen($v_content);
          $p_header['compression'] = 8;
        }
        
        // ----- Call the header generation
        if (($v_result = $this->privWriteFileHeader($p_header)) != 1) {
          @fclose($v_file);
          return $v_result;
        }

        // ----- Write the compressed (or not) content
        @fwrite($this->zip_fd, $v_content, $p_header['compressed_size']);

        }

      }

      // ----- Look for a virtual file (a file from string)
      else if ($p_filedescr['type'] == 'virtual_file') {
          
        $v_content = $p_filedescr['content'];

        // ----- Calculate the CRC
        $p_header['crc'] = @crc32($v_content);
        
        // ----- Look for no compression
        if ($p_options[PCLZIP_OPT_NO_COMPRESSION]) {
          // ----- Set header parameters
          $p_header['compressed_size'] = $p_header['size'];
          $p_header['compression'] = 0;
        }
        
        // ----- Look for normal compression
        else {
          // ----- Compress the content
          $v_content = @gzdeflate($v_content);

          // ----- Set header parameters
          $p_header['compressed_size'] = strlen($v_content);
          $p_header['compression'] = 8;
        }
        
        // ----- Call the header generation
        if (($v_result = $this->privWriteFileHeader($p_header)) != 1) {
          @fclose($v_file);
          return $v_result;
        }

        // ----- Write the compressed (or not) content
        @fwrite($this->zip_fd, $v_content, $p_header['compressed_size']);
      }

      // ----- Look for a directory
      else if ($p_filedescr['type'] == 'folder') {
        // ----- Look for directory last '/'
        if (@substr($p_header['stored_filename'], -1) != '/') {
          $p_header['stored_filename'] .= '/';
        }

        // ----- Set the file properties
        $p_header['size'] = 0;
        //$p_header['external'] = 0x41FF0010;   // Value for a folder : to be checked
        $p_header['external'] = 0x00000010;   // Value for a folder : to be checked

        // ----- Call the header generation
        if (($v_result = $this->privWriteFileHeader($p_header)) != 1)
        {
          return $v_result;
        }
      }
    }

    // ----- Look for post-add callback
    if (isset($p_options[PCLZIP_CB_POST_ADD])) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->privConvertHeader2FileInfo($p_header, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
//      eval('$v_result = '.$p_options[PCLZIP_CB_POST_ADD].'(PCLZIP_CB_POST_ADD, $v_local_header);');
      $v_result = $p_options[PCLZIP_CB_POST_ADD](PCLZIP_CB_POST_ADD, $v_local_header);
      if ($v_result == 0) {
        // ----- Ignored
        $v_result = 1;
      }

      // ----- Update the informations
      // Nothing can be modified
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privAddFileUsingTempFile()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privAddFileUsingTempFile($p_filedescr, &$p_header, &$p_options)
  {
    $v_result=PCLZIP_ERR_NO_ERROR;
    
    // ----- Working variable
    $p_filename = $p_filedescr['filename'];


    // ----- Open the source file
    if (($v_file = @fopen($p_filename, "rb")) == 0) {
      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to open file '$p_filename' in binary read mode");
      return PclZip::errorCode();
    }

    // ----- Creates a compressed temporary file
    $v_gzip_temp_name = PCLZIP_TEMPORARY_DIR.uniqid('pclzip-').'.gz';
    if (($v_file_compressed = @gzopen($v_gzip_temp_name, "wb")) == 0) {
      fclose($v_file);
      PclZip::privErrorLog(PCLZIP_ERR_WRITE_OPEN_FAIL, 'Unable to open temporary file \''.$v_gzip_temp_name.'\' in binary write mode');
      return PclZip::errorCode();
    }

    // ----- Read the file by PCLZIP_READ_BLOCK_SIZE octets blocks
    $v_size = filesize($p_filename);
    while ($v_size != 0) {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($v_file, $v_read_size);
      //$v_binary_data = pack('a'.$v_read_size, $v_buffer);
      @gzputs($v_file_compressed, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Close the file
    @fclose($v_file);
    @gzclose($v_file_compressed);

    // ----- Check the minimum file size
    if (filesize($v_gzip_temp_name) < 18) {
      PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'gzip temporary file \''.$v_gzip_temp_name.'\' has invalid filesize - should be minimum 18 bytes');
      return PclZip::errorCode();
    }

    // ----- Extract the compressed attributes
    if (($v_file_compressed = @fopen($v_gzip_temp_name, "rb")) == 0) {
      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open temporary file \''.$v_gzip_temp_name.'\' in binary read mode');
      return PclZip::errorCode();
    }

    // ----- Read the gzip file header
    $v_binary_data = @fread($v_file_compressed, 10);
    $v_data_header = unpack('a1id1/a1id2/a1cm/a1flag/Vmtime/a1xfl/a1os', $v_binary_data);

    // ----- Check some parameters
    $v_data_header['os'] = bin2hex($v_data_header['os']);

    // ----- Read the gzip file footer
    @fseek($v_file_compressed, filesize($v_gzip_temp_name)-8);
    $v_binary_data = @fread($v_file_compressed, 8);
    $v_data_footer = unpack('Vcrc/Vcompressed_size', $v_binary_data);

    // ----- Set the attributes
    $p_header['compression'] = ord($v_data_header['cm']);
    //$p_header['mtime'] = $v_data_header['mtime'];
    $p_header['crc'] = $v_data_footer['crc'];
    $p_header['compressed_size'] = filesize($v_gzip_temp_name)-18;

    // ----- Close the file
    @fclose($v_file_compressed);

    // ----- Call the header generation
    if (($v_result = $this->privWriteFileHeader($p_header)) != 1) {
      return $v_result;
    }

    // ----- Add the compressed data
    if (($v_file_compressed = @fopen($v_gzip_temp_name, "rb")) == 0)
    {
      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open temporary file \''.$v_gzip_temp_name.'\' in binary read mode');
      return PclZip::errorCode();
    }

    // ----- Read the file by PCLZIP_READ_BLOCK_SIZE octets blocks
    fseek($v_file_compressed, 10);
    $v_size = $p_header['compressed_size'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($v_file_compressed, $v_read_size);
      //$v_binary_data = pack('a'.$v_read_size, $v_buffer);
      @fwrite($this->zip_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Close the file
    @fclose($v_file_compressed);

    // ----- Unlink the temporary file
    @unlink($v_gzip_temp_name);
    
    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privCalculateStoredFilename()
  // Description :
  //   Based on file descriptor properties and global options, this method
  //   calculate the filename that will be stored in the archive.
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privCalculateStoredFilename(&$p_filedescr, &$p_options)
  {
    $v_result=1;
    
    // ----- Working variables
    $p_filename = $p_filedescr['filename'];
    if (isset($p_options[PCLZIP_OPT_ADD_PATH])) {
      $p_add_dir = $p_options[PCLZIP_OPT_ADD_PATH];
    }
    else {
      $p_add_dir = '';
    }
    if (isset($p_options[PCLZIP_OPT_REMOVE_PATH])) {
      $p_remove_dir = $p_options[PCLZIP_OPT_REMOVE_PATH];
    }
    else {
      $p_remove_dir = '';
    }
    if (isset($p_options[PCLZIP_OPT_REMOVE_ALL_PATH])) {
      $p_remove_all_dir = $p_options[PCLZIP_OPT_REMOVE_ALL_PATH];
    }
    else {
      $p_remove_all_dir = 0;
    }


    // ----- Look for full name change
    if (isset($p_filedescr['new_full_name'])) {
      // ----- Remove drive letter if any
      $v_stored_filename = PclZipUtilTranslateWinPath($p_filedescr['new_full_name']);
    }
    
    // ----- Look for path and/or short name change
    else {

      // ----- Look for short name change
      // Its when we cahnge just the filename but not the path
      if (isset($p_filedescr['new_short_name'])) {
        $v_path_info = pathinfo($p_filename);
        $v_dir = '';
        if ($v_path_info['dirname'] != '') {
          $v_dir = $v_path_info['dirname'].'/';
        }
        $v_stored_filename = $v_dir.$p_filedescr['new_short_name'];
      }
      else {
        // ----- Calculate the stored filename
        $v_stored_filename = $p_filename;
      }

      // ----- Look for all path to remove
      if ($p_remove_all_dir) {
        $v_stored_filename = basename($p_filename);
      }
      // ----- Look for partial path remove
      else if ($p_remove_dir != "") {
        if (substr($p_remove_dir, -1) != '/')
          $p_remove_dir .= "/";

        if (   (substr($p_filename, 0, 2) == "./")
            || (substr($p_remove_dir, 0, 2) == "./")) {
            
          if (   (substr($p_filename, 0, 2) == "./")
              && (substr($p_remove_dir, 0, 2) != "./")) {
            $p_remove_dir = "./".$p_remove_dir;
          }
          if (   (substr($p_filename, 0, 2) != "./")
              && (substr($p_remove_dir, 0, 2) == "./")) {
            $p_remove_dir = substr($p_remove_dir, 2);
          }
        }

        $v_compare = PclZipUtilPathInclusion($p_remove_dir,
                                             $v_stored_filename);
        if ($v_compare > 0) {
          if ($v_compare == 2) {
            $v_stored_filename = "";
          }
          else {
            $v_stored_filename = substr($v_stored_filename,
                                        strlen($p_remove_dir));
          }
        }
      }
      
      // ----- Remove drive letter if any
      $v_stored_filename = PclZipUtilTranslateWinPath($v_stored_filename);
      
      // ----- Look for path to add
      if ($p_add_dir != "") {
        if (substr($p_add_dir, -1) == "/")
          $v_stored_filename = $p_add_dir.$v_stored_filename;
        else
          $v_stored_filename = $p_add_dir."/".$v_stored_filename;
      }
    }

    // ----- Filename (reduce the path of stored name)
    $v_stored_filename = PclZipUtilPathReduction($v_stored_filename);
    $p_filedescr['stored_filename'] = $v_stored_filename;
    
    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privWriteFileHeader()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privWriteFileHeader(&$p_header)
  {
    $v_result=1;

    // ----- Store the offset position of the file
    $p_header['offset'] = ftell($this->zip_fd);

    // ----- Transform UNIX mtime to DOS format mdate/mtime
    $v_date = getdate($p_header['mtime']);
    $v_mtime = ($v_date['hours']<<11) + ($v_date['minutes']<<5) + $v_date['seconds']/2;
    $v_mdate = (($v_date['year']-1980)<<9) + ($v_date['mon']<<5) + $v_date['mday'];

    // ----- Packed data
    $v_binary_data = pack("VvvvvvVVVvv", 0x04034b50,
	                      $p_header['version_extracted'], $p_header['flag'],
                          $p_header['compression'], $v_mtime, $v_mdate,
                          $p_header['crc'], $p_header['compressed_size'],
						  $p_header['size'],
                          strlen($p_header['stored_filename']),
						  $p_header['extra_len']);

    // ----- Write the first 148 bytes of the header in the archive
    fputs($this->zip_fd, $v_binary_data, 30);

    // ----- Write the variable fields
    if (strlen($p_header['stored_filename']) != 0)
    {
      fputs($this->zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
    }
    if ($p_header['extra_len'] != 0)
    {
      fputs($this->zip_fd, $p_header['extra'], $p_header['extra_len']);
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privWriteCentralFileHeader()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privWriteCentralFileHeader(&$p_header)
  {
    $v_result=1;

    // TBC
    //for(reset($p_header); $key = key($p_header); next($p_header)) {
    //}

    // ----- Transform UNIX mtime to DOS format mdate/mtime
    $v_date = getdate($p_header['mtime']);
    $v_mtime = ($v_date['hours']<<11) + ($v_date['minutes']<<5) + $v_date['seconds']/2;
    $v_mdate = (($v_date['year']-1980)<<9) + ($v_date['mon']<<5) + $v_date['mday'];


    // ----- Packed data
    $v_binary_data = pack("VvvvvvvVVVvvvvvVV", 0x02014b50,
	                      $p_header['version'], $p_header['version_extracted'],
                          $p_header['flag'], $p_header['compression'],
						  $v_mtime, $v_mdate, $p_header['crc'],
                          $p_header['compressed_size'], $p_header['size'],
                          strlen($p_header['stored_filename']),
						  $p_header['extra_len'], $p_header['comment_len'],
                          $p_header['disk'], $p_header['internal'],
						  $p_header['external'], $p_header['offset']);

    // ----- Write the 42 bytes of the header in the zip file
    fputs($this->zip_fd, $v_binary_data, 46);

    // ----- Write the variable fields
    if (strlen($p_header['stored_filename']) != 0)
    {
      fputs($this->zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
    }
    if ($p_header['extra_len'] != 0)
    {
      fputs($this->zip_fd, $p_header['extra'], $p_header['extra_len']);
    }
    if ($p_header['comment_len'] != 0)
    {
      fputs($this->zip_fd, $p_header['comment'], $p_header['comment_len']);
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privWriteCentralHeader()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privWriteCentralHeader($p_nb_entries, $p_size, $p_offset, $p_comment)
  {
    $v_result=1;

    // ----- Packed data
    $v_binary_data = pack("VvvvvVVv", 0x06054b50, 0, 0, $p_nb_entries,
	                      $p_nb_entries, $p_size,
						  $p_offset, strlen($p_comment));

    // ----- Write the 22 bytes of the header in the zip file
    fputs($this->zip_fd, $v_binary_data, 22);

    // ----- Write the variable fields
    if (strlen($p_comment) != 0)
    {
      fputs($this->zip_fd, $p_comment, strlen($p_comment));
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privList()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privList(&$p_list)
  {
    $v_result=1;

    // ----- Magic quotes trick
    $this->privDisableMagicQuotes();

    // ----- Open the zip file
    if (($this->zip_fd = @fopen($this->zipname, 'rb')) == 0)
    {
      // ----- Magic quotes trick
      $this->privSwapBackMagicQuotes();
      
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open archive \''.$this->zipname.'\' in binary read mode');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Read the central directory informations
    $v_central_dir = array();
    if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1)
    {
      $this->privSwapBackMagicQuotes();
      return $v_result;
    }

    // ----- Go to beginning of Central Dir
    @rewind($this->zip_fd);
    if (@fseek($this->zip_fd, $v_central_dir['offset']))
    {
      $this->privSwapBackMagicQuotes();

      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Read each entry
    for ($i=0; $i<$v_central_dir['entries']; $i++)
    {
      // ----- Read the file header
      if (($v_result = $this->privReadCentralFileHeader($v_header)) != 1)
      {
        $this->privSwapBackMagicQuotes();
        return $v_result;
      }
      $v_header['index'] = $i;

      // ----- Get the only interesting attributes
      $this->privConvertHeader2FileInfo($v_header, $p_list[$i]);
      unset($v_header);
    }

    // ----- Close the zip file
    $this->privCloseFd();

    // ----- Magic quotes trick
    $this->privSwapBackMagicQuotes();

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privConvertHeader2FileInfo()
  // Description :
  //   This function takes the file informations from the central directory
  //   entries and extract the interesting parameters that will be given back.
  //   The resulting file infos are set in the array $p_info
  //     $p_info['filename'] : Filename with full path. Given by user (add),
  //                           extracted in the filesystem (extract).
  //     $p_info['stored_filename'] : Stored filename in the archive.
  //     $p_info['size'] = Size of the file.
  //     $p_info['compressed_size'] = Compressed size of the file.
  //     $p_info['mtime'] = Last modification date of the file.
  //     $p_info['comment'] = Comment associated with the file.
  //     $p_info['folder'] = true/false : indicates if the entry is a folder or not.
  //     $p_info['status'] = status of the action on the file.
  //     $p_info['crc'] = CRC of the file content.
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privConvertHeader2FileInfo($p_header, &$p_info)
  {
    $v_result=1;

    // ----- Get the interesting attributes
    $v_temp_path = PclZipUtilPathReduction($p_header['filename']);
    $p_info['filename'] = $v_temp_path;
    $v_temp_path = PclZipUtilPathReduction($p_header['stored_filename']);
    $p_info['stored_filename'] = $v_temp_path;
    $p_info['size'] = $p_header['size'];
    $p_info['compressed_size'] = $p_header['compressed_size'];
    $p_info['mtime'] = $p_header['mtime'];
    $p_info['comment'] = $p_header['comment'];
    $p_info['folder'] = (($p_header['external']&0x00000010)==0x00000010);
    $p_info['index'] = $p_header['index'];
    $p_info['status'] = $p_header['status'];
    $p_info['crc'] = $p_header['crc'];

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privExtractByRule()
  // Description :
  //   Extract a file or directory depending of rules (by index, by name, ...)
  // Parameters :
  //   $p_file_list : An array where will be placed the properties of each
  //                  extracted file
  //   $p_path : Path to add while writing the extracted files
  //   $p_remove_path : Path to remove (from the file memorized path) while writing the
  //                    extracted files. If the path does not match the file path,
  //                    the file is extracted with its memorized path.
  //                    $p_remove_path does not apply to 'list' mode.
  //                    $p_path and $p_remove_path are commulative.
  // Return Values :
  //   1 on success,0 or less on error (see error code list)
  // --------------------------------------------------------------------------------
  function privExtractByRule(&$p_file_list, $p_path, $p_remove_path, $p_remove_all_path, &$p_options)
  {
    $v_result=1;

    // ----- Magic quotes trick
    $this->privDisableMagicQuotes();

    // ----- Check the path
    if (   ($p_path == "")
	    || (   (substr($p_path, 0, 1) != "/")
		    && (substr($p_path, 0, 3) != "../")
			&& (substr($p_path,1,2)!=":/")))
      $p_path = "./".$p_path;

    // ----- Reduce the path last (and duplicated) '/'
    if (($p_path != "./") && ($p_path != "/"))
    {
      // ----- Look for the path end '/'
      while (substr($p_path, -1) == "/")
      {
        $p_path = substr($p_path, 0, strlen($p_path)-1);
      }
    }

    // ----- Look for path to remove format (should end by /)
    if (($p_remove_path != "") && (substr($p_remove_path, -1) != '/'))
    {
      $p_remove_path .= '/';
    }
    $p_remove_path_size = strlen($p_remove_path);

    // ----- Open the zip file
    if (($v_result = $this->privOpenFd('rb')) != 1)
    {
      $this->privSwapBackMagicQuotes();
      return $v_result;
    }

    // ----- Read the central directory informations
    $v_central_dir = array();
    if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1)
    {
      // ----- Close the zip file
      $this->privCloseFd();
      $this->privSwapBackMagicQuotes();

      return $v_result;
    }

    // ----- Start at beginning of Central Dir
    $v_pos_entry = $v_central_dir['offset'];

    // ----- Read each entry
    $j_start = 0;
    for ($i=0, $v_nb_extracted=0; $i<$v_central_dir['entries']; $i++)
    {

      // ----- Read next Central dir entry
      @rewind($this->zip_fd);
      if (@fseek($this->zip_fd, $v_pos_entry))
      {
        // ----- Close the zip file
        $this->privCloseFd();
        $this->privSwapBackMagicQuotes();

        // ----- Error log
        PclZip::privErrorLog(PCLZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');

        // ----- Return
        return PclZip::errorCode();
      }

      // ----- Read the file header
      $v_header = array();
      if (($v_result = $this->privReadCentralFileHeader($v_header)) != 1)
      {
        // ----- Close the zip file
        $this->privCloseFd();
        $this->privSwapBackMagicQuotes();

        return $v_result;
      }

      // ----- Store the index
      $v_header['index'] = $i;

      // ----- Store the file position
      $v_pos_entry = ftell($this->zip_fd);

      // ----- Look for the specific extract rules
      $v_extract = false;

      // ----- Look for extract by name rule
      if (   (isset($p_options[PCLZIP_OPT_BY_NAME]))
          && ($p_options[PCLZIP_OPT_BY_NAME] != 0)) {

          // ----- Look if the filename is in the list
          for ($j=0; ($j<sizeof($p_options[PCLZIP_OPT_BY_NAME])) && (!$v_extract); $j++) {

              // ----- Look for a directory
              if (substr($p_options[PCLZIP_OPT_BY_NAME][$j], -1) == "/") {

                  // ----- Look if the directory is in the filename path
                  if (   (strlen($v_header['stored_filename']) > strlen($p_options[PCLZIP_OPT_BY_NAME][$j]))
                      && (substr($v_header['stored_filename'], 0, strlen($p_options[PCLZIP_OPT_BY_NAME][$j])) == $p_options[PCLZIP_OPT_BY_NAME][$j])) {
                      $v_extract = true;
                  }
              }
              // ----- Look for a filename
              elseif ($v_header['stored_filename'] == $p_options[PCLZIP_OPT_BY_NAME][$j]) {
                  $v_extract = true;
              }
          }
      }

      // ----- Look for extract by ereg rule
      // ereg() is deprecated with PHP 5.3
      /* 
      else if (   (isset($p_options[PCLZIP_OPT_BY_EREG]))
               && ($p_options[PCLZIP_OPT_BY_EREG] != "")) {

          if (ereg($p_options[PCLZIP_OPT_BY_EREG], $v_header['stored_filename'])) {
              $v_extract = true;
          }
      }
      */

      // ----- Look for extract by preg rule
      else if (   (isset($p_options[PCLZIP_OPT_BY_PREG]))
               && ($p_options[PCLZIP_OPT_BY_PREG] != "")) {

          if (preg_match($p_options[PCLZIP_OPT_BY_PREG], $v_header['stored_filename'])) {
              $v_extract = true;
          }
      }

      // ----- Look for extract by index rule
      else if (   (isset($p_options[PCLZIP_OPT_BY_INDEX]))
               && ($p_options[PCLZIP_OPT_BY_INDEX] != 0)) {
          
          // ----- Look if the index is in the list
          for ($j=$j_start; ($j<sizeof($p_options[PCLZIP_OPT_BY_INDEX])) && (!$v_extract); $j++) {

              if (($i>=$p_options[PCLZIP_OPT_BY_INDEX][$j]['start']) && ($i<=$p_options[PCLZIP_OPT_BY_INDEX][$j]['end'])) {
                  $v_extract = true;
              }
              if ($i>=$p_options[PCLZIP_OPT_BY_INDEX][$j]['end']) {
                  $j_start = $j+1;
              }

              if ($p_options[PCLZIP_OPT_BY_INDEX][$j]['start']>$i) {
                  break;
              }
          }
      }

      // ----- Look for no rule, which means extract all the archive
      else {
          $v_extract = true;
      }

	  // ----- Check compression method
	  if (   ($v_extract)
	      && (   ($v_header['compression'] != 8)
		      && ($v_header['compression'] != 0))) {
          $v_header['status'] = 'unsupported_compression';

          // ----- Look for PCLZIP_OPT_STOP_ON_ERROR
          if (   (isset($p_options[PCLZIP_OPT_STOP_ON_ERROR]))
		      && ($p_options[PCLZIP_OPT_STOP_ON_ERROR]===true)) {

              $this->privSwapBackMagicQuotes();
              
              PclZip::privErrorLog(PCLZIP_ERR_UNSUPPORTED_COMPRESSION,
			                       "Filename '".$v_header['stored_filename']."' is "
				  	    	  	   ."compressed by an unsupported compression "
				  	    	  	   ."method (".$v_header['compression'].") ");

              return PclZip::errorCode();
		  }
	  }
	  
	  // ----- Check encrypted files
	  if (($v_extract) && (($v_header['flag'] & 1) == 1)) {
          $v_header['status'] = 'unsupported_encryption';

          // ----- Look for PCLZIP_OPT_STOP_ON_ERROR
          if (   (isset($p_options[PCLZIP_OPT_STOP_ON_ERROR]))
		      && ($p_options[PCLZIP_OPT_STOP_ON_ERROR]===true)) {

              $this->privSwapBackMagicQuotes();

              PclZip::privErrorLog(PCLZIP_ERR_UNSUPPORTED_ENCRYPTION,
			                       "Unsupported encryption for "
				  	    	  	   ." filename '".$v_header['stored_filename']
								   ."'");

              return PclZip::errorCode();
		  }
    }

      // ----- Look for real extraction
      if (($v_extract) && ($v_header['status'] != 'ok')) {
          $v_result = $this->privConvertHeader2FileInfo($v_header,
		                                        $p_file_list[$v_nb_extracted++]);
          if ($v_result != 1) {
              $this->privCloseFd();
              $this->privSwapBackMagicQuotes();
              return $v_result;
          }

          $v_extract = false;
      }
      
      // ----- Look for real extraction
      if ($v_extract)
      {

        // ----- Go to the file position
        @rewind($this->zip_fd);
        if (@fseek($this->zip_fd, $v_header['offset']))
        {
          // ----- Close the zip file
          $this->privCloseFd();

          $this->privSwapBackMagicQuotes();

          // ----- Error log
          PclZip::privErrorLog(PCLZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');

          // ----- Return
          return PclZip::errorCode();
        }

        // ----- Look for extraction as string
        if ($p_options[PCLZIP_OPT_EXTRACT_AS_STRING]) {

          $v_string = '';

          // ----- Extracting the file
          $v_result1 = $this->privExtractFileAsString($v_header, $v_string, $p_options);
          if ($v_result1 < 1) {
            $this->privCloseFd();
            $this->privSwapBackMagicQuotes();
            return $v_result1;
          }

          // ----- Get the only interesting attributes
          if (($v_result = $this->privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted])) != 1)
          {
            // ----- Close the zip file
            $this->privCloseFd();
            $this->privSwapBackMagicQuotes();

            return $v_result;
          }

          // ----- Set the file content
          $p_file_list[$v_nb_extracted]['content'] = $v_string;

          // ----- Next extracted file
          $v_nb_extracted++;
          
          // ----- Look for user callback abort
          if ($v_result1 == 2) {
          	break;
          }
        }
        // ----- Look for extraction in standard output
        elseif (   (isset($p_options[PCLZIP_OPT_EXTRACT_IN_OUTPUT]))
		        && ($p_options[PCLZIP_OPT_EXTRACT_IN_OUTPUT])) {
          // ----- Extracting the file in standard output
          $v_result1 = $this->privExtractFileInOutput($v_header, $p_options);
          if ($v_result1 < 1) {
            $this->privCloseFd();
            $this->privSwapBackMagicQuotes();
            return $v_result1;
          }

          // ----- Get the only interesting attributes
          if (($v_result = $this->privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted++])) != 1) {
            $this->privCloseFd();
            $this->privSwapBackMagicQuotes();
            return $v_result;
          }

          // ----- Look for user callback abort
          if ($v_result1 == 2) {
          	break;
          }
        }
        // ----- Look for normal extraction
        else {
          // ----- Extracting the file
          $v_result1 = $this->privExtractFile($v_header,
		                                      $p_path, $p_remove_path,
											  $p_remove_all_path,
											  $p_options);
          if ($v_result1 < 1) {
            $this->privCloseFd();
            $this->privSwapBackMagicQuotes();
            return $v_result1;
          }

          // ----- Get the only interesting attributes
          if (($v_result = $this->privConvertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted++])) != 1)
          {
            // ----- Close the zip file
            $this->privCloseFd();
            $this->privSwapBackMagicQuotes();

            return $v_result;
          }

          // ----- Look for user callback abort
          if ($v_result1 == 2) {
          	break;
          }
        }
      }
    }

    // ----- Close the zip file
    $this->privCloseFd();
    $this->privSwapBackMagicQuotes();

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privExtractFile()
  // Description :
  // Parameters :
  // Return Values :
  //
  // 1 : ... ?
  // PCLZIP_ERR_USER_ABORTED(2) : User ask for extraction stop in callback
  // --------------------------------------------------------------------------------
  function privExtractFile(&$p_entry, $p_path, $p_remove_path, $p_remove_all_path, &$p_options)
  {
    $v_result=1;

    // ----- Read the file header
    if (($v_result = $this->privReadFileHeader($v_header)) != 1)
    {
      // ----- Return
      return $v_result;
    }


    // ----- Check that the file header is coherent with $p_entry info
    if ($this->privCheckFileHeaders($v_header, $p_entry) != 1) {
        // TBC
    }

    // ----- Look for all path to remove
    if ($p_remove_all_path == true) {
        // ----- Look for folder entry that not need to be extracted
        if (($p_entry['external']&0x00000010)==0x00000010) {

            $p_entry['status'] = "filtered";

            return $v_result;
        }

        // ----- Get the basename of the path
        $p_entry['filename'] = basename($p_entry['filename']);
    }

    // ----- Look for path to remove
    else if ($p_remove_path != "")
    {
      if (PclZipUtilPathInclusion($p_remove_path, $p_entry['filename']) == 2)
      {

        // ----- Change the file status
        $p_entry['status'] = "filtered";

        // ----- Return
        return $v_result;
      }

      $p_remove_path_size = strlen($p_remove_path);
      if (substr($p_entry['filename'], 0, $p_remove_path_size) == $p_remove_path)
      {

        // ----- Remove the path
        $p_entry['filename'] = substr($p_entry['filename'], $p_remove_path_size);

      }
    }

    // ----- Add the path
    if ($p_path != '') {
      $p_entry['filename'] = $p_path."/".$p_entry['filename'];
    }
    
    // ----- Check a base_dir_restriction
    if (isset($p_options[PCLZIP_OPT_EXTRACT_DIR_RESTRICTION])) {
      $v_inclusion
      = PclZipUtilPathInclusion($p_options[PCLZIP_OPT_EXTRACT_DIR_RESTRICTION],
                                $p_entry['filename']); 
      if ($v_inclusion == 0) {

        PclZip::privErrorLog(PCLZIP_ERR_DIRECTORY_RESTRICTION,
			                     "Filename '".$p_entry['filename']."' is "
								 ."outside PCLZIP_OPT_EXTRACT_DIR_RESTRICTION");

        return PclZip::errorCode();
      }
    }

    // ----- Look for pre-extract callback
    if (isset($p_options[PCLZIP_CB_PRE_EXTRACT])) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
//      eval('$v_result = '.$p_options[PCLZIP_CB_PRE_EXTRACT].'(PCLZIP_CB_PRE_EXTRACT, $v_local_header);');
      $v_result = $p_options[PCLZIP_CB_PRE_EXTRACT](PCLZIP_CB_PRE_EXTRACT, $v_local_header);
      if ($v_result == 0) {
        // ----- Change the file status
        $p_entry['status'] = "skipped";
        $v_result = 1;
      }
      
      // ----- Look for abort result
      if ($v_result == 2) {
        // ----- This status is internal and will be changed in 'skipped'
        $p_entry['status'] = "aborted";
      	$v_result = PCLZIP_ERR_USER_ABORTED;
      }

      // ----- Update the informations
      // Only some fields can be modified
      $p_entry['filename'] = $v_local_header['filename'];
    }


    // ----- Look if extraction should be done
    if ($p_entry['status'] == 'ok') {

    // ----- Look for specific actions while the file exist
    if (file_exists($p_entry['filename']))
    {

      // ----- Look if file is a directory
      if (is_dir($p_entry['filename']))
      {

        // ----- Change the file status
        $p_entry['status'] = "already_a_directory";
        
        // ----- Look for PCLZIP_OPT_STOP_ON_ERROR
        // For historical reason first PclZip implementation does not stop
        // when this kind of error occurs.
        if (   (isset($p_options[PCLZIP_OPT_STOP_ON_ERROR]))
		    && ($p_options[PCLZIP_OPT_STOP_ON_ERROR]===true)) {

            PclZip::privErrorLog(PCLZIP_ERR_ALREADY_A_DIRECTORY,
			                     "Filename '".$p_entry['filename']."' is "
								 ."already used by an existing directory");

            return PclZip::errorCode();
		    }
      }
      // ----- Look if file is write protected
      else if (!is_writeable($p_entry['filename']))
      {

        // ----- Change the file status
        $p_entry['status'] = "write_protected";

        // ----- Look for PCLZIP_OPT_STOP_ON_ERROR
        // For historical reason first PclZip implementation does not stop
        // when this kind of error occurs.
        if (   (isset($p_options[PCLZIP_OPT_STOP_ON_ERROR]))
		    && ($p_options[PCLZIP_OPT_STOP_ON_ERROR]===true)) {

            PclZip::privErrorLog(PCLZIP_ERR_WRITE_OPEN_FAIL,
			                     "Filename '".$p_entry['filename']."' exists "
								 ."and is write protected");

            return PclZip::errorCode();
		    }
      }

      // ----- Look if the extracted file is older
      else if (filemtime($p_entry['filename']) > $p_entry['mtime'])
      {
        // ----- Change the file status
        if (   (isset($p_options[PCLZIP_OPT_REPLACE_NEWER]))
		    && ($p_options[PCLZIP_OPT_REPLACE_NEWER]===true)) {
	  	  }
		    else {
            $p_entry['status'] = "newer_exist";

            // ----- Look for PCLZIP_OPT_STOP_ON_ERROR
            // For historical reason first PclZip implementation does not stop
            // when this kind of error occurs.
            if (   (isset($p_options[PCLZIP_OPT_STOP_ON_ERROR]))
		        && ($p_options[PCLZIP_OPT_STOP_ON_ERROR]===true)) {

                PclZip::privErrorLog(PCLZIP_ERR_WRITE_OPEN_FAIL,
			             "Newer version of '".$p_entry['filename']."' exists "
					    ."and option PCLZIP_OPT_REPLACE_NEWER is not selected");

                return PclZip::errorCode();
		      }
		    }
      }
      else {
      }
    }

    // ----- Check the directory availability and create it if necessary
    else {
      if ((($p_entry['external']&0x00000010)==0x00000010) || (substr($p_entry['filename'], -1) == '/'))
        $v_dir_to_check = $p_entry['filename'];
      else if (!strstr($p_entry['filename'], "/"))
        $v_dir_to_check = "";
      else
        $v_dir_to_check = dirname($p_entry['filename']);

        if (($v_result = $this->privDirCheck($v_dir_to_check, (($p_entry['external']&0x00000010)==0x00000010))) != 1) {
  
          // ----- Change the file status
          $p_entry['status'] = "path_creation_fail";
  
          // ----- Return
          //return $v_result;
          $v_result = 1;
        }
      }
    }

    // ----- Look if extraction should be done
    if ($p_entry['status'] == 'ok') {

      // ----- Do the extraction (if not a folder)
      if (!(($p_entry['external']&0x00000010)==0x00000010))
      {
        // ----- Look for not compressed file
        if ($p_entry['compression'] == 0) {

    		  // ----- Opening destination file
          if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) == 0)
          {

            // ----- Change the file status
            $p_entry['status'] = "write_error";

            // ----- Return
            return $v_result;
          }


          // ----- Read the file by PCLZIP_READ_BLOCK_SIZE octets blocks
          $v_size = $p_entry['compressed_size'];
          while ($v_size != 0)
          {
            $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
            $v_buffer = @fread($this->zip_fd, $v_read_size);
            /* Try to speed up the code
            $v_binary_data = pack('a'.$v_read_size, $v_buffer);
            @fwrite($v_dest_file, $v_binary_data, $v_read_size);
            */
            @fwrite($v_dest_file, $v_buffer, $v_read_size);            
            $v_size -= $v_read_size;
          }

          // ----- Closing the destination file
          fclose($v_dest_file);

          // ----- Change the file mtime
          touch($p_entry['filename'], $p_entry['mtime']);
          

        }
        else {
          // ----- TBC
          // Need to be finished
          if (($p_entry['flag'] & 1) == 1) {
            PclZip::privErrorLog(PCLZIP_ERR_UNSUPPORTED_ENCRYPTION, 'File \''.$p_entry['filename'].'\' is encrypted. Encrypted files are not supported.');
            return PclZip::errorCode();
          }


          // ----- Look for using temporary file to unzip
          if ( (!isset($p_options[PCLZIP_OPT_TEMP_FILE_OFF])) 
              && (isset($p_options[PCLZIP_OPT_TEMP_FILE_ON])
                  || (isset($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD])
                      && ($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD] <= $p_entry['size'])) ) ) {
            $v_result = $this->privExtractFileUsingTempFile($p_entry, $p_options);
            if ($v_result < PCLZIP_ERR_NO_ERROR) {
              return $v_result;
            }
          }
          
          // ----- Look for extract in memory
          else {

          
            // ----- Read the compressed file in a buffer (one shot)
            $v_buffer = @fread($this->zip_fd, $p_entry['compressed_size']);
            
            // ----- Decompress the file
            $v_file_content = @gzinflate($v_buffer);
            unset($v_buffer);
            if ($v_file_content === FALSE) {
  
              // ----- Change the file status
              // TBC
              $p_entry['status'] = "error";
              
              return $v_result;
            }
            
            // ----- Opening destination file
            if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) == 0) {
  
              // ----- Change the file status
              $p_entry['status'] = "write_error";
  
              return $v_result;
            }
  
            // ----- Write the uncompressed data
            @fwrite($v_dest_file, $v_file_content, $p_entry['size']);
            unset($v_file_content);
  
            // ----- Closing the destination file
            @fclose($v_dest_file);
            
          }

          // ----- Change the file mtime
          @touch($p_entry['filename'], $p_entry['mtime']);
        }

        // ----- Look for chmod option
        if (isset($p_options[PCLZIP_OPT_SET_CHMOD])) {

          // ----- Change the mode of the file
          @chmod($p_entry['filename'], $p_options[PCLZIP_OPT_SET_CHMOD]);
        }

      }
    }

  	// ----- Change abort status
  	if ($p_entry['status'] == "aborted") {
        $p_entry['status'] = "skipped";
  	}
	
    // ----- Look for post-extract callback
    elseif (isset($p_options[PCLZIP_CB_POST_EXTRACT])) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
//      eval('$v_result = '.$p_options[PCLZIP_CB_POST_EXTRACT].'(PCLZIP_CB_POST_EXTRACT, $v_local_header);');
      $v_result = $p_options[PCLZIP_CB_POST_EXTRACT](PCLZIP_CB_POST_EXTRACT, $v_local_header);

      // ----- Look for abort result
      if ($v_result == 2) {
      	$v_result = PCLZIP_ERR_USER_ABORTED;
      }
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privExtractFileUsingTempFile()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privExtractFileUsingTempFile(&$p_entry, &$p_options)
  {
    $v_result=1;
        
    // ----- Creates a temporary file
    $v_gzip_temp_name = PCLZIP_TEMPORARY_DIR.uniqid('pclzip-').'.gz';
    if (($v_dest_file = @fopen($v_gzip_temp_name, "wb")) == 0) {
      fclose($v_file);
      PclZip::privErrorLog(PCLZIP_ERR_WRITE_OPEN_FAIL, 'Unable to open temporary file \''.$v_gzip_temp_name.'\' in binary write mode');
      return PclZip::errorCode();
    }


    // ----- Write gz file format header
    $v_binary_data = pack('va1a1Va1a1', 0x8b1f, Chr($p_entry['compression']), Chr(0x00), time(), Chr(0x00), Chr(3));
    @fwrite($v_dest_file, $v_binary_data, 10);

    // ----- Read the file by PCLZIP_READ_BLOCK_SIZE octets blocks
    $v_size = $p_entry['compressed_size'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($this->zip_fd, $v_read_size);
      //$v_binary_data = pack('a'.$v_read_size, $v_buffer);
      @fwrite($v_dest_file, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Write gz file format footer
    $v_binary_data = pack('VV', $p_entry['crc'], $p_entry['size']);
    @fwrite($v_dest_file, $v_binary_data, 8);

    // ----- Close the temporary file
    @fclose($v_dest_file);

    // ----- Opening destination file
    if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) == 0) {
      $p_entry['status'] = "write_error";
      return $v_result;
    }

    // ----- Open the temporary gz file
    if (($v_src_file = @gzopen($v_gzip_temp_name, 'rb')) == 0) {
      @fclose($v_dest_file);
      $p_entry['status'] = "read_error";
      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open temporary file \''.$v_gzip_temp_name.'\' in binary read mode');
      return PclZip::errorCode();
    }


    // ----- Read the file by PCLZIP_READ_BLOCK_SIZE octets blocks
    $v_size = $p_entry['size'];
    while ($v_size != 0) {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = @gzread($v_src_file, $v_read_size);
      //$v_binary_data = pack('a'.$v_read_size, $v_buffer);
      @fwrite($v_dest_file, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }
    @fclose($v_dest_file);
    @gzclose($v_src_file);

    // ----- Delete the temporary file
    @unlink($v_gzip_temp_name);
    
    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privExtractFileInOutput()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privExtractFileInOutput(&$p_entry, &$p_options)
  {
    $v_result=1;

    // ----- Read the file header
    if (($v_result = $this->privReadFileHeader($v_header)) != 1) {
      return $v_result;
    }


    // ----- Check that the file header is coherent with $p_entry info
    if ($this->privCheckFileHeaders($v_header, $p_entry) != 1) {
        // TBC
    }

    // ----- Look for pre-extract callback
    if (isset($p_options[PCLZIP_CB_PRE_EXTRACT])) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
//      eval('$v_result = '.$p_options[PCLZIP_CB_PRE_EXTRACT].'(PCLZIP_CB_PRE_EXTRACT, $v_local_header);');
      $v_result = $p_options[PCLZIP_CB_PRE_EXTRACT](PCLZIP_CB_PRE_EXTRACT, $v_local_header);
      if ($v_result == 0) {
        // ----- Change the file status
        $p_entry['status'] = "skipped";
        $v_result = 1;
      }

      // ----- Look for abort result
      if ($v_result == 2) {
        // ----- This status is internal and will be changed in 'skipped'
        $p_entry['status'] = "aborted";
      	$v_result = PCLZIP_ERR_USER_ABORTED;
      }

      // ----- Update the informations
      // Only some fields can be modified
      $p_entry['filename'] = $v_local_header['filename'];
    }

    // ----- Trace

    // ----- Look if extraction should be done
    if ($p_entry['status'] == 'ok') {

      // ----- Do the extraction (if not a folder)
      if (!(($p_entry['external']&0x00000010)==0x00000010)) {
        // ----- Look for not compressed file
        if ($p_entry['compressed_size'] == $p_entry['size']) {

          // ----- Read the file in a buffer (one shot)
          $v_buffer = @fread($this->zip_fd, $p_entry['compressed_size']);

          // ----- Send the file to the output
          echo $v_buffer;
          unset($v_buffer);
        }
        else {

          // ----- Read the compressed file in a buffer (one shot)
          $v_buffer = @fread($this->zip_fd, $p_entry['compressed_size']);
          
          // ----- Decompress the file
          $v_file_content = gzinflate($v_buffer);
          unset($v_buffer);

          // ----- Send the file to the output
          echo $v_file_content;
          unset($v_file_content);
        }
      }
    }

	// ----- Change abort status
	if ($p_entry['status'] == "aborted") {
      $p_entry['status'] = "skipped";
	}

    // ----- Look for post-extract callback
    elseif (isset($p_options[PCLZIP_CB_POST_EXTRACT])) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
//      eval('$v_result = '.$p_options[PCLZIP_CB_POST_EXTRACT].'(PCLZIP_CB_POST_EXTRACT, $v_local_header);');
      $v_result = $p_options[PCLZIP_CB_POST_EXTRACT](PCLZIP_CB_POST_EXTRACT, $v_local_header);

      // ----- Look for abort result
      if ($v_result == 2) {
      	$v_result = PCLZIP_ERR_USER_ABORTED;
      }
    }

    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privExtractFileAsString()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privExtractFileAsString(&$p_entry, &$p_string, &$p_options)
  {
    $v_result=1;

    // ----- Read the file header
    $v_header = array();
    if (($v_result = $this->privReadFileHeader($v_header)) != 1)
    {
      // ----- Return
      return $v_result;
    }


    // ----- Check that the file header is coherent with $p_entry info
    if ($this->privCheckFileHeaders($v_header, $p_entry) != 1) {
        // TBC
    }

    // ----- Look for pre-extract callback
    if (isset($p_options[PCLZIP_CB_PRE_EXTRACT])) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->privConvertHeader2FileInfo($p_entry, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
//      eval('$v_result = '.$p_options[PCLZIP_CB_PRE_EXTRACT].'(PCLZIP_CB_PRE_EXTRACT, $v_local_header);');
      $v_result = $p_options[PCLZIP_CB_PRE_EXTRACT](PCLZIP_CB_PRE_EXTRACT, $v_local_header);
      if ($v_result == 0) {
        // ----- Change the file status
        $p_entry['status'] = "skipped";
        $v_result = 1;
      }
      
      // ----- Look for abort result
      if ($v_result == 2) {
        // ----- This status is internal and will be changed in 'skipped'
        $p_entry['status'] = "aborted";
      	$v_result = PCLZIP_ERR_USER_ABORTED;
      }

      // ----- Update the informations
      // Only some fields can be modified
      $p_entry['filename'] = $v_local_header['filename'];
    }


    // ----- Look if extraction should be done
    if ($p_entry['status'] == 'ok') {

      // ----- Do the extraction (if not a folder)
      if (!(($p_entry['external']&0x00000010)==0x00000010)) {
        // ----- Look for not compressed file
  //      if ($p_entry['compressed_size'] == $p_entry['size'])
        if ($p_entry['compression'] == 0) {
  
          // ----- Reading the file
          $p_string = @fread($this->zip_fd, $p_entry['compressed_size']);
        }
        else {
  
          // ----- Reading the file
          $v_data = @fread($this->zip_fd, $p_entry['compressed_size']);
          
          // ----- Decompress the file
          if (($p_string = @gzinflate($v_data)) === FALSE) {
              // TBC
          }
        }
  
        // ----- Trace
      }
      else {
          // TBC : error : can not extract a folder in a string
      }
      
    }

  	// ----- Change abort status
  	if ($p_entry['status'] == "aborted") {
        $p_entry['status'] = "skipped";
  	}
	
    // ----- Look for post-extract callback
    elseif (isset($p_options[PCLZIP_CB_POST_EXTRACT])) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->privConvertHeader2FileInfo($p_entry, $v_local_header);
      
      // ----- Swap the content to header
      $v_local_header['content'] = $p_string;
      $p_string = '';

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
//      eval('$v_result = '.$p_options[PCLZIP_CB_POST_EXTRACT].'(PCLZIP_CB_POST_EXTRACT, $v_local_header);');
      $v_result = $p_options[PCLZIP_CB_POST_EXTRACT](PCLZIP_CB_POST_EXTRACT, $v_local_header);

      // ----- Swap back the content to header
      $p_string = $v_local_header['content'];
      unset($v_local_header['content']);

      // ----- Look for abort result
      if ($v_result == 2) {
      	$v_result = PCLZIP_ERR_USER_ABORTED;
      }
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privReadFileHeader()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privReadFileHeader(&$p_header)
  {
    $v_result=1;

    // ----- Read the 4 bytes signature
    $v_binary_data = @fread($this->zip_fd, 4);
    $v_data = unpack('Vid', $v_binary_data);

    // ----- Check signature
    if ($v_data['id'] != 0x04034b50)
    {

      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'Invalid archive structure');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Read the first 42 bytes of the header
    $v_binary_data = fread($this->zip_fd, 26);

    // ----- Look for invalid block size
    if (strlen($v_binary_data) != 26)
    {
      $p_header['filename'] = "";
      $p_header['status'] = "invalid_header";

      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT, "Invalid block size : ".strlen($v_binary_data));

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Extract the values
    $v_data = unpack('vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $v_binary_data);

    // ----- Get filename
    $p_header['filename'] = fread($this->zip_fd, $v_data['filename_len']);

    // ----- Get extra_fields
    if ($v_data['extra_len'] != 0) {
      $p_header['extra'] = fread($this->zip_fd, $v_data['extra_len']);
    }
    else {
      $p_header['extra'] = '';
    }

    // ----- Extract properties
    $p_header['version_extracted'] = $v_data['version'];
    $p_header['compression'] = $v_data['compression'];
    $p_header['size'] = $v_data['size'];
    $p_header['compressed_size'] = $v_data['compressed_size'];
    $p_header['crc'] = $v_data['crc'];
    $p_header['flag'] = $v_data['flag'];
    $p_header['filename_len'] = $v_data['filename_len'];

    // ----- Recuperate date in UNIX format
    $p_header['mdate'] = $v_data['mdate'];
    $p_header['mtime'] = $v_data['mtime'];
    if ($p_header['mdate'] && $p_header['mtime'])
    {
      // ----- Extract time
      $v_hour = ($p_header['mtime'] & 0xF800) >> 11;
      $v_minute = ($p_header['mtime'] & 0x07E0) >> 5;
      $v_seconde = ($p_header['mtime'] & 0x001F)*2;

      // ----- Extract date
      $v_year = (($p_header['mdate'] & 0xFE00) >> 9) + 1980;
      $v_month = ($p_header['mdate'] & 0x01E0) >> 5;
      $v_day = $p_header['mdate'] & 0x001F;

      // ----- Get UNIX date format
      $p_header['mtime'] = @mktime($v_hour, $v_minute, $v_seconde, $v_month, $v_day, $v_year);

    }
    else
    {
      $p_header['mtime'] = time();
    }

    // TBC
    //for(reset($v_data); $key = key($v_data); next($v_data)) {
    //}

    // ----- Set the stored filename
    $p_header['stored_filename'] = $p_header['filename'];

    // ----- Set the status field
    $p_header['status'] = "ok";

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privReadCentralFileHeader()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privReadCentralFileHeader(&$p_header)
  {
    $v_result=1;

    // ----- Read the 4 bytes signature
    $v_binary_data = @fread($this->zip_fd, 4);
    $v_data = unpack('Vid', $v_binary_data);

    // ----- Check signature
    if ($v_data['id'] != 0x02014b50)
    {

      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'Invalid archive structure');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Read the first 42 bytes of the header
    $v_binary_data = fread($this->zip_fd, 42);

    // ----- Look for invalid block size
    if (strlen($v_binary_data) != 42)
    {
      $p_header['filename'] = "";
      $p_header['status'] = "invalid_header";

      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT, "Invalid block size : ".strlen($v_binary_data));

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Extract the values
    $p_header = unpack('vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $v_binary_data);

    // ----- Get filename
    if ($p_header['filename_len'] != 0)
      $p_header['filename'] = fread($this->zip_fd, $p_header['filename_len']);
    else
      $p_header['filename'] = '';

    // ----- Get extra
    if ($p_header['extra_len'] != 0)
      $p_header['extra'] = fread($this->zip_fd, $p_header['extra_len']);
    else
      $p_header['extra'] = '';

    // ----- Get comment
    if ($p_header['comment_len'] != 0)
      $p_header['comment'] = fread($this->zip_fd, $p_header['comment_len']);
    else
      $p_header['comment'] = '';

    // ----- Extract properties

    // ----- Recuperate date in UNIX format
    //if ($p_header['mdate'] && $p_header['mtime'])
    // TBC : bug : this was ignoring time with 0/0/0
    if (1)
    {
      // ----- Extract time
      $v_hour = ($p_header['mtime'] & 0xF800) >> 11;
      $v_minute = ($p_header['mtime'] & 0x07E0) >> 5;
      $v_seconde = ($p_header['mtime'] & 0x001F)*2;

      // ----- Extract date
      $v_year = (($p_header['mdate'] & 0xFE00) >> 9) + 1980;
      $v_month = ($p_header['mdate'] & 0x01E0) >> 5;
      $v_day = $p_header['mdate'] & 0x001F;

      // ----- Get UNIX date format
      $p_header['mtime'] = @mktime($v_hour, $v_minute, $v_seconde, $v_month, $v_day, $v_year);

    }
    else
    {
      $p_header['mtime'] = time();
    }

    // ----- Set the stored filename
    $p_header['stored_filename'] = $p_header['filename'];

    // ----- Set default status to ok
    $p_header['status'] = 'ok';

    // ----- Look if it is a directory
    if (substr($p_header['filename'], -1) == '/') {
      //$p_header['external'] = 0x41FF0010;
      $p_header['external'] = 0x00000010;
    }


    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privCheckFileHeaders()
  // Description :
  // Parameters :
  // Return Values :
  //   1 on success,
  //   0 on error;
  // --------------------------------------------------------------------------------
  function privCheckFileHeaders(&$p_local_header, &$p_central_header)
  {
    $v_result=1;

  	// ----- Check the static values
  	// TBC
  	if ($p_local_header['filename'] != $p_central_header['filename']) {
  	}
  	if ($p_local_header['version_extracted'] != $p_central_header['version_extracted']) {
  	}
  	if ($p_local_header['flag'] != $p_central_header['flag']) {
  	}
  	if ($p_local_header['compression'] != $p_central_header['compression']) {
  	}
  	if ($p_local_header['mtime'] != $p_central_header['mtime']) {
  	}
  	if ($p_local_header['filename_len'] != $p_central_header['filename_len']) {
  	}
  
  	// ----- Look for flag bit 3
  	if (($p_local_header['flag'] & 8) == 8) {
          $p_local_header['size'] = $p_central_header['size'];
          $p_local_header['compressed_size'] = $p_central_header['compressed_size'];
          $p_local_header['crc'] = $p_central_header['crc'];
  	}

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privReadEndCentralDir()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privReadEndCentralDir(&$p_central_dir)
  {
    $v_result=1;

    // ----- Go to the end of the zip file
    $v_size = filesize($this->zipname);
    @fseek($this->zip_fd, $v_size);
    if (@ftell($this->zip_fd) != $v_size)
    {
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'Unable to go to the end of the archive \''.$this->zipname.'\'');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- First try : look if this is an archive with no commentaries (most of the time)
    // in this case the end of central dir is at 22 bytes of the file end
    $v_found = 0;
    if ($v_size > 26) {
      @fseek($this->zip_fd, $v_size-22);
      if (($v_pos = @ftell($this->zip_fd)) != ($v_size-22))
      {
        // ----- Error log
        PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'Unable to seek back to the middle of the archive \''.$this->zipname.'\'');

        // ----- Return
        return PclZip::errorCode();
      }

      // ----- Read for bytes
      $v_binary_data = @fread($this->zip_fd, 4);
      $v_data = @unpack('Vid', $v_binary_data);

      // ----- Check signature
      if ($v_data['id'] == 0x06054b50) {
        $v_found = 1;
      }

      $v_pos = ftell($this->zip_fd);
    }

    // ----- Go back to the maximum possible size of the Central Dir End Record
    if (!$v_found) {
      $v_maximum_size = 65557; // 0xFFFF + 22;
      if ($v_maximum_size > $v_size)
        $v_maximum_size = $v_size;
      @fseek($this->zip_fd, $v_size-$v_maximum_size);
      if (@ftell($this->zip_fd) != ($v_size-$v_maximum_size))
      {
        // ----- Error log
        PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT, 'Unable to seek back to the middle of the archive \''.$this->zipname.'\'');

        // ----- Return
        return PclZip::errorCode();
      }

      // ----- Read byte per byte in order to find the signature
      $v_pos = ftell($this->zip_fd);
      $v_bytes = 0x00000000;
      while ($v_pos < $v_size)
      {
        // ----- Read a byte
        $v_byte = @fread($this->zip_fd, 1);

        // -----  Add the byte
        //$v_bytes = ($v_bytes << 8) | Ord($v_byte);
        // Note we mask the old value down such that once shifted we can never end up with more than a 32bit number 
        // Otherwise on systems where we have 64bit integers the check below for the magic number will fail. 
        $v_bytes = ( ($v_bytes & 0xFFFFFF) << 8) | Ord($v_byte); 

        // ----- Compare the bytes
        if ($v_bytes == 0x504b0506)
        {
          $v_pos++;
          break;
        }

        $v_pos++;
      }

      // ----- Look if not found end of central dir
      if ($v_pos == $v_size)
      {

        // ----- Error log
        PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT, "Unable to find End of Central Dir Record signature");

        // ----- Return
        return PclZip::errorCode();
      }
    }

    // ----- Read the first 18 bytes of the header
    $v_binary_data = fread($this->zip_fd, 18);

    // ----- Look for invalid block size
    if (strlen($v_binary_data) != 18)
    {

      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT, "Invalid End of Central Dir Record size : ".strlen($v_binary_data));

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Extract the values
    $v_data = unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', $v_binary_data);

    // ----- Check the global size
    if (($v_pos + $v_data['comment_size'] + 18) != $v_size) {

	  // ----- Removed in release 2.2 see readme file
	  // The check of the file size is a little too strict.
	  // Some bugs where found when a zip is encrypted/decrypted with 'crypt'.
	  // While decrypted, zip has training 0 bytes
	  if (0) {
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_BAD_FORMAT,
	                       'The central dir is not at the end of the archive.'
						   .' Some trailing bytes exists after the archive.');

      // ----- Return
      return PclZip::errorCode();
	  }
    }

    // ----- Get comment
    if ($v_data['comment_size'] != 0) {
      $p_central_dir['comment'] = fread($this->zip_fd, $v_data['comment_size']);
    }
    else
      $p_central_dir['comment'] = '';

    $p_central_dir['entries'] = $v_data['entries'];
    $p_central_dir['disk_entries'] = $v_data['disk_entries'];
    $p_central_dir['offset'] = $v_data['offset'];
    $p_central_dir['size'] = $v_data['size'];
    $p_central_dir['disk'] = $v_data['disk'];
    $p_central_dir['disk_start'] = $v_data['disk_start'];

    // TBC
    //for(reset($p_central_dir); $key = key($p_central_dir); next($p_central_dir)) {
    //}

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privDeleteByRule()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privDeleteByRule(&$p_result_list, &$p_options)
  {
    $v_result=1;
    $v_list_detail = array();

    // ----- Open the zip file
    if (($v_result=$this->privOpenFd('rb')) != 1)
    {
      // ----- Return
      return $v_result;
    }

    // ----- Read the central directory informations
    $v_central_dir = array();
    if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1)
    {
      $this->privCloseFd();
      return $v_result;
    }

    // ----- Go to beginning of File
    @rewind($this->zip_fd);

    // ----- Scan all the files
    // ----- Start at beginning of Central Dir
    $v_pos_entry = $v_central_dir['offset'];
    @rewind($this->zip_fd);
    if (@fseek($this->zip_fd, $v_pos_entry))
    {
      // ----- Close the zip file
      $this->privCloseFd();

      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Read each entry
    $v_header_list = array();
    $j_start = 0;
    for ($i=0, $v_nb_extracted=0; $i<$v_central_dir['entries']; $i++)
    {

      // ----- Read the file header
      $v_header_list[$v_nb_extracted] = array();
      if (($v_result = $this->privReadCentralFileHeader($v_header_list[$v_nb_extracted])) != 1)
      {
        // ----- Close the zip file
        $this->privCloseFd();

        return $v_result;
      }


      // ----- Store the index
      $v_header_list[$v_nb_extracted]['index'] = $i;

      // ----- Look for the specific extract rules
      $v_found = false;

      // ----- Look for extract by name rule
      if (   (isset($p_options[PCLZIP_OPT_BY_NAME]))
          && ($p_options[PCLZIP_OPT_BY_NAME] != 0)) {

          // ----- Look if the filename is in the list
          for ($j=0; ($j<sizeof($p_options[PCLZIP_OPT_BY_NAME])) && (!$v_found); $j++) {

              // ----- Look for a directory
              if (substr($p_options[PCLZIP_OPT_BY_NAME][$j], -1) == "/") {

                  // ----- Look if the directory is in the filename path
                  if (   (strlen($v_header_list[$v_nb_extracted]['stored_filename']) > strlen($p_options[PCLZIP_OPT_BY_NAME][$j]))
                      && (substr($v_header_list[$v_nb_extracted]['stored_filename'], 0, strlen($p_options[PCLZIP_OPT_BY_NAME][$j])) == $p_options[PCLZIP_OPT_BY_NAME][$j])) {
                      $v_found = true;
                  }
                  elseif (   (($v_header_list[$v_nb_extracted]['external']&0x00000010)==0x00000010) /* Indicates a folder */
                          && ($v_header_list[$v_nb_extracted]['stored_filename'].'/' == $p_options[PCLZIP_OPT_BY_NAME][$j])) {
                      $v_found = true;
                  }
              }
              // ----- Look for a filename
              elseif ($v_header_list[$v_nb_extracted]['stored_filename'] == $p_options[PCLZIP_OPT_BY_NAME][$j]) {
                  $v_found = true;
              }
          }
      }

      // ----- Look for extract by ereg rule
      // ereg() is deprecated with PHP 5.3
      /*
      else if (   (isset($p_options[PCLZIP_OPT_BY_EREG]))
               && ($p_options[PCLZIP_OPT_BY_EREG] != "")) {

          if (ereg($p_options[PCLZIP_OPT_BY_EREG], $v_header_list[$v_nb_extracted]['stored_filename'])) {
              $v_found = true;
          }
      }
      */

      // ----- Look for extract by preg rule
      else if (   (isset($p_options[PCLZIP_OPT_BY_PREG]))
               && ($p_options[PCLZIP_OPT_BY_PREG] != "")) {

          if (preg_match($p_options[PCLZIP_OPT_BY_PREG], $v_header_list[$v_nb_extracted]['stored_filename'])) {
              $v_found = true;
          }
      }

      // ----- Look for extract by index rule
      else if (   (isset($p_options[PCLZIP_OPT_BY_INDEX]))
               && ($p_options[PCLZIP_OPT_BY_INDEX] != 0)) {

          // ----- Look if the index is in the list
          for ($j=$j_start; ($j<sizeof($p_options[PCLZIP_OPT_BY_INDEX])) && (!$v_found); $j++) {

              if (($i>=$p_options[PCLZIP_OPT_BY_INDEX][$j]['start']) && ($i<=$p_options[PCLZIP_OPT_BY_INDEX][$j]['end'])) {
                  $v_found = true;
              }
              if ($i>=$p_options[PCLZIP_OPT_BY_INDEX][$j]['end']) {
                  $j_start = $j+1;
              }

              if ($p_options[PCLZIP_OPT_BY_INDEX][$j]['start']>$i) {
                  break;
              }
          }
      }
      else {
      	$v_found = true;
      }

      // ----- Look for deletion
      if ($v_found)
      {
        unset($v_header_list[$v_nb_extracted]);
      }
      else
      {
        $v_nb_extracted++;
      }
    }

    // ----- Look if something need to be deleted
    if ($v_nb_extracted > 0) {

        // ----- Creates a temporay file
        $v_zip_temp_name = PCLZIP_TEMPORARY_DIR.uniqid('pclzip-').'.tmp';

        // ----- Creates a temporary zip archive
        $v_temp_zip = new PclZip($v_zip_temp_name);

        // ----- Open the temporary zip file in write mode
        if (($v_result = $v_temp_zip->privOpenFd('wb')) != 1) {
            $this->privCloseFd();

            // ----- Return
            return $v_result;
        }

        // ----- Look which file need to be kept
        for ($i=0; $i<sizeof($v_header_list); $i++) {

            // ----- Calculate the position of the header
            @rewind($this->zip_fd);
            if (@fseek($this->zip_fd,  $v_header_list[$i]['offset'])) {
                // ----- Close the zip file
                $this->privCloseFd();
                $v_temp_zip->privCloseFd();
                @unlink($v_zip_temp_name);

                // ----- Error log
                PclZip::privErrorLog(PCLZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');

                // ----- Return
                return PclZip::errorCode();
            }

            // ----- Read the file header
            $v_local_header = array();
            if (($v_result = $this->privReadFileHeader($v_local_header)) != 1) {
                // ----- Close the zip file
                $this->privCloseFd();
                $v_temp_zip->privCloseFd();
                @unlink($v_zip_temp_name);

                // ----- Return
                return $v_result;
            }
            
            // ----- Check that local file header is same as central file header
            if ($this->privCheckFileHeaders($v_local_header,
			                                $v_header_list[$i]) != 1) {
                // TBC
            }
            unset($v_local_header);

            // ----- Write the file header
            if (($v_result = $v_temp_zip->privWriteFileHeader($v_header_list[$i])) != 1) {
                // ----- Close the zip file
                $this->privCloseFd();
                $v_temp_zip->privCloseFd();
                @unlink($v_zip_temp_name);

                // ----- Return
                return $v_result;
            }

            // ----- Read/write the data block
            if (($v_result = PclZipUtilCopyBlock($this->zip_fd, $v_temp_zip->zip_fd, $v_header_list[$i]['compressed_size'])) != 1) {
                // ----- Close the zip file
                $this->privCloseFd();
                $v_temp_zip->privCloseFd();
                @unlink($v_zip_temp_name);

                // ----- Return
                return $v_result;
            }
        }

        // ----- Store the offset of the central dir
        $v_offset = @ftell($v_temp_zip->zip_fd);

        // ----- Re-Create the Central Dir files header
        for ($i=0; $i<sizeof($v_header_list); $i++) {
            // ----- Create the file header
            if (($v_result = $v_temp_zip->privWriteCentralFileHeader($v_header_list[$i])) != 1) {
                $v_temp_zip->privCloseFd();
                $this->privCloseFd();
                @unlink($v_zip_temp_name);

                // ----- Return
                return $v_result;
            }

            // ----- Transform the header to a 'usable' info
            $v_temp_zip->privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
        }


        // ----- Zip file comment
        $v_comment = '';
        if (isset($p_options[PCLZIP_OPT_COMMENT])) {
          $v_comment = $p_options[PCLZIP_OPT_COMMENT];
        }

        // ----- Calculate the size of the central header
        $v_size = @ftell($v_temp_zip->zip_fd)-$v_offset;

        // ----- Create the central dir footer
        if (($v_result = $v_temp_zip->privWriteCentralHeader(sizeof($v_header_list), $v_size, $v_offset, $v_comment)) != 1) {
            // ----- Reset the file list
            unset($v_header_list);
            $v_temp_zip->privCloseFd();
            $this->privCloseFd();
            @unlink($v_zip_temp_name);

            // ----- Return
            return $v_result;
        }

        // ----- Close
        $v_temp_zip->privCloseFd();
        $this->privCloseFd();

        // ----- Delete the zip file
        // TBC : I should test the result ...
        @unlink($this->zipname);

        // ----- Rename the temporary file
        // TBC : I should test the result ...
        //@rename($v_zip_temp_name, $this->zipname);
        PclZipUtilRename($v_zip_temp_name, $this->zipname);
    
        // ----- Destroy the temporary archive
        unset($v_temp_zip);
    }
    
    // ----- Remove every files : reset the file
    else if ($v_central_dir['entries'] != 0) {
        $this->privCloseFd();

        if (($v_result = $this->privOpenFd('wb')) != 1) {
          return $v_result;
        }

        if (($v_result = $this->privWriteCentralHeader(0, 0, 0, '')) != 1) {
          return $v_result;
        }

        $this->privCloseFd();
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privDirCheck()
  // Description :
  //   Check if a directory exists, if not it creates it and all the parents directory
  //   which may be useful.
  // Parameters :
  //   $p_dir : Directory path to check.
  // Return Values :
  //    1 : OK
  //   -1 : Unable to create directory
  // --------------------------------------------------------------------------------
  function privDirCheck($p_dir, $p_is_dir=false)
  {
    $v_result = 1;


    // ----- Remove the final '/'
    if (($p_is_dir) && (substr($p_dir, -1)=='/'))
    {
      $p_dir = substr($p_dir, 0, strlen($p_dir)-1);
    }

    // ----- Check the directory availability
    if ((is_dir($p_dir)) || ($p_dir == ""))
    {
      return 1;
    }

    // ----- Extract parent directory
    $p_parent_dir = dirname($p_dir);

    // ----- Just a check
    if ($p_parent_dir != $p_dir)
    {
      // ----- Look for parent directory
      if ($p_parent_dir != "")
      {
        if (($v_result = $this->privDirCheck($p_parent_dir)) != 1)
        {
          return $v_result;
        }
      }
    }

    // ----- Create the directory
    if (!@mkdir($p_dir, 0777))
    {
      // ----- Error log
      PclZip::privErrorLog(PCLZIP_ERR_DIR_CREATE_FAIL, "Unable to create directory '$p_dir'");

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privMerge()
  // Description :
  //   If $p_archive_to_add does not exist, the function exit with a success result.
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privMerge(&$p_archive_to_add)
  {
    $v_result=1;

    // ----- Look if the archive_to_add exists
    if (!is_file($p_archive_to_add->zipname))
    {

      // ----- Nothing to merge, so merge is a success
      $v_result = 1;

      // ----- Return
      return $v_result;
    }

    // ----- Look if the archive exists
    if (!is_file($this->zipname))
    {

      // ----- Do a duplicate
      $v_result = $this->privDuplicate($p_archive_to_add->zipname);

      // ----- Return
      return $v_result;
    }

    // ----- Open the zip file
    if (($v_result=$this->privOpenFd('rb')) != 1)
    {
      // ----- Return
      return $v_result;
    }

    // ----- Read the central directory informations
    $v_central_dir = array();
    if (($v_result = $this->privReadEndCentralDir($v_central_dir)) != 1)
    {
      $this->privCloseFd();
      return $v_result;
    }

    // ----- Go to beginning of File
    @rewind($this->zip_fd);

    // ----- Open the archive_to_add file
    if (($v_result=$p_archive_to_add->privOpenFd('rb')) != 1)
    {
      $this->privCloseFd();

      // ----- Return
      return $v_result;
    }

    // ----- Read the central directory informations
    $v_central_dir_to_add = array();
    if (($v_result = $p_archive_to_add->privReadEndCentralDir($v_central_dir_to_add)) != 1)
    {
      $this->privCloseFd();
      $p_archive_to_add->privCloseFd();

      return $v_result;
    }

    // ----- Go to beginning of File
    @rewind($p_archive_to_add->zip_fd);

    // ----- Creates a temporay file
    $v_zip_temp_name = PCLZIP_TEMPORARY_DIR.uniqid('pclzip-').'.tmp';

    // ----- Open the temporary file in write mode
    if (($v_zip_temp_fd = @fopen($v_zip_temp_name, 'wb')) == 0)
    {
      $this->privCloseFd();
      $p_archive_to_add->privCloseFd();

      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open temporary file \''.$v_zip_temp_name.'\' in binary write mode');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Copy the files from the archive to the temporary file
    // TBC : Here I should better append the file and go back to erase the central dir
    $v_size = $v_central_dir['offset'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($this->zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Copy the files from the archive_to_add into the temporary file
    $v_size = $v_central_dir_to_add['offset'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($p_archive_to_add->zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Store the offset of the central dir
    $v_offset = @ftell($v_zip_temp_fd);

    // ----- Copy the block of file headers from the old archive
    $v_size = $v_central_dir['size'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($this->zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Copy the block of file headers from the archive_to_add
    $v_size = $v_central_dir_to_add['size'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($p_archive_to_add->zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Merge the file comments
    $v_comment = $v_central_dir['comment'].' '.$v_central_dir_to_add['comment'];

    // ----- Calculate the size of the (new) central header
    $v_size = @ftell($v_zip_temp_fd)-$v_offset;

    // ----- Swap the file descriptor
    // Here is a trick : I swap the temporary fd with the zip fd, in order to use
    // the following methods on the temporary fil and not the real archive fd
    $v_swap = $this->zip_fd;
    $this->zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;

    // ----- Create the central dir footer
    if (($v_result = $this->privWriteCentralHeader($v_central_dir['entries']+$v_central_dir_to_add['entries'], $v_size, $v_offset, $v_comment)) != 1)
    {
      $this->privCloseFd();
      $p_archive_to_add->privCloseFd();
      @fclose($v_zip_temp_fd);
      $this->zip_fd = null;

      // ----- Reset the file list
      unset($v_header_list);

      // ----- Return
      return $v_result;
    }

    // ----- Swap back the file descriptor
    $v_swap = $this->zip_fd;
    $this->zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;

    // ----- Close
    $this->privCloseFd();
    $p_archive_to_add->privCloseFd();

    // ----- Close the temporary file
    @fclose($v_zip_temp_fd);

    // ----- Delete the zip file
    // TBC : I should test the result ...
    @unlink($this->zipname);

    // ----- Rename the temporary file
    // TBC : I should test the result ...
    //@rename($v_zip_temp_name, $this->zipname);
    PclZipUtilRename($v_zip_temp_name, $this->zipname);

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privDuplicate()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privDuplicate($p_archive_filename)
  {
    $v_result=1;

    // ----- Look if the $p_archive_filename exists
    if (!is_file($p_archive_filename))
    {

      // ----- Nothing to duplicate, so duplicate is a success.
      $v_result = 1;

      // ----- Return
      return $v_result;
    }

    // ----- Open the zip file
    if (($v_result=$this->privOpenFd('wb')) != 1)
    {
      // ----- Return
      return $v_result;
    }

    // ----- Open the temporary file in write mode
    if (($v_zip_temp_fd = @fopen($p_archive_filename, 'rb')) == 0)
    {
      $this->privCloseFd();

      PclZip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open archive file \''.$p_archive_filename.'\' in binary write mode');

      // ----- Return
      return PclZip::errorCode();
    }

    // ----- Copy the files from the archive to the temporary file
    // TBC : Here I should better append the file and go back to erase the central dir
    $v_size = filesize($p_archive_filename);
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < PCLZIP_READ_BLOCK_SIZE ? $v_size : PCLZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($v_zip_temp_fd, $v_read_size);
      @fwrite($this->zip_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Close
    $this->privCloseFd();

    // ----- Close the temporary file
    @fclose($v_zip_temp_fd);

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privErrorLog()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function privErrorLog($p_error_code=0, $p_error_string='')
  {
    if (PCLZIP_ERROR_EXTERNAL == 1) {
      PclError($p_error_code, $p_error_string);
    }
    else {
      $this->error_code = $p_error_code;
      $this->error_string = $p_error_string;
    }
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privErrorReset()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function privErrorReset()
  {
    if (PCLZIP_ERROR_EXTERNAL == 1) {
      PclErrorReset();
    }
    else {
      $this->error_code = 0;
      $this->error_string = '';
    }
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privDisableMagicQuotes()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privDisableMagicQuotes()
  {
    $v_result=1;

    // ----- Look if function exists
    if (   (!function_exists("get_magic_quotes_runtime"))
	    || (!function_exists("set_magic_quotes_runtime"))) {
      return $v_result;
	}

    // ----- Look if already done
    if ($this->magic_quotes_status != -1) {
      return $v_result;
	}

	// ----- Get and memorize the magic_quote value
	$this->magic_quotes_status = @get_magic_quotes_runtime();

	// ----- Disable magic_quotes
	if ($this->magic_quotes_status == 1) {
	  @set_magic_quotes_runtime(0);
	}

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : privSwapBackMagicQuotes()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function privSwapBackMagicQuotes()
  {
    $v_result=1;

    // ----- Look if function exists
    if (   (!function_exists("get_magic_quotes_runtime"))
	    || (!function_exists("set_magic_quotes_runtime"))) {
      return $v_result;
	}

    // ----- Look if something to do
    if ($this->magic_quotes_status != -1) {
      return $v_result;
	}

	// ----- Swap back magic_quotes
	if ($this->magic_quotes_status == 1) {
  	  @set_magic_quotes_runtime($this->magic_quotes_status);
	}

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  }
  // End of class
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclZipUtilPathReduction()
  // Description :
  // Parameters :
  // Return Values :
  // --------------------------------------------------------------------------------
  function PclZipUtilPathReduction($p_dir)
  {
    $v_result = "";

    // ----- Look for not empty path
    if ($p_dir != "") {
      // ----- Explode path by directory names
      $v_list = explode("/", $p_dir);

      // ----- Study directories from last to first
      $v_skip = 0;
      for ($i=sizeof($v_list)-1; $i>=0; $i--) {
        // ----- Look for current path
        if ($v_list[$i] == ".") {
          // ----- Ignore this directory
          // Should be the first $i=0, but no check is done
        }
        else if ($v_list[$i] == "..") {
		  $v_skip++;
        }
        else if ($v_list[$i] == "") {
		  // ----- First '/' i.e. root slash
		  if ($i == 0) {
            $v_result = "/".$v_result;
		    if ($v_skip > 0) {
		        // ----- It is an invalid path, so the path is not modified
		        // TBC
		        $v_result = $p_dir;
                $v_skip = 0;
		    }
		  }
		  // ----- Last '/' i.e. indicates a directory
		  else if ($i == (sizeof($v_list)-1)) {
            $v_result = $v_list[$i];
		  }
		  // ----- Double '/' inside the path
		  else {
            // ----- Ignore only the double '//' in path,
            // but not the first and last '/'
		  }
        }
        else {
		  // ----- Look for item to skip
		  if ($v_skip > 0) {
		    $v_skip--;
		  }
		  else {
            $v_result = $v_list[$i].($i!=(sizeof($v_list)-1)?"/".$v_result:"");
		  }
        }
      }
      
      // ----- Look for skip
      if ($v_skip > 0) {
        while ($v_skip > 0) {
            $v_result = '../'.$v_result;
            $v_skip--;
        }
      }
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclZipUtilPathInclusion()
  // Description :
  //   This function indicates if the path $p_path is under the $p_dir tree. Or,
  //   said in an other way, if the file or sub-dir $p_path is inside the dir
  //   $p_dir.
  //   The function indicates also if the path is exactly the same as the dir.
  //   This function supports path with duplicated '/' like '//', but does not
  //   support '.' or '..' statements.
  // Parameters :
  // Return Values :
  //   0 if $p_path is not inside directory $p_dir
  //   1 if $p_path is inside directory $p_dir
  //   2 if $p_path is exactly the same as $p_dir
  // --------------------------------------------------------------------------------
  function PclZipUtilPathInclusion($p_dir, $p_path)
  {
    $v_result = 1;
    
    // ----- Look for path beginning by ./
    if (   ($p_dir == '.')
        || ((strlen($p_dir) >=2) && (substr($p_dir, 0, 2) == './'))) {
      $p_dir = PclZipUtilTranslateWinPath(getcwd(), FALSE).'/'.substr($p_dir, 1);
    }
    if (   ($p_path == '.')
        || ((strlen($p_path) >=2) && (substr($p_path, 0, 2) == './'))) {
      $p_path = PclZipUtilTranslateWinPath(getcwd(), FALSE).'/'.substr($p_path, 1);
    }

    // ----- Explode dir and path by directory separator
    $v_list_dir = explode("/", $p_dir);
    $v_list_dir_size = sizeof($v_list_dir);
    $v_list_path = explode("/", $p_path);
    $v_list_path_size = sizeof($v_list_path);

    // ----- Study directories paths
    $i = 0;
    $j = 0;
    while (($i < $v_list_dir_size) && ($j < $v_list_path_size) && ($v_result)) {

      // ----- Look for empty dir (path reduction)
      if ($v_list_dir[$i] == '') {
        $i++;
        continue;
      }
      if ($v_list_path[$j] == '') {
        $j++;
        continue;
      }

      // ----- Compare the items
      if (($v_list_dir[$i] != $v_list_path[$j]) && ($v_list_dir[$i] != '') && ( $v_list_path[$j] != ''))  {
        $v_result = 0;
      }

      // ----- Next items
      $i++;
      $j++;
    }

    // ----- Look if everything seems to be the same
    if ($v_result) {
      // ----- Skip all the empty items
      while (($j < $v_list_path_size) && ($v_list_path[$j] == '')) $j++;
      while (($i < $v_list_dir_size) && ($v_list_dir[$i] == '')) $i++;

      if (($i >= $v_list_dir_size) && ($j >= $v_list_path_size)) {
        // ----- There are exactly the same
        $v_result = 2;
      }
      else if ($i < $v_list_dir_size) {
        // ----- The path is shorter than the dir
        $v_result = 0;
      }
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclZipUtilCopyBlock()
  // Description :
  // Parameters :
  //   $p_mode : read/write compression mode
  //             0 : src & dest normal
  //             1 : src gzip, dest normal
  //             2 : src normal, dest gzip
  //             3 : src & dest gzip
  // Return Values :
  // --------------------------------------------------------------------------------
  function PclZipUtilCopyBlock($p_src, $p_dest, $p_size, $p_mode=0)
  {
    $v_result = 1;

    if ($p_mode==0)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < PCLZIP_READ_BLOCK_SIZE ? $p_size : PCLZIP_READ_BLOCK_SIZE);
        $v_buffer = @fread($p_src, $v_read_size);
        @fwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }
    else if ($p_mode==1)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < PCLZIP_READ_BLOCK_SIZE ? $p_size : PCLZIP_READ_BLOCK_SIZE);
        $v_buffer = @gzread($p_src, $v_read_size);
        @fwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }
    else if ($p_mode==2)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < PCLZIP_READ_BLOCK_SIZE ? $p_size : PCLZIP_READ_BLOCK_SIZE);
        $v_buffer = @fread($p_src, $v_read_size);
        @gzwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }
    else if ($p_mode==3)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < PCLZIP_READ_BLOCK_SIZE ? $p_size : PCLZIP_READ_BLOCK_SIZE);
        $v_buffer = @gzread($p_src, $v_read_size);
        @gzwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclZipUtilRename()
  // Description :
  //   This function tries to do a simple rename() function. If it fails, it
  //   tries to copy the $p_src file in a new $p_dest file and then unlink the
  //   first one.
  // Parameters :
  //   $p_src : Old filename
  //   $p_dest : New filename
  // Return Values :
  //   1 on success, 0 on failure.
  // --------------------------------------------------------------------------------
  function PclZipUtilRename($p_src, $p_dest)
  {
    $v_result = 1;

    // ----- Try to rename the files
    if (!@rename($p_src, $p_dest)) {

      // ----- Try to copy & unlink the src
      if (!@copy($p_src, $p_dest)) {
        $v_result = 0;
      }
      else if (!@unlink($p_src)) {
        $v_result = 0;
      }
    }

    // ----- Return
    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclZipUtilOptionText()
  // Description :
  //   Translate option value in text. Mainly for debug purpose.
  // Parameters :
  //   $p_option : the option value.
  // Return Values :
  //   The option text value.
  // --------------------------------------------------------------------------------
  function PclZipUtilOptionText($p_option)
  {
    
    $v_list = get_defined_constants();
    for (reset($v_list); $v_key = key($v_list); next($v_list)) {
	    $v_prefix = substr($v_key, 0, 10);
	    if ((   ($v_prefix == 'PCLZIP_OPT')
           || ($v_prefix == 'PCLZIP_CB_')
           || ($v_prefix == 'PCLZIP_ATT'))
	        && ($v_list[$v_key] == $p_option)) {
        return $v_key;
	    }
    }
    
    $v_result = 'Unknown';

    return $v_result;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclZipUtilTranslateWinPath()
  // Description :
  //   Translate windows path by replacing '\' by '/' and optionally removing
  //   drive letter.
  // Parameters :
  //   $p_path : path to translate.
  //   $p_remove_disk_letter : true | false
  // Return Values :
  //   The path translated.
  // --------------------------------------------------------------------------------
  function PclZipUtilTranslateWinPath($p_path, $p_remove_disk_letter=true)
  {
    if (stristr(php_uname(), 'windows')) {
      // ----- Look for potential disk letter
      if (($p_remove_disk_letter) && (($v_position = strpos($p_path, ':')) != false)) {
          $p_path = substr($p_path, $v_position+1);
      }
      // ----- Change potential windows directory separator
      if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0,1) == '\\')) {
          $p_path = strtr($p_path, '\\', '/');
      }
    }
    return $p_path;
  }
  // --------------------------------------------------------------------------------
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
 // TODO added this curly brace to end the pclzip class? is this needed? I just added it atcleanup!
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
class xorcrypt {
	private $password = NULL;

	public function set_key($password) {
		$this->password = $password;
	}

	private function get_rnd_iv($iv_len) {
		$iv = '';
		while ($iv_len-- > 0) {
			$iv .= chr(mt_rand() & 0xff);
		}
		return $iv;
	}

	public function encrypt($plain_text, $iv_len = 16) {
		$plain_text .= "\x13";
		$n = strlen($plain_text);
		if ($n % 16) {
			$plain_text .= str_repeat("\0", 16 - ($n % 16));
			$i = 0;
			$enc_text = $this->get_rnd_iv($iv_len);
			$iv = substr($this->password ^ $enc_text, 0, 512);
			while ($i < $n) {
				$block = substr($plain_text, $i, 16) ^ pack('H*', sha1($iv));
				$enc_text .= $block;
				$iv = substr($block . $iv, 0, 512) ^ $this->password;
				$i += 16;
			}
			return base64_encode($enc_text);
		} else {}
	}

	public function decrypt($enc_text, $iv_len = 16) {
		$enc_text = base64_decode($enc_text);
		$n = strlen($enc_text);
		$i = $iv_len;
		$plain_text = '';
		$iv = substr($this->password ^ substr($enc_text, 0, $iv_len), 0, 512);
		while ($i < $n) {
			$block = substr($enc_text, $i, 16);
			$plain_text .= $block ^ pack('H*', sha1($iv));
			$iv = substr($block . $iv, 0, 512) ^ $this->password;
			$i += 16;
		}
		return stripslashes(preg_replace('/\\x13\\x00*$/', '', $plain_text));
	}
}

/* Example:
$xorCrypt = new xorcrypt();
$xorCrypt->set_key("Your Secret Key");

$text = "hello world!";
$encrypted = $xorCrypt->encrypt($text);

echo $encrypted;
echo "<br>";
echo $xorCrypt->decrypt($encrypted);
*/
?>