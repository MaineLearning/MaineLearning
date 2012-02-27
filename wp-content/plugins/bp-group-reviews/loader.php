<?php
/*
Plugin Name: BP Group Reviews
Author: boonebgorges
Author URL: http://boonebgorges.com
Description: Adds a review/rating section to BuddyPress groups. As seen on buddypress.org/extend/plugins
Version: 1.3.1
*/

define( 'BP_GROUP_REVIEWS_VERSION', '1.3.1' );

if ( !defined( 'BP_GROUP_REVIEWS_SLUG' ) )
	define( 'BP_GROUP_REVIEWS_SLUG', 'reviews' );

if ( !defined( 'BP_GROUP_REVIEWS_DIR' ) )
	define( 'BP_GROUP_REVIEWS_DIR', WP_PLUGIN_DIR . '/bp-group-reviews/' );

if ( !defined( 'BP_GROUP_REVIEWS_URL' ) )
	define( 'BP_GROUP_REVIEWS_URL', WP_PLUGIN_URL . '/bp-group-reviews/' );


function bpgr_loader() {
	require_once( dirname(__FILE__) . '/bp-group-reviews.php' );
}
add_action( 'bp_include', 'bpgr_loader' );


function bpgr_textdomain() {
	$locale = get_locale();

	// First look in wp-content/languages, where custom language files will not be overwritten by upgrades. Then check the packaged language file directory.
	$mofile_custom = WP_CONTENT_DIR . "/languages/bpgr-$locale.mo";
	$mofile_packaged = BP_GROUP_REVIEWS_DIR . "languages/bpgr-$locale.mo";
	
	if ( file_exists( $mofile_custom ) ) {
		load_textdomain( 'bpgr', $mofile_custom );
		return;
	} else if ( file_exists( $mofile_packaged ) ) {
		load_textdomain( 'bpgr', $mofile_packaged );
		return;
	}
}
add_action ( 'plugins_loaded', 'bpgr_textdomain' );


?>
