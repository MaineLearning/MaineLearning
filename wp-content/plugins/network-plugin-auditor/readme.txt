=== Plugin Name ===
Contributors: ksemel
Donate Link: http://bonsaibudget.com/donate/
Tags: network, multisite, plugin management, admin
Requires at least: 3.2.1
Tested up to: 3.3.1
Stable tag: trunk

For multisite/network installations only.  Adds columns to your network admin to show which sites are using each plugin.

== Description ==

As my wordpress network grew I found it challenging to track which plugins were used on which sites as the only way to check was to visit each dashboard one at a time.  What a hassle!

This plugin adds a column to your network admin to show which sites have each plugin active (on the plugin page), and which plugins are active on each site (on the sites page). Now you can easily determine which plugins are used on your network sites and which can be safely removed.

== Installation ==

1. Upload the files to the /wp-content/plugins/ directory.
2. Network Activate the plugin through the 'Network Plugins' menu in WordPress

== Changelog ==

= 1.0.1 =

Bug fix: Check column_name before adding the output (Thanks to gabriel-reguly for the catch!)

= 1.0 =

Initial release

== Upgrade Notice ==

= 1.0 =

Initial release

== Frequently Asked Questions ==

= Will this plugin work with my single-site wordpress installation? =

No, the columns are only added in the network admin dashboard.  There is no change on the normal site dashboards so there is nothing to see on a single-site installation.

== Screenshots ==

1. Plugin Active on the Network Plugins page
2. Plugin Active on the Network Sites page