<?php
/**
 *
 * Plugin Name: ServerBuddy
 * Plugin URI: http://pluginbuddy.com/free-wordpress-plugins/serverbuddy/
 * Description: Various tools & tests to analyze server configuration & troubleshoot issues. Navigate to Tools: ServerBuddy. By PluginBuddy.com.
 * Version: 1.0.2
 * Author: Dustin Bolton
 * Author URI: http://dustinbolton.com/
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


if ( !class_exists( 'pluginbuddy_serverbuddy' ) ) {
	class pluginbuddy_serverbuddy {
		// DEPRECATED VARS; use plugin_info():
		var $_version = '1.0.2';									// DEPRECATED v0.3.7. Use $this->plugin_info( 'version' ) to get plugin version. Update this number until obsolete.
		var $_url = 'http://pluginbuddy.com/purchase/serverbuddy/';	// DEPRECATED v0.3.7. Use $this->plugin_info( 'url' ) to get plugin url. Update this url until obsolete.
		var $_var = 'pluginbuddy_serverbuddy';						// DEPRECATED v0.3.10. Use $this->_slug. Match _var and _slug until removal.
		
		var $_wp_minimum = '2.9.0';
		var $_php_minimum = '5.2';
		
		var $_slug = 'pluginbuddy_serverbuddy';						// Format: pluginbuddy-pluginnamehere. All lowecase, no dashes.
		var $_name = 'ServerBuddy';									// Pretty plugin name. Only used for display so any format is valid.
		var $_series = '';											// Series name if applicable.
		var $_timestamp = 'M j, Y, g:iA';							// PHP timestamp format.
		var $_defaults = array(
			'role_access'						=>		'administrator',
		);
		
		
		// Default constructor. This is automatically run on each page load.
		function pluginbuddy_serverbuddy() {
			$this->_pluginPath = dirname( __FILE__ );
			$this->_pluginRelativePath = ltrim( str_replace( '\\', '/', str_replace( rtrim( ABSPATH, '\\\/' ), '', $this->_pluginPath ) ), '\\\/' );
			$this->_pluginURL = site_url() . '/' . $this->_pluginRelativePath;
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) { $this->_pluginURL = str_replace( 'http://', 'https://', $this->_pluginURL ); }
			$this->_selfLink = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?page=' . $this->_var;
			
			if ( is_admin() ) { // Runs when in the admin dashboard.
				require_once( $this->_pluginPath . '/classes/admin.php' );
				add_action( 'wp_ajax_' . $this->_var . '_icicletree', array( &$this, 'ajax_icicletree' ) ); // Directory listing for exluding
				
				add_filter( 'plugin_row_meta', array( &$this, 'filter_plugin_row_meta' ), 10, 2 );
			} else { // Runs when in non-dashboard parts of the site.
				add_shortcode( 'serverbuddy', array( &$this, 'shortcode' ) );
			}
		}
		
		// name, title, description, author, authoruri, version, pluginuri OR url, textdomain, domainpath, network
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
		 */
		function alert( $message, $error = false, $error_code = '' ) {
			$log_error = false;
			
			echo '<div id="message" class="';
			if ( $error === false ) {
				echo 'updated fade';
			} else {
				echo 'error';
				$log_error = true;
			}
			if ( $error_code != '' ) {
				$message .= '<p><a href="http://ithemes.com/codex/page/' . $this->_name . ':_Error_Codes#' . $error_code . '" target="_new"><i>' . $this->_name . ' Error Code ' . $error_code . ' - Click for more details.</i></a></p>';
				$log_error = true;
			}
			if ( $log_error === true ) {
				$this->log( $message . ' Error Code: ' . $error_code, 'error' );
			}
			echo '"><p><strong>' . $message . '</strong></p></div>';
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
			
			$tip = '<a href="http://www.youtube.com/embed/' . $video_key . '?autoplay=1&TB_iframe=1&width=640&height=400" class="thickbox pluginbuddy_tip" title="Video Tutorial - ' . $title . '"><img src="' . $this->_pluginURL . '/images/pluginbuddy_play.png" alt="(video)" /></a>';
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
				$fh = fopen( WP_CONTENT_DIR . '/uploads/' . $this->_var . '.txt', 'a');
				fwrite( $fh, '[' . date( $this->_timestamp . ' ' . get_option( 'gmt_offset' ), time() + (get_option( 'gmt_offset' )*3600) ) . '-' . $log_type . '] ' . $text . "\n" );
				fclose( $fh );
			}
		}
		
		
		function load() {
			$this->_options=get_option($this->_var);
			$options = array_merge( $this->_defaults, (array)$this->_options );
			
			if ( $options !== $this->_options ) {
				// Defaults existed that werent already in the options so we need to update their settings to include some new options.
				$this->_options = $options;
				$this->save();
			}
			
			return true;
		}
		
		
		function save() {
			add_option( $this->_var, $this->_options, '', 'no' ); // 'No' prevents autoload if we wont always need the data loaded.
			update_option( $this->_var, $this->_options );
			return true;
		}
		
		
		function format_size( $size ) {
			$sizes = array( ' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
			if ( $size == 0 ) {
				return( 'empty' );
			} else {
				return ( round( $size / pow( 1024, ( $i = floor( log( $size, 1024 ) ) ) ), $i > 1 ? 2 : 0) . $sizes[$i] );
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
		
		
		function ajax_icicletree() {
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
			//$icicle_json .= '"id": "node_' . str_replace( '/', ':', $dir_name ) . '"' . "\n";
			
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
		
		
		function filter_plugin_row_meta( $plugin_meta, $plugin_file ) {
			if ( strstr( $plugin_file, strtolower( $this->_name ) ) ) {
				$plugin_meta[2] = '<a title="Visit plugin site" href="http://pluginbuddy.com/backupbuddy/">Visit PluginBuddy.com</a>';
				return $plugin_meta;
			} else {
				return $plugin_meta;
			}
		}
		
	} // End class
	
	$pluginbuddy_serverbuddy = new pluginbuddy_serverbuddy();
}
?>