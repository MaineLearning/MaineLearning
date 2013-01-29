=== Plugin Name ===
Contributors: ksemel
Donate Link: http://bonsaibudget.com/donate/
Tags: network, multisite, plugin management, theme management, admin
Requires at least: 3.2.1
Tested up to: 3.5
Stable tag: trunk

For multisite/network installations only.  Adds columns to your network admin to show which sites are using each plugin and theme.

== Description ==

As my wordpress network grew I found it challenging to track which plugins and themes were used on each site, and the only way to check was to visit each dashboard one at a time.  What a hassle!

This plugin adds a column to your network admin to show which sites have each plugin active (on the plugin page), and which plugins are active on each site (on the sites page), and the active theme on each blog (on the themes page). Now you can easily determine which plugins and themes are used on your network sites and which can be safely removed.

== Installation ==

1. Upload the files to the /wp-content/plugins/ directory.
2. Network Activate the plugin through the 'Network Plugins' menu in WordPress

== Changelog ==

= 1.3.2 =

- Reduced transient name length to under 45 characters

= 1.3.1 =

- Fixed a bug where the primary blog would show all available themes as active even if they were not.
- Fix over-long transient names in db fields

= 1.3 =

- Fixed Wordpress 3.5 compatibility issues

= 1.2 =

- Fixed an issue where the database prefixes were not determined correctly (Thank you montykaplan for your debugging log info!)
- Added messaging for the case where the database prefix is blank (which isn't supported in multisite as of 3.3)

= 1.1 =

- Added support for Themes.  Now shows which themes are actually used and by which blog in your themes list
- Stored some of the more intensive queries in the transient cache to improve performance
- Improved error handling

= 1.0.1 =

Bug fix: Check column_name before adding the output (Thanks to gabriel-reguly for the catch!)

= 1.0 =

Initial release

== Upgrade Notice ==

= 1.3.2 =

- Reduced transient name length to under 45 characters

= 1.3.1 =

- Fixed a bug where the primary blog would show all available themes as active even if they were not.
- Fix over-long transient names in db fields

= 1.3 =

- Fixed Wordpress 3.5 compatibility issues

= 1.2 =

- Fixed an issue where the database prefixes were not determined correctly (Thank you montykaplan for your debugging log info!)
- Added messaging for the case where the database prefix is blank (which isn't supported in multisite as of 3.3)

= 1.0 =

Initial release

== Frequently Asked Questions ==

= Will this plugin work with my single-site wordpress installation? =

No, the columns are only added in the network admin dashboard.  There is no change on the normal site dashboards so there is nothing to see on a single-site installation.

= All my blogs are showing blank columns except for the first! =

Please update to version 1.2 for improved support for custom database prefixes.

= Can I use this plugin as an Must-Use plugin? =

Yes!  Just copy the network-plugin-auditor.php file to your mu-plugins folder.  Be aware that you will not receive automatic notices of updates if you choose to install the plugin this way.

== Screenshots ==

1. Plugin Active on the Network Plugins page
2. Plugin Active on the Network Sites page
3. Plugin Active on the Network Themes page