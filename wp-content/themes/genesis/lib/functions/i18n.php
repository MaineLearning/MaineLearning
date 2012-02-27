<?php
/**
 * Controls translation of Genesis.
 *
 * @category Genesis
 * @package  Admin
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

/** Allow theme to be localized */
load_theme_textdomain( 'genesis', GENESIS_LANGUAGES_DIR );

/** Add support for locale-specific customisations */
$locale = get_locale();
$locale_file = GENESIS_LANGUAGES_DIR . "/$locale.php";
if ( is_readable( $locale_file ) )
	require_once( $locale_file );

/** Uncomment this to test your localization, make sure to enter the right language code. */
/*
add_filter( 'locale','test_localization' );
function genesis_test_localization( $locale ) {
	return 'nl_NL';
}
/**/