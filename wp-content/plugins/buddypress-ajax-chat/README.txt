=== Buddypress-Ajax-Chat ===
Contributors: dfa3272008
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=gpfu08@gmail.com&item_name=Donation&currency_code=USD
Tags: buddypress, ajax, chat, shoutbox, plugin, group, social
Requires at least: WP 2.9.1, BuddyPress 1.2
Tested up to : WP 3.1.1, BuddyPress 1.2.8
Stable tag: /trunk/

== Description ==

<p>This plugin will extend Buddypress to have an Ajax Chat client that works with Buddypress groups,
as well as all Wordpress users.  As of version 1.2.6 Buddypress Ajax Chat works with both 
Wordpress MU and Wordpress Single User.</p>
<p>No more ads!!!</p>  
<p>Please drop a line in the <a href="http://wordpress.org/tags/buddypress-ajax-chat?forum_id=10">Buddypress Ajax Chat Support Group Forum</a>
 if you have any questions or want to have a certain feature added.</p>
<p>I'd be happy to do so:)</p>
<br />
<p> 1.4.4 <br />
* Added ability to disable shoutbox in groups with main override setting in admin's config.
</p>

== Changelog ==

= 1.4.4 = 
* Added ability to disable shoutbox in groups with main override setting in admin's config.

= 1.4.3 = 
* Fixed popup window missing single quote to not allow for window to pop up.

= 1.4.2 = 
* Completely ad free and fixed error.

= 1.4.1 = 
* Completely ad free.

= 1.4.0 = 
* Fixed issue with User login name vs Display name setting.

= 1.3.9 = 
* Added super admin support for wordpress multisite.

= 1.3.8 =
* Added Japanese language support (thanks chestnut_jp!)

= 1.3.7 =
* Added cookie code to allow both localhost (both 127.0.0.1 and localhost) installs as well as remote name domain installs

= 1.3.6 =
* Added cookie code to allow both localhost installs as well as remote name domain installs
* Added fix for Japenese characters showing up as, '???' in chat messages

= 1.3.5 =
* Updated flash bridge script (Fixes alert message in chrome of TypeError: Object #<an HTMLObjectElement> has no method 'create')
* Rewrote the way no ads is handled.  This will provide better support for people who don't want to see no ads.

= 1.3.4 =
* Changed double quotes in configuration to be single quotes so that they're not interpreted.
*   This would cause chat to break in databases with $ in them or usernames or some other data.

= 1.3.3 =
* Replaced jQuery dqDrag with jQuery ui draggable

= 1.3.2.1 =
* Now works with wp 3.0.0.

= 1.3.2 =
* Fixed issue with bp in single wordpress not showing.

= 1.3.1 =
* Added bp loader check before starting plugin.
* Added check to see if logged in or not to save on resources
* Added RU language support (thanks Koshnv)

= 1.3.0 =
* Fixed issue with Russian characters showing up as ?????? in channel names.

= 1.2.9 =
* Fixed chat to work with BP 1.1.3
* Fixed issue with call on line 283 of CustomAJAXChat.php (Thanks Eric :) )
* Fixed php notice warning when running with display_errors on in CustomAJAXChat.php

= 1.2.8 = 
* Fixed group chat automatic channel switching not working
* Added ability to use display name or login name by sitewide config
* Changed look & feel of sitewide config to be logically organized
* Sitewide ability to disable shoutbox completely
* Fixed display name online list
* Fixed logout from chat link to log you out
* Terminated chat even if full blown chat is in a popup when log out occurs
* Made chat a lot faster and use less resources!
* Fixed cookie bug where you'd get the cookies already sent bug
* Added long tags for php settings that didn't allow short tags (php.ini setting)
* Fixed db call that would cause blank screen or cookie errors
* Added new screen images to plugin

= 1.2.7 =
* Private test build never released publicly. 

= 1.2.6 = 
* Tested on Wordpress Single User and Wordpress MU
* Fixed serveral bugs in Wordpress Single User install that made the plugin useless before!
* Fixed groups to channel synchronization
* Fixed so that plugin works on installs that are in a sub folder 

= 1.2.5 = 
* If group admin is online automatically pop up shoutbox
* Simplified the chat menu's on top and on profile menu
* If group admin is offline then display group admin is offline
* Added rounded corners and stylesheet behavior to shoutbox in dashboard area

= 1.2.4.1 = 
* Feels like a Monday!  Fixed logout bug. (Thanks to banfi for fixing this and telling me about it.  You ROCK!!!)

= 1.2.4 = 
* Fixed stupid, stupid mistake caused shoutbox to break...

= 1.2.3 = 
* Default css to include shoutbox close link
* Fixed issue with install on wordpress (not wordpress mu) with get_blog_option call.

= 1.2.2 = 
* Fixed bug where full blown chat window in popup didn't logout.
* Set chat shoutbox text size to .9em.

= 1.2.1 =
* Added custom css to shoutbox
* Added default css to shoutbox to allow scrolling of chat window content.

= 1.2.0.0 =
* Lots of additions
* Error message displayed if bp-chat/config not writable
* Configuration page - option to have shoutbox pop up all the time
* Configuration page - option to have full blown chat pop out in to it's own window
* Group chat integration is tighter - Chat shows up in group area
* Group chat integration shows if group admin or group moderator is online
* Add own css to chat display, pick your own colors, etc
* Min/Max buttons for shoutbox with animation
* Error message when logged out now fixed.

= 1.1.8.3 =
* Reverted back to slug group names due to error in listing them out.
* Updated readme for common install error message.

= 1.1.8.2 =
* Fixed upgrade issue not making config file.

= 1.1.8.1 =
* Fixed issue with not displaying css for rounded corners.

= 1.1.8 =
* Fixed issue with showing the, "You're currently logged out".
* Now works with wordpress/buddypress as well as wordpress mu/buddypress
* Fixed the install from wordpress itself, rather than have to download and install

= 1.1.7.1 =
* Checked if function exists (silly checkin mistake)

= 1.1.7 =
* Easy install rolls out (Just activate plugin, no more configuration)
* Many bugfixes
* Compatible with Buddypress 1.2
* Spaces in chat group name

= 1.1.6 =
* Updated dimensions.js file due to possible JQuery exception failure.

= 1.1.5 =
* Added Italian  langauge files

= 1.1.4 =
* Fixed main chat not displaying in some setups.

= 1.1.3 =
* Added french langauge files
* Removed the javascript menu resize 
* Cleaned up a lot of unused code
* Fixed language notation calls

= 1.1.2 =
* Allow the ability to log in via subdomain and/or main domain to main chat. 

= 1.1.1 =
* Added borders to shoutbox 
* Removed redundant close text at bottom of shoutbox with above X to close

= 1.1.0 =
* Moved everything in to one directory to make installs easier
* Fixed php warnings when clicking on Buddpress Core actions like popular, active, etc
* Cleaned up css
* Added rounded css divs for shoutbox
* Localized the plugin.  Now you can make your own PO files for it for your language:)
* Added support to install in a sub directory

= 1.0.1 =
* Fixed location of Shoutbox delete icon
* Made chat faster and less of a burden on the server
* Provided way better help for installing
* Works with Buddypress 1.1.1 
* No more plugin.php warning
* Cleaned up chat icon
* Removed roll dice feature from menu (still available from command line)
* Made shoutbox work on all Buddypress 1.1.1 and MU screens
* Provide install support for a flat fee on <a href="http://dynamicendeavorsllc.com/premium/">Flat rate install</a>
* Changed the default theme to match more with the defaul Buddypress theme

= 1.0.0 =
* Initial version.

== Installation ==

New Install or Upgrade

1.  Just install from wordpress/wordpress mu or you can download to /wp-content/plugins
    as /wp-content/plugins/buddypress-ajax-chat/...

2.  Last step
    A.  log in as admin to wordpress/wordpress mu
    B.  go to the plugins section to activate Buddypress Ajax Chat (bp-chat)
    C.  Click on Activate Buddypress Chat sitewide for the bp-chat plugin 
    D.  Click on the Dashboard link to make sure the plugin is activated

Optional:

1.  Add your own theme configuration.
    A.  Default comes with a facebookish color scheme.
        i.  Just copy the themes/facebookish/buddypress-ajax-chat directory to your child theme

            Example:
              wp-content/theme/bp_anygig/buddypress-ajax-chat/_inc/buddypress-ajax-chat.css
    B.  Or make your own css file called buddypress-ajax-chat/_inc/buddypress-ajax-chat.css

            Example:
              wp-content/theme/bp_anygig/buddypress-ajax-chat/_inc/buddypress-ajax-chat.css


2.  Configure site wide chat options:
    A.  Log in as admin
    B.  Click on Buddypress Dashboard
    C.  Click on Chat Settings

Happy Chatting:)

== Frequently Asked Questions ==

= I'm stuck.  Where can I get help? =

<a href="http://wordpress.org/tags/buddypress-ajax-chat?forum_id=10">Buddypress Ajax Chat Support Group Forum</a>
Here, there is a forum.  I or someone trolling the forum will help you:)  If it's urgent then try <a href="http://dynamicendeavorsllc.com/contact-us/">contacting me</a>
and let me know you need help.  I'm happy to help, but please tryt the forum first:)

= I get a can't install error. =

Chmod 777 bp-chat/config directory.  The plugin makes two files to be referenced later.  If you can't
write to this directory then the plugin won't work.

= I'm not techincal can you install it for me? =

Yes, we can install it for you.  Check out this link for more info:
Provide install support for a flat fee on <a href="http://dynamicendeavorsllc.com/premium/">Flat rate install</a>

= I want my own adsense code in there.  Can you install it for me? =

Yes, for a small fee we can bundle up the code for you and email it to you.
Provide own adsense code <a href="http://dynamicendeavorsllc.com/premium/">Send me a version with my adsense code</a>

= Special Credit =

Special thanks to Tom Granger for helping educate me on how to get multilingual support
as well as bug fixes.  Thanks Tom:)

Special thanks to Daniele Argiolas for providing the Italian language files:)

I made the ES langauge files so bare with me as it has been many years since anyone has
spoken Spanish with me.  Que lastima...

== Screenshots ==

1.  Full blown Chat screen.
2.  Chat Menu added to the Buddypress menu.
3.  Full blown Chat screen with a multiple online users listed.
4.  Original shoutbox
5.  Moveable shoutbox location A.
6.  Moveable shoutbox location B.
7.  Default shoutbox location.
8.  New Style shoutbox look and feel
9.  Group chat screen
10.  Sitewide configuration screen

== License ==

Copyright (c) 2009 <a href="http://dynamicendeavorsllc.com">dynamicendeavorsllc.com</a>  Dynamic Endeavors LLC. All rights reserved.
Released under the GPL license http://www.opensource.org/licenses/gpl-license.php
BuddyPress Ajax Chat integration is an add-on for WordPress MU http://wordpress.org/ and BuddyPress http://buddypress.com.
This program is distributed with the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
Free version: Buddypress/Ajax Chat integration can be used and distributed for free if all adsense ads belonging to Dynamic Endeavors LLC are retained as part of the code.
Paid Version: If you don’t want Dynamic Endeavors LLC Google Adsense ads as part of Buddypress/Ajax Chat integration an "ad free" version can be found here: http://dynamicendeavorsllc.com/premium/bp_chat_no_adsense/ . A fee of $5.00 is required for the ad free version. Note: You can add your own Google ads to this version.
Paid version with integrated 3rd party Google Adsense: If you want to host your own Google Adsense ads and have Dynamics Endeavors LLC integrate the ads for you as part of Buddypress/Ajax Chat integration please send us an email to gpfu08 (at) gmail.com. A fee of $10.00 is required for the 3rd party integrated version made payable via paypal.com to gpfu08(at)gmail.com.
Modifications: If you make any changes or modifications and we'd like to know about them. We’re always looking for ways to improve our plugins, so, if you make any code changes please contact us at gpfu08(at)gmail.com.
The "paid" version of this Buddypress Ajax Chat is licensed on a "per server basis". For each server that you install BuddyPress Ajax Chat without our adsense code on you are required to purchase a license. A server license is defined as per domain name. 
A paid version must be used over a free version if you site has questionable content.  
Questionable content such as pornography or other deemed material that violates Google's TOS is prohibited with the free version.
A donation does not imply any type of service contract.  
 
This is an add-on for WordPress MU 
http://wordpress.org/

**********************************************************************
This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
**********************************************************************

Note:  There are three versions of this plugin:
       Free (no license required, but our adsense code stays)
       Adsense removed (requires license, our adsense code removed)
       Custom - Your adsense added (requires licens, your adsense code added)
