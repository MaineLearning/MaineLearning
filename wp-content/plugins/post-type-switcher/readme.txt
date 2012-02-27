=== Post Type Switcher ===
Contributors: johnjamesjacoby
Tags: post type
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 1.0

A simple way to change a post type in WordPress.

== Description ==

A simple way to change a post type in WordPress.

Any combination is possible, even custom post types.

Page to Post
Post to Page
Page to Attachment
Post to Custom

== Changelog ==

= Version 1.0 =
* Fix JS bugs
* Audit post save bail conditions
* Tweak UI for WordPress 3.3

= Version 0.3 =
* Use the API to change the post type, fixing a conflict with persistent object caches
* No longer requires JavaScript

= Version 0.2 =
* Disallow post types that are not public and do not have a visible UI

= Version 0.1 =
* Release

== Installation ==

* Install the plugin into the plugins/post-type-swticher directory, and activate.
* From the post edit screen, above the "Publish" button is the "Post Type" interface.
* Change post types as needed.

== Frequently Asked Questions ==

= Why would I need this? =
I needed it to move WordPress posts into a custom post type of my own, so this plugin was borned!

= Does this ruin my taxonomy associations? =
It shouldn't. The only thing this plugin does for version 0.1 is change the 'post_type' property of the post.
