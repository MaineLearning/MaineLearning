=== WP Accessibility ===
Contributors: joedolson
Donate link: http://www.joedolson.com/donate.php
Tags: title, accessibility, accessible, navigation, wcag, a11y
Requires at least: 3.4.2
Tested up to: 3.4.2
Stable tag: trunk
License: GPLv2 or later

WP Accessibility provides options to improve accessibility issues in your WordPress site. 

== Description ==

This plug-in is targeted towards correcting a variety of accessibility issues frequently encountered in WordPress themes. This plug-in will probably change frequently, as I add support for additional issues or (hopefully) remove features that are no longer needed. 

At the moment, the plug-in can:

* Remove redundant title attributes from page lists, category lists, and archive menus. 
* Enable skip links with WebKit support by enqueuing JavaScript support for moving keyboard focus.
* Add skip links with user-defined targets.
* Add language and text direction attributes to your HTML attribute
* Remove the target attribute from links.
* Force a search page error when a search is made with an empty text string. 
* Remove tabindex from elements that are focusable.
* Strip title attributes from images inserted into content. 
* Add post titles to standard "read more" links.

The plug-in is intended to make up for some deficiencies commonly found in themes. It can't correct every problem (by a long shot), but can provide some assistance.

== Installation ==

1. Download the plugin's zip file, extract the contents, and upload them to your wp-content/plugins folder.
2. Login to your WordPress dashboard, click "Plugins", and activate WP Accessibility.
2. Customise your settings on the Settings > WP Accessibility page.

== Changelog ==

= 1.0.0 =

* Initial release!

== Frequently Asked Questions ==

= WP Accessibility is inserting some information via javascript. Is this really accessible? =

Yes. It does require that the user is operating a device that has javascript support, but that encompasses the vast majority of devices and browsers today, including screen readers.

= I installed WP Accessibility and ran some tests, but I'm still getting errors WP Accessibility is supposed to correct. =

Even if WP Accessibility is running correctly, not all accessibility testing tools will be aware of the fixes. Here's a resource for more information: [Mother Effing Tool Confuser](http://mothereffingtoolconfuser.com/).

== Screenshots ==

1. Settings Page

== Upgrade Notice ==

* No notices yet!