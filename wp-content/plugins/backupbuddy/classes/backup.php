<?php
/**
 *	pluginbuddy_backupbuddy_backup Class
 *
 *	Handles all preparation and steps for the actual backup processes.
 *	
 *	Version: 2.0.0
 *	Author: Dustin Bolton
 *	Author URI: http://dustinbolton.com/
 *
 *	&$parent		reference	Reference to the parent class.
 *
 */

if ( !class_exists( "pluginbuddy_backupbuddy_backup" ) ) {
	class pluginbuddy_backupbuddy_backup {
		var $_errors = array();
		var $_status_logging_started = false; // Marked true once anything has been status logged during this process. Used by status().
		
		function pluginbuddy_backupbuddy_backup( &$parent ) {
			$this->_parent = &$parent;
			$this->_var = &$parent->_var;
			$this->_name = &$parent->_name;
			$this->_options = &$parent->_options;
			$this->_pluginPath = &$parent->_pluginPath;
			$this->_pluginURL = &$parent->_pluginURL;
			$this->_selfLink = &$parent->_selfLink;
			
			// Load options if they have not been loaded yet...
			if ( empty( $this->_options ) ) {
				$this->_parent->load();
			}
			
			// Load zipbuddy object if it has not been initialized yet...
			if ( !isset( $this->_parent->_zipbuddy ) ) {
				require_once( $this->_pluginPath . '/lib/zipbuddy/zipbuddy.php' );
				$this->_parent->_zipbuddy = new pluginbuddy_zipbuddy( $this->_options['backup_directory'] );
			}
		}
		
		
		function log() {
			$args = func_get_args();
			call_user_func_array( array( $this->_parent, 'log' ) , $args );
		}
		
		
		// $pre_backup	array		FUTURE: Array of function processes to run prior to backing up.
		// $post_backup	array		Array of function processes to run after backing up processes complete.
		// $trigger		string		manual for manual backup, scheduled for schedule-triggered backup.
		function start_backup_process( $type, $trigger = 'manual', $pre_backup = array(), $post_backup = array(), $schedule_title = '', $options = array() ) {
			$this->status( 'details', __( 'Starting backup process function.', 'it-l10n-backupbuddy' ) );
			$this->status( 'details', __('Peak memory usage', 'it-l10n-backupbuddy') . ': ' . round( memory_get_peak_usage() / 1048576, 3 ) . ' MB' );
			
			//RON
			$this->_options = wp_parse_args( $options, $this->_options );
			// Prepare backup directory (need this up high for status logging)
			if ( !file_exists( $this->_options['backup_directory'] ) ) {
				if ( $this->_parent->mkdir_recursive( $this->_options['backup_directory'] ) === false ) {
					$this->error( __('Unable to create backup storage directory', 'it-l10n-backupbuddy') . ' (' . $this->_options['backup_directory'] . ')', '9002' );
					return false;
				}
			}
			if ( !is_writable( $this->_options['backup_directory'] ) ) {
				$this->error( __('Archive directory is not writable. Check your permissions.', 'it-l10n-backupbuddy') . ' (' . $this->_options['backup_directory'] . ')', '9016' );
				return false;
			}
			
			
			if ( $this->pre_backup( $type, $trigger, $pre_backup, $post_backup, $schedule_title ) === false ) {
				return false;
			}
			
			if ( ( $this->_options['backup_mode'] == '2' )  || ( $trigger == 'scheduled' ) ) {
				$this->status( 'message', 'Running in modern backup mode based on settings.' );
				
				// If using alternate cron on a manually triggered backup then skip running the cron on this pageload to avoid header already sent warnings.
				if ( ( $trigger == 'manual' ) && defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON ) {
					$this->cron_next_step( false );
				} else {
					$this->cron_next_step( true );
				}
			} else { // classic mode
				$this->status( 'message', 'Running in classic backup mode based on settings.' );
				
				$this->process_backup( $this->_backup['serial'], $trigger );
			}
			
			return true;
		}
		
		
		// Calls cron to run the next step of the backup NOW.
		function cron_next_step( $spawn_cron = true ) {
			$this->status( 'details', __('Scheduling Cron', 'it-l10n-backupbuddy') );
			$this->log( 'Scheduling Cron for ' . $this->_backup['serial'] . '.' );
			
			// Check to see that the database is still around.
			// We have the option of kicking it to get it going again but I'm not sure that we want to do that here since we may have undetected failured in the backup at this point.
			// We should probably kick the DB earlier on a case by case basis to make sure we are safe.
			global $wpdb;
			if ( @mysql_ping( $wpdb->dbh ) === false ) { // Still connected to database.
				$this->status( 'error', __( 'ERROR #9027: The mySQL server went away and was unavailable for scheduling the next cron step. This is almost always caused by mySQL running out of memory. The backup integrity can no longer be guaranteed so the backup has been halted.' ) );
				return false;
			}
			
			wp_schedule_single_event( time(), $this->_parent->_var . '-cron_process_backup', array( $this->_backup['serial'] ) );
			if ( $spawn_cron === true ) {
				spawn_cron( time() + 150 ); // Adds > 60 seconds to get around once per minute cron running limit.
			}
			update_option( '_transient_doing_cron', 0 ); // Prevent cron-blocking for next item.
		}
		
		
		function process_backup( $serial, $trigger = 'manual' ) {
			$this->_backup = &$this->_options['backups'][$serial];
			if ( !isset( $this->_backup['steps'] ) ) { return; } // No more steps to perform; return.
			
			$this->status( 'details', __('Setting greedy script limits.', 'it-l10n-backupbuddy') );
			$this->_parent->set_greedy_script_limits( $this );
			$this->status( 'details', __('Finished greedy script limits.', 'it-l10n-backupbuddy') );
			
			$this_process = array_shift( $this->_backup['steps'] );
			if ( count( $this->_backup['steps'] ) == 0 ) { unset( $this->_backup['steps'] ); } // No steps left; remove steps array.
			$this->_parent->save(); // Save shifted steps array.
			
			$this->status( 'details', __('Peak memory usage', 'it-l10n-backupbuddy') . ': ' . round( memory_get_peak_usage() / 1048576, 3 ) . ' MB' );
			$this->status( 'details', __('Starting function', 'it-l10n-backupbuddy'), ' `' . $this_process['function'] . '`.' );
			if ( method_exists( $this, $this_process['function'] ) ) {
				$response = call_user_func_array( array( &$this, $this_process['function'] ), $this_process['args'] );
			} else {
				$this->status( 'error', __( 'Error #82783745: Invalid function `' . $this_process['function'] . '`' ) );
				$response = false;
			}
			if ( $response === false ) {
				$this->status( 'error', sprintf( __('Failed function %s. Backup terminated.', 'it-l10n-backupbuddy'), $this_process['function']));
				
				$this->status( 'details', __('Peak memory usage', 'it-l10n-backupbuddy') . ': ' . round( memory_get_peak_usage() / 1048576, 3 ) . ' MB' );
				
				$this->status( 'action', 'halt_script' ); // Halt JS on page.
				
				if ( $this->_options['log_level'] == '3' ) {
					$debugging = "\n\n\n\n\n\nDebugging information sent due to error logging set to high debugging mode: \n\n" . $this->rand_string( 10 ) . base64_encode( print_r( debug_backtrace(), true ) ) . "\n\n";
				} else {
					$debugging = '';
				}
				$this->_parent->mail_error( 'One or more backup steps reported a failure. Backup failure running function `' . $this_process['function'] . '` with the arguments `' . implode( ',', $this_process['args'] ) . '` with backup serial `' . $serial . '`. Please run a manual backup of the same type to verify backups are working properly.' . $debugging );
			} else {
				$this->status( 'details', sprintf( __('Finished function `%s`.', 'it-l10n-backupbuddy'), $this_process['function'] ) );
				$this->status( 'details', __('Peak memory usage', 'it-l10n-backupbuddy') . ': ' . round( memory_get_peak_usage() / 1048576, 3 ) . ' MB' );
			
				if ( count( $this->_backup['steps'] ) > 0 ) { // Only schedule next step if more steps exist.
					if ( ( $this->_options['backup_mode'] == '2' )  || ( $trigger == 'scheduled' ) ) {
						$this->cron_next_step();
					} else { // classic mode
						$this->process_backup( $this->_backup['serial'], $trigger );
					}
				}
			}
		}
		
		
		function pre_backup( $type, $trigger, $pre_backup = array(), $post_backup = array(), $schedule_title ) {
			$this->_options['last_backup'] = time();
			$this->_options['edits_since_last'] = 0;
			
			$serial = $this->rand_string( 10 );
			$this->_backup = &$this->_options['backups'][$serial]; // Set reference.
			$this->_backup['serial'] = $serial; // SET SERIAL BEFORE DOING ANYTHING ELSE OR LOGGING WILL NOT WORK.
			$this->_options['last_backup_serial'] = $this->_backup['serial'];
			$this->_backup['backup_mode'] = $this->_options['backup_mode'];
			
			$this->status( 'details', __('Setting greedy script limits.', 'it-l10n-backupbuddy') );
			$this->_parent->set_greedy_script_limits( $this );
			$this->status( 'details', __('Finished greedy script limits.', 'it-l10n-backupbuddy') );
			
			// Schedule a cleanup of this backup 6 hours from now in case this backup fails. If we get cleaned up before this runs this is okay.
			wp_schedule_single_event( ( time() + ( 6 * 60 * 60 ) ), $this->_parent->_var . '-cron_final_cleanup', array( $this->_backup['serial'] ) );
			
			$this->_backup['type'] = $type;
			$this->_backup['start_time'] = time();
			$this->_backup['updated_time'] = time();
			$this->_backup['status'] = array();
			$this->_backup['schedule_title'] = $schedule_title;
			
			if ( $type == 'full' ) {
				$this->status( 'message', __('Full backup mode.', 'it-l10n-backupbuddy') );
			} elseif ( $type == 'db' ) {
				$this->status( 'message', __('Database only backup mode.', 'it-l10n-backupbuddy') );
			} else {
				$this->status( 'error', __('Unknown backup mode.', 'it-l10n-backupbuddy') );
			}
			$this->status( 'details', __('Performing pre-backup procedures.', 'it-l10n-backupbuddy') );
			
			// function located in backupbuddy.php line 1568
			$siteurl = $this->_parent->backup_prefix();
			
			$this->_backup['backup_directory'] = $this->_options['backup_directory'];
			$this->_backup['archive_file'] = $this->_options['backup_directory'] . 'backup-' . $siteurl . '-' . str_replace( '-', '_', date( 'Y-m-d' ) ) . '-' . $this->_backup['serial'] . '.zip';
			$this->_backup['trigger'] = $trigger;
			
			if ( $this->_options['force_compatibility'] == '1' ) {
				$this->_backup['force_compatibility'] = true;
			} else {
				$this->_backup['force_compatibility'] = false;
			}
			
			
			// ********** BEGIN SETTING UP STEPS ARRAY **********
			
			$this->_backup['steps'] = array(); // Set up steps array.
			
			if ( $this->_options['skip_database_dump'] != '1' ) { // Backup database if not skipping.
				$this->_backup['steps'][] = array(
												'function'	=>	'backup_create_database_dump',
												'args'		=>	array(),
											);
			} else {
				$this->status( 'message', __( 'Skipping database dump based on advanced options.', 'it-l10n-backupbuddy' ) );
			}
			$this->_backup['steps'][] = array(
											'function'	=>	'backup_zip_files',
											'args'		=>	array(),
										);
			$this->_backup['steps'][] = array(
											'function'	=>	'post_backup',
											'args'		=>	array(),
										);
							
			$this->_backup['steps'] = array_merge( $pre_backup, $this->_backup['steps'], $post_backup );
			
			// ********** END SETTING UP STEPS ARRAY **********
			
			
			unset( $siteurl );
			
			$this->anti_directory_browsing( $this->_backup['backup_directory'] );
			
			// Prepare temporary directory for holding SQL and data file.
			$this->_backup['temp_directory'] = ABSPATH . 'wp-content/uploads/backupbuddy_temp/' . $this->_backup['serial'] . '/';
			if ( !file_exists( $this->_backup['temp_directory'] ) ) {
				if ( $this->_parent->mkdir_recursive( $this->_backup['temp_directory'] ) === false ) {
					$this->status( 'details', sprintf(__('Error #9002: Unable to create temporary storage directory (%s).', 'it-l10n-backupbuddy'), $this->_backup['temp_directory']) );
					$this->error( 'Unable to create temporary storage directory (' . $this->_backup['temp_directory'] . ')', '9002' );
					return false;
				}
			}
			if ( !is_writable( $this->_backup['temp_directory'] ) ) {
				$this->status( 'details', sprintf( __('Error #9015: Temp data directory is not writable. Check your permissions. (%s).', 'it-l10n-backupbuddy'), $this->_backup['temp_directory'] ) );
				$this->error( 'Temp data directory is not writable. Check your permissions. (' . $this->_backup['temp_directory'] . ')', '9015' );
				return false;
			}
			$this->anti_directory_browsing( ABSPATH . 'wp-content/uploads/backupbuddy_temp/' );
			
			// Prepare temporary directory for holding ZIP file while it is being generated.
			$this->_backup['temporary_zip_directory'] = $this->_options['backup_directory'] . 'temp_zip_' . $this->_backup['serial'] . '/';
			if ( !file_exists( $this->_backup['temporary_zip_directory'] ) ) {
				if ( $this->_parent->mkdir_recursive( $this->_backup['temporary_zip_directory'] ) === false ) {
					$this->status( 'details', sprintf( __('Error #9002: Unable to create temporary ZIP storage directory (%s).', 'it-l10n-backupbuddy'), $this->_backup['temporary_zip_directory'] ) );
					$this->error( 'Unable to create temporary ZIP storage directory (' . $this->_backup['temporary_zip_directory'] . ')', '9002' );
					return false;
				}
			}
			if ( !is_writable( $this->_backup['temporary_zip_directory'] ) ) {
				$this->status( 'details', sprintf( __('Error #9015: Temp data directory is not writable. Check your permissions. (%s).', 'it-l10n-backupbuddy'), $this->_backup['temporary_zip_directory'] ) );
				$this->error( 'Temp data directory is not writable. Check your permissions. (' . $this->_backup['temporary_zip_directory'] . ')', '9015' );
				return false;
			}
			
			// Generate data file.
			if ( $this->backup_create_dat_file() !== true ) {
				$this->status( 'details', __('Problem creating DAT file.', 'it-l10n-backupbuddy' ) );
				return false;
			}
			
			$this->status( 'details', __('Finished pre-backup procedures.', 'it-l10n-backupbuddy') );
			$this->status( 'action', 'finish_settings' );
			
			// Save $this->_backup into options (it's a reference of course!).
			$this->_parent->save();
			
			return true;
		}
		
		
		/**
		 *	backup_create_dat_file()
		 *
		 *	Generates backupbuddy_dat.php within the temporary directory containing the
		 *	random serial in its name. This file contains a serialized array that has been
		 *	XOR encrypted for security.  The XOR key is backupbuddy_SERIAL where SERIAL
		 *	is the randomized set of characters in the ZIP filename. This file contains
		 *	various information about the source site.
		 *
		 *	@return		null
		 *
		 */
		function backup_create_dat_file( $backup = array(), $options = array(), $skip_status = false ) {
			$this->status( 'details', __('Creating import data file.', 'it-l10n-backupbuddy'), $skip_status );
			global $wpdb, $current_blog;
			if ( !isset( $this->_backup ) ) $this->_backup = array();
			$this->_options = wp_parse_args( $options, $this->_options );
			$this->_backup = wp_parse_args( $backup, $this->_backup );
			$is_multisite = $is_multisite_export = false; //$from_multisite is from a site within a network
			$upload_url_rewrite = $upload_url = '';
			if ( is_multisite() && is_network_admin() ) { // MS Network Export (full network)
				$is_multisite = true;
			} elseif ( is_multisite() ) { // MS Export (individual site)
				$is_multisite_export = true;
				$uploads = wp_upload_dir();
				$upload_url_rewrite = site_url( str_replace( ABSPATH, '', $uploads[ 'basedir' ] ) ); // URL we rewrite uploads to. REAL direct url.
				$upload_url = $uploads[ 'baseurl' ]; // Pretty virtual path to uploads directory.
			}
			
			$dat_content = array(
				// Backup details.
				'backupbuddy_version'		=> $this->_parent->plugin_info( 'version' ),
				'backup_time'				=> isset( $this->_backup['start_time'] ) ? $this->_backup[ 'start_time' ] : '',
				'backup_type'				=> isset( $this->_backup['type'] ) ? $this->_backup[ 'type' ] : '',
				'serial'					=> isset( $this->_backup['serial'] ) ? $this->_backup[ 'serial' ] : '',
				
				// WordPress details.
				'abspath'					=> ABSPATH,
				'siteurl'					=> site_url(),
				'homeurl'					=> home_url(),
				//'home'						=> get_option( 'home' ), Never used.
				'blogname'					=> get_option( 'blogname' ),
				'blogdescription'			=> get_option( 'blogdescription' ),
				
				// Database details. Possibly sensitive values we will be set
				// several lines down if high security mode is NOT enabled.
				'db_prefix'					=> $wpdb->prefix,
				'db_name'					=> '',
				'db_user'					=> '',
				'db_server'					=> '',
				'db_password'				=> '',
				
				/*For multisite */
				'is_multisite' 				=> $is_multisite,
				'is_multisite_export' 		=> $is_multisite_export,
				'domain'					=> is_object( $current_blog ) ? $current_blog->domain : '', // Ex: bob.com
				'path'						=> is_object( $current_blog ) ? $current_blog->path : '', // Ex: /wordpress/
				'upload_url' 				=> $upload_url,  // Pretty URL.
				'upload_url_rewrite' 		=> $upload_url_rewrite, // Real existing URL that the pretty URL will be rewritten to.
			);
			
			
			// If high security mode is enabled we will omit the following database details.
			if ( $this->_options['high_security'] != '1' ) { // High security off.
				$dat_content['db_name'] = DB_NAME;
				$dat_content['db_user'] = DB_USER;
				$dat_content['db_server'] = DB_HOST;
				$dat_content['db_password'] = DB_PASSWORD;
			} else { // High security on.
				// TODO: High security mode dat settings.
				$this->status( 'details', __('High security mode enabled.', 'it-l10n-backupbuddy'), $skip_status);
			}
			
			// If currently using SSL or forcing admin SSL then we will check the hardcoded defined URL to make sure it matches.
			if ( is_ssl() OR ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN == true ) ) {
				$dat_content['siteurl'] = get_option('siteurl');
				$this->status( 'details', __('Compensating for SSL in siteurl.', 'it-l10n-backupbuddy'), $skip_status );
			}
			
			// Serialize .dat file array.
			$dat_content = base64_encode( serialize( $dat_content ) );
			
			
			// Encrypt serialized data with XOR encryption to obscure data.
			
			//require_once( $this->_pluginPath . '/lib/xorcrypt/xorcrypt.php' );
			//$xorcrypt = new xorcrypt();
			//$xorcrypt->set_key( 'backupbuddy_' . $this->_backup['serial'] );
			//$dat_content = $xorcrypt->encrypt( $dat_content );
			
			// Write data to the dat file.
			$dat_file = $this->_backup['temp_directory'] . 'backupbuddy_dat.php';
			if ( false === ( $file_handle = fopen( $dat_file, 'w' ) ) ) {
				$this->status( 'details', sprintf( __('Error #9017: Temp data file is not creatable/writable. Check your permissions. (%s)', 'it-l10n-backupbuddy'), $dat_file  ), $skip_status );
				$this->error( 'Temp data file is not creatable/writable. Check your permissions. (' . $dat_file . ')', '9017' );
				return false;
			}
			fwrite( $file_handle, "<?php die('Access Denied.'); ?>\n" . $dat_content );
			fclose( $file_handle );
			
			$this->status( 'details', __('Finished creating import data file.', 'it-l10n-backupbuddy'), $skip_status );
			
			return true;
		}		
		
		function backup_create_database_dump( $backup = array(), $options = array(), $tables = array(), $skip_status = false ) {
			$this->status( 'action', 'start_database', $skip_status );
			//sleep(7);
			$this->status( 'message', __('Starting database backup process.', 'it-l10n-backupbuddy'), $skip_status );
			
			
			$database_file = $this->_backup['temp_directory'] . 'db_1.sql';
			if ( false === ( $file_handle = fopen( $database_file, 'w' ) ) ) {
				$this->status( 'details', sprintf( __('Error #9018: Database file is not creatable/writable. Check your permissions. (%s)', 'it-l10n-backupbuddy'), $database_file ), $skip_status);
				$this->error( 'Database file is not creatable/writable. Check your permissions. (' . $database_file . ')', '9018' );
				return false;
			}
			
			// --- START: Database prep & setting up list of tables to backup. ---
			
			global $wpdb;
			mysql_connect( DB_HOST, DB_USER, DB_PASSWORD );
			mysql_select_db( DB_NAME );
			

			if ( count( $tables ) <= 0 ) :
				$tables = array();
				$result = mysql_query( 'SHOW TABLES' );
				$this->status( 'message', sprintf( __('Saving database content to file. (%d tables)', 'it-l10n-backupbuddy'), mysql_num_rows( $result ) ), $skip_status );
				while( $row = mysql_fetch_row( $result ) ) {
					array_push( $tables, $row[0] );
				}
				mysql_free_result( $result ); // Free memory.
			
				// Make $tables only include the tables we want to actually back up.
				$this->_parent->log( 'backup_create_database_dump(): Calculating table inclusion/exclusion.' );
				if ( $this->_options['backup_nonwp_tables'] == '1' ) { // All tables minus excluded ones.
					$this->status( 'message', __('Including non-WordPress tables in backup (if found; based on settings).', 'it-l10n-backupbuddy' ), $skip_status );
					foreach( $this->_options['exclude_tables'] as $this_table ) { // Look to exclude any tables.
						if ( false !== ( $table_key = array_search( $this_table, $tables ) ) ) { // Found this table so it needs removed from list.
							unset( $tables[$table_key] );
						}
					}
				} else { // Limit to WordPress prefixed tables plus additionally defined ones.
					$this->status( 'message', __('Only including WordPress tables in backup (unless more explicitly defined).', 'it-l10n-backupbuddy' ), $skip_status );
					$temp_tables = array();
					foreach( $tables as $this_table_key => $this_table ) { // Strip out differently prefixed tables into another list temporarily.
						if ( substr( $this_table, 0, strlen( $wpdb->prefix ) ) != $wpdb->prefix ) { // Does not match so pull it out!
							array_push( $temp_tables, $this_table );
							unset( $tables[$this_table_key] );
						}
					}
					// Now $tables only includes WP prefixed tables. $temp_tables has all the rest. We need to see which ones to keep.
					// Set $temp_tables to be values in $temp_tables that are also in the include list.
					$temp_tables = array_intersect( $this->_options['include_tables'], $temp_tables );
					
					// Put the included tables together with the WP prefixed tables.
					$tables = array_merge( $tables, $temp_tables );
					
					// Cleanup.
					unset( $this_table_key );
					unset( $this_table );
					unset( $temp_tables );
				}
				if ( !empty( $this->_options['exclude_tables'] ) ) {
					$this->status( 'details', __('Additional tables are set to be excluded based on settings.', 'it-l10n-backupbuddy') );
				}
				if ( !empty( $this->_options['include_tables'] ) ) {
					$this->status( 'details', __('Additional tables are set to be included based on settings.', 'it-l10n-backupbuddy') );
				}
				
				// --- END: Database prep & setting up list of tables to backup. ---
				
				//$this->_parent->log( 'backup_create_database_dump(): Beginning database dump.' );
				$this->status( 'details', __('Beginning actual SQL dump.', 'it-l10n-backupbuddy'), $skip_status );
				flush();
			endif; //tables
			
			$_count = 0;
			$insert_sql = '';
			
			global $wpdb; // Used later for checking that we are still connected to DB.
			
			// Iterate through all the tables to backup.
			// TODO: Future ability to break up DB exporting to multiple page loads if needed.
			foreach( $tables as $table_key => $table ) {
				$create_table = mysql_query("SHOW CREATE TABLE `{$table}`");
				
				// Table creation text.
				$create_table_array = mysql_fetch_array( $create_table );
				mysql_free_result( $create_table ); // Free memory.
				$insert_sql .= str_replace( "\n", '', $create_table_array[1] ) . ";\n"; // Remove internal linebreaks; only put one at end.
				unset( $create_table_array );
				
				// Row creation text for all rows within this table.
				$table_query = mysql_query("SELECT * FROM `$table`") or $this->error( 'Unable to read database table ' . $table . '. Your backup will not include data from this table (you may ignore this warning if you do not need this specific data). This is due to the following error: ' . mysql_error(), '9001');
				$num_fields = mysql_num_fields($table_query);
				while ( $fetch_row = mysql_fetch_array( $table_query ) ) {
					$insert_sql .= "INSERT INTO `$table` VALUES(";
					for ( $n=1; $n<=$num_fields; $n++ ) {
						$m = $n - 1;
						$insert_sql .= "'" . mysql_real_escape_string( $fetch_row[$m] ) . "', ";
					}
					$insert_sql = substr( $insert_sql, 0, -2 );
					$insert_sql .= ");\n";
					//RON
					//Do a search/replace for is_multisite() and is_network_admin() and strip /files/ and change to /wp-content/uploads
					if ( is_multisite() && !is_network_admin() ) {
						//Keep track of WWW and strip https or http
						if ( strstr( $table, 'posts' ) ) {
							$site_url = str_replace( 'https', 'http', site_url() );
							$site_url_no_www = str_replace( 'www.', '', $site_url );
							$insert_sql = str_replace( $site_url . '/files', $site_url . '/wp-content/uploads', $insert_sql );
							$insert_sql = str_replace( $site_url_no_www . '/files', $site_url_no_www . '/wp-content/uploads', $insert_sql );
						}
					}
					fwrite( $file_handle, $insert_sql );
					$insert_sql = '';
					
					// Help keep HTTP alive.
					$_count++;
					if ($_count >= 400) {
						if ( $this->_backup['backup_mode'] == '1' ) {
							echo ' ';
						}
						flush();
						$_count = 0;
					}
				} // End foreach $tables.
				
				// testing: mysql_close( $wpdb->dbh );
				// Verify database is still connected and working properly. Sometimes mysql runs out of memory and dies in the above foreach.
				if ( @mysql_ping( $wpdb->dbh ) ) { // Still connected to database.
					mysql_free_result( $table_query ); // Free memory.
				} else { // Database not connected.
					$this->status( 'error', __( 'ERROR #9026: The mySQL server went away unexpectedly during database dump. This is almost always caused by mySQL running out of memory. The backup integrity can no longer be guaranteed so the backup has been halted.' ) . ' ' . __( 'Last table dumped before database server went away: ' ) . '`' . $table . '`.' );
					return false;
				}
				
				// Help keep HTTP alive.
				if ( $this->_backup['backup_mode'] == '1' ) {
					echo ' ';
				}
				flush();
				
				//unset( $tables[$table_key] );
			}
			
			fclose( $file_handle );
			unset( $file_handle );
			
			$this->status( 'details', __('Finished actual SQL dump.', 'it-l10n-backupbuddy'), $skip_status);
			$this->status( 'status', 'database_end', $skip_status);
			$this->status( 'action', 'finish_database', $skip_status );
			
			return true;
		}
		
		
		function backup_zip_files() {
			$this->status( 'action', 'start_files' );
			//sleep(7);
			
			$excludes = trim( $this->_options['excludes'] );
			$excludes = explode( "\n", $excludes );
			
			//Clean up directory exclusions.
			foreach ( $excludes as $exclude_id => $exclude_value ) {
				if ( empty( $exclude_value ) ) {
					unset( $excludes[$exclude_id] );
				}
			} 
			
			// Add backup archive directory to be excluded.
			array_push( $excludes, ltrim( str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_options['backup_directory'] ), ' \\/' ) ); // Exclude backup directory with other archives.
			
			/*
			if ( $this->_options['force_compatibility'] == 1 ) {
				$this->status( 'message', 'Forcing compatibility mode (PCLZip) based on settings. This is slower and less reliable.' );
				$this->_parent->_zipbuddy->set_zip_methods( array( 'pclzip' ) );
			}
			*/
			
			if ( $this->_options['compression'] == '1' ) {
				$compression = true;
			} else {
				$compression = false;
			}
			
			if ( $this->_backup['type'] == 'full' ) {
				$directory_to_add = ABSPATH;
			} elseif ( $this->_backup['type'] == 'db' ) {
				$directory_to_add = $this->_backup['temp_directory'];
			} else {
				$this->status( 'error', __('Backup FAILED. Unknown backup type.', 'it-l10n-backupbuddy') );
				$this->status( 'action', 'halt_script' ); // Halt JS on page.
			}
			
			if ( $this->_options['log_level'] == 3 ) { // If full logging mode then output exec() response to details.
				$quiet_response = false;
			} else {
				$quiet_response = true;
			}
			
			$this->_parent->_zipbuddy->set_status_callback( array( &$this, 'status' ) ); // Set logging callback.
			if ( defined( 'PB_DEMO_MODE' ) ) {
				$this->status( 'message', 'Operating in demonstration mode. Backup file creation will be skipped.' );
			} else {
				if ( $this->_parent->_zipbuddy->add_directory_to_zip( $this->_backup['archive_file'], $directory_to_add, $compression, $excludes, $this->_backup['temporary_zip_directory'], $this->_backup['force_compatibility'], $quiet_response ) === true ) {
					$this->status( 'message', __('Backup ZIP file successfully created.', 'it-l10n-backupbuddy') );
					if ( chmod( $this->_backup['archive_file'], 0644) ) {
						$this->status( 'details', __('Chmod to 0644 succeeded.', 'it-l10n-backupbuddy') );
					} else {
						$this->status( 'details', __('Chmod to 0644 failed.', 'it-l10n-backupbuddy') );
					}
				} else {
					$this->status( 'error', __('Backup FAILED. Unable to successfully generate ZIP archive. Error #3382.', 'it-l10n-backupbuddy' ) );
					$this->status( 'error', __('Error #3382 help: http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#3382', 'it-l10n-backupbuddy' ) );
					$this->status( 'action', 'halt_script' ); // Halt JS on page.
					return false;
				}
			}
			
			// Need to make sure the database connection is active
			// This is not essential so use include and not require (suppress any warning)
			@include_once( $this->_pluginPath . '/lib/wpdbutils/wpdbutils.php' );
			if ( class_exists( 'pluginbuddy_wpdbutils' ) ) {
				// This is the database object we want to use
				global $wpdb;
				
				// Get our helper object and let it use us to output status messages
				$dbhelper = new pluginbuddy_wpdbutils( $wpdb, $this );
				$dbhelper->set_status_callback( array( &$this, 'status' ) );
				
				// If we cannot kick the database into life then signal the error and return false which will stop the backup
				// Otherwise all is ok and we can just fall through and let the function return true
				if ( !$dbhelper->kick() ) {
					$this->status( 'error', __('Backup FAILED. Backup file produced but Database Server has gone away, unable to schedule next backup step', 'it-l10n-backupbuddy' ) );
					return false;
				}
			} else {
				// Utils not available so cannot verify database connection status - just notify
				$this->status( 'details', __('Database Server connection status unverified.', 'it-l10n-backupbuddy' ) );
			}
			
			return true;
		}
		
		function trim_old_archives() {
			$this->status( 'details', __('Trimming old archives (if needed).', 'it-l10n-backupbuddy') );
			
			$summed_size = 0;
			
			$file_list = glob( $this->_options['backup_directory'] . 'backup*.zip' );
			if ( is_array( $file_list ) && !empty( $file_list ) ) {
				foreach( (array) $file_list as $file ) {
					$file_stats = stat( $file );
					$modified_time = $file_stats['mtime'];
					$filename = str_replace( $this->_options['backup_directory'], '', $file ); // Just the file name.
					$files[$modified_time] = array(
														'filename'				=>		$filename,
														'size'					=>		$file_stats['size'],
														'modified'				=>		$modified_time,
													);
					$summed_size += ( $file_stats['size'] / 1048576 ); // MB
				}
			}
			unset( $file_list );
			if ( empty( $files ) ) { // return if no archives (nothing else to do).
				return true;
			} else {
				krsort( $files );
			}
			
			// Limit by number of archives if set. Deletes oldest archives over this limit.
			if ( ( $this->_options['archive_limit'] > 0 ) && ( count( $files ) ) > $this->_options['archive_limit'] ) {
				// Need to trim.
				$i = 0;
				foreach( $files as $file ) {
					$i++;
					if ( $i > $this->_options['archive_limit'] ) {
						$this->status( 'details', sprintf( __('Deleting old archive `%s` due as it causes archives to exceed total number allowed.', 'it-l10n-backupbuddy'), $file['filename'] ) );
						unlink( $this->_options['backup_directory'] . $file['filename'] );
					}
				}
			}
			
			// Limit by size of archives, oldest first if set.
			$files = array_reverse( $files, true ); // Reversed so we delete oldest files first as long as size limit still is surpassed; true = preserve keys.
			if ( ( $this->_options['archive_limit_size'] > 0 ) && ( $summed_size > $this->_options['archive_limit_size'] ) ) {
				// Need to trim.
				foreach( $files as $file ) {
					if ( $summed_size > $this->_options['archive_limit_size'] ) {
						$summed_size = $summed_size - ( $file['size'] / 1048576 );
						$this->status( 'details', sprintf( __('Deleting old archive `%s` due as it causes archives to exceed total size allowed.', 'it-l10n-backupbuddy'),  $file['filename'] ) );
						if ( $file['filename'] != basename( $this->_backup['archive_file'] ) ) { // Delete excess archives as long as it is not the just-made backup.
							unlink( $this->_options['backup_directory'] . $file['filename'] );
						} else {
							$message = __( 'ERROR #9028: Based on your backup archive limits (size limit) the backup that was just created would be deleted. Skipped deleting this backup. Please update your archive limits.' );
							$this->status( 'message', $message );
							$this->_parent->mail_error( $message );
						}
					}
				}
			}
			
			return true;
		}
		
		function post_backup() {
			$this->status( 'message', __('Cleaning up after backup.', 'it-l10n-backupbuddy') );
			
			// Delete temporary data directory.
			if ( file_exists( $this->_backup['temp_directory'] ) ) {
				$this->status( 'details', __('Removing temp data directory.', 'it-l10n-backupbuddy') );
				$this->_parent->delete_directory_recursive( $this->_backup['temp_directory'] );
			}
			// Delete temporary ZIP directory.
			if ( file_exists( $this->_options['backup_directory'] . 'temp_zip_' . $this->_backup['serial'] . '/' ) ) {
				$this->status( 'details', __('Removing temp zip directory.', 'it-l10n-backupbuddy') );
				$this->_parent->delete_directory_recursive( $this->_options['backup_directory'] . 'temp_zip_' . $this->_backup['serial'] . '/' );
			}
			
			$this->trim_old_archives(); // Clean up any old excess archives pushing us over defined limits in settings.
			
			$message = __('completed successfully in ', 'it-l10n-backupbuddy') . $this->_parent->format_duration( time() - $this->_backup['start_time'] ) . '. File: ' . basename( $this->_backup['archive_file'] );
			if ( $this->_backup['trigger'] == 'manual' ) {
				$this->status( 'details', __('Sending manual backup email notification.', 'it-l10n-backupbuddy') );
				$this->_parent->mail_notify_manual( 'Manual backup ' . $message );
			} elseif ( $this->_backup['trigger'] == 'scheduled' ) {
				$this->status( 'details', __('Sending scheduled backup email notification.', 'it-l10n-backupbuddy') );
				$this->_parent->mail_notify_scheduled( __('Scheduled backup', 'it-l10n-backupbuddy') . ' (' . $this->_backup['schedule_title'] . ') ' . $message );
			} else {
				$this->log( 'Error #4343434. Unknown backup trigger.', 'error' );
			}
			
			// Schedule cleanup (12 hours from now; time for remote transfers) of log status file and data structure.
			wp_schedule_single_event( ( time() + ( 12 * 60 * 60 ) ), $this->_parent->_var . '-cron_final_cleanup', array( $this->_backup['serial'] ) );
			
			$this->status( 'message', __('Finished cleaning up.', 'it-l10n-backupbuddy') );
			$this->status( 'action', 'archive_url^' . site_url() . '/wp-content/uploads/backupbuddy_backups/' . basename( $this->_backup['archive_file'] ) );
			
			if ( $this->_backup['backup_mode'] == '1' ) {
				$stats = stat( $this->_backup['archive_file'] );
				$this->status( 'details', __('Final ZIP file size', 'it-l10n-backupbuddy') . ': ' . $this->_parent->_parent->format_size( $stats['size'] ) );
				$this->status( 'action', 'archive_size^' . $this->_parent->_parent->format_size( $stats['size'] ) );
			}
			
			$this->status( 'message', __('Backup completed successfully in', 'it-l10n-backupbuddy') . ' ' . $this->_parent->format_duration( time() - $this->_backup['start_time'] ) . '. ' . __('Done.', 'it-l10n-backupbuddy') );
			$this->status( 'action', 'finish_backup' );
		}
		
		
		function send_remote_destination( $destination_id ) {
			$this->status( 'details', sprintf( __('Sending file to remote destination `%s`.', 'it-l10n-backupbuddy'), $destination_id ) );
			$this->_parent->log( 'Sending file to remote destination.' );
			return $this->_parent->send_remote_destination( $destination_id, $this->_backup['archive_file'] );
		}
		
		function post_remote_delete() {
			$this->status( 'details', __('Deleting local copy of file sent remote.', 'it-l10n-backupbuddy') );
			$this->_parent->log( 'Deleting local copy of file sent remote.' );
			if ( file_exists( $this->_backup['archive_file'] ) ) {
				unlink( $this->_backup['archive_file'] );
			}
			
			if ( file_exists( $this->_backup['archive_file'] ) ) {
				$this->status( 'details', __('Error. Unable to delete local archive as requested.', 'it-l10n-backupbuddy') );
				return false; // Didnt delete.
			} else {
				$this->status( 'details', __('Deleted local archive as requested.', 'it-l10n-backupbuddy') );
				return true; // Deleted.
			}
		}
		
		
		/**
		 *	anti_directory_browsing()
		 *
		 *	Helps security by attempting to block directory browsing by creating
		 *	both index.htm files and .htaccess files turning browsing off.
		 *
		 *	$directory	string		Full absolute pass to insert anti-directory-browsing files into.
		 *
		 *	@return		null
		 *
		 */
		function anti_directory_browsing( $directory = '' ) {
			if ( empty( $directory ) ) {
				$directory = $this->_options['backup_directory'];
			}
			
			if ( !file_exists( $directory ) ) {
				if ( $this->_parent->mkdir_recursive( $directory ) === false ) {
					$this->status( 'details', sprintf( __('Error #9002: Unable to create directory (%s).', 'it-l10n-backupbuddy'), $directory) );
					$this->error( 'Unable to create directory (' . $directory . ')', '9002' );
					return false;
				}
			}
			
			// index.php
			if ( !file_exists( $directory . 'index.php' ) ) { // $this->_options['backup_directory']
				$fh = fopen( $directory . 'index.php', 'a' );
				fwrite( $fh, '<html></html>' );
				fclose( $fh );
				unset( $fh );
			}
			if ( !file_exists( $directory . 'index.php' ) ) {
				$error_message = __('Error #983489. Unable to create anti-directory browsing files (index.php). Backup halted to maintain security.', 'it-l10n-backupbuddy');
				$this->status( 'details', $error_message );
				die( $error_message );
			}
			
			// index.htm
			if ( !file_exists( $directory . 'index.htm' ) ) {
				$fh = fopen( $directory . 'index.htm', 'a' );
				fwrite( $fh, '<html></html>' );
				fclose( $fh );
				unset( $fh );
			}
			if ( !file_exists( $directory . 'index.htm' ) ) {
				$error_message = __( 'Error #983489. Unable to create anti-directory browsing files (index.htm). Backup halted to maintain security.', 'it-l10n-backupbuddy');
				$this->status( 'details', $error_message );
				die( $error_message );
			}
			
			// index.html
			if ( !file_exists( $directory . 'index.html' ) ) {
				$fh = fopen( $directory . 'index.html', 'a' );
				fwrite( $fh, '<html></html>' );
				fclose( $fh );
				unset( $fh );
			}
			if ( !file_exists( $directory . 'index.html' ) ) {
				$error_message =  __( 'Error #983489. Unable to create anti-directory browsing files (index.html). Backup halted to maintain security.', 'it-l10n-backupbuddy');
				$this->status( 'details', $error_message );
				die( $error_message );
			}
			
			// .htaccess
			if ( !file_exists( $directory . '.htaccess' ) ) {
				$fh = fopen( $directory . '.htaccess', 'a' );
				fwrite( $fh, "IndexIgnore *\n" );
				fclose( $fh );
				unset( $fh );
			}
			if ( !file_exists( $directory . '.htaccess' ) ) {
				$error_message =  __( 'Error #983489. Unable to create anti-directory browsing files. Backup halted to maintain security.', 'it-l10n-backupbuddy');
				$this->status( 'details', $error_message );
				die( $error_message );
			}
			
			return true;
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
		 *	error()
		 *
		 *	Appends error message and optional error code to $this->_errors array
		 *	with optional error code. Also logs to text file via parent file's log()
		 *	function. Passes optional error code.
		 *
		 *	$error		string		Error text.
		 *	$code		integer		Numeric error code number. Used for matching in the codex.
		 *	@return		null
		 *
		 */
		function error( $error, $code = '' ) {
			array_push( $this->_errors, array( $error, $code ) );
			if ( !empty( $code ) ) {
				$error = '(Error #' . $code . ') ' . $error;
			}
			$this->_parent->log( $error, 'error' );
		}
		
		function get_errors() {
			if ( empty( $this->_errors ) ) { // Return if no errors.
				return;
			}
			$error_list = array();
			foreach( $this->_errors as $error ) {
				$this_error = 'Error';
				if ( !empty( $error[1] ) ) {
					$this_error .= ' #' . $error[1];
				}
				$this_error .= ': ' . $error[0] . '<br >';
				
				array_push( $error_list, $this_error );
			}
			
			return implode( ' ', $error_list );
		}
		
		
		/**
		 *	response()
		 *
		 *	Handles sending data to the client. Useful for sending commands, messages, and debugging information to the client.
		 *	Automatically handles the proper formatting for the BB ajax client code if in AJAX mode.
		 *	Parts split with the pipe | deliminator.
		 *
		 *	$type		string		action		Command to tell the client/ajax something internal. Send commands with this.
		 *										Actions split with the hat ^ deliminator.
		 *							message		Normal message to display to the user.
		 *							warning		Same as message but noted as a warning to allow client to bring more attention.
		 *							error		[FATAL ERROR] Same as warning BUT ALSO should instruct client that backup process has HALTED!
		 *							details		Additional details. These may be technical. Use for sending more obscure or
												detailed messages, debugging.
		 *	$message	string					Message or command to send to the client. Not applicable for done or ping types.
		 *	@return		null
		 *
		 */
		 // TODO: What is $skip for and who added it? Dustin didn't.
		function status( $type, $message = '', $skip = false ) {
			if ( $skip ) return;
			
			// Note the first item logged for a new PHP process for easily seeing PHP loads.
			if ( $this->_status_logging_started === false ) {
				$this->_status_logging_started = true;
				$this->status( 'details', '(Started new PHP page load process.)' );
			}
			
			if ( isset( $this->_backup ) ) {
				if ( $this->_backup['backup_mode'] == '2' ) { // 2.0 MODE
					$status = $this->_parent->localize_time( time() ) . '|' . $type . '|' . $message;
					
					$fp = fopen( $this->_options['backup_directory'] . 'temp_status_' . $this->_backup['serial'] . '.txt', 'a' );
					fwrite( $fp, $status . "\n" );
					fclose( $fp );
				} else { // CLASSIC MODE
					$message = htmlentities( $message );
					$status = date( $this->_parent->_parent->_timestamp, time() ) . ': ' . $message;
					
					// Add to messages only if a message.
					if ( $type == 'message' ) {
						echo '
						<script type="text/javascript">
						jQuery( "#backupbuddy_messages" ).append( "\n' . $status . '");
						textareaelem = document.getElementById( "backupbuddy_messages" );
						textareaelem.scrollTop = textareaelem.scrollHeight;
						</script>
						';
					}
					if ( $type == 'action' ) {
						if ( substr( $message, 0, 11 ) == 'archive_url' ) {
							echo '
							<script type="text/javascript">
							jQuery( "#pb_backupbuddy_archive_url" ).attr( "href", "' . substr( $message, 12 ) . '");
							jQuery( "#pb_backupbuddy_archive_download" ).slideDown();
							</script>
							';
						} elseif ( substr( $message, 0, 12 ) == 'archive_size' ) {
							echo '
							<script type="text/javascript">
							jQuery( ".backupbuddy_archive_size" ).html( "' . substr( $message, 13 ) . '" );
							</script>
							';
						}
					}
					
					// Add to details.
					echo '
					<script type="text/javascript">
					jQuery( "#backupbuddy_details" ).append( "\n' . $status . '");
					textareaelem = document.getElementById( "backupbuddy_details" );
					textareaelem.scrollTop = textareaelem.scrollHeight;
					</script>
					';
					
					flush();
				}
			}
			
			if ( $type == 'error' ) {
				$this->log( $message, 'error' );
			} elseif ( $type == 'message' ) {
				$this->log( $message );
			} elseif ( $type == 'details' ) {
				$this->log( $message );
			}
			
			return true;
		}
		
	} // End class
	
	//$pluginbuddy_backupbuddy_backup = new pluginbuddy_backupbuddy_backup( $this );
}
?>