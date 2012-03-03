<?php
if ( !class_exists( "pluginbuddy_backupbuddy_admin" ) ) {
	class pluginbuddy_backupbuddy_admin {
		function pluginbuddy_backupbuddy_admin( &$parent ) {
			$this->_parent = &$parent;
			$this->_var = &$parent->_var;
			$this->_name = &$parent->_name;
			$this->_options = &$parent->_options;
			$this->_pluginPath = &$parent->_pluginPath;
			$this->_pluginURL = &$parent->_pluginURL;
			$this->_selfLink = &$parent->_selfLink;
			
			//If multisite, we'll want to backup from network admin area
			//Otherwise, run from the regular area
			//For multisite, add an admin_menu for backing up a regular site
			if ( is_multisite() && $this->is_network_activated() && !defined( 'PB_DEMO_MODE' ) ) {
				add_action( 'wp_ajax_export_ms_site', array( &$this, 'ajax_site_export' ) );
				add_action('network_admin_menu', array( &$this, 'admin_menu' ) ); // Add menu in network admin.
				//Also add a menu for the individual site for MS to MS
				add_action( 'admin_menu', array( &$this, 'site_admin_menu' ) ); //add menu to site admin
			} elseif ( is_multisite() && !$this->is_network_activated() && !defined( 'PB_DEMO_MODE' ) ) {
				//Since this is a multisite install, BackupBuddy should be network activated - Throw a warning
				add_action( 'all_admin_notices', array( &$this, 'network_warning' ) );
			} else {
				//Not a multisite network - Business as usual
				add_action('admin_menu', array( &$this, 'admin_menu' ) ); // Add menu in admin.
			}
			
			require_once( $this->_pluginPath . '/lib/zipbuddy/zipbuddy.php' );
			$this->_parent->_zipbuddy = new pluginbuddy_zipbuddy( $this->_options['backup_directory'] );
			
			add_action( 'admin_init', array( &$this, 'password_generator' ) );
		} //end constructor
		function password_generator() {
			//Called by admin_init action
			//View admin-ajax.php and it requires the admin_init action for ajax
			require_once( $this->_pluginPath . '/lib/passwords/passwords.php' );
			$pb_passwords = new pluginbuddy_passwords( array( 'pagehooks' => 'backupbuddy_page_pluginbuddy_backupbuddy-repairbuddy' ) );
		} //end password_generator
		function copy( $source, $destination, $args = array() ) {
			$default_args = array(
				'max_depth'    => 100,
				'folder_mode'  => 0755,
				'file_mode'    => 0744,
				'ignore_files' => array(),
			);
			$args = array_merge( $default_args, $args );
			
			$this->_copy( $source, $destination, $args );
		}
		function _copy( $source, $destination, $args, $depth = 0 ) {
			if ( $depth > $args['max_depth'] )
				return true;
				
			if ( in_array( basename( $source ), $args[ 'ignore_files' ] ) ) return true;
			
			if ( is_file( $source ) ) {
				if ( is_dir( $destination ) || preg_match( '|/$|', $destination ) ) {
					$destination = preg_replace( '|/+$|', '', $destination );
					
					$destination = "$destination/" . basename( $source );
				}
				
				if ( false === $this->mkdir( dirname( $destination ), $args['folder_mode'] ) )
					return false;
				
				if ( false === @copy( $source, $destination ) )
					return false;
				
				@chmod( $destination, $args['file_mode'] );
				
				return true;
			}
			else if ( is_dir( $source ) || preg_match( '|/\*$|', $source ) ) {
				if ( preg_match( '|/\*$|', $source ) )
					$source = preg_replace( '|/\*$|', '', $source );
				else if ( preg_match( '|/$|', $destination ) )
					$destination = $destination . basename( $source );
				
				$destination = preg_replace( '|/$|', '', $destination );
				
				$files = array_diff( array_merge( glob( $source . '/.*' ), glob( $source . '/*' ) ), array( $source . '/.', $source . '/..' ) );
				
				if ( false === $this->mkdir( $destination, $args['folder_mode'] ) )
					return false;
				
				$result = true;
				
				foreach ( (array) $files as $file ) {
					if ( false === $this->_copy( $file, "$destination/", $args, $depth + 1 ) )
						$result = false;
				}
				
				return $result;
			}
			
			return false;
		}
		function mkdir( $directory, $args = array() ) {
			if ( is_dir( $directory ) )
				return true;
			if ( is_file( $directory ) )
				return false;
			
			
			if ( is_int( $args ) )
				$args = array( 'permissions' => $args );
			if ( is_bool( $args ) )
				$args = array( 'create_index' => false );
			
			$default_args = array(
				'permissions'	=> 0755,
				'create_index'	=> true,
			);
			$args = array_merge( $default_args, $args );
			
			
			if ( ! is_dir( dirname( $directory ) ) ) {
				if ( false === $this->mkdir( dirname( $directory ), $args ) )
					return false;
			}
			
			if ( false === @mkdir( $directory, $args['permissions'] ) )
				return false;
			
			if ( true === $args['create_index'] )
				$this->write( "$directory/index.php", '<?php // Silence is golden.' );
			
			return true;
		}
		function write( $path, $content, $args = array() ) {
			if ( is_bool( $args ) )
				$args = array( 'append' => $args );
			else if ( is_int( $args ) )
				$args = array( 'permissions' => $args );
			else if ( ! is_array( $args ) )
				$args = array();
			
			$default_args = array(
				'append'      => false,
				'permissions' => 0644,
			);
			$args = array_merge( $default_args, $args );
			
			
			$mode = ( false === $args['append'] ) ? 'w' : 'a';
			
			if ( ! is_dir( dirname( $path ) ) ) {
				$this->mkdir( dirname( $path ) );
				
				if ( ! is_dir( dirname( $path ) ) )
					return false;
			}
			
			$created = ! is_file( $path );
			
			if ( false === ( $handle = fopen( $path, $mode ) ) )
				return false;
			
			$result = fwrite( $handle, $content );
			fclose( $handle );
			
			if ( false === $result )
				return false;
			
			if ( ( true === $created ) && is_int( $args['append'] ) )
				@chmod( $path, $args['append'] );
			
			return true;
		}
		
		function ajax_site_export() {
			$this->_parent->set_greedy_script_limits();
			
			global $wpdb, $current_site, $current_blog;
			$blog_id = absint( $current_blog->blog_id );
			check_ajax_referer( 'export-site' );
			$return_args = array(
				'completion' => 0,
				'message' => '',
				'errors' => false
			);
			require_once( $this->_parent->_pluginPath . '/lib/zipbuddy/zipbuddy.php' );
			$step = absint( $_POST[ 'step' ] );
			$zip_id = sanitize_text_field( $_POST[ 'zip_id' ] );
			$upload_dir = wp_upload_dir();
			$original_upload_base_dir = $upload_dir[ 'basedir' ];
			$extract_files_to = $original_upload_base_dir . '/' . $zip_id;
			
			switch( $step ) {
				case 1:
					//Step 1 - Download a copy of WordPress
					$wp_url = 'http://wordpress.org/latest.zip';
					$wp_file = download_url( $wp_url );
					if ( is_wp_error( $wp_file ) ) {
						$return_args[ 'errors' ] = true;
						$return_args[ 'message' ] = $wp_file->get_error_message();
						$this->log( 'MS-MS Step 1 - ' . $wp_file->get_error_message(), 'error');
					} else {
						$return_args[ 'message' ] = __( 'WordPress Successfully Downloaded - Extracting WordPress to a Temporary Directory', 'it-l10n-backupbuddy' );
						$return_args[ 'completion' ] = 5;
						$return_args[ 'wp_file' ] = $wp_file;
					}
					break;
				case 2:
					//Step 2 - Extract WP into a separate directory
					$wp_file = file_exists( $_POST[ 'wp_file' ] ) ? $_POST[ 'wp_file' ] : false;
					if ( !$wp_file ) {
						$return_args[ 'errors' ] = true;
						$return_args[ 'message' ] = __( 'WordPress file could not be located', 'it-l10n-backupbuddy' );
						$this->log( 'MS-MS Step 2 - ' . __( 'WordPress file could not be located', 'it-l10n-backupbuddy' ), 'error');
					} else {
						$return_args[ 'debug' ] = $extract_files_to;
						$return_args[ 'message' ] = __( 'WordPress extracted - Creating installation file', 'it-l10n-backupbuddy' );
						$return_args[ 'completion' ] = 15;	
						$zipbuddy = new pluginbuddy_zipbuddy( $extract_files_to );
						ob_start();
						//todo - upload to wp-contennt
						//when merging, update extract_files to use new importbuddy version of zip / unzip functionality
						$zipbuddy->unzip( $wp_file, $extract_files_to );
						$return_args[ 'debug' ] = ob_get_clean();
						unlink( $wp_file );
					}
					break;
				case 3:
					//Step 3 - Create new WP-Config File
					$wp_config_path = $extract_files_to . '/wordpress/';
					if ( !file_exists( $wp_config_path ) ) {
						$return_args[ 'errors' ] = true;
						$return_args[ 'message' ] = __( 'Temporary WordPress installation not found', 'it-l10n-backupbuddy' );
						$return_args[ 'debug' ] = $wp_config_path;
						$this->log( 'MS-MS Step 3 - ' . __( 'Temporary WordPress installation file could not be located', 'it-l10n-backupbuddy' ) . $wp_config_path, 'error');

					} else {
						$to_file = "<?php\n";
						$to_file .= sprintf( "define( 'DB_NAME', '%s' );\n", '' );
						$to_file .= sprintf( "define( 'DB_USER', '%s' );\n", '' );
						$to_file .= sprintf( "define( 'DB_PASSWORD', '%s' );\n", '' );
						$to_file .= sprintf( "define( 'DB_HOST', '%s' );\n", '' );
						$charset = defined( 'DB_CHARSET' ) ? DB_CHARSET : '';
						$collate = defined( 'DB_COLLATE' ) ? DB_COLLATE : '';
						$to_file .= sprintf( "define( 'DB_CHARSET', '%s' );\n", $charset );
						$to_file .= sprintf( "define( 'DB_COLLATE', '%s' );\n", $collate );
						
						//Attempt to remotely retrieve salts
						$salts = wp_remote_get( 'https://api.wordpress.org/secret-key/1.1/salt/' );
						if ( !is_wp_error( $salts ) ) {
							$to_file .= wp_remote_retrieve_body( $salts ) . "\n";
						}
						$to_file .= sprintf( "define( 'WPLANG', '%s' );\n", WPLANG );
						$to_file .= sprintf( '$table_prefix = \'%s\';' . "\n", 'bbms' . $blog_id . '_' );
						
						$to_file .= "if ( !defined('ABSPATH') ) { \n\tdefine('ABSPATH', dirname(__FILE__) . '/'); }";
						$to_file .= "/** Sets up WordPress vars and included files. */\n
						require_once(ABSPATH . 'wp-settings.php');";
						$to_file .= "\n?>";
						$wp_config_path .= 'wp-config.php';
						
						//Create the file, save, and close
						$file_handle = fopen( $wp_config_path, 'w' );
						fwrite( $file_handle, $to_file );
						fclose( $file_handle );
						
						//Prepare the response
						$return_args[ 'debug' ] = $extract_files_to;
						$return_args[ 'message' ] = __( 'Installation file created - Copying Over Plugins.', 'it-l10n-backupbuddy' );
						$return_args[ 'completion' ] = 25;	
					}
					break;
				case 4:
					//Step 4 - Copy over plugins
					//Move over plugins
					$plugin_items = get_transient( $zip_id );
					//Populate $items_to_copy for all plugins to copy over
					if ( is_array( $plugin_items ) ) {
						$items_to_copy = array();
						//Get content directories by using this plugin as a base
						$content_dir = $dropin_plugins_dir = dirname( dirname( dirname( rtrim( plugin_dir_path(__FILE__), '/' ) ) ) );
						$mu_plugins_dir = $content_dir . '/mu-plugins';
						$plugins_dir = $content_dir . '/plugins';
						
						//Get the special plugins (mu, dropins, network activated)
						foreach ( $plugin_items as $type => $plugins ) {
							foreach ( $plugins as $plugin ) {
								if ( $type == 'mu' ) {
									$items_to_copy[ $plugin ] = $mu_plugins_dir . '/' . $plugin;
								} elseif ( $type == 'dropin' ) {
									$items_to_copy[ $plugin ] = $dropin_plugins_dir . '/' . $plugin;
								} elseif ( $type == 'network' || $type == 'site' ) {
									//Determine if we're a folder-based plugin, or a file-based plugin (such as hello.php)
									$plugin_path = dirname( $plugins_dir . '/' . $plugin );
									if ( basename( $plugin_path ) == 'plugins' ) {
										$plugin_path = $plugins_dir . '/' . $plugin;
									}
									$items_to_copy[ basename( $plugin_path ) ] = $plugin_path;		
								}
							} //end foreach $plugins
						} //end foreach special plugins
						
						
						//Copy the files over
						$wp_dir = '';
						if ( count( $items_to_copy ) > 0 ) {
							$wp_dir = $extract_files_to . '/wordpress/';
							$wp_plugin_dir = $wp_dir . '/wp-content/plugins/';
							foreach ( $items_to_copy as $file => $original_destination ) {
								if ( file_exists( $original_destination ) && file_exists( $wp_plugin_dir ) ) {
									$this->copy( $original_destination, $wp_plugin_dir . $file ); 
								}
							}
						}
						//Prepare the response
						$return_args[ 'debug' ] = $wp_dir;
						$return_args[ 'message' ] = __( 'Plugins copied over.  Now copying over the active theme.', 'it-l10n-backupbuddy' );
						$return_args[ 'completion' ] = 50;	

					} else {
						//Nothing has technically failed at this point - There just aren't any plugins to copy over
						$return_args[ 'message' ] = __( 'Plugins copied over.  Now copying over the active theme.', 'it-l10n-backupbuddy' );
						$return_args[ 'completion' ] = 50;	
						$this->log( 'MS-MS Step 4 - ' . __( 'No plugins to copy over', 'it-l10n-backupbuddy' ), 'error');
					}
					break;
				case 5:
					//Step 5 - Copy over themes
					$current_theme = current_theme_info();
					$template_dir = $current_theme->template_dir;
					$stylesheet_dir = $current_theme->stylesheet_dir;
					//If $template_dir and $stylesheet_dir don't match, that means we have a child theme and need to copy over the parent also
					$items_to_copy = array();
					$items_to_copy[ basename( $template_dir ) ] = $template_dir;
					if ( $template_dir != $stylesheet_dir ) {
						$items_to_copy[ basename( $stylesheet_dir ) ] = $stylesheet_dir;
					}
					//Copy the files over
					if ( count( $items_to_copy ) > 0 ) {
						$wp_dir = $extract_files_to . '/wordpress/';
						$wp_theme_dir = $wp_dir . '/wp-content/themes/';
						foreach ( $items_to_copy as $file => $original_destination ) {
							if ( file_exists( $original_destination ) && file_exists( $wp_theme_dir ) ) {
								$this->copy( $original_destination, $wp_theme_dir . $file ); 
							}
						}
					}
					$return_args[ 'debug' ] = $wp_dir;
					$return_args[ 'message' ] = __( 'Theme has been copied over.  Now copying over media files.', 'it-l10n-backupbuddy' );
					$return_args[ 'completion' ] = 60;	
					break;
				case 6:
					//Step 6 - Copy over media/upload files
					$upload_dir = wp_upload_dir();
					$original_upload_base_dir = $upload_dir[ 'basedir' ];
					$destination_upload_base_dir = $extract_files_to . '/wordpress/wp-content/uploads';
					$this->copy( $original_upload_base_dir, $destination_upload_base_dir, array( 'ignore_files' => array( $zip_id ) ) );
					$return_args[ 'debug' ] = $destination_upload_base_dir;
					$return_args[ 'message' ] = __( 'Media has been copied over.  Now preparing the export.', 'it-l10n-backupbuddy' );
					$return_args[ 'completion' ] = 70;
					break;
				case 7:
					//Step 7 - Create Users Table
					//Get users of current site
					global $wpdb, $current_blog;
					$user_args = array(
						'blog_id' => $current_blog->blog_id
					);
					$users = get_users( $user_args );
					
					//Copy over the user and usermeta tables
					$found_tables = array( $wpdb->users, $wpdb->usermeta );
					$user_tablename = $usermeta_tablename = '';
					$sql_to_execute = array();
					if ( $found_tables ) {
						foreach ( $found_tables as $index => $tablename) {
							$new_table = '';
							if ( strstr( $tablename, 'users' ) ) {
								$new_table = $user_tablename = $wpdb->prefix . 'users';
							}
							if ( strstr( $tablename, 'usermeta' ) ) {
								$new_table = $usermeta_tablename = $wpdb->prefix . 'usermeta';
							}
							$sql_to_execute[] = sprintf( 'CREATE TABLE %1$s LIKE %2$s', $new_table, $tablename );
							$sql_to_execute[] = sprintf( 'INSERT %1$s SELECT * FROM %2$s', $new_table, $tablename );
							//Couldn't use $wpdb->prepare here because sql doesn't like quotes around the tablenames
						}
					}
					
					//Tables have been created, now execute a query to remove the users and user data that doesn't matter
					$users_to_capture = array();
					if ( $users ) {
						foreach ( $users as $user ) {
							array_push( $users_to_capture, $user->ID );
						}
					}
					$users_to_capture = implode( ',', $users_to_capture );
					$sql_to_execute[] = sprintf( "DELETE from %s WHERE ID NOT IN( %s )", $user_tablename, $users_to_capture );
					$sql_to_execute[] = sprintf( "DELETE from %s WHERE user_id NOT IN( %s )", $usermeta_tablename, $users_to_capture );
					
					//Execute queries
					foreach ( $sql_to_execute as $sql ) {
						$wpdb->query( $sql );
					}
					//Return the response
					$return_args[ 'message' ] = __( 'Building the export file and cleaning up.', 'it-l10n-backupbuddy' );
					$return_args[ 'completion' ] = 80;
					break;
				case 8:
					//Step 8 - Backup 
					global $current_site, $wpdb;
					require_once( $this->_parent->_pluginPath . '/classes/backup.php' );
					$backup_directory = $extract_files_to . '/wordpress/';
					$temp_directory = $backup_directory . '/wp-content/uploads/backupbuddy_temp/';
					$prefix = $wpdb->prefix;
					$pluginbuddy_backup = new pluginbuddy_backupbuddy_backup( $this->_parent );
					//Get a list of tables to backup
					$query = "SHOW TABLES LIKE '{$prefix}%'";
					$results = $wpdb->get_results( $query, ARRAY_A );
					$tables_to_ignore = array(
						$prefix . 'blogs',
						$prefix . 'blog_versions',
						$prefix . 'site',
						$prefix . 'sitemeta',
						$prefix . 'registration_log',
						$prefix . 'signups',
						$prefix . 'sitecategories'
					);	
					$list_of_tables = array();
					foreach ( $results as $results_key => $table_array ) {
						foreach ( $table_array as $key => $tablename ) {
							if ( preg_match( "/^{$prefix}(?!\d+)/", $tablename ) && !in_array( $tablename, $tables_to_ignore ) ) {
								array_push( $list_of_tables, $tablename );
							}
						}
					}
					
					//Do the database dump
					$backup_serial = $pluginbuddy_backup->rand_string( 10 );
					$dat_directory = $temp_directory . '/' . $backup_serial  . '/';
					$backup = array(
						'backup_time' =>time(),
						'serial' => $backup_serial,
						'backup_type' => 'full',
						'temp_directory' => $dat_directory,
						'backup_mode' => '2'
					);
					$options = array(
						'include_tables' => array(),
						'temp_directory' => $dat_directory,
						'high_security' => '1',
						'backup_nonwp_tables' => '0'
					);
					//Create the temp directory
					$this->_parent->mkdir_recursive( $dat_directory );
					$pluginbuddy_backup->anti_directory_browsing( $temp_directory );
					
					//Create the dat file
					$pluginbuddy_backup->backup_create_dat_file( $backup, $options, true );
					
					//Create the database dump
					$pluginbuddy_backup->backup_create_database_dump( $backup, $options, $list_of_tables, true );
					
					//Archive the file
					require_once( $this->_parent->_pluginPath . '/lib/zipbuddy/zipbuddy.php' );
					$zipbuddy = new pluginbuddy_zipbuddy( $backup_directory );
					$archive_directory = $this->_parent->_options[ 'backup_directory' ];
					$this->_parent->mkdir_recursive( $archive_directory );
					$pluginbuddy_backup->anti_directory_browsing( $archive_directory );
					$archive_file = $archive_directory . 'backup-' . $this->_parent->backup_prefix() . '-' . str_replace( '-', '_', date( 'Y-m-d' ) ) . '-' . $backup_serial . '.zip';
					$zipbuddy->add_directory_to_zip( $archive_file, $backup_directory, true );
					//Return the response
					$archive_file_url = str_replace( ABSPATH, '', $archive_file );
					$archive_file_url = site_url( $archive_file_url );
					$file_args = sprintf( '<a href="%s">%s</a>', $archive_file_url, __( 'Download Now', 'it-l10n-backupbuddy' ) );
					$return_args[ 'message' ] = sprintf( __( 'You\'re done!  The export file is ready for download. %s', 'it-l10n-backupbuddy' ), $file_args );
					$return_args[ 'completion' ] = 100;
					//Cleanup
					$this->_parent->delete_directory_recursive( $extract_files_to );
					break;
					
			} //end switch
			
			die( json_encode( $return_args ) );
			
		} //end ajax_site_export
		//Output a warning that BackupBuddy should be network activated when on a multisite network
		function network_warning() {
			?>
			<div class='error'><p><strong>BackupBuddy should be <a href='<?php echo esc_url( admin_url( 'network/plugins.php' ) ); ?>'>Network-Activated</a> when installed on Multisite.</strong></p></div>
			<?php
		} //end network_warning
		
		//Returns true if a plugin is network activated, false if not
		function is_network_activated() {
			$multisite_network = false;
			if ( ! function_exists( 'is_plugin_active_for_network' ) )  require_once( ABSPATH . '/wp-admin/includes/plugin.php' );		
			if ( is_plugin_active_for_network( $this->_parent->_pluginBase ) ) {
				$multisite_network = true;
			}
			return $multisite_network;
		}
		
		function alert() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'alert' ), $args );
		}
		
		function video() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'video' ), $args );
		}
		
		function tip() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'tip' ), $args );
		}
		
		function log() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'log' ), $args );
		}
		
		function plugin_info() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'plugin_info' ), $args );
		}
		
		function mail_error() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'mail_error' ), $args );
		}
		
		function format_size() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'format_size' ), $args );
		}
		
		// backup.php uses this.
		function localize_time() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'localize_time' ), $args );
		}
		
		// backup.php uses this
		function backup_prefix() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'backup_prefix' ), $args );
		}
		
		// backup.php uses this
		function mkdir_recursive() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'mkdir_recursive' ), $args );
		}
		
		// backup.php uses this
		function delete_directory_recursive() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'delete_directory_recursive' ), $args );
		}
		
		// backup.php uses this
		function format_duration() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'format_duration' ), $args );
		}
		
		// backup.php uses this
		function mail_notify_manual() {
			$args = func_get_args();
			return call_user_func_array( array( $this->_parent, 'mail_notify_manual' ), $args );
		}
		
		function set_greedy_script_limits( &$status_callback = '' ) {
			return $this->_parent->set_greedy_script_limits( $status_callback );
			//return call_user_func_array( array( $this->_parent, 'set_greedy_script_limits' ), array( $status_callback ) );
			// Disabled above line as the pass by reference failed to accept the default parameter if null.
		}
		
		function save() {
			return $this->_parent->save();
		}
		
		function title( $title ) {
			echo '<h2><img src="' . $this->_pluginURL .'/images/icon.png" style="vertical-align: -7px;"> ' . $title . '</h2>';
		}
		
		
		function nonce() {
			wp_nonce_field( $this->_parent->_var . '-nonce' );
		}
		
		// Used by savesettings().
		function strip_tags_deep( $value ) {
		  return is_array( $value ) ?
		    array_map( array( &$this, 'strip_tags_deep' ), $value ) :
		    strip_tags( $value );
		}
		
		/**
		 *	savesettings()
		 *	
		 *	Saves a form into the _options array.
		 *	
		 *	Use savepoint to set the root array key path. Accepts variable depth, dividing array keys with pound signs.
		 *	Ex:	$_POST['savepoint'] value something like array_key_name#subkey
		 *		<input type="hidden" name="savepoint" value="files#exclusions" /> to set the root to be $this->_options['files']['exclusions']
		 *	
		 *	All inputs with the name beginning with pound will act as the array keys to be set in the _options with the associated posted value.
		 *	Ex:	$_POST['#key_name'] or $_POST['#key_name#subarray_key_name'] value is the array value to set.
		 *		<input type="text" name="#name" /> will save to $this->_options['name']
		 *		<input type="text" name="#group#17#name" /> will save to $this->_options['groups'][17]['name']
		 *
		 *	$savepoint_root		string		Override the savepoint. Same format as the form savepoint.
		 */
		function savesettings( $savepoint_root = '' ) {
			check_admin_referer( $this->_parent->_var . '-nonce' );
			foreach( $_POST as $post_index => $post_value ) {
				$_POST[$post_index] = $this->strip_tags_deep( $post_value ); // Do not use just strip_tags as it breaks array post vars.
			}
			if ( !empty( $savepoint_root ) ) { // Override savepoint.
				$_POST['savepoint'] = $savepoint_root;
			}
			
			if ( !empty( $_POST['savepoint'] ) ) {
				$savepoint_root = stripslashes( $_POST['savepoint'] ) . '#';
			} else {
				$savepoint_root = '';
			}
			
			$posted = stripslashes_deep( $_POST ); // Unescape all the stuff WordPress escaped. Sigh @ WordPress for being like PHP magic quotes.
			foreach( $posted as $index => $item ) {
				if ( substr( $index, 0, 1 ) == '#' ) {
					$savepoint_subsection = &$this->_options;
					$savepoint_levels = explode( '#', $savepoint_root . substr( $index, 1 ) );
					foreach ( $savepoint_levels as $savepoint_level ) {
						$savepoint_subsection = &$savepoint_subsection{$savepoint_level};
					}
					$savepoint_subsection = $item;
				}
			}
			$this->_parent->save();
			$this->alert( __('Settings saved...', 'it-l10n-backupbuddy') );
		}
		
		
		function admin_scripts() {
			wp_enqueue_script( $this->_var . '_tooltip', $this->_pluginURL . '/js/tooltip.js' );
			wp_print_scripts( $this->_var . '_tooltip' );
			wp_enqueue_script( $this->_var . '_admin', $this->_pluginURL . '/js/admin.js' );
			wp_print_scripts( $this->_var . '_admin' );
			
			echo '<link rel="stylesheet" href="'.$this->_pluginURL . '/css/admin.css" type="text/css" media="all" />';
		}
		
		
		/**
		 *	get_feed()
		 *
		 *	Gets an RSS or other feed and inserts it as a list of links...
		 *
		 *	$feed		string		URL to the feed.
		 *	$limit		integer		Number of items to retrieve.
		 *	$append		string		HTML to include in the list. Should usually be <li> items including the <li> code.
		 *	$replace	string		String to replace in every title returned. ie twitter includes your own username at the beginning of each line.
		 *	$cache_time	int			Amount of time to cache the feed, in seconds.
		 */
		function get_feed( $feed, $limit, $append = '', $replace = '', $cache_time = 300 ) {
			require_once(ABSPATH.WPINC.'/feed.php');  
			$rss = fetch_feed( $feed );
			if (!is_wp_error( $rss ) ) {
				$maxitems = $rss->get_item_quantity( $limit ); // Limit 
				$rss_items = $rss->get_items(0, $maxitems); 
				
				echo '<ul class="pluginbuddy-nodecor">';

				$feed_html = get_transient( md5( $feed ) );
				if ( $feed_html === false ) {
					foreach ( (array) $rss_items as $item ) {
						$feed_html .= '<li>- <a href="' . $item->get_permalink() . '">';
						$title =  $item->get_title(); //, ENT_NOQUOTES, 'UTF-8');
						if ( $replace != '' ) {
							$title = str_replace( $replace, '', $title );
						}
						if ( strlen( $title ) < 30 ) {
							$feed_html .= $title;
						} else {
							$feed_html .= substr( $title, 0, 32 ) . ' ...';
						}
						$feed_html .= '</a></li>';
					}
					set_transient( md5( $feed ), $feed_html, $cache_time ); // expires in 300secs aka 5min
				}
				echo $feed_html;
				
				echo $append;
				echo '</ul>';
			} else {
				echo __('Temporarily unable to load feed...', 'it-l10n-backupbuddy');
			}
		}
		
		function view_msgettingstarted() {
			$this->_parent->versions_confirm();
			require( 'view_msgettingstarted.php' );
		}
		function view_msbackup() {
			require( 'view_msbackup.php' );
		} //end view_msbackup
		function view_msduplicate() {
			require( 'view_msduplicate.php' );
		} //end view_msbackup
		
		function view_repairbuddy() {
			require( 'view_repairbuddy.php' );
		} //end view_msbackup
		
		function view_gettingstarted() {
			if ( !empty( $_GET['custom'] ) ) {
				require( 'view_custom-' . $_GET['custom'] . '.php' );
			} else {
				$this->_parent->versions_confirm();
				require( 'view_gettingstarted.php' );
			}
		}
		
		function view_multisiteimport() {
			$this->_parent->versions_confirm();
			$this->admin_scripts();
			require( 'view_msimport.php' );
		} //end view_multisiteimport
		
		
		function view_settings() {
			$this->_parent->versions_confirm();
			require( 'view_settings.php' );
		}
		
		
		function view_backup() {
			$this->_parent->versions_confirm();
			require( 'view_backup.php' );
		}
		
		
		function view_malware() {
			require( 'view_malware.php' );
		}
		
		
		function view_tools() {
			$this->_parent->versions_confirm();
			require( 'view_tools.php' );
		}
		
		
		function view_scheduling() {
			$this->_parent->versions_confirm();
			require( 'view_scheduling.php' );
		}
		/** site_admin_menu()
		 *
		 * MULTISITE sub-SITE limited menu; ADDED BY RONALD: Initialize menu for admin section.
		 *
		 */
		function site_admin_menu() {
			$multisite_export = isset( $this->_options[ 'multisite_export' ] ) ? $this->_options[ 'multisite_export' ] : "1";
			if ( $multisite_export != "1" ) return;
			//Todo - Recommend a different getting started page customized for a single site here
			// Add main menu (default when clicking top of menu)
			add_menu_page( $this->_parent->_name, $this->_parent->_name, 'administrator', $this->_parent->_var, array( &$this, 'view_msgettingstarted' ), $this->_parent->_pluginURL.'/images/backupbuddy16.png');
			// Add sub-menu items (first should match default page above)
			add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('Getting Started', 'it-l10n-backupbuddy'), __('Getting Started', 'it-l10n-backupbuddy'), 'administrator', $this->_parent->_var, array(&$this, 'view_msgettingstarted'));
			add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('Export (BETA)', 'it-l10n-backupbuddy'), __('Export (BETA)', 'it-l10n-backupbuddy'), 'administrator', $this->_parent->_var.'-msbackup', array(&$this, 'view_msbackup'));
			add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('Duplicate (BETA)', 'it-l10n-backupbuddy'), __('Duplicate (BETA)', 'it-l10n-backupbuddy'), 'administrator', $this->_parent->_var.'-msduplicate', array(&$this, 'view_msduplicate'));
		}
		
		
		/** admin_menu()
		 *
		 * Initialize menu for admin section.
		 *
		 */		
		function admin_menu() {
			$role = 'administrator';
			if ( !defined( 'PB_DEMO_MODE' ) ) {
				if ( is_multisite() ) $role = 'manage_network';
			}
			if ( isset( $this->_parent->_series ) && ( $this->_parent->_series != '' ) ) {
				// Handle series menu. Create series menu if it does not exist.
				global $menu;
				$found_series = false;
				foreach ( $menu as $menus => $item ) {
					if ( $item[0] == $this->_parent->_series ) {
						$found_series = true;
					}
				}
				
				if ( $found_series === false ) {
					add_menu_page(  $this->_parent->_series . ' ' . __('Getting Started', 'it-l10n-backupbuddy'), 
									$this->_parent->_series, 
									$role, 
									'pluginbuddy-' . strtolower( $this->_parent->_series ), 
									array( &$this, 'view_gettingstarted'), 
									$this->_parent->_pluginURL.'/images/pluginbuddy.png' );

					add_submenu_page(   'pluginbuddy-' . strtolower( $this->_parent->_series ), 
										$this->_parent->_name . ' ' . __('Getting Started' , 'it-l10n-backupbuddy'), 
										__('Getting Started', 'it-l10n-backupbuddy'), 
										$role, 
										'pluginbuddy-' . strtolower( $this->_parent->_series ), 
										array(&$this, 'view_gettingstarted') );
				}
				// Register for getting started page
				global $pluginbuddy_series;
				if ( !isset( $pluginbuddy_series[ $this->_parent->_series ] ) ) {
					$pluginbuddy_series[ $this->_parent->_series ] = array();
				}
				$pluginbuddy_series[ $this->_parent->_series ][ $this->_parent->_name ] = $this->_pluginPath;
				
				add_submenu_page( 'pluginbuddy-' . strtolower( $this->_parent->_series ), $this->_parent->_name, $this->_parent->_name, $role, $this->_parent->_var.'-settings', array(&$this, 'view_settings'));
			} else { // NOT IN A SERIES!
				// Add main menu (default when clicking top of menu)
				add_menu_page( $this->_parent->_name, $this->_parent->_name, $role, $this->_parent->_var, array( &$this, 'view_gettingstarted' ), $this->_parent->_pluginURL.'/images/backupbuddy16.png');
				// Add sub-menu items (first should match default page above)
				add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('Getting Started', 'it-l10n-backupbuddy'), __('Getting Started', 'it-l10n-backupbuddy'), $role, $this->_parent->_var, array(&$this, 'view_gettingstarted'));
				
				add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('Backup & Restore', 'it-l10n-backupbuddy'), __('Backup & Restore', 'it-l10n-backupbuddy'), $role, $this->_parent->_var.'-backup', array(&$this, 'view_backup'));
				add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('Malware Scan', 'it-l10n-backupbuddy'), __('Malware Scan', 'it-l10n-backupbuddy'), $role, $this->_parent->_var.'-malware', array(&$this, 'view_malware'));
				add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('Server Info.', 'it-l10n-backupbuddy'), __('Server Info.', 'it-l10n-backupbuddy'), $role, $this->_parent->_var.'-tools', array(&$this, 'view_tools'));
				add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('Scheduling', 'it-l10n-backupbuddy'),__('Scheduling', 'it-l10n-backupbuddy'), $role, $this->_parent->_var.'-scheduling', array(&$this, 'view_scheduling'));
				if ( function_exists( 'is_network_admin' ) && is_network_admin() ) {
					add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('Multisite Import (BETA)', 'it-l10n-backupbuddy'), __('Multisite Import (BETA)', 'it-l10n-backupbuddy'), $role, $this->_parent->_var . '-msimport', array(&$this, 'view_multisiteimport'));
				}
				//if ( isset( $this->_options[ 'enable_repair_buddy' ] ) && $this->_options[ 'enable_repair_buddy' ] == "1" ) {
					$hook = 'backupbuddy_page_pluginbuddy_backupbuddy-repairbuddy';
					//add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('RepairBuddy', 'it-l10n-backupbuddy'), __('RepairBuddy', 'it-l10n-backupbuddy'), $role, $this->_parent->_var.'-repairbuddy', array(&$this, 'view_repairbuddy') );
					//add_action( 'admin_print_scripts-' . $hook, array( &$this, 'repairbuddy_scripts' ) );
					//add_action( 'admin_print_styles-' . $hook, array( &$this, 'repairbuddy_styles' ) );
				//}
				add_submenu_page( $this->_parent->_var, $this->_parent->_name. ' ' . __('Settings', 'it-l10n-backupbuddy'), __('Settings', 'it-l10n-backupbuddy'), $role, $this->_parent->_var.'-settings', array(&$this, 'view_settings'));
			}
		}
		
		
		function repairbuddy_scripts() {
			wp_enqueue_script( 'repairbuddy', $this->_pluginURL. '/classes/repairbuddy/repairbuddy.js', array( 'jquery', 'password-strength-meter' ) );
		} //end repairbuddy_scripts
		function repairbuddy_styles() {
			wp_enqueue_style( 'repairbuddy', $this->_pluginURL . '/classes/repairbuddy/styles.css' );
		} //end repairbuddy_styles
		
		function ajax_remotedestination() {
			require_once( $this->_pluginPath . '/classes/ajax_remotedestination.php' );
		}
		
		function importbuddy_link() {
			//return admin_url( 'admin-ajax.php' ) . '?action=backupbuddy_importbuddy&pass=' . md5( $this->_options['import_password'] );
			if ( !empty( $this->_options['import_password'] ) ) {
				$import_pass_query = '&pass=' . md5( $this->_options['import_password'] );
			} else {
				$import_pass_query = '';
			}
			return admin_url( 'admin-ajax.php' ) . '?action=backupbuddy_importbuddy' . $import_pass_query;
		}
		
		
		function pretty_destination_type( $type ) {
			if ( $type == 'rackspace' ) {
				return 'Rackspace';
			} elseif ( $type == 'email' ) {
				return 'Email';
			} elseif ( $type == 's3' ) {
				return 'Amazon S3';
			} elseif ( $type == 'ftp' ) {
				return 'FTP';
			} elseif ( $type == 'dropbox' ) {
				return 'Dropbox';
			} else {
				return $type;
			}
		}
		
		
	} // End class
	//$pluginbuddy_backupbuddy_admin = new pluginbuddy_backupbuddy_admin( $this );
}
