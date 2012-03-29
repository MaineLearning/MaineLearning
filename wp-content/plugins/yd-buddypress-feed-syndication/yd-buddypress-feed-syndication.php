<?php
/**
 * @package YD_BuddyPress-feed-syndication
 * @author Yann Dubois
 * @version 2.1.0
 */

/*
 Plugin Name: YD BuddyPress Feed Syndication
 Plugin URI: http://www.yann.com/
 Description: Syndicate and aggregate any RSS feeds into your BuddyPress user or group activity feed/wall (like with Facebook).
 Version: 2.1.0
 Author: Yann Dubois
 Author URI: http://www.yann.com/
 License: GPL2
 */

/**
 * @copyright 2010  Yann Dubois  ( email : yann _at_ abc.fr )
 *
 *  Original development of this plugin was kindly funded by www.selliance.com
 *
 *	Includes patches kindly provided by Guillaume Dott @ Selliance.com
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 Revision 2.1.0:
 - Production release of 2011/08/27
 - New feature: Max feeds per user != max feeds per groups (as suggested by J.Pisano)
 - Interface styling improvements (thanks go to John/Roman & Jubal for suggestions)
 - Interface now says you can aggregate only if max allowed feeds > 1
 - Better BuddyPress feed integration for groups (thanks to Guillaume Dott @ Selliance.com )
 - Better Internationalization and localization / translation of the admin interface
 - New, more comprehensive .po/.mo files (previously existing translations need a serious update!)
 - Updated French translation files
 - Bugfix when maximum number of feeds is initially 1 (pointed by J.Pisano)
 - Bugfix for translation (thanks to Guillaume Dott @ Selliance.com )
 - Bugfix for duplicate posts (thanks to Guillaume Dott @ Selliance.com )
 - Bugfix for group creation process (thanks to Guillaume Dott @ Selliance.com )
 Revision 2.0.4:
 - Additional bugfix for when Group component is turned off
 Revision 2.0.3:
 - Bugfix to the bugfix ;-)
 Revision 2.0.2:
 - Bugfix: don't crash when BuddyPress is not loaded
 Revision 2.0.1:
 - Bugfix: don't crash when Group Component is not activated in BuddyPress Component Setup (thanks to Jubal for pointing this out)
 Revision 2.0.0:
 - Production release of 2011/08/25
 - Russian language translation kindly provided by Diogen Platonovitch ( http://platonovich.ru/ )
 - Feature: added user nickname and RSS title information
 - Feature: limit to the amount of aggregated feeds a user can add
 - Feature: group feeds
 - Feature: links can be opened in another window
 - Bugfix: "view" button next to the title
 Revision 1.1.0:
 - Production release of 2011/07/22
 - Bugfix: feeds without author (thanks to Guillaume Dott @ Selliance.com )
 - Bugfix: SimplePie default cache lifetime (thanks to Guillaume Dott @ Selliance.com )
 - Features: added buttons to force feed reloads in the option page
 Revision 1.0.0:
 - Production release of 2011/07/18
 - Italian language translation kindly provided by Czz (Giancarlo Cuzzolin @ Uajika.tk)
 Revision 0.2.0:
 - Original beta release 02
 - French language version
 - Supports setting of RSS refresh delay
 - Better management of broken RSS addresses
 Revision 0.1.1:
 - Original beta release 00
 - Slightly better feed registration interface
 Revision 0.1.0:
 - Original alpha release 00
 */

/** Class includes **/

include_once( dirname( __FILE__ ) . '/inc/yd-widget-framework.inc.php' );	// standard framework VERSION 20110405-01 or better
include_once( dirname( __FILE__ ) . '/inc/ydbfs.inc.php' );					// custom classes

/**
 * 
 * Just fill up necessary settings in the configuration array
 * to create a new custom plugin instance...
 * 
 */
global $ydbfs_o;
$ydbfs_o = new ydbfsPlugin( 
	array(
		'name' 				=> 'YD BuddyPress Feed Syndication',
		'version'			=> '2.1.0',
		'has_option_page'	=> true,
		'option_page_title' => 'YD Feed Syndication',
		'op_donate_block'	=> false,
		'op_credit_block'	=> true,
		'op_support_block'	=> false,
		'has_toplevel_menu'	=> false,
		'has_shortcode'		=> false,
		'shortcode'			=> 'ydbfs',
		'has_widget'		=> false,
		'widget_class'		=> 'YD_BuddyPressFeedSyndicationWidget',
		'has_cron'			=> true,
		'crontab'			=> array(
			'daily'			=> array( 'ydbfsPlugin', 'daily_update' ),
			'hourly'		=> array( 'ydbfsPlugin', 'hourly_update' )
		),
		'has_stylesheet'	=> false,
		'stylesheet_file'	=> 'css/yd.css',
		'has_translation'	=> true,
		'translation_domain'=> 'ydbfs', // must be copied in the widget class!!!
		'translations'		=> array(
			array( 'English', 'Yann Dubois', 'http://www.yann.com/' ),
			array( 'French', 'Yann Dubois', 'http://www.yann.com/' ),
			array( 'Italian', 'Czz', 'Uajika.tk' ),
			array( 'Russian', 'Diogen Platonovich', 'http://platonovich.ru/' )
		),
		'initial_funding'		=> array( 'Selliance', 'http://www.selliance.com' ),
		'additional_funding'	=> array( 
			array( 'Selliance', 'http://www.selliance.com' )
		),
		'form_blocks'			=> array(
			'Main options'		=> array( 
				'limit'			=> 'text',
				'group_limit'	=> 'text', 
				'open_out'		=> 'bool',
			)
		),
		'option_field_labels'=>array(
				'limit'			=> 'Maximum number of user-aggregated feeds:',
				'group_limit'	=> 'Maximum number of group-aggregated feeds:',
				'open_out'		=> 'Open feed links in new window.',
		),
		'option_defaults'	=> array(
				'limit'			=> 5,
				'group_limit'	=> 5, 
				'open_out'		=> 1,
		),
		'form_add_actions'	=> array(
				'Manually run hourly process'	=> array( 'ydbfsPlugin', 'hourly_update' ),
				'Manually force feed reload'	=> array( 'ydbfsPlugin', 'force_update' ),
				'Manually check broken feeds'	=> array( 'ydbfsPlugin', 'force_check' ),
				'Check latest'					=> array( 'ydbfsPlugin', 'check_update' )
		),
		'has_cache'				=> false,
		'option_page_text'		=> '',
		'backlinkware_text' 	=> '',
		'plugin_file'			=> __FILE__,
		'has_activation_notice'	=> false,
		'activation_notice' 	=> '',
		'form_method'			=> 'post'
 	)
);
?>