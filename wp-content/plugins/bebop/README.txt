=== Bebop ===

Contributors: Dale Mckeown
Tags: WordPress, BuddyPress, OER, Open Educational Resources, ukeor, oerri, Rapid Innovation, JISC, LNCD, The University of Lincoln, Facebook, Twitter, SlideShare, Youtube, Vimeo, RSS.

Tested up to: WordPress 3.5.1, BuddyPress 1.6.4


== Licence ==

Released under the GNU General Public Licence - https://www.gnu.org/copyleft/gpl.html
Copyright 2013 The University of Lincoln - http://www.lincoln.ac.uk.


== Description ==

Bebop is a BuddyPress plugin which allows you to connect your BuddyPress profile to other social media platforms such as Twitter, Flickr, Slideshare, Youtube and Vimeo. Bebop is an aggregator and curatorial tool, pulling content from other web services into your BuddyPress activity timeline. It will also pull in content from RSS/Atom feeds, so will work with sites like Tumblr, WordPress and Blogger.
Bebop gives you control over exactly what items of content appear in your activity stream and works on WordPress network installs, too.
Bebop was developed by the University of Lincoln for a JISC-funded 'Open Educational Resources' project, to allow teachers to select and aggregate teaching resources they have shared on third-party sites into their BuddyPress profile. BuddyPress and Bebop are being used at the University of Lincoln to power staff and student profiles.

German (de_DE) Language pack courtesy of HansRuedi Keller (http://www.info.ch)
=======

== Installation ==

Requirements.
- PHP 5.2.1+

Instructions:
1. Upload this plugin to your '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

OR

Install via the 'Add New' button on your plugins page.

THEN

Configure extensions in the 'OER Provider' admin menu.

Please read the documentation on Github: http://github.com/lncd/bebop/wiki

Please report any bugs or issues on Github: http://github.com/lncd/bebop/issues
=======

== Changelog ==

v1.3.1 - 11.03.2013 - Minor release, but please backup your database before upgrading!
* Fixed the upgrade error in 1.3.0.
* Rewrote database checks for checking secondary ids.
* added twitter developer link.
* Fixed multiple spelling mistakes and language file inconsistencies.


v1.3.0 - 07.03.2013 - Major release - Please backup your database before upgrading!
* Rewrote all secondary id's to be universal. Also fixed icompatability issues with 32bit operating systems.
* Fixed some minor WP 3.5.1 and BP 1.6.4 compatability issues.
* Better garbage collection on PHP 5.3
* Added 'Resources' tab to the wp-buddypress admin bar.
* Added tutorials for obtaining and installing API keys.


v1.2.2 - 21.12.2012 - Minor Release

* Fixed WP 3.5 compatability issues.

v1.2.1 - 08.11.2012 - Minor Release

* This update addresses an issue which accidentally allowed hidden content from groups to be displayed on the activity stream.

v1.2 - 02.11.2012 - Major Release

This is a Major release, providing a new Facebook extension, and adding pagination where required. This release also fixes bugs with RSS feeds, user removal, and corrects many spelling mistakes. Enjoy!

Feature Updates:
* A brand new Facebook extension is added in this release, allowing content from facebook to be displayed in the activity stream.
* Increased RSS output limit from 250 to 500.
* Moved the user verification option from the general settings to the extension settings, allowing content from certain extensions to be posted to the activity stream automatically, while other extensions can still be verified.
* Added admin option in each extension to hide content for that extension form the sitewide activity stream.
* Added pagination to all admin and user content where needed, such as admin backend logs, content, and user content manager.
* Added German (de_DE) languae pack, courtesy of HansRuedi Keller (http://www.info.ch)


Bug Fixes:
* RSS - Fixed a bug which only allowed 20 items to be returned at a time fro RSS feeds.
* RSS - Redefined pubDate in the RSS feeds as the date the item was uploaded to the original source.
* RSS - Added wpPubDate the the RSS feeds and defined it as the date it was added to BuddyPress.
* Content Stream - had to allow bebop-core-user.php to be included on every page so the new 'hide_sitewide' feature works as expected.
* Admin Area - forced checkboxes in forms to be on same line as text.
* Admin Area - Fixed the user removal bug, so admins can actually remove users from extensions now...


Other:
* Corrected various spelling mistakes.


v1.1.1 - 25.09.2012 - Minor Release

This is a minor update which fixes some bugs found in version 1.1.

Feature Updates:
* Made RSS feed link buttons only visible when the user is importing content. Prevents clutter of buttons when a user is not actively using an extension.


Bug Fixes:
* Fixed a bug which was overriding the custom bebop activity feed query when first loaded, which caused all content to be displayed, not just Bebop generated content.
* Fixed an issue with updating the main WordPress cron time.
* Stopped admin secondary buttons from having an underline link.
* Fixed a page redirection error which was redirecting to the site URL when trying to access another users resources.


Other:
* Fixed an issue with with strange log messages of no consumer key is set for an extension.
* Added some padding to admin secondary buttons.
* .gitignore'd a few other unnecessary files and folders.
* Updated the readme.txt file.
* Changed feed back to RSS
* Changes some admin terminology so that it suites our social media users a little better.


v1.1 - 24.09.2012 - Major Release

This version changes some fundamental aspects of the plugin source code. See features for details.

Feature Updates:
* When a new account is added to an extension, an initial import is fired to allow content to become available in the unverified resources without having to wait for the cron import to do its magic.
* Introduced a secondary cron which scans a new database table every 15 seconds to detect feeds which are due an initial import. This means the default cron can be ran less often, while new users/feeds can still import content.
* Changed default cron time to 10 minutes to support the above feature.
* Updated all extension import scripts to allow processing of the new cron.
* Changed the logo for the plugin.
* Improved RSS atom compatibility.
* Added some hooks in relevant places to allow further extensions to be developed and easily hooked into bebop's functionality.
* Moved RSS links to top of the Teaching Resources home tab, to prevent repetition and to make feeds visible even with no-one logged in.
* Made it easier for extensions to hook into bebop.
* Separated admin functions from normal functions to increase performance.
* Placed buttons in the admin general settings which can automatically fire a cron. This could aid problem solving with users if they cannot get content to import.
* Added another general settings option which allows content to be either verified automatically, or be verified by the user through the OER manager.


Bug Fixes:
* Removed importer queue as it was no longer required.
* Fixed a rather complexed quote escaping problem in user feeds.
* Fixed a bug which displayed the user's activity stream if no OER extensions are active.
* Fixed an issue which forced the RSS feeds generator to always output results for 'all_oers' regardless of what content supposed to display.
* Prefixed some functions to prevent some possible name conflicts with other possible plugins.
* Fixed a Flickr import bug which accidentally removed data from the OER manager table.
* Fixed a bug which made it difficult to remove a users feed from a regular extension.
* Fixed a bug which threw a fatal error if BuddyPress is not active when Bebop is activated.
* Fixed a bug which overwrote standard  WordPress CSS rules.


Other:
* Made a few import error/log messages a bit more clear.


v1.0.1 - 11.09.2012 - minor release.

This version adds a couple of extra features, but more importantly fixes a major bug regarding import limits and importing items.

Feature Updates:
* RSS feeds are not shown for bebop content in the activity streams. The extension RSS feed for the user is shown, and the "all OER" feed is shown if more than one feed is active.
* The Twitter extension has been updated to conform with the new Twitter API.
* The admin interface has been replaced by standard WordPress and BuddyPress markup, resulting in a more "WordPressy" feel.
* The user interface has been replaced by standard WordPress and BuddyPress markup, resulting in a more "WordPressy" feel.
* Import scripts that require an API key now log to the general log instead of the error log incase a API key is not found. Changed because it is not specifically a Bebop error, but a user error.
* Changed admin settings link in OER providers to a button.
* Changed 'Generic RSS' extension to 'Feed' Extension.


Bug Fixes:
* A bug was fixed which made the OER filter tab display the wrong text.
* RSS feeds are closer to validating properly.
* Fixed deactivation bug where "bebop_tables()" class is not found.
* Fixed import twitter bug where no username is specified.
* Fixed error log not displaying properly.
* Fixed a user settings bug where edit options were available but no API key is set in the admin panel.
* Fixed bug where the same content is loaded when the 'load more' button is clicked.
* Fixed a bug where the OER filter is displaying extra content filters from other plugins.
* Fixed a bug that stopped OER content from being imported if no import limit was set for an extension.
* Fixed a bug where admin error messages were showing the "settings saved" when they should return an error.
* Fixes a bug where additional filters from other plugins were being added to the OER filters.
* Fixed bug where it is not possible to reset a deleted OER.


Other:
* Support email changed to Github wiki.
* Removed the "break;" from import scripts - to retrieve older data if an import limit is removed.
* Changed some terminology here and there.

v1.0 - 30.08.2012 - Initial release.