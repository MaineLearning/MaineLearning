=== Plugin Name ===
Contributors: westpointer
Donate link: http://buglenotes.com/
Tags: BuddyPress
Requires at least: 2.8.4
Tested up to: 2.8.4
Stable tag: 1.0

Automatically join members to groups based on user profile fields. Requires BuddyPress

== Description ==

Automatically join a member to a group based on a profile field. For example, say you have a profile field where the person selects his state from a drop down list. If he selects, New York, he'll be automatically added to the group New York. Or, if you have a profile field for "Favorite Animal" and the member selects "Dog", you could automatically have him become a member of the group "Dog's Rule" and "Best Dog Food".

== Installation ==

1. Upload the folder `bn-auto-join-group` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure in wp-admin via `Profile -> Groups Link` under the `Site Admin` section

== Frequently Asked Questions ==

= What version of BuddyPress is needed? =

This plugin was tested with version 1.0.3 of BuddyPress.

= What if I add a link to an existing profile field? =

The profile of all members will be scanned.  Any members that makes the new link, will automatically be joined to the appropriate BuddyPress group.

= Why would I install this? =

If you are using BuddyPress, groups may be the soul of what you are doing.  This plugin will be sure members get into groups that they should be joining.  A good example is a site based on a college.  If you wanted all the English majors to be members of the `English` group, you would create a profile field where the member selects his major.  Any member who selects `English` for the profile field, will be automatically made a member of the `English` group.

== Screenshots ==
None

== Changelog ==

= 1.0 =
* Orginal release

== Arbitrary section ==

None

== A brief Markdown Example ==

None