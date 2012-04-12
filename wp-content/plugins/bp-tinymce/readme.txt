=== Plugin Name ===
Contributors: boonebgorges, cuny-academic-commons
Tags: buddypress, tinymce, wysiwyg, rich text, editor
Requires at least: WP 3.0, BuddyPress 1.2.6
Tested up to: WP 3.2.1, BuddyPress 1.2.9
Stable tag: 0.4.1

Replaces textareas throughout BuddyPress with the TinyMCE rich text box.

== Description ==

This plugin enables rich text editing for BuddyPress users. It uses the TinyMCE editor that is distributed with Wordpress. 

== Installation ==

* Install and activate. Rich editing will be enabled on all BP sections of your site.

*** IMPORTANT: This plugin allows certain pieces of HTML to be put into BuddyPress, including hrefs. Make sure that you are satisfied with the security of the plugin before activiating it on a production site! ***


== Changelog ==

= 0.4.1 =
* Added filters
* Abstracted out is_teeny setting for filtering
* Improved support for BP 1.5 bp-default activity posting buttons

= 0.4 =
* Updated to use WP's built-in TinyMCE
* Improved AJAX performance

= 0.3.1 =
* An attempt at hacking around AJAX issues

= 0.3 =
* Lots of refactoring
* Improved language support (tooltips work!)
* Compatibility with latest bp-default messages and activity ajax

= 0.2.1 =
* Fixed problem with a target attribute

= 0.2 =
* Compatibility with "what's new" box on group home pages in BP 1.2

= 0.1 =
* Initial release
