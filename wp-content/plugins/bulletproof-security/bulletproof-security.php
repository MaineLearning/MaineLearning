<?php
/*
Plugin Name: BulletProof Security
Plugin URI: http://www.ait-pro.com/aitpro-blog/297/bulletproof-security-plugin-support/bulletproof-security-wordpress-plugin-support/
Text Domain: bulletproof-security
Domain Path: /languages/
Description: Website Security Protection: BulletProof Security protects your website against XSS, RFI, CRLF, CSRF, Base64, Code Injection and SQL Injection hacking attempts. One-click .htaccess WordPress security protection. Protects wp-config.php, bb-config.php, php.ini, php5.ini, install.php and readme.html with .htaccess security protection.  Security Logging. HTTP Error Logging. One-click Website Maintenance Mode (HTTP 503). Additional website security checks: DB errors off, file and folder permissions check... System Info: PHP, MySQL, OS, Server, Memory Usage, IP, SAPI, DNS, Max Upload... Built-in .htaccess file editing, uploading and downloading.
Version: .47.8
Author: Edward Alexander
Author URI: http://www.ait-pro.com/
*/

/*  Copyright (C) 2010 Edward Alexander @ AITpro.com (email : edward @ ait-pro.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define( 'BULLETPROOF_VERSION', '.47.8' );
$bps_version = '.47.8';

// Load BPS Global class - not doing anything with this Class in BPS Free
require_once( WP_PLUGIN_DIR . '/bulletproof-security/includes/class.php' );

add_action( 'init', 'bulletproof_security_load_plugin_textdomain' );

// Load i18n Language Translation
function bulletproof_security_load_plugin_textdomain() {
	load_plugin_textdomain('bulletproof-security', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
}

// Load BPS functions.php
require_once( WP_PLUGIN_DIR . '/bulletproof-security/includes/functions.php' );
	remove_action('wp_head', 'wp_generator');
	
// If in WP Admin Dashboard
if ( is_admin() ) {
    require_once( WP_PLUGIN_DIR . '/bulletproof-security/admin/includes/admin.php' );
	register_activation_hook(__FILE__, 'bulletproof_security_install');
	register_deactivation_hook(__FILE__, 'bulletproof_security_deactivation');
    register_uninstall_hook(__FILE__, 'bulletproof_security_uninstall');

	add_action( 'admin_init', 'bulletproof_security_admin_init' );
    add_action( 'admin_menu', 'bulletproof_security_admin_menu' );
}

// "Settings" link on Plugins Options Page 
function bps_plugin_actlinks( $links, $file ){
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
	if ( $file == $this_plugin ){
	$settings_link = '<a href="admin.php?page=bulletproof-security/admin/options.php">' . __('Settings', 'bulletproof-security') . '</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}
	add_filter( "plugin_action_links", 'bps_plugin_actlinks', 10, 2 );

// Add links on plugins page
function bps_plugin_extra_links($links, $file) {
	static $this_plugin;
	if (!current_user_can('install_plugins'))
		return $links;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
	if ( $file == $this_plugin ){
		$links[] = '<a href="http://forum.ait-pro.com/" target="_blank">' . __('Forum - Support', 'bulleproof-security').'</a>';
		$links[] = '<a href="http://affiliates.ait-pro.com/po/" target="_blank">' . __('Upgrade', 'bulleproof-security').'</a>';
		$links[] = '<a href="http://www.ait-pro.com/bulletproof-security-pro-flash/bulletproof.html" target="_blank">' . __('Flash Movie', 'bulleproof-security').'</a>';
	}
	return $links;
}
	add_filter('plugin_row_meta', 'bps_plugin_extra_links', 10, 2);
?>