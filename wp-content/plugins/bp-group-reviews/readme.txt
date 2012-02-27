=== BP Group Reviews ===
Contributors: boonebgorges, apeatling 
Donate link: http://teleogistic.net/donate
Tags: buddypress, group, groups, review, rating, star
Requires at least: WP 3.2, BP 1.5
Tested up to: WP 3.2.1, BP 1.5.1
Stable tag: 1.3.1

Adds a reviews/rating section to BuddyPress groups. As seen on the buddypress.org/extend/plugins

== Description ==

BP Group Reviews adds a new tab to your BuddyPress groups, where users can leave reviews and star ratings for the group.

The guts of the plugin were written by Andy Peatling for use on the [Extend section of buddypress.org](http://buddypress.org/extend/plugins). His code was adapted and expanded into this plugin by Boone Gorges. All praise goes to Andy, all blame for things broken goes to Boone :)

Follow the plugin's development at [http://github.com/boonebgorges/bp-group-reviews](http://github.com/boonebgorges/bp-group-reviews)

== Installation ==

1. Activate the plugin from the plugins screen
1. Enable reviews on a given group at Admin > Group Settings
1. That's it

== Frequently Asked Questions ==

= Can I write a translation for the plugin? =

Sure can. There's a .pot file in the plugin's languages directory.

= I need a hook =

Let me know and I'll put it in there for you.

== Translation credits ==

* Italian: Luca Camellini
* Persian: [Alefba](http://alefba.us)
* Dutch: GooseNL
* Spanish: SeluGlindoo
* Russian: slaFFik

== Changelog ==

= 1.3.1 =
* Increased compatibility with BP 1.6 canonical redirects
* Display the content of a failed review in the post box if returned by an error

= 1.3 =
* BP 1.5 Compatibility

= 1.2.3 =
* Fixes a bug that caused BPGR to interfere with topic listing on Forums directory.

= 1.2.2 =
* Fixes bug that caused number of reviews not to be counted in some legacy cases
* Hides group review data in directories and in group headers when the group has had reviews but then has them turned off

= 1.2.1 =
* Fixes bug that caused errors when activity component is disabled
* Prevents non-members from accessing the Reviews tabs of private and hidden groups

= 1.2 =
* Adds Highest Rated widget
* Fixed bug that caused BPGR JS to load before jQuery in some cases. Props Brajesh
* Fixed bug that may have caused review post form for non-logged-in members. Props slaFFik
* Russian translation added. Props slaFFik
* Fixed bug that showed wrong rating count in groups directory

= 1.1.1 =
* Fixed bug that made it unreasonably difficult to unhook directory ratings

= 1.1 =
* Rating data in hooked into the group directory
* Users are limited by default to one review per group (see includes/templatetags.php bpgr_allow_multiple_reviews() for details)
* Spanish translation added (props SeluGlindoo)

= 1.0.3 =
* Reviews can now be enabled on a group-by-group basis (group admin > group settings)
* Persian translation added (props Alefba)
* Dutch translation added (props GooseNL)

= 1.0.2 =
* Missing gettext calls fixed
* Typo fixed (props Luca Camellini)
* Italian translation added (props Luca Camellini)

= 1.0.1 =
* Loads ratings metadata into the groups loop for better performance
* Missing gettext calls fixed

= 1.0 =
* Initial release

