=== Plugin Name ===
Contributors: MrMaz
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8591311
Tags: buddypress, social, networking, links, rich media, embed, youtube, flickr, metacafe
Requires at least: 3.4
Tested up to: 3.4.2
Stable tag: 0.7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

BuddyPress Links is a drop-in link, image, video and other rich media sharing component for BuddyPress 1.5 and 1.6

== Description ==

#### Overview

If you're running a BuddyPress community, and would like to give your members the ability to
easily share content from across the web, this is your solution! BuddyPress Links allows your
members to quickly and easily share links, images, videos, and other rich content.

All links that are shared by your community (with privacy mode set to public) are displayed
in a central directory where your members can filter, vote, and comment on them.

> *If you are anxious to try it out, go to the fully functional demo at http://demo.presscrew.com/*

Links is fully integrated with these BuddyPress core components:

> *Profiles, Activity Stream, Widgets, Notifications, Admin Bar, Admin Dashboard*

This deep integration adds a powerful new social tool through which your members can interact:

* Your members can see the links they've submitted on their own profile, as well as other's.
* Privacy settings allow your members to have total control over who sees which links.
* A new activity stream tab is added so your members can quickly see new links which have been added by friends or by other members from across the site.
* Your members can comment and vote on each other's links.
* A powerful ranking algorithm turns your links directory into a social book-marking destination.

We've seen some amazing examples of BuddyPress Links in action. Use it to create a directory of images or videos (with built-in lightbox support) for your members to vote upon. Or use it to build a tutorial section of your community with links pointing to articles or rich media from across the web. BuddyPress Links is flexible enough to handle it all.

#### Feature List

Members can:

* Create and manage links from their profile
* Assign links to a category
* Control the visibility of their links (public, friends only, and hidden)
* Upload an image "avatar" to show with a link
* Auto embed rich media from URLs (YouTube, Flickr, and metacafe are supported)
* Automatic thumbnail picker available as of 0.2.1
* Embed a PicApp.com or Fotoglif.com image and use as the avatar
* Vote on other member's links
* Comment on other member's links
* @mentions support

Administrators can:

* Manage all links (modify, delete)
* Manage link categories (create, modify, delete)
* Enable and customize widgets

Other features include:

* "Digg style" popularity algorithm
* Rich profile and directory sorting and filtering
* Most recent links news feed
* Hundreds of action and filter hooks
* Full i18n support (need translators!)

== Screenshots ==

1. Links behaves just like a root component, complete with its own directory for links which have their privacy set to public.
2. Links for embedded videos and images play in a modal window at their original size.
3. Links is fully integrated into the activity stream, including several powerful filters for limiting results.
4. When creating a link, rich media and thumbnails are auto-detected. Quickly select a thumbnail to display for the link.
5. Edit fetched link content to clean up title and description, or to completely customize the content to your liking.
6. You can choose to use the fetched thumbnail, or to upload a custom image from your computer.
7. Each link has its own privacy setting for full control over who sees your links.
8. Uploading a custom link image (avatar) is identical to other root components.
9. Links is fully integrated into member profiles. View link related activity for yourself or another member. Conveniently create a new link directly from your profile.
10. Links activity is integrated into the profile, just like the core components.
11. A BuddyPress compatible link list widget is available to add to any sidebar. Advanced filtering and sorting is possible directly in the widget.
12. Links completely integrates with the groups component (pro version only). Group members can add links to the group. All group link activity is visible from the group’s activity page. Group administrators have full control over which links are allowed.
13. Easily share an existing link with your profile or any group you are a member of (pro version only). Use this powerful feature to track external resources for yourself, or inside of a group.
14. Links has a complete set of administration tools available in the WordPress dashboard.
15. Manage all links from a central location.
16. Manage available link categories.

== Installation ==

* BuddyPress Links 0.7.x requires WordPress 3.4 or higher with BuddyPress 1.6 or higher installed.
* BuddyPress Links 0.6.x requires WordPress 3.3 or higher with BuddyPress 1.5 or higher installed.
* BuddyPress Links 0.5.x requires WordPress 3.0 or higher with BuddyPress 1.2.6 or higher installed.
* BuddyPress Links 0.4.x requires WordPress 2.9.2 or higher with BuddyPress 1.2.x installed.
* BuddyPress Links 0.3.x requires WordPress 2.9.1 or higher with BuddyPress 1.2.x installed.
* BuddyPress Links 0.2.x requires WordPress 2.8.4 or higher with BuddyPress 1.1.x installed.

####Plugin:

1. Upload everything into the "/wp-content/plugins" directory of your installation.
1. Activate BuddyPress Links in the "Plugins" admin panel using the "Activate" link.
1. DO NOT COPY/MOVE THEME FILES TO YOUR CHILD THEME. This is no longer required as of 0.3

####Upgrading from an earlier version:

1. BACK UP ALL OF YOUR DATA.
1. The wire has been deprecated as of 0.3. ALL LINKS WIRE POSTS WILL BE LOST!
1. This version can use data created by previous versions, assuming you are porting your site to the new BP 1.2 default theme!

####Warning!

The 0.3.x and higher branches are not backwards compatible with the BuddyPress 1.1.x branch, or compatible with the 1.2.x classic theme.
The links data from the 0.2.x branch is compatible with 0.3.x and higher, except that all links wire posts will be lost.

== Upgrade Notice ==

= 0.7 =

No changes that affect data were made, however it is always a good idea to back up your data just in case!

= 0.6 =

No changes that affect data were made, however it is always a good idea to back up your data just in case!

= 0.5 =

No changes that affect data were made, however it is always a good idea to back up your data just in case!

= 0.4 =

BACK UP YOUR DATA! DO NOT attempt to install version 0.3 or higher on BP 1.1.X!  DO NOT try to use this plugin with the classic theme!

= 0.3 =

DO NOT attempt to install version 0.3 or higher on BP 1.1.X!  DO NOT try to use this plugin with the classic theme!

= 0.2 =

This version contains the first support for rich media embedding. *Please make sure that you update the "links" directory in your theme (see Installation).*

== Changelog ==

= 0.7.1 =

* Removed custom cron scheduling due to theme conflicts
* Fixed pagination of links manager admin screen
* Killed colorbox dynamic image window sizing in favor of fixed size
* Minor fixes to resolve issues related to WordPress 3.5

= 0.7 =

* Added new settings screen, sweet!
* Added activity nav item position setting
* Vote panel template tags now print unescaped html instead of passing to sprintf()
* Cleaned up admin screen html, added sidebar with upgrade links and other info

= 0.6.6 =

* Fixed friends only privacy setting was displayed even when friends component disabled
* Fixed admin bar items showing up when user not logged in
* Fixed issue with slugs containing extended characters
* Fixed dashboard styles that were in conflict with other plugins

= 0.6.5 =

* Fixed embedded video issues caused by responsive themes
* Upgraded to Colorbox 1.3.2
* Fixing missing sidebar on create link page
* Additional BuddyPress 1.6.x fixes

= 0.6.4 =

* Removed group integration
* Removed profile sharing

= 0.6.3 =

* PHP 5.4.x compatibility fixes
* BuddyPress 1.6.x compatibility fixes

= 0.6.2 =

* Wrap link loop item category with span
* Clean up style enqueueing
* Added constant to force external link from directory listing

= 0.6.1 =

* Sidebar widget works again
* Automatically fix URLs which are entered without http://
* Fixed link list pagination issues
* Fixed possible link list filtering issues
* Fixed AJAX spinner when fetching details

= 0.6 =

* Upgraded to latest version of colorbox
* Moved dashboard menu to its own top level spot
* Fixed navigation menu formatting
* Fixed create/edit form errors not displaying
* Fixed issue with videos not playing
* Fixed double link in global navigation
* Fixed link home sub-navigation not displaying
* Fixed avatars not displaying properly
* Fixed broken custom avatar uploading
* Many additional minor BuddyPress 1.5 compatibility fixes

= 0.5 =

* Tested with WordPress 3.x and BuddyPress 1.2.6
* Improved compatibility when groups component is disabled
* Improved compatibility when activity component is disabled
* Added configuration constant for disabling groups integration
* Added configuration constant for using select box for categories on create form
* Added filter to bp_links_is_url_valid() to allow extended validation
* Fixed pubdate bug in feed generator
* Fixed linkmeta bug where empty values where being passed to array_map()
* Updated RU translation, props SlaFFik

= 0.4.1 =

* Fixed comment count bug
* Fixed nasty bug that caused filtering not to work for specific translations
* Fixed some translatable string issues, props SlaFFik
* Updated RU translation, props SlaFFik

= 0.4 =

* Initial group integration support added
* Added profile and group sharing features
* Create link directly from user profile and group pages
* Moved link list update/error messages to inside the current link's li block
* Added external link icon next to main link URL on the link list
* All link list targets and rels are no longer set by default and must be explicitly set with a filter
* All link list content is now separately filterable for finer control over URLs and content
* Load members profile links using plugins template instead of members home action
* Link description can be configured as optional with a constant
* Usability fixes to the link create/admin form (props Mike Pratt)
* Changing the component slug is now officially supported
* Heavy duty javascript refactoring

= 0.3.2 =

* Fixed broken paging
* Fixed bug with status check in some queries
* My Links now correctly only shows the displayed user's links
* My Links activity now correctly only shows the displayed user's links activity

= 0.3.1 =

* Fixed nasty SQL query bug, big props to windhamdavid
* Fixed broken category filtering that affected recently active links for single user
* Updated French translations, props Chouf1
* Added German translation, props Michael Berra
* Added Swedish translation, props Ezbizniz

= 0.3 =

* Baseline BuddyPress 1.2 support, REQUIRES BP 1.2 or higher
* Removed classic theme support (may re-support in the future if there is a huge demand)
* Wire support has been dropped and replaced with the activity stream
* Deep and seamless activity stream integration, complete with RSS feeds
* @mentions support, complete with e-mail notifications
* Lightbox for viewing photos and videos without leaving the site
* Moved template files to plugin dir to ease future upgrading
* Added support for template overriding from child theme
* Moved link loop item HTML from hard coded PHP to a template (links-loop-item.php)
* Added the much requested filters for link REL and TARGET
* Completely hooked into default theme AJAX (no duplicate functionality)
* Removed redundant "Home" link from link list
* Major overhaul of how we hook into the dashboard
* Replaced full blown widget with a basic widget based on groups
* Replaced custom elapsed time function with bp_core_time_since for continuity
* Added filters for changing navigation tab names.
* Fixed many old bugs

= 0.2.1 =

* Added support for auto embedding standard web pages
* Added automatic thumb picker for rich web pages
* Fixed layout bug that was affecting all webkit browsers
* Some other minor bug fixes

= 0.2 =

* Added support for auto-embedding of rich media (API documentation coming soon!)
* Reduced create/admin form to one page
* Wider selection of thumb sizes for the links widget
* Many CSS improvements and fixes
* Lots of general refactoring
* Some minor bug fixes

= 0.1 =

* First beta versions
* Many, many i18n fixes
* A few bug fixes

== Frequently Asked Questions ==

= What is the license? =

Released under the GNU GENERAL PUBLIC LICENSE 3.0 (http://www.gnu.org/licenses/gpl.txt)

All original code is Copyright (c) 2009 Marshall Sorenson. All rights reserved.

= How do I customize the default templates? =

To override only certain templates from the bp-links-default theme directory,
create a directory named "bp-links-default" in your child theme,
and replace the template using the EXACT same path AND filename.

To create a totally custom theme in order to completely bypass any core links
themes you will need to define a custom theme name.

For example, if your active WordPress theme is 'bluesky', and you wanted
to define your links theme as 'links-custom', you would put your files in:

/path/to/wp-content/themes/bluesky/links-custom

And in wp-config.php you would place this define statement:

define( 'BP_LINKS_CUSTOM_THEME', 'links-custom' )

To find out which template files are required to exist, do a recursive search for 'bp_links_load_template'

= Where can I get support? =

The support forum for the 0.4 branch can be found here: http://buddypress.org/forums/topic/buddypress-links-04x-releases-and-support

= Where can I find documentation? =

Coming soon

= Where can I report a bug? =

Look for MrMaz in #buddypress-dev

Or on buddypress.org http://buddypress.org/community/members/MrMaz/

Or on his website http://marshallsorenson.com/

Please search the forums first!!!
