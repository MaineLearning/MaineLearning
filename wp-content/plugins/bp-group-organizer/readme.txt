=== BP Group Organizer ===
Contributors: ddean
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=6BKDCMJRYPKNN&lc=US&item_name=BP%20Group%20Organizer&currency_code=USD
Tags: buddypress, group, groups, edit, organize, move, drag and drop
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 1.0.7

Easily create, edit, and delete BuddyPress groups - with drag and drop simplicity

== Description ==

This plugin creates an administrative interface for organizing all of your BuddyPress groups.  

Based on the WordPress Menu editor, BP Group Organizer makes it simple to get your BuddyPress groups just the way you want them, or just to get a handle on the growth of your site.

* Easily create groups in one step
* Edit multiple groups and group properties without waiting for page reloads
* Delete unwanted groups in a flash

For users of BP Group Hierarchy, this plugin also allows you to move groups around the hierarchy by dragging and dropping. Get your groups just the way you want them, quickly and easily.

== Installation ==

1. Install BP Group Organizer through the WordPress Plugin Directory

 OR
 
1. Unzip `bp-group-organizer.zip`
1. Upload the contents to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

No questions yet.

== Screenshots ==

1. The organizer

== Changelog ==

= 1.0.7 =
* Fixed: short open tag introduced in 1.0.6 - thanks, nfocom

= 1.0.6 =
* Added: Import / export group structure to CSV
* Added: support for bbPress group forums
* Added: support for BP 1.7 Groups admin menu
* Changed: updated buttons for WP 3.5

= 1.0.5 =
* Fixed: group forums were not fully initialized for groups created in the Organizer - thanks, thosch
* Changed: dropped BP 1.2 compatibility; no code was removed, but this release is not tested against BP 1.2

= 1.0.4 =
* Added: Group avatars displayed in the organizer
* Changed: remove a bunch of legacy nav menu code; may improve speed for sites with lots of groups
* Changed: trimmed JS load a little more
* Fixed: slashes would multiply in group name and description when saving - thanks, zanzaboonda

= 1.0.3 =
* Added: Organizer handle changes to indicate when a group is Private or Hidden
* Changed: no longer Network-only
* Changed: reduced JS size for organizer; should improve speed
* Fixed: some bugs that triggered warnings

= 1.0.2 =
* Changed: switched to load_plugin_textdomain for translation
* Fixed: resolved a rendering issue with WP 3.3

= 1.0.1 =
* Added: plugin description
* Added: textdomain for translation
* Changed: disabled forum options if Discussion Forums are turned off
* Changed: strings to use BuddyPress versions where possible
* Fixed: admin link rendered incorrectly on some BP 1.2.x installs

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.0.7 =
Fixed short open tag - all users with short_open_tag off should upgrade immediately

= 1.0.6 =
Import / export group structure, other updates

= 1.0.5 =
Bug fix for group forums

= 1.0.4 =
Bug fix for groups with quotes in name or description

= 1.0.3 =
Bug fixes and group status indicator

= 1.0.2 =
Updated for WP 3.3 and with better translation support

= 1.0.1 =
Fixed issue with admin link and generally cleaned things up 

= 1.0 =
Initial release
