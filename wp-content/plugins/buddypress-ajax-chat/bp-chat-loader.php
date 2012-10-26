<?php
/*
Plugin Name: BuddyPress Chat Component
Plugin URI: http://dynamicendeavorsllc.com
Description: This BuddyPress component adds chat functionality to Buddypress.
Version: 1.4.4
Revision Date: 5/23/2011
Requires at least: WPMU 2.9.1, BuddyPress 1.1.3
Tested up to: WPMU 3.1.1, BuddyPress 1.2.8
License: (Chat: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html)
Author: David Aubin
Author URI: http://codewarrior.getpaidfrom.us
Network: true
 */
define ( 'BP_CHAT_VERSION', '1.4.4' );

function bp_ajax_chat_plugin_init() {
    require( dirname( __FILE__ ) . '/bp-chat.php' );
}

$buddypress_installed = false;

$active_plugins = get_site_option( 'active_sitewide_plugins' );
if ( isset( $active_plugins['buddypress/bp-loader.php'] ) )
{
    $buddypress_installed = true;
}
if ( false == $buddypress_installed )
{
    $active_plugins = get_option( 'active_plugins' );
    foreach ($active_plugins as $key => $value)
	if ( $value == 'buddypress/bp-loader.php' )
	{
	    $buddypress_installed = true;
	    break;
	}
}
if ( $buddypress_installed )
    bp_ajax_chat_plugin_init();
?>
