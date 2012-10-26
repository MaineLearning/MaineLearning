=== WPBook ===
Contributors: johneckman, davelester, BandonRandon
Tags: facebook, platform, application, blog, mirror
Stable tag: 2.5.4
Tested up to: 3.4
Requires at least: 2.9.0

Plugin to embed WordPress Blog into Facebook Platform. Requires PHP 5. 

Requires ability to access your WordPress blog via HTTPS (SSL). 


== Description ==

NOTE: Major changes between 2.0.x and versions 2.1 or later. 
Please see: http://wpbook.net/docs/upgrade/ 
for information on how to upgrade if you used 2.0.x previously. 

WPBook enables users to add your (self-hosted, not wordpress.com) wordpress 
blog as a Facebook application. Facebook users will see your posts in a 
Facebook look and feel, and can leave comments with their Facebook identity. 

Comments are shared - meaning comments made by users on your blog at its 
regular domain and comments made by users inside Facebook are all shown to 
users of either "view" of your content. 

Facebook users can also add a profile tab to a Facebook page, using the 
"add profile tab" button at the top of the default canvas page. 
(NOTE: Facebook no longer allows the addition of tabs by applications
to the Facebook profiles of individual users). 

WPBook also post notifications automatically to your wall, or the wall
of pages for which you are an admin, to which you've added the app, and 
for which you've granted stream publish permission, when you write a new post.

(This includes Application Profile pages and group walls, if you are an admin
and have enabled fans to write on your walls). 

Finally, WPBook can also import comments made on your wall (or the wall of
a Fan page) in response to excerpts it has posted, and show those in your 
WordPress blog as full comments. 

As of 1.5, this plugin requires PHP 5. 

== Installation ==

(Note: Best installation instructions: http://wpbook.net/docs/ )

1. Copy the entire wpbook directory into your wordpress plugins folder,
   /wp-content/plugins/

   You should have a directory structure like this:
   /wp-content/plugins/wpbook/wpbook.php
   /wp-content/plugins/wpbook/theme/
   /wp-content/plugins/wpbook/client/


2. Set up a New Application at http://www.facebook.com/developers/, obtaining
   a secret and API key.  

   (See http://wpbook.net/docs/ for screensheets of FB settings)

3. Login to Wordpress Admin and activate the plugin

4. Using the WPBook menu, (Dashboard->Settings->WPBook) fill 
   in the appropriate information including Facebook application secret 
   and app ID, as well as your application canvas url. 

5. (OPTIONAL) If you'd like, copy wpbook_theme into your wp-content/themes/ 
   directory and customize the css, or edit the html directly in 
   index.php. If this theme (named 'WPBook') is found installed, WPBook
   will use it rather than the built in theme. This theme will NOT 
   be overwritten by updates unless you choose to copy it. 

== Frequently Asked Questions ==

= How do I edit the way my Facebook Application (mirrored blog) looks? =

In the wpbook/theme directory, there is an index.php file.  Most of 
what you want is there.  

There's also a default/style.css which basically mimics Facebook's styles, 
as well as some other files for processing comments and the like.  

= WPBook picks and image at random for each of my posts! =

Actually, what's happening is that Facebook is choosing an image at 
random for your posts. 

This happens because either you have not set a "featured image" for the 
post, or your theme doesn't support "featured images." 

If you haven't set a featured image, do set one - WPBook will pass that
to Facebook along with the post. 

If your theme doesn't support featured images, you'll need to add
code to your theme's functions.php file. 

See:
http://wordpress.org/support/topic/how-do-i-add-featured-image-support-to-any-theme

== Changelog ==

= 2.5.4 = 
 * Fixed typo in wpbook_cron.php that would throw warnings in debug.log 
 * Tightened handling of $wpbook_message post_meta - should now not save if
   empty or undefined 
 * Fixed bad define statement in publish_to_facebook.php. Resulted in warnings
   for users whose permissions did not allow writing to the debug file. 

= 2.5.3 = 
 * Cleaned up typo in wpbook_cron which may have caused issues with comment import
 * Removed deprecated warning in add_options_page
 * Changed to use different auth_url in Invite mode for invite friends when in 
   forced https mode on Facebook

= 2.5.2 =
 * doh! Bad typo in the facebook publish code, rending access tokens invalid. 
   Quick fix, everyone should be on 2.5.2. 

= 2.5.1 = 
 * Had missed some entries in the theme which referred to offline_access
   Shouldn't cause problems for anyone but should be removed as it will be 
   deprecated. 

= 2.5 = 
 * Added hook to remove all the post_meta that wpbook created
   while installed
 * Accomodate Facebook having deprecated offline_access permission. For most
   users you won't notice a difference. However, sometime after June 2012 you
   will notice that your tokens expire every 60 days and require new tokens 
   to be generated. Sorry, but that's Facebook's new policy.    

= 2.4 = 
 * Added wpbook message field to meta box. This will be used over the excerpt
   if it is present. 
 * Bugfix for duplicate post_meta (custom fields) - never interfered with 
   functionality but did create unnecessary date. Now only one wpbook_publish per
   post. 
 * Added user_groups permission for publishing to non-public groups

= 2.3.5 = 
 * Added auto-draft to publish action, better support for Windows Live Writer 
   and other XML-RPC clients. 
 * Added check for Facebook class inside comments.php file
 * Reverted some changes to Gravatar code that was overly restrictive in 2.3.4
 
= 2.3.4 = 
 * Bugfix for iFrame style page tabs, introduced via the newer Facebook sdk. 
   Was throwing "headers already sent" warnings for those pages. 
 * Bugfix for overly broad filter for global avatars outside comments. 

= 2.3.3 = 
 * Delete options from db on uninstall

= 2.3.2 = 
 * Bug fix posting to page - left wrong $access_token in place in pushing 2.3.1
 
= 2.3.1 = 
 * Introduced user-requested option to post as "link" rather than "post" type.
   Links pull metadata from FB open graph and use that to fetch image
   and short description. 

= 2.3 = 
 * Updated to the latest Facebook PHP SDK, 3.1.1

= 2.2.3 =
 * Bugfix: User should not have to be logged in to view the fanpage
   tab. This means hiding the 'invite friends' link on that tab, which 
   makes sense since the user should really 'like' that page, not
   invite people to the app - that makes sense on the app canvas
   page instead
 * Bugfix: Moved from 'like' button back to 'share this post' for the
   page tabs as well. The 'like' doesn't really work effectively 
   inside FB pages/apps - always just points to the app not the post
 * Bugfix: Support https mode. (Thanks to patch from cshiflet). 
   In order for https mode to work, of course, your blog must be
   accessible under an https url, but if you have https working
   on your blog WPBook will continue to operate for users browsing
   facebook under https. 

= 2.2.2 = 
 * Bugfix: fwrite errors for supplied argument is not a valid stream resource
 * Reverted from "like" button to "share" button for canvas pages within
   application. Although Facebook is phasing out the share button, the like
   button they want to replace it doesn't yet work inside app pages. 
   They default the og:url to the canvas page, which means all your likes
   point to the app, not the specific post
 * If posts have a "featured image" / "post thumbnail" defined, that 
   post thumbnail is passed through the share link
 * Added a 'wpbook_attachment' filter for other plugins to use
 * Added a functions.php to the wpbook theme so that it could natively
   support the post_thumbnails in index.php

= 2.2.1 =
 * Something wrong in checked in copy - cleanup release

= 2.2 = 
 * Added the "Read More" action link. Because of a Facebook bug 
   (http://bugs.developers.facebook.net/show_bug.cgi?id=15377) I can't
   add more than one action link to a post, so no "share" button. 
 * Added posting options for Group walls, and comment import form 
   Group walls
 * Limit the size of debug files created to 500k, for users who
   enable debugging and then forget. 
 * Clean up DEBUG for cases where permissions fail or file is not writeable
 * Made "disable ssl verification" an option so that only users who need it
   will have it and others won't get conflict
 * Cleanup to the admin screens in general, more clarity around what
   is required and better language on the admin screens about what
   is being checked. (Thanks BandonRandon for patches) 
 * Improved "Check permissions" page
 * Added wpbook logo which had been missing
 * Fix for get_themes() issues with WordPress 3.0.1 through 3.0.5
   (Thanks BandonRandon for patch)

= 2.1.4 = 
 * Bugfix: Access token for importing comments from streams that aren't
   public
 * Bugfix: Get right Facebook avatar for comments made as pages

= 2.1.3 = 
 * Bugfix: Error for fopen filename can't be empty - wasn't declaring
   debug filename early enough in publish_to_facebook.php

= 2.1.2 = 
 * Bugfix: Don't store access_token in usermeta but in options table
   (Impacts users who were trying to post as authors other than admin)
 * Bugfix: Post Thumbnails was failing, resulting in random images
 * Bugfix: ssl options for self-signed certs (impacts posting to 
   Facebook for users on servers with self-signed ssl certs)
 * Store separate access token and "manage_pages" permissions so that
   we can publish to pages as pages, not as users

= 2.1.1 = 
 * Bugfix - wrap call to get_the_post_thumbnail in function_exists() so 
   that themes which don't support it don't break
 * If you are not using post thumbnails (because your theme doesn't
   support it, I can't use those thumbnails to post to the wall. 

= 2.1 = 
 * Shifted from _GET and _POST to _REQUEST - to handle Facebook's changes
   which deprecated _GET
 * Released 2.1

= 2.1b2 = 
 * Added wpbook_theme which can be copied to themes directory, enabling
   users to customize the theme without it getting overwritten
   (Thanks to Brook Dukes / BandonRandon for the patch)
   (copy the 'wpbook_theme' folder to 'wp-content/theme' and make any
   changes to this theme. To go back to the default theme delete the
   wpbook_theme or change the theme name in the stylesheet)
 * Cleaned up the "More Posts" section of the index.php template
   to not show when there is no previous or next page of posts
 * Added capability, based on a patch supplied by @sebaxtian, to 
   allow user to post to FB as notes rather than wall excerpts

= 2.1b1=
 * Changed to Facebook Graph API, PHP SDK
   * Posting to Profile Wall
   * Posting to Page, App, or Group Wall
   * OAuth authentication for Canvas
 * Upped minimum WordPress to 2.9.0
 * Using "featured_image" thumbnails for posting to FB wall
 * Added Facebook Like button replacing "share" button
   * Points to external link
 * Updated comment import for new Graph API
 * Updated permissions checking page for storing access_token in user_meta
 
= 2.0.13 = 
 * Moved and Unhid the infinite_session_key in admin WPBook setting screen
 * Fixed attribution line function which prevented %author% from working
 * Added global gravatar setting - otherwise we only filter gravatars
   inside facebook. (This prevents wpbook from interfering with other
   gravatars in themes outside fb). 
 * Added DONOTCACHEPAGE constant when pages are viewed inside facebook - 
   this should enable WPBook to better coordinate with wp-super-cache. 
 * Added initial support for iFrame based tabs - still needs work

= 2.0.12 = 
 * Fixed regression - cron was looking for FB client in wrong directory
   (Thanks Olivier)

= 2.0.11 = 
 * Removed "add to profile" tab options
 * README updates - link to instructions
 * Conditional checking for fb_page_target to avoid 'premature end of FQL query"
 * README updates on profile tabs
 * Add pending_to_publish state
 * Filter JS out of FB share link
 * Added more debugging info

= 2.0.10 = 
 * Changes by bandonrandon, see 
   http://bandonrandon.wordpress.com/2010/10/10/wpbook-2-0-10-beta-release/
 * Move includes into their own directory
 * Incorporate FB avatar in comments imported
 * New Admin Layout, images
 * Bug fixes: default for 'post to facebook' is set to true
 * Links in permissions page point to wpbook.net
 * FB tabs view moved to its own file in theme directory

= 2.0.9.2 =
 * Typo in wpbook_cron.php (defin should be define) - triggered only
   when debug disabled 
 * Added thead to allowed tags in tab view

= 2.0.9.1 =
 * SVN issue - removing html entities from comment author

= 2.0.9 =
 * Fixed the lost navigation issue - previous and next page of posts
   listed at bottom of archive pages (Though not on tabs)
 * %category_link% and %tab_link% in header/footer were broken - fixed. 
 * Attempt at fix for comment authors with non-ascii characters in their names
 * Removing old profilebox code and references - Facebook no longer allows
   profile boxes - replaced by tabs
 * Excerpts posting to walls with [caption] shortcodes - fixed
 * Timestamp on imported comments - fixed
 * Added user_ID to comment-data array for comment import

= 2.0.8.1 = 
 * Ouch! Checked in version had extra whitespace before opening php tag
 * Array checking for page type should be cleaner

= 2.0.8 =
 * Changed the "catch permissions" logic for retrieving the infinite session key
   again - hopefully eliminate conflict with theme-my-login plugin and any other
   plugin trying to set cookies. 
 * Adapted logic to allow for posting to the walls of Application Profile
   pages, Group pages, and regular Fan Pages

= 2.0.7 = 
 * Added new way to grant permissions directly for a pageID entered into
   the wpbook settings interface. Should improve capability for folks looking
   to publish to a page for which they aren't necessarily an admin, but
   for which permissions can be granted
 * Removed instructions, replaced with pointer to online version 
   (easier to keep updated, limit size of download)

= 2.0.6 =
 * Misc bug fixes: gravatar strtolower, htmlspecialchars on blog titles,
   link to instructions wrong in admin, adding <script> tag to allowed html

= 2.0.5 = 
 * left out a key global for $wp_version

= 2.0.4 = 
 * Error in post-meta checking for individual posts
 * Debug info left in publish to facebook routine

= 2.0.3 = 
 * Quick bugfix release
 * Elminate extra whitespace on includes
 * Clean up include structure to break on full functions no mid-stream
 * Update checking for post_meta for 'suppress' function

= 2.0.2 =
 * Added option which enables app and profile tabs
   (/?app_tab=true&fb_force_mode=fbml )
 * Fixed excerpt issues with posts with no custom excerpt, teaser, or manual
 * Catch excerpt over 1000 characters
 * Use nohtml filter on excerpts for walls
 * Use less restrictive filter for application tabs - still no embedded
   videos though
 * Made debug log generation a setting
 * Fixed typo in wpbook_cron.php - declaring constant for method comments vs comment
 * Moved infinite_session_key to a setting user enters (consistency)
 * Added "publish this post to FB" setting, which allows users to suppress
   stream publishing for an individual post
 * Changed saving of postmeta to be on PostID, not the id of the current revision
 * moved try/catch blocks into conditional includes to avoid error messages
   when installed on PHP4 hosts
 * Rearranged options to simplify, and better group settings
 * Rewrote install instructions with new screenshots to show changed FB screens
 * Updating profile references to tabs as profile boxes are going away (already
   gone from Fan Pages)

= 2.0.1 =
 * Capture of infinite_session_key 
 * Introduced debug log
 * Attribution line setting

= 2.0.0 = 
 * Added promote external links
 * Introduced the ability to import comments from post walls, using wp_cron


= 1.5.7 =
 * Bugfix - typo in index.php, only visible when specific exception was triggered. 

= 1.5.6 =
 * Changed mechanism for getting pageID for publishing to page's wall. 
   Rather than getting this each time via FQL, we only get it the first
   time in Grant Permissions page, then store it as a setting. 
 * Thanks to Larry Bertolino and others for help debugging and for persistence
   in trying to make this work

= 1.5.5 = 
 * Changed mechanism for requesting offline.access, stream.publish permissions
   for users and fan pages. 
 * This should help eliminate the API 100 and API 200 errors some have
   encountered

= 1.5.4 = 
 * Cleaned up theme/index.php if/else loops
 * Eliminates FBML errors for blog owner on invite page

= 1.5.3 =
 * Fixed Activation check for PHP5
 * Added Try/Catch around FQL call for pages of which user is admin
 * Added option for 'publish to pages' separate from 'publish to stream'
   (This enables publishing to author's wall, page's wall, OR both)
 * Added code to carry querystring into "external permalink" function
 * Fixed spelling of pieces in external permalink function

= 1.5.2 =
 * Now checks for PHP 5 at activation, will not allow activation under PHP4
 * Checks for zero pages of which user is admin (avoid edge case exception)
 * Added link to installation instructions to permissions page
 * Added offline-access permission request (some users had not yet granted
   this permission)
 * Added "show errors" mode, which when enabled calls wp_die when
   the Facebook client throws exceptions - a bit extreme but does show the 
   exceptions to the user

= 1.5.1 =
 * Oops. Forgot to check for user who isn't an admin of any pages. 

= 1.5 =
 * Now requires PHP 5
 * Enables user to post to stream, including to pages. 
 * Catches exceptions from Facebook client. (Doesn't yet surface those in 
   good error messages, but at least they are caught)
 * Fixed, I hope, issue with comments inside Facebook for some users
 * Clean up of some admin styles (resized gravatar images as well as
   some basic hierarchy on options)
 * Added Pageing options as their own section
 * Allow user to select pages to be excluded
 * Added option to allow a menu of parent pages at top of the app
   below the title
 * Fixed "Facebok" typo in line line 182 of theme/index.php
 * Option to turn on and off page list under content 
   (independent of menu)
 * Option to turn on/off recent post under content
 * Allow user to set the amount of recent post to show under content (default 10)
 * Cleaned up custom header/footer now only one function instead of two
   (no reason to have two functions)
 * Added %tag_links% and %category_links% to custom header footer as
   well as made archive pages work. 

= 1.4.2 =

* Bugfix for those who install WordPress in a subdirectory, for home comment submission was failing in 1.4 and 1.4.1. 
* Bugfix for wpbook_admin_javascript.js which included an outmoded jQuery selector syntax and broke the admin js in 2.9.1
* Bugfix for wpbook_admin_javascript.js which included hardcoded paths assuming wp_content path relative to wp-admin (shows images for default gravatar icons by default now rather than waiting for tooltip hover)

= 1.4.1 =
* Doh! Typo snuck into release package. (See http://wordpres.org/support/topic/348292)

= 1.4 =
* Fixed bug which made invite friends link only work on the home page
* Fixed bug in setting for custom/header footer which included a permalink
  (See http://wordpress.org/support/topic/306263)
* Added Gravatar support (thanks Brooke)
* Added list of pages (thanks Brooke)
* Removed hard coded references to wp-content and plugins directories
  (See http://willnorris.com/2009/05/wordpress-plugin-pet-peeve-hardcoding-wp-content)
* Removed hard coded reference to config.php
  (See http://willnorris.com/2009/06/wordpress-plugin-pet-peeve-2-direct-calls-to-plugin-files)

= 1.3.1 = 
* Fix for XAMPP Windows users - add ABSPATH to include for config.php
* Fix for users who have the application name *in* the permalink structure
* Cleanup for images in instructions that were too wide for layout
* Cleanup button title for submit on invite friends page
* Remove unnecessary second 'include_once' in comments.php

= 1.3 =
* Mostly improvements to the admin interface user experience - better 
  separation of options into required, customization, social, and advanced. 
* Ability to include a custom header/footer for each post, including author,
  date, time, category, and tags. 
* Bugfix: No longer echoing blog name twice on the invite friends screen. 
* Bugfix: Caught case where profile box could get updated with links to 
  the original source (outside FB). 
* Note: This is expected to be the final PHP4 compatible version. Facebook's 
  client only supports PHP5, and I need to be able to wrap certain client
  calls in Try/Catch, which requires PHP5, to avoid nasty "uncaught exception"
  bugs. (Yes, there are unofficial PHP4 clients, but they are unsupported).
  If someone wants to create a PHP4 only version which trails the ongoing
  development, they are welcome to, taking this as the place from which to
  begin a fork.  

= 1.2 =
* Changed the mechanism for "Add to Profile" to avoid issues with
  the fb:ref url method, using fb:ref handle instead
* Eliminated /wpbook/theme/recent_posts.php
* Incorporated Brooke Dukes' fixes to admins screens
* Added timestamp to posts

= 1.1.1 =
* Fixed minor bug which broke FB resize javascript when 'add to profile'
  option was off
* Fixed minor bug in the description of the plugin (display). 

= 1.1 =
* Fixed (I hope!) Profile.setFBML issues for pages, profiles
  Eliminated the need to copy defaultFBML into settings
* Added option to view link in external site
* Added option to move links (share, external) top or bottom
* Added option to enable "add to profile"
* Created documentation with photos

= 1.0 =
* Added simplexml44 library (BSD Licensed) for php4client
* Added option for "Give Credit" 
* Added option for "Enable Share"
* Added option for "Allow Comments"
* Moved "Invite Friends" to top of page
* Cleaned up CSS for "recent posts" in main page
* Added fix to facebookapi_php5_restlib.php which affected hosts where
  curl libraries were not present or enabled
* Jumped version to 1.0 - functionally complete

= 0.9.7 =
* template_directory deprecated in 2.7, use bloginfo('wpurl') instead

= 0.9.6 = 
* Clean up from moving plugin in to directory
* Added Share button to share posts on FB
* Added fix for conflict with other Facebook-based plugins

= 0.9.5 = 
* Moved plugin into wpbook dir in subversion
* Moved theme subdirectory inside plugin subdir
*   Required several function changes
* Added check for existing FacebookRestClient

= 0.9.4 =
* Bug in javascript (NULL isn't the same as null) for profile

= 0.9.3 =
* Bug in commenting inside Facebook due to $facebook->redirect
* Now redirects to the post on which the user commented
* Added instruction for adding to FB Pages to settings page in WordPress

= 0.9.2 =
* Didn't realize I had set default FBML inside Facebook, masked a bug
* Should now set profile FBML before calling add profile box

= 0.9.1 =
* Fixed xd_reciever.html versus xd_receiver.html issue
* (You'd think a guy with a PhD in English would know how to spell.) 

= 0.9  = 
* Added profile boxes
* Shows 5 most recent posts in profile box
* Also sets FBML for "pages" profile boxes

= 0.8.2 =
* Added option to require email address of comment author
* Can be set separately only for Facebook comment authors
* Functionality added by Brooke Dukes. 

= 0.8.1 =
* Oops. Typo in README.txt - Brooke Dukes.
* Issue with some text not being displayed
  on the invite form
* Tested with Wordpress 2.6.2

= 0.8 =
* Thanks to Brooke Dukes for contributing facebook invites - if you
  select 'display invite friends link' checkbox in the wp-admin 
  settings for WPBook, you can invite facebook friends!
* Display email box for commentors (optional)

= 0.7.5 = 
* bug fix: style.css is in template directory, not necessarily
  based on /wp-content/themes/wp-facebook - account for subdirs
* Same goes for the FB.XdComm.Server.init call

= 0.7.4 =
* bug fix for subdirectory based blogs
* fixed hardcoded offset of permalinks
* added note to readme to update theme when updating plugin
* Updated javascript in theme to reflect "new" facebook js 0.4
  (See http://wiki.developers.facebook.com/index.php/Resizable_IFrame#New_Profile_Update)
* Fixed erroneous link in "theme not installed" check
* Added ABSPATH as appropriate to catch the right includes
* Removed hard dependency on specific Avatars plugin, now uses default gravatar

= 0.7.3 =
* bug fix
* adding namespacing to plugin function
* anded min version to readme

= 0.7.2 =
* bug fix
* no try { } catch {} in PHP4

= 0.7.1 =
* bug fix
* comments_facebook.php was not being found
* created fb_comments_template function instead

= 0.7 =
* Major architecture changes
* Relies on a theme, not creation of a page
* Inspired by Alex King's mobile plugin (http://alexking.org/projects/wordpress)
* Enables recent posts and post navigation
* Added app canvas url to options for use as redirect post-comment submission

= 0.6 =
* Added support for posting comments
* Switched to iFrame to allow more code in blog posts
* Added Facebook javascript for resizing iFrame
* Added style.css for styling
* fixed bug in storing options
* consolidated Facebook client stuff in config.php
* auto detect php version and set client include accordingly

= 0.5 =
* Added support for PHP4 Facebook Client Library
* Options combined into associative array to speed-up and remove 
  interference w/ other plugins

= 0.4 =
* First push to WP-Plugins Directory

== To Do ==
* Use settings API better / clean up settings (maybe a whole new
  box for settings in left nav? enabling sub-pages)
* Enable multi-author blogs. (Separate FB publish destinations 
  for each author? Separate FB app for each author? Filter tab 
  view to only show each author's posts?)
* Leverage Facebook API to publish notifications to stream when
  user leaves a comment (comment poster's stream and users streams)
* Threaded comments. (If user has them enabled - requires WP 2.7.x)
* Error handling - do something with the FB exceptions caught
  Probably use set_transient to show - will require WP 2.8 or greater

