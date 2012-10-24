=== Network Privacy ===
Contributors: wpmuguru, cgrymala, scribu
Tags: privacy, plugin, wordpress, network 
Requires at least: 3.3
Tested up to: 3.5
Stable tag: 0.1.4

Adds more privacy options to both single Wordpress sites and WordPress networks.

== Description ==

This plugin adds 2 privacy options in a single install and 3 in a network install. In a network install the network administrators (Super admins) have the option to set the privacy setting for the entire network. When the privacy is set for the entire network, the extra privacy options are not shown on individual site's Settings -> Privacy.

*Features*

In a single site WordPress install adds the following privacy options:

*	I would like my site to be visible only to Site subscribers.
*	I would like my site to be visible only to Site administrators.  

In a WordPress network activated on an individual site adds the following privacy options:

*	I would like my site to be visible only to Registered network users.
*	I would like my site to be visible only to Site subscribers.
*	I would like my site to be visible only to Site administrators.  
*	I would like my site to be visible only to Site contributors (and above)

When Network Activated or in the mu-plugins folder in a WordPress network adds the following:

*	A privacy selector in the Super Admin -> Options page to allow individual site privacy or the 3 above across to network.

Support can be obtained through:

[Try the Wordpress Forums first](http://wordpress.org/support/plugin/network-privacy)

[Twitter](http://twitter.com/wpmuguru)

[Ron & Andrea](http://ronandandrea.com/contact/)

== Installation ==

1. To install in the mu-plugins folder Upload `ra-network-privacy.php` to the `/wp-content/mu-plugins/` directory. It will be listed in your "Must Use" plugins list and always active.
1. To install in the plugins folder Upload the `network-privacy` folder to the `/wp-content/plugins/` directory. It will be listed in your regular plugins list.
1. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 0.1.4 =
* add 'ra_network_privacy_caps' filter
* change default cap for administrators from add_users to promote_users
* insert fix for BuddyPress [thanks to Simon Wheatley](http://wordpress.org/support/topic/plugin-network-privacy-problem-with-buddypress)

= 0.1.3 =
* Fix bug in "Network Users" option

= 0.1.2 =
* update to be compatible with WP 3.3
* remove Edit site option due to hook changes in WP

= 0.1.1 =
* fix login redirect check for public & non public sites

= 0.1 =
* Original version.
