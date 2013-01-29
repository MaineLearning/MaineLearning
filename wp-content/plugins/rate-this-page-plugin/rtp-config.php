<?php
/* ================================================================
 * Defined directory names
 * ================================================================ */
define( 'RTP_PLUGIN_DIR_JS', plugin_dir_url( __FILE__ ) . 'js/' );
define( 'RTP_PLUGIN_DIR_CSS', plugin_dir_url( __FILE__ ) . 'css/' );
define( 'RTP_PLUGIN_DIR_IMG', plugin_dir_url( __FILE__ ) . 'img/' );
define( 'RTP_PLUGIN_DIR_THEME', plugin_dir_url( __FILE__ ) . 'themes/' );

/* ================================================================
 * Defined cookie variables
 * ================================================================ */
define( 'RTP_COOKIE_EXPIRATION', time() + 1209600 ); //Expiration of 14 days.
define( 'RTP_COOKIE_EXPIRATION2', time() + 604800 ); //Expiration of 7 days.
define( 'RTP_COOKIE_RANDMIN', 0 );
define( 'RTP_COOKIE_RANDMAX', 999999 );
define( 'RTP_COOKIE_NAME', 'rtp-cookie-session' );
define( 'RTP_COOKIE_LOGNAME', 'rtp-cookie-log_' );

/* ================================================================
 * Defined plugin name
 * ================================================================ */
define( 'RTP_PLUGIN_NAME', 'Rate This Page' );
define( 'RTP_PLUGIN_SNAME', 'rate-this-page' );
?>