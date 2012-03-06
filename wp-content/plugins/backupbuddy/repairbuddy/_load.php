<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
// Try to prevent browser timeouts. Greedy script limits are handled on the steps that need them.
header( 'Keep-Alive: 3600' );
header( 'Connection: keep-alive' );
 
$php_minimum = '5.1'; // User's PHP must be equal or newer to this version.

if ( version_compare( PHP_VERSION, $php_minimum ) < 0 ) {
	die( 'ERROR #9013. See <a href="http://ithemes.com/codex/page/BackupBuddy:_Error_Codes#9013">this codex page for details</a>. Sorry! PHP version ' . $php_minimum . ' or newer is required for BackupBuddy to properly run. You are running PHP version ' . PHP_VERSION . '.' );
}

//Load the Database
//If the database can connect, PB_WP_CONFIG and PB_DB_LOADED will both be defined
function pb_load_wp_config() {
	$path = dirname( dirname( __FILE__ ) ) . '/';
	if ( !file_exists( $path . 'wp-config.php' ) ) {
		return false;
	}
	$lines = file( $path . 'wp-config.php' );
	
	foreach( $lines as $line ) {
		if ( preg_match( '/define\([\s]*(\'|")DB_HOST(\'|"),[\s]*(\'|")(.*)(\'|")[\s]*\);/i', $line, $matches ) > 0 ) {
			define( 'PB_DB_SERVER', $matches[4] );
		}
		if ( preg_match( '/define\([\s]*(\'|")DB_USER(\'|"),[\s]*(\'|")(.*)(\'|")[\s]*\);/i', $line, $matches ) > 0 ) {
			define( 'PB_DB_USER', $matches[4] );
		}
		if ( preg_match( '/define\([\s]*(\'|")DB_PASSWORD(\'|"),[\s]*(\'|")(.*)(\'|")[\s]*\);/i', $line, $matches ) > 0 ) {
			define( 'PB_DB_PASSWORD', $matches[4] );
		}
		if ( preg_match( '/define\([\s]*(\'|")DB_NAME(\'|"),[\s]*(\'|")(.*)(\'|")[\s]*\);/i', $line, $matches ) > 0 ) {
			define( 'PB_DB_NAME', $matches[4] );
		}
		if ( !defined( 'PB_DB_NAME' ) ) {
			if ( preg_match( '/define\([\s]*(\'|")DB_NAME(\'|"),[\s]*(\'|")(.*)(\'|")[\s]*\);/i', $line, $matches ) > 0 ) {
				define( 'PB_DB_NAME', $matches[4] );
			}
		}
		
		if ( preg_match( '/\$table_prefix[\s]*=[\s]*(\'|")(.*)(\'|");/i', $line, $matches ) > 0 ) {
			define( 'PB_DB_PREFIX', $matches[2] );
		}
		
	} //end foreach $lines
	if ( defined( 'PB_DB_SERVER' ) && defined( 'PB_DB_USER' ) && defined( 'PB_DB_PASSWORD' ) && defined( 'PB_DB_NAME' ) && defined( 'PB_DB_PREFIX' ) ) {
		define( 'PB_WP_CONFIG', 'true' );
		return true;
	}
	return false;
} //end wp_config_into_options
function pb_connect_database() {
	// Set up database connection.
	if ( !defined( 'PB_WP_CONFIG' ) ) return false;
	if ( false === @mysql_connect( PB_DB_SERVER, PB_DB_USER, PB_DB_PASSWORD ) ) {
		return false;
	}
	$database_name = mysql_real_escape_string( PB_DB_NAME );
	
	flush();
	
	// Select the database.
	if ( false === @mysql_select_db( PB_DB_NAME ) ) {
		return false;
	}
	
	// Set up character set. Important.
	mysql_query("SET NAMES 'utf8'");	
	define( 'PB_DB_LOADED', 'true' );	
	return true;
}
if ( pb_load_wp_config() ) {
	pb_connect_database();
}


//Load WordPress Conditionally
if ( isset( $_GET['bootstrap'] ) && ( $_GET['bootstrap'] == 'true' ) || isset( $_POST[ 'load_wp' ] ) ) {
	$path = dirname( dirname( __FILE__ ) );
	if ( defined( 'PB_DB_LOADED' ) ) {
		if ( file_exists( $path . '/wp-load.php' ) ) {
			ob_start(); //Suppress errors
			@require_once( $path . '/wp-load.php' );
			ob_end_clean();
			define( 'PB_WP_LOADED', 'true' ); //So others can know if WP loaded successfully
		} 
	} 
}
if ( !defined( 'PB_WP_LOADED' ) ) {
	define( 'ABSPATH', dirname( dirname( __FILE__ ) ) . '/' );

	function __( $text, $domain ) {
		return $text;
	}
	function _e( $text, $domain ) {
		echo $text;
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
} 

//Setup Actions and Filters
global $pb_repairbuddy_actions;
global $pb_repairbuddy_filters;

function pb_add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
	global $pb_repairbuddy_actions;
	if ( !is_array( $pb_repairbuddy_actions ) ) $pb_repairbuddy_actions = array();
	$pb_repairbuddy_actions[ $tag ][ $priority ][] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
} //end pb_add_action
function pb_do_action( $tag, $args = '' ) {
	//Get the current tag
	global $pb_repairbuddy_actions;
	if ( isset( $pb_repairbuddy_actions[ $tag ] ) ) {
		ksort( $pb_repairbuddy_actions[ $tag ] );
	} else { 
		return false; 
	}
	$tags = $pb_repairbuddy_actions[ $tag ];
	foreach ( $tags as $priority => $items ) {
		foreach ( $items as $index => $item ) {
			//Make sure the function or method exists
			$function = $item[ 'function' ];
			if ( is_string( $function ) && !function_exists( $function ) ) {
				continue;
			} elseif ( is_array( $function ) ) {
				$object = $function[ 0 ];
				$method = $function[ 1 ];
				if ( !method_exists( $object, $method ) ) {
					continue;
				}
			}
			if ( is_string( $args ) ) {
				call_user_func( $function, $args );
			} elseif ( is_array( $args ) ) {
				call_user_func_array( $function, $args );
			}
		} //end foreach $items
	} //end foreach $tags	
} //end pb_do_action

// LOAD MODULES
require_once( '_modules.php' );
$rb_files = glob( ABSPATH . 'repairbuddy/modules/*' );
if ( !is_array( $rb_files ) || empty( $rb_files ) ) {
	$rb_files = array();
}
foreach( $rb_files as $file ) {
	if ( file_exists( $file . '/init.php' ) ) {
		require_once( $file . '/init.php' );
	}
	//If init.php doesn't exist, do nothing! muwahahahaha
}
?>