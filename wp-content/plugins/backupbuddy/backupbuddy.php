<?php
/**
 *
 * Plugin Name: BackupBuddy
 * Plugin URI: http://pluginbuddy.com/backupbuddy/
 * Description: Backup - Restore - Migrate. Backs up files, settings, and content for a complete snapshot of your site. Allows migration to a new host or URL.
 * Version: 2.2.35
 * Author: The PluginBuddy Team
 * Author URI: http://pluginbuddy.com
 *
 * Written by Dustin Bolton.
 *
 * Installation:
 * 
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 * 
 * Usage:
 * 
 * 1. Navigate to the new plugin menu in the Wordpress Administration Panel.
 *
 */

//if (!class_exists("pluginbuddy_backupbuddy")) {
	class pluginbuddy_backupbuddy {
		var $_version = '2.2.35';
		var $_updater = '1.0.8';									// Deprecated variable. Use $this->plugin_info( 'version' ).
		var $_url = 'http://pluginbuddy.com/backupbuddy/';			// Deprecated variable. Purchase URL. Use $this->plugin_info( 'url' ).
		var $_var = 'pluginbuddy_backupbuddy';						// Deprecared variable. Use $this->_slug.
		
		var $_wp_minimum = '3.0.0';
		var $_php_minimum = '5.2';
		
		var $_slug = 'pluginbuddy_backupbuddy';						// Format: pluginbuddy-pluginnamehere. All lowecase, no dashes.
		var $_name = 'BackupBuddy';									// Pretty plugin name. Only used for display so any format is valid.
		var $_series = '';											// Series name if applicable.
		var $_timestamp = 'M j, Y H:i:s';								// PHP timestamp format.
		var $_defaults = array(
			'data_version'				=>		'2',				// Data structure version. Added BB 2.0 to ease updating.
			'import_password'			=>		'',					// Importbuddy password.
			'backup_reminders'			=>		1,					// Todo: High security mode.
			'edits_since_last'			=>		0,					// Number of post/page edits since the last backup began.
			'last_backup'				=>		0,					// Timestamp of when last backup started.
			'last_backup_serial'		=>		'',					// Serial of last backup zip.
			'compression'				=>		1,					// Zip compression.
			'force_compatibility'		=>		0,					// Force compatibility mode even if normal is detected.
			'skip_database_dump'		=>		0,					// When enabled the database dump step will be skipped.
			'backup_nonwp_tables'		=>		0,					// Backup tables not prefixed with the WP prefix.
			'include_tables'			=>		array(),			// Additional tables to include.
			'exclude_tables'			=>		array(),			// Tables to exclude.
			'integrity_check'			=>		1,					// Zip file integrity check on the backup listing.
			'schedules'					=>		array(),			// Array of scheduled schedules.
			'log_level'					=>		'1',				// Valid options: 0 = none, 1 = errors only, 2 = errors + warnings, 3 = debugging (all kinds of actions)
			'excludes'					=>		'',					// Newline deliminated list of directories to exclude from the backup.
			'backup_reminders'			=>		1,					// Whether or not to show reminders to backup on post/page edits & on the WP upgrade page.
			'high_security'				=>		0,					// TODO: Future feature. Strip mysql password & admin user password. Prompt on import.
			'next_schedule_index'		=>		100,				// Next schedule index. Prevent any risk of hanging scheduled crons from having the same ID as a new schedule.
			'archive_limit'				=>		0,					// Maximum number of archives to storage. Deletes oldest if exceeded.
			'archive_limit_size'		=>		0,					// Maximum size of all archives to store. Deletes oldest if exeeded.
			
			'email_notify_scheduled'	=>		'',
			'email_notify_manual'		=>		'',
			'email_notify_error'		=>		'',
			
			'backups'					=>		array(),			// Array of currently ocurring backups.
			'remote_destinations'		=>		array(),			// Array of remote destinations (S3, Rackspace, email, ftp, etc)
			'backup_file_integrity'		=>		array(),
			'role_access'				=>		'administrator',
			'dropboxtemptoken'			=>		'',
			'backup_mode'				=>		'2',				// 1 = 1.x, 2 = 2.x mode
			'multisite_export'			=>		'1',				
			'backup_directory'			=>		'',
			//'enable_repair_buddy' 		=>		'2',
			'repairbuddy_password'		=>		'',
			'log_serial'				=>		'',
			
			'temporary_options'			=>		array(				// Temporary options for a limited number of BackupBuddy versions. Useful for enabling beta or other temporary features.
													'experimental_zip'		=>		'0',
												),
			//'suppress_notifications'	=>		array(),			// Slug of notification messages to suppress from showing again.
		);
		
		var $_scheduledefaults = array(
			'title'						=>		'',
			'type'						=>		'db',
			'interval'					=>		'monthly',
			'first_run'					=>		'',
			'delete_after'				=>		0,
			'remote_destinations'		=>		'',
			'last_run'					=>		'0',
		);
		
		var $_s3defaults = array(
			'title'			=>		'',
			'accesskey'		=>		'',
			'secretkey'		=>		'',
			'bucket'		=>		'',
			'directory'		=>		'',
			'ssl'			=>		1,
			'archive_limit'	=>		0,
		);
		
		var $_dropboxdefaults = array(
			'title'			=>		'',
			'token'			=>		'',
			'directory'		=>		'backupbuddy',
			'archive_limit'	=>		0,
		);
		
		var $_rackspacedefaults = array(
			'title'			=>		'',
			'username'		=>		'',
			'api_key'		=>		'',
			'container'		=>		'',
			'server'		=>		'https://auth.api.rackspacecloud.com',
			'archive_limit'	=>		0,
		);
			
		var $_emaildefaults = array(
			'title'			=>		'',
			'email'			=>		'',
		);
			
		var $_ftpdefaults = array(
			'title'			=>		'',
			'address'		=>		'',
			'username'		=>		'',
			'password'		=>		'',
			'path'			=>		'',
			'ftps'			=>		0,
			'archive_limit'	=>		0,
		);
		
		
		// Default constructor. This is run when the plugin first runs.
		function pluginbuddy_backupbuddy() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			$this->_pluginBase = plugin_basename( __FILE__  );
			
			// Actions that are available to trigger even outside of admin.
			add_action( $this->_var . '-cron_process_backup', array( &$this, 'cron_process_backup' ), 10, 1 );
			add_action( $this->_var . '-cron_final_cleanup', array( &$this, 'cron_final_cleanup' ), 10, 1 );
			add_action( $this->_var . '-cron_s3_copy', array( &$this, 'cron_process_s3_copy' ), 10, 5 );
			add_action( $this->_var . '-cron_dropbox_copy', array( &$this, 'cron_process_dropbox_copy' ), 10, 2 );
			add_action( $this->_var . '-cron_rackspace_copy', array( &$this, 'cron_process_rackspace_copy' ), 10, 5 );
			add_action( $this->_var . '-cron_ftp_copy', array( &$this, 'cron_process_ftp_copy' ), 10, 5 );
			add_action( 'pb_backupbuddy-cron_remotesend', array( &$this, 'cron_remotesend' ), 10, 2 );
			add_action( 'pb_backupbuddy-cron_scheduled_backup', array( &$this, 'cron_process_scheduled_backup' ), '', 5 );
			
			// Filters that are available to trigger even outside of admin.
			add_filter( 'cron_schedules', array( &$this, 'filter_cron_add_schedules' ) );
						
			// Make localization happen.
			load_plugin_textdomain( 'it-l10n-backupbuddy', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
			
			if ( is_admin() ) { // Admin part of site.
				$this->load();
				
				if ( empty( $this->_options['backup_directory'] ) ) {
					$this->activate();
				}
				add_action( 'init', array( &$this, 'upgrader_register' ), 50 );
				add_action( 'init', array( &$this, 'upgrader_select' ), 100 );
				add_action( 'init', array( &$this, 'upgrader_instantiate' ), 101 );
				// Keep backup directory up to date.
				$this->verify_backup_directory();
				
				require_once( $this->_pluginPath . '/classes/admin.php' );
				$pluginbuddy_backupbuddy_admin = new pluginbuddy_backupbuddy_admin( $this );
				if ( !defined( 'PB_DEMO_MODE' ) ) {
					require_once( $this->_pluginPath . '/lib/updater/updater.php' );
				}
				register_activation_hook( __FILE__, array( &$this, 'activate' ) ); // Run some code when plugin is activated in dashboard.
				
				add_action( 'wp_dashboard_setup', array( &$this, 'action_wp_dashboard_setup' ) ); // Dashboard Stats
				if ( $this->_options['backup_reminders'] == '1' ) { 
					add_action( 'load-update-core.php', array( &$this, 'action_update_notice' ) );
					add_action( 'post_updated_messages', array( &$this, 'action_post_updated_messages' ) );
				}
				
				// Ajax Actions
				add_action( 'wp_ajax_pb_backupbuddy', array( &$this, 'ajax' ) ); // Directory listing for exluding
				add_action( 'wp_ajax_pb_backupbuddy_remotedestination', array( &$this, 'ajax_remotedestination' ) ); // Directory listing for exluding
				add_action( 'wp_ajax_pb_backupbuddy_remotetest', array( &$this, 'ajax_remotetest' ) ); // Test S3, etc.
				add_action( 'wp_ajax_pb_backupbuddy_filetree', array( &$this, 'ajax_filetree' ) ); // Directory listing for exluding
				add_action( 'wp_ajax_' . $this->_var . '_icicletree', array( &$this, 'ajax_icicletree' ) ); // Directory listing for exluding
				add_action( 'wp_ajax_pb_backupbuddy_remotesend', array( &$this, 'ajax_remotesend' ) ); // Remote send offsite (manually).
				add_action( 'wp_ajax_pb_backupbuddy_md5hash', array( &$this, 'ajax_md5hash' ) ); // Generate a MD5 hash for file verification.
				add_action( 'wp_ajax_backupbuddy_importbuddy_classic', array( &$this, 'ajax_importbuddy_classic' ) ); // Direct downloading of importbuddy.php using ajax portal.
				add_action( 'wp_ajax_backupbuddy_importbuddy', array( &$this, 'ajax_importbuddy' ) ); // Direct downloading of importbuddy.php using ajax portal.
				add_action( 'wp_ajax_backupbuddy_repairbuddy', array( &$this, 'ajax_repairbuddy' ) ); // Direct downloading of repairbuddy.php using ajax portal.
				add_action( 'wp_ajax_backupbuddy_repairbuddy_reset', array( &$this, 'ajax_repairbuddy_reset' ) ); // Reset password.
				
				add_filter( 'plugin_row_meta', array( &$this, 'filter_plugin_row_meta' ), 10, 2 );
				
			}
		}
		
		
		function verify_backup_directory() {
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			
			if ( $this->_options['backup_directory'] != ( ABSPATH . 'wp-content/uploads/backupbuddy_backups/' ) ) {
				$this->_options['backup_directory'] = ABSPATH . 'wp-content/uploads/backupbuddy_backups/';
				$this->save();
			}
		}
		
		
		// Returns array of subdirectories that contain WordPress.
		function get_wordpress_locations() {
			$wordpress_locations = array();
			
			$files = glob( ABSPATH . '*/' );
			if ( !is_array( $files ) || empty( $files ) ) {
				$files = array();
			}
			foreach( $files as $file ) {
				if ( file_exists( $file . 'wp-config.php' ) ) {
					$wordpress_locations[]  = '/' . str_replace( ABSPATH, '', $file );
				}
			}
			
			return $wordpress_locations;
		}
		
		
		/**
		 *	plugin_info()
		 *
		 *	Provides various plugin information on demand. Iteration 1.
		 *
		 *	$type		string		Information to return. Available values: name, title, description,
		 *							author, authoruri, version, pluginuri OR url, textdomain, domainpath, network
		 *	@return		string		Returns value of information requested in $type
		 */
		function plugin_info( $type ) {
			if ( empty( $this->_info ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$this->_info = array_change_key_case( get_plugin_data( __FILE__, false, false ), CASE_LOWER );
				$this->_info['url'] = $this->_info['pluginuri'];
			}
			if ( !empty( $this->_info[$type] ) ) {
				return $this->_info[$type];
			} else {
				return 'UNKNOWN_VAR_354-' . $type;
			}
		}
		
		
		/**
		 *	alert()
		 *
		 *	Displays a message to the user at the top of the page when in the dashboard.
		 *
		 *	$message		string		Message you want to display to the user.
		 *	$error			boolean		OPTIONAL! true indicates this alert is an error and displays as red. Default: false
		 *	$error_code		int			OPTIONAL! Error code number to use in linking in the wiki for easy reference.
		 *	@return			null
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
				$message .= '<p><a href="http://ithemes.com/codex/page/' . $this->_name . ':_Error_Codes#' . $error_code . '" target="_new"><i>' . $this->_name . ' ' . __( 'Error Code', 'it-l10n-backupbuddy' ) . ' ' . $error_code . ' - ' .  __( 'Click for more details.', 'it-l10n-backupbuddy') . '</i></a></p>';
				$log_error = true;
			}
			if ( $log_error === true ) {
				$this->log( $message . ' Error Code: ' . $error_code, 'error' );
			}
			echo '"><p><strong>'.$message.'</strong></p></div>';
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
			$tip = ' <a class="pluginbuddy_tip" title="' . $title . ' - ' . $message . '"><img src="' . $this->_pluginURL . '/images/pluginbuddy_tip.png" alt="(?)" /></a>';
			if ( $echo_tip === true ) {
				echo $tip;
			} else {
				return $tip;
			}
		}
		
		
		/**
		 *	video()
		 *
		 *	Displays a message to the user when they hover over the question mark. Gracefully falls back to normal tooltip.
		 *	HTML is supposed within tooltips.
		 *
		 *	$video_key		string		YouTube video key from the URL ?v=VIDEO_KEY_HERE
		 *	$title			string		Title of message to show to user. This is displayed at top of tip in bigger letters. Default is blank. (optional)
		 *	$echo_tip		boolean		Whether to echo the tip (default; true), or return the tip (false). (optional)
		 */
		function video( $video_key, $title = '', $echo_tip = true ) {
			global $wp_scripts;
			if ( !in_array( 'thickbox', $wp_scripts->done ) ) {
				wp_enqueue_script( 'thickbox' );
				wp_print_scripts( 'thickbox' );
				wp_print_styles( 'thickbox' );
			}
			
			if ( strstr( $video_key, '#' ) ) {
				$video = explode( '#', $video_key );
				$video[1] = '&start=' . $video[1];
			} else {
				$video[0] = $video_key;
				$video[1] = '';
			}
			
			$tip = '<a href="http://www.youtube.com/embed/' . urlencode( $video[0] ) . '?autoplay=1' . $video[1] . '&TB_iframe=1&width=640&height=400" class="thickbox pluginbuddy_tip" title="' . __('Video Tutorial', 'it-l10n-backupbuddy') . ' - ' . $title . '"><img src="' . $this->_pluginURL . '/images/pluginbuddy_play.png" alt="(' . __('video', 'it-l10n-backupbuddy') . ')" /></a>';
			if ( $echo_tip === true ) {
				echo $tip;
			} else {
				return $tip;
			}
		}
		
		
		/**
		 *	log()
		 *
		 *	Logs to a text file depending on settings.
		 *	0 = none, 1 = errors only, 2 = errors + warnings, 3 = debugging (all kinds of actions)
		 *
		 *	$text	string			Text to log.
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
			
			if ( $this->_options['log_serial'] == '' ) {
				$this->_options['log_serial'] = $this->rand_string( 10 );
				$this->save();
			}
			
			if ( $write === true ) {
				$fh = @fopen( WP_CONTENT_DIR . '/uploads/' . $this->_var . '-' . $this->_options['log_serial'] . '.txt', 'a');
				if ( $fh ) {
					fwrite( $fh, '[' . date( $this->_timestamp . ' ' . get_option( 'gmt_offset' ), time() + (get_option( 'gmt_offset' )*3600) ) . '-' . $log_type . '] ' . $text . "\n" );
					fclose( $fh );
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
		
		
		function load() {
			$this->_options = $this->get_site_option($this->_var);
			//Try to read site-specific settings in
			if ( is_multisite() ) {
				$multisite_option = get_option( $this->_var );
				if ( $multisite_option ) {
					delete_option( $this->_var );
					$this->_options = $multisite_option;
					$this->save();
				}
			}
			$options = array_merge( $this->_defaults, (array)$this->_options );
			
			
			// 1.  User may have old options stored (get_option)
			// 2.  Care about only on multisite
			// 3.  Remove all old options
			
			if ( $options !== $this->_options ) {
				// Defaults existed that werent already in the options so we need to update their settings to include some new options.
				$this->_options = $options;
				$this->save();
			}
			
			return true;
		}
		
		
		function save() {
			add_site_option($this->_var, $this->_options, '', 'no'); // 'No' prevents autoload if we wont always need the data loaded.
			$this->update_site_option($this->_var, $this->_options);
			return true;
		}
		
		
		function get_site_option( $option, $default = false, $use_cache = true ) {
			global $wpdb;
					
			if ( !is_multisite() ) {
				$value = $this->get_option($option, $default);
			} else {
				$row = $wpdb->get_row( $wpdb->prepare("SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = %s AND site_id = %d", $option, $wpdb->siteid ) );
		
				// Has to be get_row instead of get_var because of funkiness with 0, false, null values
				if ( is_object( $row ) )
					$value = $row->meta_value;
				else
					$value = $default;
		
				$value = maybe_unserialize( $value );
			}
			return $value;			
		} //end get_site_option
		
		function get_option( $option, $default = false ) {
			global $wpdb;
				
			$option = trim($option);
			if ( empty($option) )
				return false;
		
			if ( defined( 'WP_SETUP_CONFIG' ) )
				return false;
		
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );
		
			// Has to be get_row instead of get_var because of funkiness with 0, false, null values
			if ( is_object( $row ) ) {
				$value = $row->option_value;
			} else {
				$value = $default;
			}			
			
			// If home is not set use siteurl.
			if ( 'home' == $option && '' == $value )
				return get_option( 'siteurl' );
		
			if ( in_array( $option, array('siteurl', 'home', 'category_base', 'tag_base') ) )
				$value = untrailingslashit( $value );
			
			$value = maybe_unserialize( $value );
			return $value;			
		} //end get_option
		function update_site_option( $option, $value ) {	
			global $wpdb;
			if ( !is_multisite() ) {
				$result = $this->update_option( $option, $value );
				if ( $result ) return true;
			} else {
				
				if ( $value && !$wpdb->get_row( $wpdb->prepare( "SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = %s AND site_id = %d", $option, $wpdb->siteid ) ) ) {
					$value = sanitize_option( $option, $value );
					$value = maybe_serialize($value);
					$wpdb->insert( $wpdb->sitemeta, array('site_id' => $wpdb->siteid, 'meta_key' => $option, 'meta_value' => $value ) );
					return true;
				} else {
					$value = sanitize_option( $option, $value );
					$value = maybe_serialize( $value );
					$result = $wpdb->update( $wpdb->sitemeta, array( 'meta_value' => $value ), array( 'site_id' => $wpdb->siteid, 'meta_key' => $option ) );
					return true;
				}
			}
			return false;
		} //end update_site_option
		
		function update_option( $option, $newvalue ) {
			global $wpdb;

			$option = trim($option);
			if ( empty($option) )
				return false;				
			
			$oldvalue = get_option( $option );
			if ( false === $oldvalue ) {
				return add_option( $option, $newvalue );
			} else {
				$newvalue = sanitize_option( $option, $newvalue );
				$newvalue = maybe_serialize( $newvalue );				
				$result = $wpdb->update( $wpdb->options, array( 'option_value' => $newvalue ), array( 'option_name' => $option ) );
				
				if ( $result ) return true;
			}
			
			return false;
		} //end update_option
		
		
		/**
		 * activate()
		 *
		 * Run on plugin activation. Handles upgrades.
		 *
		 */
		function activate() {
			// Migrate storage location for settings from pre-2.0 to post-2.0 location.
			$upgrade_options = get_option( 'ithemes-backupbuddy' );
			if ( $upgrade_options != false ) {
				$this->_options = $upgrade_options;
				
				$this->_options['email_notify_error'] = $this->_options['email'];
				if ( $this->_options['email_notify_manual'] == 1 ) {
					$this->_options['email_notify_manual'] = $this->_options['email'];
				}
				if ( $this->_options['email_notify_scheduled'] == 1 ) {
					$this->_options['email_notify_scheduled'] = $this->_options['email'];
				}
				unset( $this->_options['email'] );
				
				$this->_options['archive_limit'] = $this->_options['zip_limit'];
				unset( $this->_options['zip_limit'] );
				
				$this->_options['import_password'] = $this->_options['password'];
				if ( $this->_options['import_password'] == '#PASSWORD#' ) {
					$this->_options['import_password'] = '';
				}
				unset( $this->_options['password'] );
				
				if ( is_array( $this->_options['excludes'] ) ) {
					$this->_options['excludes'] = implode( "\n", $this->_options['excludes'] );
				}
				
				$this->_options['last_backup'] = $this->_options['last_run'];
				unset( $this->_options['last_run'] );
				
				// FTP.
				if ( !empty( $this->_options['ftp_server'] ) ) {
					$this->_options['remote_destinations'][0] = array(
																	'title'			=>		'FTP',
																	'address'		=>		$this->_options['ftp_server'],
																	'username'		=>		$this->_options['ftp_user'],
																	'password'		=>		$this->_options['ftp_pass'],
																	'path'			=>		$this->_options['ftp_path'],
																	'type'			=>		'ftp',
																);
					if ( $this->_options['ftp_type'] == 'ftp' ) {
						$this->_options['remote_destinations'][0]['ftps'] = 0;
					} else {
						$this->_options['remote_destinations'][0]['ftps'] = 1;
					}
				}
				
				// Amazon S3.
				if ( !empty( $this->_options['aws_bucket'] ) ) {
					$this->_options['remote_destinations'][1] = array(
																	'title'			=>		'S3',
																	'accesskey'		=>		$this->_options['aws_accesskey'],
																	'secretkey'		=>		$this->_options['aws_secretkey'],
																	'bucket'		=>		$this->_options['aws_bucket'],
																	'directory'		=>		$this->_options['aws_directory'],
																	'ssl'			=>		$this->_options['aws_ssl'],
																	'type'			=>		's3',
																);
				}
				
				// Email destination.
				if ( !empty( $this->_options['email'] ) ) {
					$this->_options['remote_destinations'][2] = array(
																	'title'			=>		'Email',
																	'email'			=>		$this->_options['email'],
																);
				}
				
				// Handle migrating scheduled remote destinations.
				foreach( $this->_options['schedules'] as $schedule_id => $schedule ) {
					$this->_options['schedules'][$schedule_id]['title'] = $this->_options['schedules'][$schedule_id]['name'];
					unset( $this->_options['schedules'][$schedule_id]['name'] );
					
					$this->_options['schedules'][$schedule_id]['remote_destinations'] = '';
					if ( $schedule['remote_send'] == 'ftp' ) {
						$this->_options['schedules'][$schedule_id]['remote_destinations'] .= '0|';
					}
					if ( $schedule['remote_send'] == 'aws' ) {
						$this->_options['schedules'][$schedule_id]['remote_destinations'] .= '1|';
					}
					if ( $schedule['remote_send'] == 'email' ) {
						$this->_options['schedules'][$schedule_id]['remote_destinations'] .= '2|';
					}
				}
				
				delete_option( 'ithemes-backupbuddy' );
			}
			
			$this->save();
			$this->verify_backup_directory();
			
			$old_log_file = WP_CONTENT_DIR . '/uploads/' . $this->_var . '.txt';
			if ( file_exists( $old_log_file ) ) {
				@unlink( $old_log_file );
			}
		}
		
		
		// Displays helpful info in a dashboard box. Called from callback within our function action_wp_dashboard_setup().
		function dashboard_stats() {
			echo '<style type="text/css">';
			echo '	.pb_fancy {';
			echo '		font-family: Georgia, "Times New Roman", "Bitstream Charter", Times, serif;';
			echo '		font-size: 18px;';
			echo '		color: #21759B;';
			echo '	}';
			echo '</style>';
			
			echo '<div>';
			
			$files = glob( $this->_options['backup_directory'] . 'backup*.zip' );
			if ( !is_array( $files ) || empty( $files ) ) {
				$files = array();
			}
			array_multisort( array_map( 'filemtime', $files ), SORT_NUMERIC, SORT_DESC, $files );
			
			echo sprintf( __('You currently have %s stored backups.', 'it-l10n-backupbuddy'), '<span class="pb_fancy"><a href="admin.php?page=pluginbuddy_backupbuddy-backup">' . count( $files ) . '</a></span>');
			if ( $this->_options['last_backup'] == 0 ) {
				echo ' ', __( 'You have not created any backups.', 'it-l10n-backupbuddy' );
			} else {
				echo ' ', sprintf( __(' Your most recent backup was %s ago.', 'it-l10n-backupbuddy'), '<span class="pb_fancy"><a href="admin.php?page=pluginbuddy_backupbuddy-backup">' . $this->time_ago( $this->_options['last_backup'] ) . '</a></span>');
			}
			echo ' ', sprintf( __('There have been %s post/page modifications since your last backup.', 'it-l10n-backupbuddy'), '<span class="pb_fancy"><a href="admin.php?page=pluginbuddy_backupbuddy-backup">' . $this->_options['edits_since_last'] . '</a></span>' );
			echo ' <span class="pb_fancy"><a href="admin.php?page=pluginbuddy_backupbuddy-backup">', __('Go create a backup!', 'it-l10n-backupbuddy'), '</a></span>';
			
			echo '</div>';
		}
		
		
		/**
		 *	set_greedy_script_limits()
		 *
		 *	Sets greedy script limits to help prevent timeouts, running out of memory, etc.
		 *
		 *	@return		null
		 *
		 */
		function set_greedy_script_limits( &$status_callback = '' )  {
			$status_callback = &$status_callback;
			
			
			// Don't abort script if the client connection is lost/closed
			@ignore_user_abort( true );
			
			// Set socket timeout to 2 hours.
			@ini_set( 'default_socket_timeout', 60 * 60 * 2 );
			
			// Set maximum runtime to 2 hours.
			$original_maximum_runtime = ini_get( 'max_execution_time' );
			@set_time_limit( 60 * 60 * 2 );
			if ( is_object( $status_callback ) === true ) {
				$status_callback->status( 'details', 'Attempted to increase maximum PHP runtime. Original: ' . $original_maximum_runtime . '; New: ' . @ini_get( 'max_execution_time' ) . '.' );
			}
			
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
						if ( is_object( $status_callback ) === true ) {
							$status_callback->status( 'details', __('Set memory limit to 256M. Previous value < 256M.', 'it-l10n-backupbuddy') );
						}
				}
			} else {
				// Couldn't determine current limit, set to 256M to be safe.
				@ini_set('memory_limit', '256M');
				if ( is_object( $status_callback ) === true ) {
					$status_callback->status( 'details', __('Set memory limit to 256M. Previous value unknown.', 'it-l10n-backupbuddy') );
				}
			}
			if ( is_object( $status_callback ) === true ) {
				$status_callback->status( 'details', 'Original PHP memory limit: ' . $current_memory_limit . '; New: ' . @ini_get( 'memory_limit' ) . '.' );
			}
		}
		
		
		// Displays a friendly backup reminder on post/page edits (if enabled).
		function action_post_updated_messages( $messages ) {
			$this->_options['edits_since_last']++;
			$this->save();
			$admin_url = '';
			//Only show the backup message for network admins or adminstrators
			if ( is_multisite() && current_user_can( 'manage_network' ) ) {
				$admin_url = admin_url( 'network/admin.php' );
			} elseif( !is_multisite() && current_user_can( 'administrator' ) ) {
				$admin_url = admin_url( 'admin.php' );
			} else {
				return $messages;
			}
			$fullbackup = esc_url( add_query_arg( array(
					'page' => 'pluginbuddy_backupbuddy-backup',
					'run_backup' => 'full'
				), $admin_url
			) );
			$dbbackup = esc_url( add_query_arg( array(
					'page' => 'pluginbuddy_backupbuddy-backup',
					'run_backup' => 'db'
				), $admin_url
			) );
			$backup_message = " | <a href='{$fullbackup}'>" . __('Full Backup', 'it-l10n-backupbuddy') . "</a> | <a href='{$dbbackup}'>" . __('Database Backup', 'it-l10n-backupbuddy') . "</a>";
			
			
			$reminder_posts = array(); // empty array to store customized post messages array
			$reminder_pages = array(); // empty array to store customized page messages array
			$others = array(); // An empty array to store the array for custom post types
			foreach ( $messages['post'] as $num => $message ) {
				$message .= $backup_message;
				if ( $num == 0 ) {
				$message = ''; // The first element in the messages['post'] array is always empty
				}
				array_push( $reminder_posts, $message ); // Insert/copy the modified message value to the last element of reminder array
			}
			$reminder_posts = array( 'post' => $reminder_posts ); // Apply the post key to the first dimension of messages array
			foreach ( $messages['page'] as $num => $message ) {
				$message .= $backup_message;
				if ( $num == 0 ) {
					$message = ''; // The first element in the messages['page'] array is always empty
				}
				array_push( $reminder_pages, $message ); // Insert/copy the modified message value to the last element of reminder array
			}
			$reminder_pages = array( 'page' => $reminder_pages ); // Apply the page key to the first dimension of messages array
			$reminder = array_merge( $reminder_posts, $reminder_pages );
			foreach ( $messages as $type => $message ) {
				if ( ( $type == 'post' ) || ( $type == 'page' ) ) { // Skip the post key since it is already defined
					continue;
				}
				$others[$type] = $message; // Since message is an array, this statement forms 2D array
			}
			$reminder = array_merge( $reminder, $others ); // Merge the arrays in the others array with reminder array in order to form an appropriate format for messages array
			
			return $reminder;
		}
		
		
		function action_wp_dashboard_setup() {
			if ( current_user_can( 'switch_themes' ) && !is_multisite() ) {
				wp_add_dashboard_widget( 'pb_backupbuddy', 'BackupBuddy',  array( &$this, 'dashboard_stats' ) );
			}
		}
		
		// Admin update page notice buffering setup.
		function action_update_notice() {
			ob_start( array( &$this, 'update_notice_dump' ) );
			add_action( 'admin_footer', create_function( '', 'ob_end_flush();' ) );
		}
		
		
		// Admin update page notice actual screen output.
		function update_notice_dump( $text = '' ) {
			return str_replace( '<h2>WordPress Updates</h2>', 
								'<h2>' . __('WordPress Updates', 'it-l10n-backupbuddy') . '</h2><div id="message" class="updated fade"><p><img src="' . $this->_pluginURL . '/images/pluginbuddy.png" style="vertical-align: -3px;" /> <a href="admin.php?page=pluginbuddy_backupbuddy-backup" target="_new" style="text-decoration: none;">' . __('Remember to back up your site with BackupBuddy before upgrading!', 'it-l10n-backupbuddy') . '</a></p></div>', 
								$text );
		}
		
		
		function ajax() {
			if ( $_GET['actionb'] == 'backup_status' ) {
				// Make sure the serial exists.
				if ( empty( $this->_options['backups'][$_POST['serial']] ) ) {
					echo '!' . $this->localize_time( time() ) . '|error|Error #5445589. Invalid backup serial (' . htmlentities( $_POST['serial'] ) . '). Verify backup directory writer permission. Fatal error.' . "\n";
					echo '!' . $this->localize_time( time() ) . '|action|halt_script' . "\n";
				} else {
					// Return the status information since last retrieval.
					$return_status = '!' . $this->localize_time( time() ) . "|ping\n";
					
					// FILE SIZES FOR STATUS UPDATES---------------------
					if ( defined( 'PB_DEMO_MODE' ) ) {
						if ( $this->_options['backups'][$_POST['serial']]['type'] == 'full' ) {
							$demo_size = 14560000;
						} else {
							$demo_size = 14000;
						}
						$return_status .= '!' . $this->localize_time( time() ) . '|details|' . __('Temporary ZIP file size', 'it-l10n-backupbuddy') .' (simulated): ' . $this->format_size( $demo_size ) . "\n";;
						$return_status .= '!' . $this->localize_time( time() ) . '|action|archive_size^' . $this->format_size( $demo_size ) . "\n";
					}
					
					$temporary_zip_directory = $this->_options['backup_directory'] . 'temp_zip_' . $_POST['serial'] . '/';
					if ( file_exists( $temporary_zip_directory ) ) { // Temp zip file.
						$directory = opendir( $temporary_zip_directory );
						while( $file = readdir( $directory ) ) {
							if ( ( $file != '.' ) && ( $file != '..' ) ) {
								$stats = stat( $temporary_zip_directory . $file );
								$return_status .= '!' . $this->localize_time( time() ) . '|details|' . __('Temporary ZIP file size', 'it-l10n-backupbuddy') .': ' . $this->format_size( $stats['size'] ) . "\n";;
								$return_status .= '!' . $this->localize_time( time() ) . '|action|archive_size^' . $this->format_size( $stats['size'] ) . "\n";
							}
						}
						closedir( $directory );
						unset( $directory );
					}
					if( file_exists( $this->_options['backups'][$_POST['serial']]['archive_file'] ) ) { // Final zip file.
						$stats = stat( $this->_options['backups'][$_POST['serial']]['archive_file'] );
						$return_status .= '!' . $this->localize_time( time() ) . '|details|' . __('Final ZIP file size', 'it-l10n-backupbuddy') . ': ' . $this->format_size( $stats['size'] ) . "\n";;
						$return_status .= '!' . $this->localize_time( time() ) . '|action|archive_size^' . $this->format_size( $stats['size'] ) . "\n";
					}
					// END FILE SIZES FOR STATUS UPDATES-----------------
					
					$status_file = $this->_options['backup_directory'] . 'temp_status_' . $_POST['serial'] . '.txt';
					if ( file_exists( $status_file ) ) {
						$file_array = file( $status_file );
						
						foreach( $file_array as $status_line ) {
							$return_status .= '!' . $status_line . "\n";
						}
						
						// Reset messages.
						file_put_contents( $this->_options['backup_directory'] . 'temp_status_' . $_POST['serial'] . '.txt', '' );
					}
					
					// Return messages.
					echo $return_status;
				}
			} elseif ( $_GET['actionb'] == 'process_backup' ) {
				
			}
			die();
		}
		
		
		// Handle manually sending remote destination files in the background cron process.
		function cron_remotesend( $destination_id, $file ) {
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			
			$this->log( 'Setting greedy script limits.' );
			$this->set_greedy_script_limits();
			
			$this->log( 'Launching remote send.' );
			$this->send_remote_destination( $destination_id, $file );
		}
		
		
		function cron_process_backup( $serial = 'blank' ) {
			$this->log( 'Processing cron backup...' );
			
			require_once( $this->_pluginPath . '/classes/backup.php' );
			$pluginbuddy_backupbuddy_backup = new pluginbuddy_backupbuddy_backup( $this );
			$pluginbuddy_backupbuddy_backup->process_backup( $serial );
		}
		
		
		function cron_process_scheduled_backup( $cron_id ) {
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			$this->log( 'cron_process_scheduled_backup: ' . $cron_id );
			
			$this->verify_backup_directory();
			
			if ( is_array( $this->_options['schedules'][$cron_id] ) ) {
				$this->_options['schedules'][$cron_id]['last_run'] = time(); // update last run time.
				$this->save();
				
				require_once( $this->_pluginPath . '/classes/backup.php' );
				$backup = new pluginbuddy_backupbuddy_backup( $this );
				
				// If any remote destinations are set then add these to the steps to perform after the backup.
				$post_backup_steps = array();
				$destinations = explode( '|', $this->_options['schedules'][$cron_id]['remote_destinations'] );
				foreach( $destinations as $destination ) {
					if ( isset( $destination ) && ( $destination != '' ) ) {
						array_push( $post_backup_steps, array(
															'function'		=>		'send_remote_destination',
															'args'			=>		array( $destination ),
														)
									);
					}
				}
				
				if ( $this->_options['schedules'][$cron_id]['delete_after'] == '1' ) {
					array_push( $post_backup_steps, array(
													'function'		=>		'post_remote_delete',
													'args'			=>		array(),
												)
							);
				}
				
				if ( $backup->start_backup_process( $this->_options['schedules'][$cron_id]['type'], 'scheduled', array(), $post_backup_steps, $this->_options['schedules'][$cron_id]['title'] ) !== true ) {
					error_log( 'FAILURE #4455484589 IN BACKUPBUDDY.' );
					echo __('Error #4564658344443: Backup failure', 'it-l10n-backupbuddy');
					echo $backup->get_errors();
				}
			}
			$this->log( 'Finished cron_process_scheduled_backup.' );
		}
		
		// Copy S3 backup to local backup directory
		function cron_process_s3_copy( $s3file, $accesskey, $secretkey, $bucket, $directory ) {
			$this->log( 'Setting greedy script limits.' );
			$this->set_greedy_script_limits();
			
			require_once( $this->_pluginPath . '/lib/s3/s3.php');
			$s3 = new S3( $accesskey, $secretkey);
			$s3->getObject($bucket, $directory . $s3file, ABSPATH . 'wp-content/uploads/backupbuddy_backups/' . $s3file );
		}
		
		// Copy Dropbox backup to local backup directory
		function cron_process_dropbox_copy( $destination_id, $file ) {
			//$this->load();
			
			$this->log( 'Setting greedy script limits.' );
			$this->set_greedy_script_limits();
			
			require_once( $this->_pluginPath . '/lib/dropbuddy/dropbuddy.php' );
			$dropbuddy = new pluginbuddy_dropbuddy( $this, $this->_options['remote_destinations'][$destination_id]['token'] );
			if ( $dropbuddy->authenticate() !== true ) {
				$this->log( 'Dropbox authentication failed in cron_process_dropbox_copy.' );
				return false;
			}
			
			$this->log( 'About to get object (the file) from Dropbox cron.' );
			file_put_contents( ABSPATH . 'wp-content/uploads/backupbuddy_backups/' . basename( $file ), $dropbuddy->get_file( $file ) );
		}
		
		// Copy Rackspace backup to local backup directory
		function cron_process_rackspace_copy( $rs_backup, $rs_username, $rs_api_key, $rs_container, $rs_path, $rs_server ) {
			$this->log( 'Setting greedy script limits.' );
			$this->set_greedy_script_limits();
			
			require_once( $this->_pluginPath . '/lib/rackspace/cloudfiles.php' );
			$auth = new CF_Authentication( $rs_username, $rs_api_key, NULL, $rs_server );
			$auth->authenticate();
			$conn = new CF_Connection( $auth );

			// Set container
			$container = $conn->get_container( $rs_container );
			
			// Get file from Rackspace
			$rsfile = $container->get_object( $rs_backup );
			$fso = fopen( ABSPATH . 'wp-content/uploads/backupbuddy_backups/' . $rs_backup, 'w' );
			$rsfile->stream($fso);
			fclose($fso);
		}
		
		
		// Copy FTP backup to local backup directory
		function cron_process_ftp_copy( $backup, $ftp_server, $ftp_username, $ftp_password, $ftp_directory ) {
			$this->log( 'Setting greedy script limits.' );
			$this->set_greedy_script_limits();
			
			// connect to server
			$conn_id = ftp_connect( $ftp_server ) or die( 'Could not connect to ' . $ftp_server );
			// login with username and password
			$login_result = ftp_login( $conn_id, $ftp_username, $ftp_password );
		
			// try to download $server_file and save to $local_file
			$local_file = ABSPATH . 'wp-content/uploads/backupbuddy_backups/' . $backup;
			if ( ftp_get( $conn_id, $local_file, $ftp_directory . $backup, FTP_BINARY ) ) {
			    echo __('Successfully written to', 'it-l10n-backupbuddy') ." $local_file\n";
			} else {
			    echo __('There was a problem', 'it-l10n-backupbuddy') . "\n";
			}
		
			// close this connection
			ftp_close( $conn_id );
		}
		
		
		// Cleanup final remaining bits post backup. Handled here so log file can be accessed by AJAX temporarily after backup.
		// Also called when finished_backup action is seen being sent to AJAX signalling we can clear it NOW since AJAX is done.
		// Also pre_backup() of backup.php schedules this 6 hours in the future of the backup in case of failure.
		function cron_final_cleanup( $serial ) {
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			
			$this->log( 'cron_final_cleanup started' );
			
			// Delete temporary data directory.
			if ( file_exists( $this->_options['backups'][$serial]['temp_directory'] ) ) {
				$this->delete_directory_recursive( $this->_options['backups'][$serial]['temp_directory'] );
			}
			
			// Delete temporary zip directory.
			if ( file_exists( $this->_options['backups'][$serial]['temporary_zip_directory'] ) ) {
				$this->delete_directory_recursive( $this->_options['backups'][$serial]['temporary_zip_directory'] );
			}
			
			// Delete status log text file.
			if ( file_exists( $this->_options['backup_directory'] . 'temp_status_' . $serial . '.txt' ) ) {
				unlink( $this->_options['backup_directory'] . 'temp_status_' . $serial. '.txt' );
			}
			
			// Cleaning up internal data structure.
			if ( !empty( $this->_options['backups'][$serial] ) && is_array( $this->_options['backups'][$serial] ) ) {
				unset( $this->_options['backups'][$serial] );
				$this->save();
			}
		}
		
		
		function send_remote_destination( $destination_id, $file ) {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				return false;
			}
			
			$destination = &$this->_options['remote_destinations'][$destination_id];
			
			if ( $destination['type'] == 's3' ) {
				$destination = array_merge( $this->_s3defaults, $destination ); // load defaults
				$response = $this->remote_send_s3( $destination['accesskey'], $destination['secretkey'], $destination['bucket'], $destination['directory'], $destination['ssl'], $file, $destination['archive_limit'] );
			} elseif ( $destination['type'] == 'dropbox' ) {
				$destination = array_merge( $this->_dropboxdefaults, $destination ); // load defaults
				$response = $this->remote_send_dropbox( $destination['token'], $destination['directory'], $file, $destination['archive_limit'] );
			} elseif ( $destination['type'] == 'rackspace' ) {
				$destination = array_merge( $this->_rackspacedefaults, $destination ); // load defaults
				$response = $this->remote_send_rackspace( $destination['username'], $destination['api_key'], $destination['container'], $file, $destination['archive_limit']. $rs['server'] );
			} elseif ( $destination['type'] == 'email' ) {
				$destination = array_merge( $this->_emaildefaults, $destination ); // load defaults
				$response = $this->remote_send_email( $destination['email'], $file );
			} elseif ( $destination['type'] == 'ftp' ) {
				$destination = array_merge( $this->_ftpdefaults, $destination ); // load defaults
				$response = $this->remote_send_ftp( $destination['address'], $destination['username'], $destination['password'], $destination['path'], $destination['ftps'], $file, $destination['archive_limit'] );
			} else {
				return false; // Invalid destination.
			}
			
			return $response;
		}
		
		
		// Generates an MD5 file hash for file integrity verification.
		function ajax_md5hash() {
			$this->ajax_header( true );
			
			echo '<div style="margin: 25px;">';
			echo '<h2>' , __('MD5 Checksum Hash', 'it-l10n-backupbuddy') , '</h2>';
			echo __('This is a string of characters that uniquely represents this file.  If this file is in any way manipulated then this string of characters will change.  This allows you to later verify that the file is intact and uncorrupted.  For instance you may verify the file after uploading it to a new location by making sure the MD5 checksum matches.', 'it-l10n-backupbuddy'),'<br /><br />';
			echo '<b>',__('MD5 Checksum', 'it-l10n-backupbuddy'),':</b><input type="text" size="40" value="' . md5_file( $this->_options['backup_directory'] . $_GET['file'] ) . '" />';
			echo '</div>';
			
			$this->ajax_footer();
			die();
		}
		
		function ajax_importbuddy_classic() {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				die( 'Access denied in demo mode.' );
			}
			
			$output = file_get_contents( dirname( __FILE__ ) . '/importbuddy.php' );
			if ( isset( $_GET['pass'] ) && !empty( $_GET['pass'] ) ) {
				$output = preg_replace('/#PASSWORD#/', $_GET['pass'], $output, 1 ); // Only replaces first instance.
			}
			$output = preg_replace('/#VERSION#/', $this->_version, $output, 1 ); // Only replaces first instance.
			
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: text/plain; name=importbuddy.php' );
			header( 'Content-Disposition: attachment; filename=importbuddy.php' );
			header( 'Expires: 0' );
			header( 'Content-Length: ' . strlen( $output ) );
			
			flush();
			
			echo $output;
			
			die();
		}
		
		function ajax_importbuddy() {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				die( 'Access denied in demo mode.' );
			}
			
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			$output = file_get_contents( dirname( __FILE__ ) . '/_importbuddy.php' );
			if ( $this->_options['import_password'] != '' ) {
				$output = preg_replace('/#PASSWORD#/', md5( $this->_options['import_password'] ), $output, 1 ); // Only replaces first instance.
			}
			$output = preg_replace('/#VERSION#/', $this->_version, $output, 1 ); // Only replaces first instance.
			
			// PACK IMPORTBUDDY
			$_packdata = array(
				// NO TRAILING OR PRECEEDING SLASHES!
				'importbuddy'						=>		'importbuddy',
				'images/working.gif'				=>		'importbuddy/images/working.gif',
				'lib/dbreplace'						=>		'importbuddy/lib/dbreplace',
				'lib/dbimport'						=>		'importbuddy/lib/dbimport',
				'lib/zipbuddy'						=>		'importbuddy/lib/zipbuddy',
				'classes/view_tools-server.php'		=>		'importbuddy/classes/view_tools-server.php',
				'classes/_get_backup_dat.php'		=>		'importbuddy/classes/_get_backup_dat.php',
				'classes/_migrate_database.php'		=>		'importbuddy/classes/_migrate_database.php',
			);
			
			$output .= "\n<?php /*\n###PACKDATA,BEGIN\n";
			foreach( $_packdata as $pack_source => $pack_destination ) {
				$pack_source = '/' . $pack_source;
				if ( is_dir( $this->_pluginPath . $pack_source ) ) {
					$files = $this->deepglob( $this->_pluginPath . $pack_source );
				} else {
					$files = array( $this->_pluginPath . $pack_source );
				}
				foreach( $files as $file ) {
					if ( is_file( $file ) ) {
						$source = str_replace( $this->_pluginPath, '', $file );
						$destination = $pack_destination . substr( $source, strlen( $pack_source ) );
						$output .= "###PACKDATA,FILE_START,{$source},{$destination}\n";
						$output .= base64_encode( file_get_contents( $file ) );
						$output .= "\n";
						$output .= "###PACKDATA,FILE_END,{$source},{$destination}\n";
					}
				}
			}
			$output .= "###PACKDATA,END\n*/";
			
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: text/plain; name=importbuddy.php' );
			header( 'Content-Disposition: attachment; filename=importbuddy.php' );
			header( 'Expires: 0' );
			header( 'Content-Length: ' . strlen( $output ) );
			
			flush();
			echo $output;
			flush();
			
			die();
		}
		
		
		function ajax_repairbuddy() {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				die( 'Access denied in demo mode.' );
			}
			
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			$output = file_get_contents( dirname( __FILE__ ) . '/_repairbuddy.php' );
			if ( $this->_options['repairbuddy_password'] != '' ) {
				$output = preg_replace('/#PASSWORD#/', md5( $this->_options['repairbuddy_password'] ), $output, 1 ); // Only replaces first instance.
			}
			$output = preg_replace('/#VERSION#/', $this->_version, $output, 1 ); // Only replaces first instance.
			
			// PACK IMPORTBUDDY
			$_packdata = array(
				// NO TRAILING OR PRECEEDING SLASHES!
				'repairbuddy'							=>		'repairbuddy',
				'lib/dbreplace'							=>		'repairbuddy/lib/dbreplace',
				'lib/zipbuddy'							=>		'repairbuddy/lib/zipbuddy',
				'classes/view_tools-database.php'		=>		'repairbuddy/modules/database_information/pages/view_tools-database.php',
				'classes/view_malware.php'				=>		'repairbuddy/modules/malware_scan/pages/view_malware.php',
				'classes/view_tools-permissions.php'	=>		'repairbuddy/modules/server_info/pages/view_tools-permissions.php',
				'classes/view_tools-server.php'			=>		'repairbuddy/modules/server_info/pages/view_tools-server.php',
				'images/buttons'						=>		'repairbuddy/images/buttons',
				'images/pluginbuddy.png'				=>		'repairbuddy/images/pluginbuddy.png',
				'images/pluginbuddy_tip.png'			=>		'repairbuddy/images/pluginbuddy_tip.png',
				'images/sucuri/3.png'					=>		'repairbuddy/images/sucuri/3.png',
			);
			
			$output .= "\n<?php /*\n###PACKDATA,BEGIN\n";
			foreach( $_packdata as $pack_source => $pack_destination ) {
				$pack_source = '/' . $pack_source;
				if ( is_dir( $this->_pluginPath . $pack_source ) ) {
					$files = $this->deepglob( $this->_pluginPath . $pack_source );
				} else {
					$files = array( $this->_pluginPath . $pack_source );
				}
				foreach( $files as $file ) {
					if ( is_file( $file ) ) {
						$source = str_replace( $this->_pluginPath, '', $file );
						$destination = $pack_destination . substr( $source, strlen( $pack_source ) );
						$output .= "###PACKDATA,FILE_START,{$source},{$destination}\n";
						$output .= base64_encode( file_get_contents( $file ) );
						$output .= "\n";
						$output .= "###PACKDATA,FILE_END,{$source},{$destination}\n";
					}
				}
			}
			$output .= "###PACKDATA,END\n*/";
			
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: text/plain; name=repairbuddy.php' );
			header( 'Content-Disposition: attachment; filename=repairbuddy.php' );
			header( 'Expires: 0' );
			header( 'Content-Length: ' . strlen( $output ) );
			
			flush();
			echo $output;
			flush();
			
			die();
		}
		
		
		function ajax_repairbuddy_reset() {
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			$this->_options['repairbuddy_password'] = '';
			$this->save();
			
			header('Location: ' . admin_url( 'admin.php' ) . '?page=pluginbuddy_backupbuddy-repairbuddy' );
		}
		
		
		function ajax_remotedestination() {
			$this->ajax_header( true );
			
			require_once( $this->_pluginPath . '/classes/admin.php' );
			$pluginbuddy_backupbuddy_admin = new pluginbuddy_backupbuddy_admin( $this );
			
			$pluginbuddy_backupbuddy_admin->ajax_remotedestination();
			
			$this->ajax_footer();
			die();
		}
		
		function ajax_remotetest() {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				die( 'Access denied in demo mode.' );
			}
			
			if ( $_POST['#type'] == 's3' ) {
				if ( true === ( $response = $this->test_s3( $_POST['#accesskey'], $_POST['#secretkey'], $_POST['#bucket'], $_POST['#directory'], $_POST['#ssl'] ) ) ) {
					echo __('Test completed successfully.', 'it-l10n-backupbuddy');
				} else {
					echo __('Failure', 'it-l10n-backupbuddy') . '; ' . $response;
				}
			} elseif ( $_POST['#type'] == 'rackspace' ) {
				if ( true === ( $response = $this->test_rackspace( $_POST['#username'], $_POST['#api_key'], $_POST['#container'], $_POST['#server'] ) ) ) {
					echo __('Test completed successfully.', 'it-l10n-backupbuddy');
				} else {
					echo __('Failure', 'it-l10n-backupbuddy') . '; ' . $response;
				}
			} elseif ( $_POST['#type'] == 'ftp' ) {
				if ( $_POST['#ftps'] == '0' ) {
					$ftp_type = 'ftp';
				} else {
					$ftp_type = 'ftps';
				}
				if ( true === ( $response = $this->test_ftp( $_POST['#address'], $_POST['#username'], $_POST['#password'], $_POST['#path'], $ftp_type ) ) ) {
					echo __('Test completed successfully.', 'it-l10n-backupbuddy');
				} else {
					echo __('Failure', 'it-l10n-backupbuddy') . '; ' . $response;
				}
			} else {
				echo __('Error #4343489. There is not an automated test available for this service at this time.', 'it-l10n-backupbuddy');
			}
			
			die();
		}
		
		
		function ajax_header( $js = false ) {
			echo '<head>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
			echo '<title>PluginBuddy</title>';
			wp_print_styles( 'global' );
			wp_print_styles( 'wp-admin' );
			wp_print_styles( 'colors-fresh' );
			wp_print_styles( 'colors-fresh' );
			
			echo '<link rel="stylesheet" href="' . $this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
			
			if ( $js === true ) {
				wp_enqueue_script( 'jquery' );
				wp_print_scripts( 'jquery' );
			}
			
			echo '<div style="padding: 5px;">';
		}
		
		
		function ajax_footer() {
			echo '</div>';
			echo '</head>';
			echo '</html>';
		}
		
		
		function ajax_filetree() {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				die( 'Access denied in demo mode.' );
			}
			
			$root = ABSPATH;
			$_POST['dir'] = urldecode( $_POST['dir'] );
			if( file_exists( $root . $_POST['dir'] ) ) {
				$files = scandir( $root . $_POST['dir'] );
				natcasesort( $files );
				if( count( $files ) > 2 ) { /* The 2 accounts for . and .. */
					echo '<ul class="jqueryFileTree" style="display: none;">';
					foreach( $files as $file ) {
						if( file_exists( $root . $_POST['dir'] . $file ) && ( $file != '.' ) && ( $file != '..' ) && ( is_dir( $root . $_POST['dir'] . $file ) ) ) {
							echo '<li class="directory collapsed"><a href="#" rel="' . htmlentities($_POST['dir'] . $file) . '/">' . htmlentities($file) . ' <img src="' . $this->_pluginURL . '/images/bullet_delete.png" style="vertical-align: -3px;" /></a></li>';
						}
					}
					echo '</ul>';
				} else {
					echo '<ul class="jqueryFileTree" style="display: none;">';
					echo '<li><a href="#" rel="' . htmlentities( $_POST['dir'] . 'NONE' ) . '"><i>Empty Directory ...</i></a></li>';
					echo '</ul>';
				}
			} else {
				echo 'Error #1127555. Unable to read site root.';
			}
			
			die();
		}
		
		//Removes a cron based on a hook name
		function delete_cron_hooks( $hooks = array(), $blog_id = 0 ) {
			die('OBSELETE delete_cron_hooks()');
			if ( defined( 'PB_DEMO_MODE' ) ) {
				die( 'Access denied in demo mode.' );
			}
			
			if ( is_multisite() && $blog_id != 0 ) {				
				switch_to_blog( $blog_id );
			}
			$crons = get_option( 'cron' );
			if ( !$crons ) return false;
			foreach ( $crons as $timestamp => $cron ) {
				foreach ( $hooks as $hook ) {
					if ( isset( $cron[ $hook ] ) ) {
						unset( $crons[ $timestamp ] );
					}
				}
			}

			$this->update_option( 'cron', $crons );
			
			
		} //end delete_cron
		
		function ajax_remotesend() {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				die( 'Access denied in demo mode.' );
			}
			
			wp_schedule_single_event( time(), 'pb_backupbuddy-cron_remotesend', array( $_POST['destination_id'], $this->_options['backup_directory'] . $_POST['file'] ) );
			spawn_cron( time() + 150 ); // Adds > 60 seconds to get around once per minute cron running limit.
			update_option( '_transient_doing_cron', 0 ); // Prevent cron-blocking for next item.
			
			echo 1;
			die();
		}
		
		
		function ajax_icicletree() {
			$this->log( 'Setting greedy script limits.' );
			$this->set_greedy_script_limits(); // Building the directory tree can take a bit.
			
			$response = $this->build_icicle( ABSPATH, ABSPATH, '', -1 );
			echo $response[0];
			die();
		}
		
		// $max_depth	int		Maximum depth of tree to display.  Npte that deeper depths are still traversed for size calculations.
		function build_icicle( $dir, $base, $icicle_json, $max_depth = 10, $depth_count = 0, $is_root = true ) {
			$bg_color = '005282';
			
			$depth_count++;
			$bg_color = dechex( hexdec( $bg_color ) - ( $depth_count * 15 ) );
			
			$icicle_json = '{' . "\n";
			
			$dir_name = $dir;
			$dir_name = str_replace( ABSPATH, '', $dir );
			$dir_name = str_replace( '\\', '/', $dir_name );
			
			$dir_size = 0;
			$sub = opendir( $dir );
			$has_children = false;
			while( $file = readdir( $sub ) ) {
				if ( ( $file == '.' ) || ( $file == '..' ) ) {
					// Do nothing.
				} elseif ( is_dir( $dir . '/' . $file ) ) {
					
					$dir_array = '';
					$response = $this->build_icicle( $dir . '/' . $file, $base, $dir_array, $max_depth, $depth_count, false );
					if ( ( $max_depth-1 > 0 ) || ( $max_depth == -1 ) ) { // Only adds to the visual tree if depth isnt exceeded.
						if ( $max_depth > 0 ) {
							$max_depth = $max_depth - 1;
						}
						
						if ( $has_children === false ) { // first loop add children section
							$icicle_json .= '"children": [' . "\n";
						} else {
							$icicle_json .= ',';
						}
						$icicle_json .= $response[0];
						
						$has_children = true;
					}
					$dir_size += $response[1];
					unset( $response );
					unset( $file );
					
					
				} else {
					$stats = stat( $dir . '/' . $file );
					$dir_size += $stats['size'];
					unset( $file );
				}
			}
			closedir( $sub );
			unset( $sub );
			
			if ( $has_children === true ) {
				$icicle_json .= ' ]' . "\n";
			}
			
			if ( $has_children === true ) {
				$icicle_json .= ',';
			}
			
			$icicle_json .= '"id": "node_' . str_replace( '/', ':', $dir_name ) . ': ^' . str_replace( ' ', '~', $this->format_size( $dir_size ) ) . '"' . "\n";
			
			$dir_name = str_replace( '/', '', strrchr( $dir_name, '/' ) );
			if ( $dir_name == '' ) { // Set root to be /.
				$dir_name = '/';
			}
			$icicle_json .= ', "name": "' . $dir_name . ' (' . $this->format_size( $dir_size ) . ')"' . "\n";
			
			$icicle_json .= ',"data": { "$dim": ' . ( $dir_size + 10 ) . ', "$color": "#' . str_pad( $bg_color, 6, '0', STR_PAD_LEFT ) . '" }' . "\n";
			$icicle_json .= '}';
			
			if ( $is_root !== true ) {
				//$icicle_json .= ',x';
			}
			
			return array( $icicle_json, $dir_size );
		}
		
		function remote_send_dropbox( $token, $directory, $file, $limit = 0 ) {
			$this->log( 'Starting Dropbox transfer.' );
			
			require_once( $this->_pluginPath . '/lib/dropbuddy/dropbuddy.php' );
			$dropbuddy = new pluginbuddy_dropbuddy( $this, $token );
			if ( $dropbuddy->authenticate() !== true ) {
				$this->log( 'Dropbox authentication failed in remote_send_dropbox.' );
				return false;
			}
			
			$this->log( 'About to put object (the file) to Dropbox cron.' );
			$status = $dropbuddy->put_file( $directory . '/' . basename( $file ), $file );
			//if ( $status['httpStatus'] != 200 ) {
			if ( $status === true ) {
				$this->log( 'SUCCESS sending to Dropbox!' );
			} else {
				$this->log( 'Dropbox file send FAILURE. HTTP Status: ' . $status['httpStatus'] . '; Body: ' . $status['body'], 'error' );
				return false;
			}
			
			
			
			// Start remote backup limit
			if ( $limit > 0 ) {
				$this->log( 'Dropbox file limit in place. Proceeding with enforcement.' );
				$meta_data = $dropbuddy->get_meta_data( $directory );
				
				// Create array of backups and organize by date
				$bkupprefix = $this->backup_prefix();
				
				$backups = array();
				foreach ( (array) $meta_data['contents'] as $file ) {
					// check if file is backup
					if ( ( strpos( $file['path'], 'backup-' . $bkupprefix . '-' ) !== FALSE ) ) {
						$backups[$file['path']] = strtotime( $file['modified'] );
					}
				}
				arsort($backups);
				
				if ( ( count( $backups ) ) > $limit ) {
					$this->log( 'Dropbox backup file count of `' . count( $backups ) . '` exceeds limit of `' . $limit . '`.' );
					$i = 0;
					$delete_fail_count = 0;
					foreach( $backups as $buname => $butime ) {
						$i++;
						if ( $i > $limit ) {
							if ( !$dropbuddy->delete( $buname ) ) { // Try to delete backup on Dropbox. Increment failure count if unable to.
								$this->log( 'Unable to delete excess Dropbox file: `' . $buname . '`' );
								$delete_fail_count++;
							}
						}
					}
					
					if ( $delete_fail_count !== 0 ) {
						$this->mail_error( sprintf( __('Dropbox remote limit could not delete %s backups.', 'it-l10n-backupbuddy'), $delete_fail_count) );
					}
				}
			} else {
				$this->log( 'No Dropbox file limit to enforce.' );
			}
			// End remote backup limit
			
			return true;
		}
		
		
		function remote_send_s3( $accesskey, $secretkey, $bucket, $directory = '', $ssl, $file, $limit = 0 ) {
			$this->log( 'Starting Amazon S3 transfer.' );
			
			require_once( dirname( __FILE__ ) . '/lib/s3/s3.php' );
			$s3 = new S3( $accesskey, $secretkey );
			
			if ( $ssl != '1' ) {
				S3::$useSSL = false;
			}
			
			$this->log( 'About to put bucket to Amazon S3 cron.' );
			$s3->putBucket( $bucket, S3::ACL_PRIVATE );
			$this->log( 'About to put object (the file) to Amazon S3 cron.' );
			if ( !empty( $directory ) ) {
				$directory = $directory . '/';
			}
			if ( true === ( $s3_response = $s3->putObject( S3::inputFile( $file ), $bucket, $directory . basename( $file ), S3::ACL_PRIVATE) ) ) {
				$this->log( 'SUCCESS sending to Amazon S3! Response: ' . $s3_response );
				
				// Start remote backup limit
				if ( $limit > 0 ) {
					$results = $s3->getBucket( $bucket );
					
					// Create array of backups and organize by date
					$bkupprefix = $this->backup_prefix();
					
					$backups = array();
					foreach( $results as $rekey => $reval ) {
						$pos = strpos( $rekey, $directory . 'backup-' . $bkupprefix . '-' );
						if ( $pos !== FALSE ) {
							$backups[$rekey] = $results[$rekey]['time'];
						}
					}
					arsort( $backups );
					
					
					if ( ( count( $backups ) ) > $limit ) {
						$i = 0;
						$delete_fail_count = 0;
						foreach( $backups as $buname => $butime ) {
							$i++;
							if ( $i > $limit ) {
								if ( !$s3->deleteObject( $bucket, $buname ) ) {
									$this->log( 'Unable to delete excess S3 file `' . $buname . '` in bucket `' . $bucket . '`.' );
									$delete_fail_count++;
								}
							}
						}
						if ( $delete_fail_count !== 0 ) {
							$this->mail_error( sprintf( __('Amazon S3 remote limit could not delete %s backups.', 'it-l10n-backupbuddy'),  $delete_fail_count ) );
						}
					}
				} else {
					$this->log( 'No S3 file limit to enforce.' );
				}
				// End remote backup limit
				
				return true;
			} else {
				$error_message = 'ERROR #9024: Connected to Amazon S3 but unable to put file. There is a problem with one of the following S3 settings: bucket, directory, or S3 permissions. Details:' . "\n\n" . $s3_response . "\n\n" . 'http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#9024';
				$this->mail_error( __( $error_message, 'it-l10n-backupbuddy') );
				$this->log( $error_message, 'error' );
				
				return false;
			}
		}
		
		
		function remote_send_rackspace( $rs_username, $rs_api_key, $rs_container, $rs_file, $limit = 0, $rs_server ) {
			$this->log( 'Starting Rackspace transfer.' );
			
			$rs_file = basename( $rs_file );
			
			require_once( $this->_pluginPath . '/lib/rackspace/cloudfiles.php' );
			$auth = new CF_Authentication( $rs_username, $rs_api_key );
			$auth->authenticate();
			$conn = new CF_Connection( $auth );

			// Set container
			$container = $conn->get_container($rs_container);
			
			$this->log( 'About to put object (the file) to Rackspace cron.' );
			
			// Create backup file
			$testbackup = $container->create_object( $rs_file );
			if ( $testbackup->load_from_filename( ABSPATH . 'wp-content/uploads/backupbuddy_backups/' . $rs_file ) ) {
				// Start remote backup limit
				if ( $limit > 0 ) {
					$bkupprefix = $this->backup_prefix();
					
					$results = $container->get_objects( 0, NULL, 'backup-' . $bkupprefix . '-' );
					// Create array of backups and organize by date
					$backups = array();
					foreach( $results as $backup ) {
						$backups[$backup->name] = $backup->last_modified;
					}
					arsort( $backups );
					
					if ( ( count( $backups ) ) > $limit ) {
						$i = 0;
						$delete_fail_count = 0;
						foreach( $backups as $buname => $butime ) {
							$i++;
							if ( $i > $limit ) {
								if ( !$container->delete_object( $buname ) ) {
									$this->log( 'Unable to delete excess Rackspace file `' . $buname . '`' );
									$delete_fail_count++;
								}
							}
						}
						
						if ( $delete_fail_count !== 0 ) {
							$this->mail_error( sprintf( __('Rackspace remote limit could not delete %s backups.', 'it-l10n-backupbuddy'), $delete_fail_count  ) );
						}
					}
				} else {
					$this->log( 'No Rackspace file limit to enforce.' );
				}
				// End remote backup limit
				
				return true;
			} else {
				$error_message = 'ERROR #9025: Connected to Rackspace but unable to put file. Verify Rackspace settings included Rackspace permissions, etc.' . "\n\n" . 'http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#9025';
				$this->mail_error( __( $error_message, 'it-l10n-backupbuddy') );
				$this->log( $error_message, 'error' );
				
				return false;
			}
		}
		
		
		function remote_send_ftp( $server, $username, $password, $path, $ftps, $file, $limit = 0 ) {
			$this->log( 'Starting remote send to FTP.' );
			
			$port = '21';
			if ( strstr( $server, ':' ) ) {
				$server_params = explode( ':', $server );
				
				$server = $server_params[0];
				$port = $server_params[1];
			}
			
			if ( $ftps == '1' ) {
				if ( function_exists( 'ftp_ssl_connect' ) ) {
					$conn_id = ftp_ssl_connect( $server, $port );
					if ( $conn_id === false ) {
						$this->log( 'Unable to connect to FTPS  (check address/FTPS support).', 'error' );
						return false;
					} else {
						$this->log( 'Connected to FTPs.' );
					}
				} else {
					$this->log( 'Your web server doesnt support FTPS in PHP.', 'error' );
					return false;
				}
			} else {
				if ( function_exists( 'ftp_connect' ) ) {
					$conn_id = ftp_connect( $server, $port );
					if ( $conn_id === false ) {
						$this->log( 'ERROR: Unable to connect to FTP (check address).', 'error' );
						return false;
					} else {
						$this->log( 'Connected to FTP.' );
					}
				} else {
					$this->log( 'Your web server doesnt support FTP in PHP.', 'error' );
					return false;
				}
			}
			
			$login_result = @ftp_login( $conn_id, $username, $password );
			if ( $login_result === false ) {
				$this->mail_error( 'ERROR #9011 ( http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#9011 ).  FTP/FTPs login failed on scheduled FTP.' );
				return false;
			} else {
				$this->log( 'Logged in. Sending backup via FTP/FTPs ...' );
			}
			
			$upload = ftp_put( $conn_id, $path . '/' . basename( $file ), $file, FTP_BINARY );
			if ( $upload === false ) {
				$this->mail_error( 'ERROR #9012 ( http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#9012 ).  FTP/FTPs file upload failed. Check file permissions & disk quota.' );
			} else {
				$this->log( 'Done uploading backup file to FTP/FTPs.' );
				
				// Start remote backup limit
				if ( $limit > 0 ) {
					// Get contents of the current directory
					$contents = ftp_nlist( $conn_id, $path );
					
					// Create array of backups
					$bkupprefix = $this->backup_prefix();
					
					$backups = array();
					foreach ( $contents as $backup ) {
						// check if file is backup
						$pos = strpos( $backup, 'backup-' . $bkupprefix . '-' );
						if ( $pos !== FALSE ) {
							array_push( $backups, $backup );
						}
					}
					$results = array_reverse( (array)$backups );
					
					if ( ( count( $results ) ) > $limit ) {
						$delete_fail_count = 0;
						$i = 0;
						foreach( $results as $backup ) {
							$i++;
							if ( $i > $limit ) {
								if ( !ftp_delete( $conn_id, $path . '/' . $backup ) ) {
									$this->log( 'Unable to delete excess FTP file `' . $path . '/' . $backup . '`.' );
									$delete_fail_count++;
								}
							}
						}
						if ( $delete_fail_count !== 0 ) {
							$this->mail_error( sprintf( __('FTP remote limit could not delete %s backups.', 'it-l10n-backupbuddy'), $delete_fail_count  ) );
						}
					}
				} else {
					$this->log( 'No FTP file limit to enforce.' );
				}
				// End remote backup limit
			}
			ftp_close( $conn_id );
			
			return true;
		}
		
		
		function remote_send_email( $email, $file ) {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				return;
			}
			
			error_log( 'sending ' . $file . ' to ' . $email . '!' );
			$this->log( 'Sending remote email.' );
			$headers = 'From: BackupBuddy <' . get_option('admin_email') . '>' . "\r\n\\";
			wp_mail( $email, 'BackupBuddy Backup', 'BackupBuddy backup for ' . site_url(), $headers, $file );
			$this->log( 'Sent remote email.' );
		}
		
		
		function test_s3( $accesskey, $secretkey, $bucket, $directory = '', $ssl ) {
			if ( empty( $accesskey ) || empty( $secretkey ) || empty( $bucket ) ) {
				return __('Missing one or more required fields.', 'it-l10n-backupbuddy');
			}
			
			$bucket_requirements = __( "Your bucket name must meet certain criteria. It must fulfill the following: \n\n Characters may be lowercase letters, numbers, periods (.), and dashes (-). \n Must start with a number or letter. \n Must be between 3 and 63 characters long. \n Must not be formatted as an IP address (e.g., 192.168.5.4). \n Should not contain underscores (_). \n Should be between 3 and 63 characters long. \n Should not end with a dash. \n Cannot contain two, adjacent periods. \n Cannot contain dashes next to periods.", 'it-l10n-backupbuddy' );
			if ( preg_match( "/^[a-z0-9][a-z0-9\-\.]*(?<!-)$/i", $bucket ) == 0 ) { // Starts with a-z or 0-9; middle is a-z, 0-9, -, or .; cannot end in a dash.
				return __( 'Your bucket contains a period next to a dash.', 'it-l10n-backupbuddy' ) . ' ' . $bucket_requirements;
			}
			if ( ( strlen( $bucket ) < 3 ) || ( strlen( $bucket ) > 63 ) ) { // Must be between 3 and 63 characters long
				return __( 'Your bucket must be between 3 and 63 characters long.', 'it-l10n-backupbuddy' ) . ' ' . $bucket_requirements;
			}
			if ( ( strstr( $bucket, '.-' ) !== false ) || ( strstr( $bucket, '-.' ) !== false ) || ( strstr( $bucket, '..' ) !== false ) ) { // Bucket names cannot contain dashes next to periods (e.g., "my-.bucket.com" and "my.-bucket" are invalid)
				return __( 'Your bucket contains a period next to a dash.', 'it-l10n-backupbuddy' ) . ' ' . $bucket_requirements;
			}
			
			require_once( dirname( __FILE__ ) . '/lib/s3/s3.php' );
			$s3 = new S3( $accesskey, $secretkey );
			
			if ( $ssl != '1' ) {
				S3::$useSSL = false;
			}
			
			if ( $s3->getBucketLocation( $bucket ) === false ) { // Easy way to see if bucket already exists.
				$s3->putBucket( $bucket, S3::ACL_PRIVATE );
			}
			
			if ( !empty( $directory ) ) {
				$directory = $directory . '/';
			}
			if ( $s3->putObject( __('Upload test for BackupBuddy for Amazon S3', 'it-l10n-backupbuddy'), $bucket, $directory . 'backupbuddy.txt', S3::ACL_PRIVATE) ) {
				// Success... just delete temp test file later...
			} else {
				return __('Unable to upload. Verify your keys, bucket name, and account permissions.', 'it-l10n-backupbuddy');
			}
			
			if ( ! S3::deleteObject( $bucket, $directory . 'backupbuddy.txt' ) ) {
				return __('Partial success. Could not delete temp file.', 'it-l10n-backupbuddy');
			}
			
			return true; // Success!
		}
		
		function test_rackspace( $rs_username, $rs_api_key, $rs_container, $rs_server ) {
			if ( empty( $rs_username ) || empty( $rs_api_key ) || empty( $rs_container ) ) {
				return __('Missing one or more required fields.', 'it-l10n-backupbuddy');
			}
			require_once($this->_pluginPath . '/lib/rackspace/cloudfiles.php');
			$auth = new CF_Authentication( $rs_username, $rs_api_key, NULL, $rs_server );
			if ( !$auth->authenticate() ) {
				return __('Unable to authenticate. Verify your username/api key.', 'it-l10n-backupbuddy');
			}

			$conn = new CF_Connection( $auth );

			// Set container
			$container = @$conn->get_container( $rs_container ); // returns object on success, string error message on failure.
			if ( !is_object( $container ) ) {
				return __( 'There was a problem selecting the container:', 'it-l10n-backupbuddy' ) . ' ' . $container;
			}
			// Create test file
			$testbackup = @$container->create_object( 'backupbuddytest.txt' );
			if ( !$testbackup->load_from_filename( $this->_pluginPath . '/readme.txt') ) {
				return __('BackupBuddy was not able to write the test file.', 'it-l10n-backupbuddy');
			}
			
			// Delete test file from Rackspace
			if ( !$container->delete_object( 'backupbuddytest.txt' ) ) {
				return __('Unable to delete file from container.', 'it-l10n-backupbuddy');
			}
			
			return true; // Success
		}

		function test_ftp( $server, $username, $password, $path, $type = 'ftp' ) {
			if ( ( $server == '' ) || ( $username == '' ) || ( $password == '' ) ) {
				return __('Missing required input.', 'it-l10n-backupbuddy');
			}
			
			$port = '21';
			if ( strstr( $server, ':' ) ) {
				$server_params = explode( ':', $server );
				
				$server = $server_params[0];
				$port = $server_params[1];
			}
			
			if ( $type == 'ftp' ) {
				$conn_id = @ftp_connect( $server, $port );
				if ( $conn_id === false ) {
					return __('Unable to connect to FTP (check address).', 'it-l10n-backupbuddy');
				}
			} else {
				if ( function_exists( 'ftp_ssl_connect' ) ) {
					$conn_id = @ftp_ssl_connect( $server, $port );
					if ( $conn_id === false ) {
						return __('Destination server does not support FTPS?', 'it-l10n-backupbuddy');
					}
				} else {
					return __('Your web server doesnt support FTPS.', 'it-l10n-backupbuddy');
				}
			}
			
			$login_result = @ftp_login( $conn_id, $username, $password );
			
			if ( ( !$conn_id ) || ( !$login_result ) ) {
			   return __('Unable to login. Bad user/pass.', 'it-l10n-backupbuddy');
			} else {
				$tmp = tmpfile(); // Write tempory text file to stream.
				fwrite( $tmp, 'Upload test for BackupBuddy' );
				rewind( $tmp );
				$upload = @ftp_fput( $conn_id, $path . '/backupbuddy.txt', $tmp, FTP_BINARY );
				fclose( $tmp );
				
				if ( !$upload ) {
					return __('Failure uploading. Check path & permissions.', 'it-l10n-backupbuddy');
				} else {
					ftp_delete( $conn_id, $path . '/backupbuddy.txt' );
				}
			}
			@ftp_close($conn_id);
			
			return true; // Success if we got this far.
		}
		
		
		function mail_error( $message ) {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				return;
			}
			
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			
			$email = $this->_options['email_notify_error'];
			if ( !empty( $email ) ) {
				wp_mail( $email, "BackupBuddy Status - Error", "An error occurred with BackupBuddy v" . $this->plugin_info( 'version' ) . " on " . date(DATE_RFC822) . " for the site ". site_url() . ".  The error is displayed below:\r\n\r\n".$message, 'From: '.$email."\r\n".'Reply-To: '.get_option('admin_email')."\r\n");
			}
		}
		
		function mail_notify_manual( $message ) {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				return;
			}
			
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			
			$email = $this->_options['email_notify_manual'];
			if ( !empty( $email ) ) {
				wp_mail( $email, "BackupBuddy Status - Manual Backup", "A manual backup occurred with BackupBuddy v" . $this->plugin_info( 'version' ) . " on " . date(DATE_RFC822) . " for the site ". site_url() . ".  The notice is displayed below:\r\n\r\n".$message, 'From: '.$email."\r\n".'Reply-To: '.get_option('admin_email')."\r\n");
			}
		}
		
		function mail_notify_scheduled( $message ) {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				return;
			}
			
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			
			$email = $this->_options['email_notify_scheduled'];
			if ( !empty( $email ) ) {
				wp_mail( $email, "BackupBuddy Status - Scheduled Backup", "A scheduled backup occurred with BackupBuddy v" . $this->plugin_info( 'version' ) . " on " . date(DATE_RFC822) . " for the site ". site_url() . ".  The notice is displayed below:\r\n\r\n".$message, 'From: '.$email."\r\n".'Reply-To: '.get_option('admin_email')."\r\n");
			}
		}
		
		
		function dir_size( $dir, $base, &$dir_array ) {
			if( !is_dir( $dir ) ) {
				return 0;
			}
			
			$ret = 0;
			$sub = opendir( $dir );
			while( $file = readdir( $sub ) ) {
				if ( ( $file == '.' ) || ( $file == '..' ) ) {
					// Do nothing.
				} elseif ( is_dir( $dir . '/' . $file ) ) {
					$this_size = $this->dir_size( $dir . '/' . $file, $base, $dir_array );
					$dir_array[ str_replace( $base, '', $dir . '/' . $file ) ] = ( $this_size / 1048576 );
					$ret += $this_size;
					unset( $file );
				} else {
					$stats = stat( $dir . '/' . $file );
					$ret += $stats['size'];
					unset( $file );
				}
			}
			closedir( $sub );
			unset( $sub );
			return $ret;
		}
		
		
		function filter_plugin_row_meta( $plugin_meta, $plugin_file ) {
			if ( strstr( $plugin_file, strtolower( $this->_name ) ) ) {
				$plugin_meta[2] = '<a title="Visit plugin site" href="http://pluginbuddy.com/backupbuddy/">' . __('Visit PluginBuddy.com', 'it-l10n-backupbuddy') . '</a>';
				return $plugin_meta;
			} else {
				return $plugin_meta;
			}
		}
		
		function filter_cron_add_schedules( $schedules = array() ) {
			$schedules['weekly'] = array( 'interval' => 604800, 'display' => 'Once Weekly' );
			$schedules['twicemonthly'] = array( 'interval' => 1296000, 'display' => 'Twice Monthly' );
			$schedules['monthly'] = array( 'interval' => 2592000, 'display' => 'Once Monthly' );
			return $schedules;
		}
		
		// TODO: coming soon.
		// Run through potential orphaned files, data structures, etc caused by failed backups and clean things up.
		// Also verifies anti-directory browsing files exists, etc.
		function periodic_cleanup( $backup_age_limit = 43200 ) {
			if ( !isset( $this->_options ) ) {
				$this->load();
			}
			
			// TODO: Check for orphaned .gz files in root from PCLZip.
			// TODO: Check for orphaned log files.
			
			// Check for orphaned backups in the data structure that havent been updates in 12+ hours & cleanup after them.
			foreach( (array)$this->_options['backups'] as $backup_serial => $backup ) {
				if ( ( time() - $backup['updated_time'] ) > $backup_age_limit ) { // If more than 12 hours has passed...
					$this->log( 'Cleaned up stale backup `' . $backup_serial . '`.' );
					$this->cron_final_cleanup( $backup_serial );
				}
			}
			
			// Verify existance of anti-directory browsing files.
			require_once( $this->_pluginPath . '/classes/backup.php' );
			$pluginbuddy_backupbuddy_backup = new pluginbuddy_backupbuddy_backup( $this );
			$pluginbuddy_backupbuddy_backup->anti_directory_browsing( $this->_options['backup_directory'] );
			
			// Remove any copy of importbuddy.php in root.
			if ( file_exists( ABSPATH . 'importbuddy.php' ) ) {
				$this->log( 'Unlinked importbuddy.php in root of site.' );
				unlink( ABSPATH . 'importbuddy.php' );
			}
		}
		
		
		function create_backup_directory() {
			if ( !file_exists( $this->_options['backup_directory'] ) ) {
				if ( $this->mkdir_recursive( $this->_options['backup_directory'] ) === false ) {
					$this->alert( __('Error: Unable to create backup storage directory', 'it-l10n-backupbuddy') . ' `' . $this->_options['backup_directory'] . '`. Please verify that proper write permissions are enabled to create this directory. You may also manually create this directory.  If you do so make sure to give permissions to allow writing backups into this directory.', true, '9022' );
					return false;
				}
			}
			return true;
		}
		
		
		/**
		 * versions_confirm()
		 *
		 * Check the version of an item and compare it to the minimum requirements BackupBuddy requires.
		 *
		 * $type		string		Optional. If left blank '' then all tests will be performed. Valid values: wordpress, php, ''.
		 * $notify		boolean		Optional. Whether or not to alert to the screen (and throw error to log) of a version issue.\
		 * @return		boolean		True if the selected type is a bad version
		 *
		 */
		function versions_confirm( $type = '', $notify = false ) {
			$bad_version = false;
			
			if ( ( $type == 'wordpress' ) || ( $type == '' ) ) {
				global $wp_version;
				if ( version_compare( $wp_version, $this->_wp_minimum, '<=' ) ) {
					if ( $notify === true ) {
						$this->alert( sprintf( __('ERROR: %1$s requires WordPress version %2$s or higher. You may experience unexpected behavior or complete failure in this environment. Please consider upgrading WordPress.', 'it-l10n-backupbuddy'), $this->_name, $this->_wp_minimum) );
						$this->log( 'Unsupported WordPress Version: ' . $wp_version , 'error' );
					}
					$bad_version = true;
				}
			}
			if ( ( $type == 'php' ) || ( $type == '' ) ) {
				if ( version_compare( PHP_VERSION, $this->_php_minimum, '<=' ) ) {
					if ( $notify === true ) {
						$this->alert( sprintf( __('ERROR: %1$s requires PHP version %2$s or higher. You may experience unexpected behavior or complete failure in this environment. Please consider upgrading PHP.', 'it-l10n-backupbuddy'), $this->_name, PHP_VERSION ) );
						$this->log( 'Unsupported PHP Version: ' . PHP_VERSION , 'error' );
					}
					$bad_version = true;
				}
			}
			
			return $bad_version;
		}
		
		// returns true on success, error message otherwise.
		function loopback_test() {
			$loopback_url = admin_url('admin-ajax.php');
			$response = wp_remote_get(
				$loopback_url,
				array(
					'method' => 'GET',
					'timeout' => 5, // 5 second delay. A loopback should be very fast.
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => null,
					'cookies' => array()
				)
			);
			
			if( is_wp_error( $response ) ) {
				return 'Error: ' . $response->get_error_message();
			} else {
				if ( $response['body'] == '-1' ) {
					return true;
				} else {
					return 'A loopback seemed to occur but the value was not correct.';
				}
			}
		}
		
		// used in backup.php
		function backup_prefix() {
			$siteurl = site_url();
			$siteurl = str_replace( 'http://', '', $siteurl );
			$siteurl = str_replace( 'https://', '', $siteurl );
			$siteurl = str_replace( '/', '_', $siteurl );
			$siteurl = str_replace( '\\', '_', $siteurl );
			$siteurl = str_replace( '.', '_', $siteurl );
			$siteurl = str_replace( ':', '_', $siteurl ); // Alternative port from 80 is stored in the site url.
			return $siteurl;
		}
		
		
		function format_size( $size ) {
			$sizes = array( ' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
			if ( $size == 0 ) {
				return( 'empty' );
			} else {
				return ( round( $size / pow( 1024, ( $i = floor( log( $size, 1024 ) ) ) ), $i > 1 ? 2 : 0) . $sizes[$i] );
			}
		}
		
		
		function format_date( $timestamp ) {
			return date( $this->_timestamp, $timestamp );
		}
		
		
		function localize_time( $timestamp ) {
			return $timestamp + ( get_option( 'gmt_offset' ) * 3600 );
		}
		
		function unlocalize_time( $timestamp ) {
			return $timestamp - ( get_option( 'gmt_offset' ) * 3600 );
		}
		
		
		// Accepts NON-localized timestamps.
		function time_ago( $timestamp ) {
			//return human_time_diff( $this->localize_time( $timestamp ), $this->localize_time( time() ) );
			return human_time_diff( $timestamp, time() );
		}
		
		// Human readable duration. Ex: 5 hours, 4 minutes, 43 seconds.
		function format_duration( $seconds ) {
			$time = time() - $seconds;
			
			$periods = array(__('second', 'it-l10n-backupbuddy'), 
							 __('minute', 'it-l10n-backupbuddy'), 
							 __('hour',   'it-l10n-backupbuddy'), 
							 __('day', 	  'it-l10n-backupbuddy'), 
							 __('week',   'it-l10n-backupbuddy'), 
							 __('month',  'it-l10n-backupbuddy'), 
							 __('year',   'it-l10n-backupbuddy'),
							 __('decade'. 'LION')
							 );
			$lengths = array('60','60','24','7','4.35','12','10');
			
			$now = time();
			
			$difference = $now - $time;
			$tense = __('ago', 'it-l10n-backupbuddy');
			
			
			for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
				$difference /= $lengths[$j];
			}
			
			$difference = round($difference);
			
			if($difference != 1) {
				$periods[$j].= "s";
			}
			
			return "$difference $periods[$j]";
		}
		
		/*
		Deletes a directory recursively including any files or subdirectories.
		*/
		function delete_directory_recursive( $dir ) {
			if ( defined( 'PB_DEMO_MODE' ) ) {
				return false;
			}
			
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
				if ( !$this->delete_directory_recursive( $dir . "/" . $item ) ) {
					chmod( $dir . "/" . $item, 0777 );
					if ( !$this->delete_directory_recursive( $dir . "/" . $item ) ) {
						return false;
					}
				}
			}
			return rmdir($dir);
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
			is_dir( dirname( $path ) ) || $this->mkdir_recursive( dirname( $path ) );
			if ( is_dir( $path ) === true ) {
				return true;
			} else {
				return @mkdir( $path );
			}
		}
		
		
		/**
		 *	deepglob()
		 *
		 *	Like the glob() function except walks down into paths to create a full listing of all results in the directory and all subdirectories.
		 *	This is essentially a recursive glob() although it does not use recursion to perform this.
		 *
		 *	@param		string		$dir		Path to pass to glob and walk through.
		 *	@return		array					Returns array of all matches found.
		 *
		 */
		function deepglob( $dir ) {
			$items = glob( $dir . '/*' );
			
			for ( $i = 0; $i < count( $items ); $i++ ) {
				if ( is_dir( $items[$i] ) ) {
					$add = glob( $items[$i] . '/*' );
					$items = array_merge( $items, $add );
				}
			}
			
			return $items;
		}
		//Register the updater version
		function upgrader_register() {
			$GLOBALS['pb_classes_upgrade_registration_list'][$this->_var] = $this->_updater;
		} //end register_upgrader
		//Select the greatest version
		function upgrader_select() {
			if ( !isset( $GLOBALS[ 'pb_classes_upgrade_registration_list' ] ) ) {
				//Fallback - Just include this class
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
				return;
			}
			//Go through each global and find the highest updater version and the plugin slug
			$updater_version = 0;
			$plugin_var = '';
			foreach ( $GLOBALS[ 'pb_classes_upgrade_registration_list' ] as $var => $version) {
				if ( version_compare( $version, $updater_version, '>=' ) ) {
					$updater_version = $version;
					$plugin_var = $var;
				}
			}
			//If the slugs match, load this version
			if ( $this->_var == $plugin_var ) {
				require_once( $this->_pluginPath . '/lib/updater/updater.php' );
			}
		} //end upgrader_select
		function upgrader_instantiate() {
			
			$pb_product = strtolower( $this->_var );
			$pb_product = str_replace( 'ithemes-', '', $pb_product );
			$pb_product = str_replace( 'pluginbuddy-', '', $pb_product );
			$pb_product = str_replace( 'pluginbuddy_', '', $pb_product );
			$pb_product = str_replace( 'pb_thumbsup', '', $pb_product );
			
			$args = array(
				'parent' => $this, 
				'remote_url' => 'http://updater2.ithemes.com/index.php',
				'version' => $this->_version,
				'plugin_slug' => $this->_var,
				'plugin_path' => plugin_basename( __FILE__ ),
				'plugin_url' => $this->_pluginURL,
				'product' => $pb_product,
				'time' => 43200,
				'return_format' => 'json',
				'method' => 'POST',
				'upgrade_action' => 'check' );
			$this->_pluginbuddy_upgrader = new iThemesPluginUpgrade( $args );

		} //end upgrader_instantiate
		
		
	} // End class
	
	 //define( 'PB_DEMO_MODE', true );
	$pluginbuddy_backupbuddy = new pluginbuddy_backupbuddy();
//}



?>