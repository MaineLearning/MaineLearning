<?php
/** 
 * The base configurations of bbPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys and bbPress Language. You can get the MySQL settings from your
 * web host.
 *
 * This file is used by the installer during installation.
 *
 * @package bbPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for bbPress */
define( 'BBDB_NAME', 'mainelea_wpbp' );

/** MySQL database username */
define( 'BBDB_USER', 'mainelea_guru12' );

/** MySQL database password */
define( 'BBDB_PASSWORD', '12dirigo' );

/** MySQL hostname */
define( 'BBDB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'BBDB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'BBDB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/bbpress/ WordPress.org secret-key service}
 *
 * @since 1.0
 */
define('BB_AUTH_KEY',        'F9&Tv+XY6Aw-2S:S4MgH)sw^{{x<m(RWC%ckUBtmK+0yh!t.4I{6F8YuW)(xW+IX');
define('BB_SECURE_AUTH_KEY', '{yZ70v! X~vbQP/a}DsT1;ZThV;jkm+|*?Ulbk!_1F*+^IC<>+c:7+T^G59paI t');
define('BB_LOGGED_IN_KEY',   'dZ2<.3m>t&cNIdj9ux#5~#y,}Q-|w2lD#q)bf-B(%~s{bHzlcXIo&uS$*5Hz|xTG');
define('BB_NONCE_KEY',       '|06~=wvY/J+r-?EE_lP>Huo6:}`M;1SZw$A^?gHRAI.B?k&d/&S{HjS} `BlPEl(');
/**#@-*/

/**
 * bbPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$bb_table_prefix = 'wp_bb_';

/**
 * bbPress Localized Language, defaults to English.
 *
 * Change this to localize bbPress. A corresponding MO file for the chosen
 * language must be installed to a directory called "my-languages" in the root
 * directory of bbPress. For example, install de.mo to "my-languages" and set
 * BB_LANG to 'de' to enable German language support.
 */
define( 'BB_LANG', 'en_US' );
$bb->custom_user_table = 'wp_users';
$bb->custom_user_meta_table = 'wp_usermeta';

$bb->uri = 'http://mainelearning.net/wp-content/plugins/buddypress/bp-forums/bbpress/';
$bb->name = 'Maine Learning Forums';
$bb->wordpress_mu_primary_blog_id = 1;

define('BB_AUTH_SALT', '#vu-)$XVXEn3XC-?FVW*-O$@I$N#y|1p5f9m[7GOc,%qL|(I82R{tGS7/PLqUxvr');
define('BB_LOGGED_IN_SALT', 'p_e%;#S$FClx~I)5!pmwo%K$i#4Xve_Y#-N>;I+Fwk7>!)X9vfH.@t=`?X*XYl^t');
define('BB_SECURE_AUTH_SALT', 'HVpRZj8)c|rBTX!-^+mO3x-Al!5;.csC3,a+@5m!lMi4)Z|Myn8n0/lDpR5KL7jz');

define('WP_AUTH_COOKIE_VERSION', 2);

?>