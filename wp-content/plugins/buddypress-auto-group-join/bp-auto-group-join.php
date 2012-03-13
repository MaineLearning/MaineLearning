<?php
/*
Plugin Name: BuddyPress Auto Group Join
Plugin URI: http://twodeuces.com/wordpress-plugins/buddypress-auto-group-join
Description: This BuddyPress component allows admin's to join members to selected groups. It will also auto join new members to the same selected groups.
Version: 2.2.1
Revision Date: Dec 30, 2011
Requires at least: WP 3.3.0, BuddyPress 1.5.0 
Tested up to: What WP 3.3.0, BuddyPress 1.5.2
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: Scott Hair
Author URI: http://twodeuces.com
*/

/*  Copyright 2010 - 2011 Scott Hair  (email : twodeuces@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
This plugin is an adoptation of the Auto Join Group Plugin by Brent Layman. It has been modified to include the Auto Join Groups section in the profile and list the group names automatically.
Place this folder in your plugins folder.  In the admin panel, configure under "BuddyPress -> Auto Join Groups" 
*/

/* 
Acknowledgements: Chestnut contributed corrections for multi-site vs single site compatibility issues as well as adding multiple language support for the plugin.
/*

Changelog
2.2.1 - Corrected date information.
2.2.0 - Corrected groups active logic - suggestion by Jeremy
2.1.4 - Correcting errors with svn.
2.1.0 - Corrected Multi-site vs Single-site issue and added language support.
2.0.4 - Corrected max group display issue.
2.0.3 - Corrected minor gramitical errors. No code change.
2.0.2 - Re-Release (Dec 16, 2010) for use with Wordpress 3.0.0 - Extensive code rework. 
1.0.0 - Initial Public Release (May 10, 2010) - No coding changes made.
0.1 - Initial Private Release (Apr 6, 2010)
*/


/******* 
* Check for object name conflicts. If one does not exists then load the required class file and instantiate an object.
* - Also defines plugin directory and url constants for later use.
* - Will also initiate actions and filters that do not require buddypress to be loaded first.
* - Any action or filter that is dependent upon buddypress loading first is included in the bp-auto-group-join-init.php file
*******/
/**
 * Added by chestnut_jp starts
 */
load_plugin_textdomain('bpAutoGroupJoin', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/languages');
/**
 * Added by chestnut_jp ends
 */
if (!class_exists("BuddyPressAutoGroupJoinObject")) {						// Checks for name conflict
	
	/* Define the path and url of the plugins directory. */
	define('AGJ_PLUGIN_DIR', WP_PLUGIN_DIR . '/buddypress-auto-group-join');
	define('AGJ_PLUGIN_URL', plugins_url( $path = '/buddypress-auto-group-join'));
	
	/* Load object file */
	require_once(AGJ_PLUGIN_DIR . '/php/bp-auto-group-join-class.php');	
	
	/* Instantiate a new object */
	if (class_exists("BuddyPressAutoGroupJoinObject")) {
		$autoGroupJoin = new BuddyPressAutoGroupJoinObject(); 
	}

	/*******
	* This function loads the init file after buddypress has been loaded.
	* - Used with bp_include action hook.
	*******/ 
	function bp_auto_group_join_init() {
		require(AGJ_PLUGIN_DIR . '/php/bp-auto-group-join-init.php');
	}

	/*******
	* This section is used for code not requiring buddypress to be loaded.
	* - Keep in mind, more action and filter hooks are added in the init file.
	*******/
	if (isset($autoGroupJoin)) {
		// Activation Hook
		
		// Actions
		add_action('bp_include', 'bp_auto_group_join_init');
		
		// Filters
		
		// Deactivation Hook
		
		// Add Shortcodes
	}
}
?>
