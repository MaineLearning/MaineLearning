<?php

class bebop_extensions {
	
	//extension developers: Use this method to register a plugin path. Use like so:
	//bebop_plugins::bebop_register_extension( /path/to/my/extension/ );
	function bebop_register_extension( $path ) {
		add_filter( 'bebop_plugin_extensions', create_function( '$extensions', '
		$extensions[] = "' . $path . '";
		return $extensions;
		' ) );
	}
	
	function bebop_extension_exists( $extension_name ) {
		$extensions = bebop_extensions::bebop_gather_extensions();
		
		foreach( $extensions as $extension) {
			if ( strrpos( $extension, $extension_name ) ) {
				return true;
			}
		}
		return false;
	}
	
	function bebop_get_extension_name_from_path( $extension_path ) {
		$exp = array_reverse( explode( '/', $extension_path ) );
		$extension_name = $exp[1];
		return $extension_name;
	}
	
	function bebop_get_extension_path_from_name( $extension_name ) {
		$extensions = bebop_extensions::bebop_gather_extensions();
		foreach( $extensions as $extension) {
			if ( strrpos( $extension, $extension_name ) ) {
				return $extension;
			}
		}
	}
	
	function bebop_gather_extensions() {
		
		//only generate the list once per page request.
		static $extension_paths;
		
		//core extensions are stored in bebop/extensions. 3rd party developers should store extensions elsewhere, or Bebop will overwrite them whenever updated.
		if ( empty( $extension_paths ) ) {
			$extension_paths = array();
			$handle = opendir( WP_PLUGIN_DIR . '/bebop/extensions' );
			if ( $handle ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( $file != '.' && $file != '..' && $file != '.DS_Store' ) {
						if ( file_exists( WP_PLUGIN_DIR . '/bebop/extensions/' . $file . '/core.php' ) ) {
							$extension_paths[] = WP_PLUGIN_DIR . '/bebop/extensions/' . $file . '/';
						}
					}
				}
			}
			// Put together a list of plugin extensions
			$plugin_extensions = apply_filters( 'bebop_plugin_extensions', array() );
			if ( ! empty( $plugin_extensions ) ) {
				$extension_paths = array_merge( $extension_paths, $plugin_extensions );
			}
		}
		return $extension_paths;
	}
	
	function bebop_load_extensions() {
		$extensions = bebop_extensions::bebop_gather_extensions();
		foreach ( $extensions as $extension ) {
			if ( file_exists( $extension . 'core.php' ) ) {
				include_once( $extension . 'core.php' );
			}
			else {
				bebop_tables::log_error( __('Extension Error', 'bebop' ), sprintf( __( 'Could not find a "core.php" file in %1$s.', 'bebop' ), $extension ) );
			}
		}
	}
	
	function bebop_get_extension_configs() {
		$config = array();
		$extensions = bebop_extensions::bebop_gather_extensions();
		foreach ( $extensions as $extension_path ) {
			if ( file_exists( $extension_path . 'config.php' ) ) {
				include_once( $extension_path . 'config.php' );
				$extension_name = bebop_extensions::bebop_get_extension_name_from_path( $extension_path );
				if ( function_exists( 'get_' . $extension_name . '_config' ) ) {
					$config[] = call_user_func( 'get_' . $extension_name . '_config' );
				}
				else {
					bebop_tables::log_error(  __('Extension Error', 'bebop' ),  sprintf( __( 'Could not find the method "get_%1$s_config" for the %1$s extension.', 'bebop' ), $extension_name ) );
				}
			}
			else {
				bebop_tables::log_error(  __('Extension Error', 'bebop' ), sprintf( __( 'Could not find a "config.php" file in %1$s.', 'bebop' ), $extension_path ) ); 
			}
		}
		return $config;
	}
	
	function bebop_get_extension_config_by_name( $extension_name ) {
		if ( bebop_extensions::bebop_extension_exists( $extension_name ) ) {
			$extension_path = bebop_extensions::bebop_get_extension_path_from_name( $extension_name );
			if ( ! function_exists( 'get_' . $extension_name . '_config' ) ) {
				include_once( $extension_path . 'config.php' );
			}
			return call_user_func( 'get_' . $extension_name . '_config' );
		}
		else {
			return false;
		}
	}
	
	function bebop_get_active_extension_names( $addslashes = false ) {
		
		//save to static property.
		static $active_extensions;
		
		if( empty( $active_extensions ) ) {
			$extensions = bebop_extensions::bebop_gather_extensions();
			$active_extensions = array();
			foreach ( $extensions as $extension_path ) {
				if ( file_exists( $extension_path . 'import.php' ) ) {
					$extension_name = bebop_extensions::bebop_get_extension_name_from_path( $extension_path );
					if ( bebop_tables::get_option_value( 'bebop_' . $extension_name . '_provider' ) == 'on' ) {
						if ( $addslashes == true ) {
							$active_extensions[] = "'" . $extension_name . "'";
						}
						else {
							$active_extensions[] = $extension_name;
						}
					}
				}
			}
		}
		return $active_extensions; 
	}
	
	function bebop_page_loader( $extension ) {
		$extension_name = strtolower( $extension );
		$extension_path = bebop_extensions::bebop_get_extension_path_from_name( $extension_name );
		if ( file_exists( $extension_path . 'config.php' ) ) {
			if ( ! function_exists( 'get_' . $extension_name . '_config' ) ) {
				require( $extension_path . 'config.php' );
			}
			$config = call_user_func( 'get_' . $extension_name . '_config' );
			
			if ( ! isset( $_GET['settings'] ) ) {
			 $page = strtolower( $config['defaultpage'] );
			}
			else {
				$page = strtolower( $_GET['settings'] );
			}
			
			include $extension_path . 'templates/admin-settings.php';
		}
		else {
			echo '<div class="bebop_error_box"><b>';
			_e('Bebop Error', 'bebop' );
			echo ':</b> ';
			sprintf( __( '%1$s is not a valid extension.', 'bebop' ), $extension_name );
			echo '</div>';
			include_once( WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin-menu.php' );
		}
	}
	
	function bebop_user_page_loader( $extension, $page = 'settings' ) {
		global $bp;

		add_action( 'wp_enqueue_scripts', 'bebop_user_stylesheets' );
		add_action( 'bp_template_content', 'bebop_user_'.$page.'_screen_content' );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}
}