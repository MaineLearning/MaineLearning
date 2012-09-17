=== Bebop ===
Contributors: Dale Mckeown
Tags: WordPress, BuddyPress, OER, Open Educational Resources, Rapid Innovation, JISC, LNCD.
Tested on: WordPress 3.4.2, BuddyPress 1.6.1


== Licence ==
Released under the GNU General Public Licence - https://www.gnu.org/copyleft/gpl.html
Copyright 2012 The University of Lincoln - http://www.lincoln.ac.uk.


== Description ==
Bebop is the name of a rapid innovation project funded by the Joint Information Systems Committee (JISC) and developed by the University of Lincoln. The project involved the utilisation of OER's from 3rd party providers such as YouTube, Vimeo, SlideShare and Flickr.

Requirements.
- PHP 5.2.1+

== Installation ==
1. Upload this plugin to your '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure extensions in the 'OER Provider' menu.

== Changelog ==

v1.0.1 - 11.09.2012 - minor release.

This version adds a couple of extra features, but more importantly fixes a major bug regarding import limits and importing items.

Feature Updates:
1. RSS feeds are not shown for bebop content in the activity streams. The extension RSS feed for the user is shown, and the "all OER" feed is shown if more than one feed is active.
2. The Twitter extension has been updated to conform with the new Twitter API.
3. The admin interface has been replaced by standard WordPress and BuddyPress markup, resulting in a more "WordPressy" feel.
4. The user interface has been replaced by standard WordPress and BuddyPress markup, resulting in a more "WordPressy" feel.
5. Import scripts that require an API key now log to the general log instead of the error log incase a API key is not found. Changed because it is not specifically a Bebop error, but a user error.
6. Changed admin settings link in OER providers to a button.
7. Changed 'Generic RSS' extension to 'Feed' Extension.


Bug fixes
1. A bug was fixed which made the OER filter tab dislay the wrong text.
2. RSS feeds are closer to validating properly.
3. Fixed deactivation bug where "bebop_tables()" class is not found.
4. Fixed import twitter bug where no username is specified.
5. Fixed error log not displaying properly.
6. Fixed a user settings bug where edit options were available but no API key is set in the admin panel.
7. Fixed bug where the same content is loaded when the 'load more' button is clicked.
8. Fixed a bug where the OER filter is displaying extra content filters from other plugins.
9. Fixed a bug that stopped OER content from being imported if no import limit was set for an extension.
10. Fixed a bug where admin error messages were showing the "settings saved" when they should return an error.
11. Fixes a bug where aditional filters from other plugins were being added to the OER filters.
12. Fixed bug where it is not possible to reset a deleted OER.


Other
1. Support email changed to github wiki.
2. Removed the "break;" from import scripts - to retrieve older data if an import limit is removed.
3. Changed some terminology here and there.

v1.0 - 30.08.2012 - Initial release.