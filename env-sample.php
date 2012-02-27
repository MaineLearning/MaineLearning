<?php
/**
 * WordPress
 */
define( 'DB_NAME', 'name' );
define( 'DB_USER', 'user' );
define( 'DB_PASSWORD', 'pw' );
define( 'DB_HOST', 'localhost' );

/**
 * bbPress (BP version)
 *
 * This is inherited automatically.
 *
 * Note: be sure to swap out the inline definitions in bb-config.php 
 */
define( 'BBDB_NAME', DB_NAME );
define( 'BBDB_USER', DB_USER );
define( 'BBDB_PASSWORD', DB_PASSWORD );
define( 'BBDB_HOST', DB_HOST );

/**
 * Other environment specific constants
 */

define('WP_CACHE', true); //Added by WP-Cache Manager

define( 'ENV_TYPE', 'local' );
define( 'WP_DEBUG', false );

define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

@ini_set('display_errors',0);

?>