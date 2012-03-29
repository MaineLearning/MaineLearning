=== YD BuddyPress Feed Syndication ===
Contributors: ydubois
Donate link: http://www.yann.com/
Tags: BuddyPress, RSS, feed, syndication, aggregation, mix, wall, stream, activity, external, notes, blog, plugin, automatic, English, extension, plug-in, Facebook, French, Français, Italian, import, import blog, importation, group, group activity, external, external blog, external group blogs
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: trunk

Description: Syndicate external RSS feeds into your BuddyPress activity feeds (like with notes on a Facebook wall).

== Description ==

= Syndicate RSS feeds into your user or group Activity stream =

Select any kind of RSS feeds that will be agregated into your BuddyPress activity stream. For example, to import an existing blog.

Each BuddyPress user can select as many external RSS feeds as they want. They will be aggregated (mixed) into their activity feed automatically, with a title, an excerpt and a link to the original post.

External blogs can also be aggregated into group activity feeds the same way.

A simple way to promote any external blog content inside a BuddyPress community.

Uses WP-cron to update feeds automatically.

Compatible with PHP5.

Requires the BuddyPress social plugin for WordPress to be installed and configured (will not work and is not useful without BuddyPress / will cause a fatal error upon installation if BuddyPress is not activated).

= Usage guide =

* Activate plugin
* Go to My account > Settings > Import a blog
* Add one or more RSS feed URL addresses with optional title
* Wait for one hour, or click the "Manually run hourly process" button in the plugin's option page
* Latest blog post title and excerpts will start appearing in your BuddyPress personal activity feed page

= Usage for group feeds =

* Go to Groups > Your group > Admin > Import blogs
* Add one or more RSS feed URL addresses with optional title
* Wait for one hour, or click the "Manually run hourly process" button in the plugin's option page
* Latest blog post title and excerpts will start appearing in BuddyPress activity pages of group members

= New optional settings of version 2.x =

* Maximum number of feeds a user or group can aggregate can be setup in the "YD BuddyPress Feed Syndication" option page with the "Limit number of feeds to aggregate to a maximum of:" setting.
* External blog links can be made to open in a different window by ticking the "Open feed links in new window." checkbox in the plugin's settings page.

= Active support =

Drop me a line on my [YD BuddyPress Feed Syndication site](http://www.yann.com/en/wp-plugins/yd-buddypress-feed-syndication "Yann Dubois' BuddyPress Feed Syndication plugin") to report bugs, ask for specific feature or improvement, or just tell me how you're using the plugin.
It's still in an active development stage, with new features coming out on a regular basis.

= Language versions & translation credits =

* English: Yann Dubois
* French: Yann Dubois
* Italian: Giancarlo Cuzzolin ( [Uajika](http://uajika.tk/ "Uajika - The social network") )
* Russian: Diogen Platonovitch ( http://platonovich.ru/ )

All those translations are provided in the distribution. The plugin is fully internationalized and can easily be translated into any other language.

= Funding =

Original development and improvements of this plugin was kindly funded by http://www.selliance.com -> Please visit their site to see this plugin in action!

== Installation ==

Wordpress automatic installation is fully supported and recommended.

== Frequently Asked Questions ==

= Where should I ask questions? =

http://www.yann.com/en/wp-plugins/yd-buddypress-feed-syndication

Use comments.

I will answer only on that page so that all users can benefit from the answer. 
So please come back to see the answer or subscribe to that page's post comments.

= Puis-je poser des questions et avoir des docs en français ? =

Oui, l'auteur est français.
("but alors... you are French?")

= What is your e-mail address? =

It is mentioned in the comments at the top of the main plugin file. However, please prefer comments on the plugin page (as indicated above) for all non-private matters.

== Screenshots ==

1. Enter a bunch of RSS URLs in the feed settings page (My account > Settings > Import a blog)...
2. ...The imported blog posts will start appearing in your personal BuddyPress activity feed!

== Revisions ==


* 2.1.0. Production release / new features / bugfix of 2011/08/27
* 2.0.4. Bugfix release 4 of 2011/08/26
* 2.0.3. Bugfix release 3 of 2011/08/26
* 2.0.2. Bugfix release 2 of 2011/08/26
* 2.0.1. Bugfix release 1 of 2011/08/26
* 2.0.0. Production release / new features / bugfix of 2011/08/25
* 1.1.0. Production release / bugfix of 2011/07/22
* 1.0.0. Production release of 2011/07/18
* 0.1.1. Beta release of 2011/07/15
* 0.1.0. Initial beta release of 2011/07/13

== Changelog ==

= 2.1.0 =
* Production release of 2011/08/27
* New feature: Max feeds per user != max feeds per groups (as suggested by J.Pisano)
* Interface styling improvements (thanks go to John/Roman & Jubal for suggestions)
* Interface now says you can aggregate only if max allowed feeds > 1
* Better BuddyPress feed integration for groups (thanks to Guillaume Dott @ Selliance.com )
* Better Internationalization and localization / translation of the admin interface
* New, more comprehensive .po/.mo files (previously existing translations need a serious update!)
* Updated French translation files
* Bugfix when maximum number of feeds is initially 1 (pointed by J.Pisano)
* Bugfix for translation (thanks to Guillaume Dott @ Selliance.com )
* Bugfix for duplicate posts (thanks to Guillaume Dott @ Selliance.com )
* Bugfix for group creation process (thanks to Guillaume Dott @ Selliance.com )
= 2.0.4 =
* Additional bugfix for when Group component is turned off
= 2.0.3 =
* Bugfix to the bugfix ;-)
= 2.0.2 =
* Bugfix: don't crash when BuddyPress is not loaded
= 2.0.1 =
* Bugfix: don't crash when Group Component is not activated in BuddyPress Component Setup (thanks to Jubal for pointing this out)
= 2.0.0 =
* Russian language translation kindly provided by Diogen Platonovitch ( http://platonovich.ru/ )
* Feature: added user nickname and RSS title information
* Feature: limit to the amount of aggregated feeds a user can add
* Feature: group feeds
* Feature: links can be opened in another window
* Bugfix: "view" button next to the title
= 1.1.0 =
* Bugfix : feeds without author (thanks to Guillaume Dott @ Selliance.com )
* Bugfix : SimplePie default cache lifetime (thanks to Guillaume Dott @ Selliance.com )
* Features: added buttons to force feed reloads in the option page
= 1.0.0 =
* Italian language version provided by Czz (Giancarlo Cuzzolin @ Uajika.tk)
= 0.2.0 =
* French language version
* Supports setting of RSS refresh delay
* Better management of broken RSS addresses
= 0.1.1 =
* Slightly better feed registration interface
= 0.1.0 =
* Initial release

== Upgrade Notice ==

= 2.1.0 =
* No specifics. Automatic upgrade works fine.
= 2.0.4 =
* No specifics. Automatic upgrade works fine.
= 2.0.3 =
* No specifics. Automatic upgrade works fine.
= 2.0.2 =
* No specifics. Automatic upgrade works fine.
= 2.0.1 =
* No specifics. Automatic upgrade works fine.
= 2.0.0 =
* No specifics. Automatic upgrade works fine.
= 1.1.0 =
* No specifics. Automatic upgrade works fine.
= 1.0.0 =
* No specifics. Automatic upgrade works fine.
= 0.2.0 =
* No specifics. Automatic upgrade works fine.
= 0.1.1 =
* No specifics. Automatic upgrade works fine.
= 0.1.0 =
* No specifics. Automatic upgrade works fine.

== Did you like it? ==

Drop me a line on http://www.yann.com/en/wp-plugins/yd-buddypress-feed-syndication

And... *please* rate this plugin --&gt;