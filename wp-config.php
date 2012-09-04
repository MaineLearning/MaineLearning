<?php



/**

 * The base configurations of the WordPress.

 *

 * This file has the following configurations: MySQL settings, Table Prefix,

 * Secret Keys, WordPress Language, and ABSPATH. You can find more information

 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing

 * wp-config.php} Codex page. You can get the MySQL settings from your web host.

 *

 * This file is used by the wp-config.php creation script during the

 * installation. You don't have to use the web site, you can just copy this file

 * to "wp-config.php" and fill in the values.

 *

 * @package WordPress

 */

// Include environment-specific constants
require( dirname(__FILE__) . '/env.php' );

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
 //Added by WP-Cache Manager
 //Added by WP-Cache Manager

/** Database Charset to use in creating database tables. */
define('WP_CACHE', true); //Added by WP-Cache Manager
define('DB_CHARSET', 'utf8');


/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');


/**#@+

 * Authentication Unique Keys and Salts.

 *

 * Change these to different unique phrases!

 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}

 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.

 *

 * @since 2.6.0

 */

define('AUTH_KEY',         '`Inac=OX71MLR~H-GUDOx?V0/;M;)k>0wX~wp(CHnCgeL}%_=Jsed?P+L[!#kBPy');
define('SECURE_AUTH_KEY',  '1kJx_,H&$!#G,J@jIWSP]h|Ca!V+{>vd: WqR.ot^s6NPU}TVMdgL.Bk+1S>Z  l');
define('LOGGED_IN_KEY',    'vC5doaef9VW)bD%n(P6-sdxz|6+|k,]|gXb#spiW~_g,#~eVR<qP7bIEJ!GDb-g]');
define('NONCE_KEY',        '~jZ(m|Op4[P{[.A%Od- >*?}<(6+iTEIO|)$74a)d1wI$~u~Kv/}je__wq^4TNWF');
define('AUTH_SALT',        '#vu-)$XVXEn3XC-?FVW*-O$@I$N#y|1p5f9m[7GOc,%qL|(I82R{tGS7/PLqUxvr');
define('SECURE_AUTH_SALT', 'HVpRZj8)c|rBTX!-^+mO3x-Al!5;.csC3,a+@5m!lMi4)Z|Myn8n0/lDpR5KL7jz');
define('LOGGED_IN_SALT',   'p_e%;#S$FClx~I)5!pmwo%K$i#4Xve_Y#-N>;I+Fwk7>!)X9vfH.@t=`?X*XYl^t');
define('NONCE_SALT',       '=DVu%p%X5|X9Ul7mYI>l |=tRTg&t|%,Vt#]`3&=k-cm}g+>DO/y&~/- 1tIR}&]');


/**#@-*/



/**

 * WordPress Database Table prefix.

 *

 * You can have multiple installations in one database if you give each a unique

 * prefix. Only numbers, letters, and underscores please!

 */

$table_prefix  = 'wp_';



/**

 * WordPress Localized Language, defaults to English.

 *

 * Change this to localize WordPress. A corresponding MO file for the chosen

 * language must be installed to wp-content/languages. For example, install

 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German

 * language support.

 */



/**

 * For developers: WordPress debugging mode.

 *

 * Change this to true to enable the display of notices during development.

 * It is strongly recommended that plugin and theme developers use WP_DEBUG

 * in their development environments.

 */

/**
 * This will log all errors notices and warnings to a file called debug.log in
 * wp-content (if Apache does not have write permission, you may need to create
 * the file first and set the appropriate permissions (i.e. use 666) )
 */


define('WP_ALLOW_MULTISITE', true);
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
$base = '/';
define( 'DOMAIN_CURRENT_SITE', 'mainelearning.net' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );
define( 'GF_LICENSE_KEY', 'a96c179746650d383ecfd09f2e9ea776');

define('WPLANG', '');

define( 'WP_CACHE', true );


/* That's all, stop editing! Happy blogging. */



/** Absolute path to the WordPress directory. */

if ( !defined('ABSPATH') )

	define('ABSPATH', dirname(__FILE__) . '/');


/** ADDED BY JFC 1/27/2011 PER http://wordpress.org/support/topic/fatal-error-in-incoming-links-dashboard-widget-memory-exhausted?replies=12. */

define('WP_MEMORY_LIMIT', '128M');

/** Change slug for BP Docs*/

define( 'BP_DOCS_SLUG', 'wiki-page' );

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
/* Note: This must be the last thing in the file! */
require_once(ABSPATH . 'wp-settings.php');



