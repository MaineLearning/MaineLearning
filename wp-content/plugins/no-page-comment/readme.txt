=== No Page Comment ===

Contributors: sethta
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5WWP2EDSCAJR4
Tags: admin, comments, custom post type, javascript, page, pages, post, posts, plugin, settings, tools, trackbacks
Requires at least: 3.1
Tested up to: 3.3.1
Stable tag: trunk

Disable comments by default on new pages and custom post types, while still giving you the ability to individually set them on a page or post basis.

== Description ==

By default, WordPress gives you two options. You can either disable comments and trackbacks by default for all pages and posts, or you can have them active by default. Unfortunately, there is no specific WordPress setting that allows comments and trackbacks to be active by default for posts, while disabling them on pages or any other post type.

There have been workarounds created by disabling comments site-wide on all pages and/or posts, but what if you may actually want to have comments on a page or two. The difference between this plugin and others is that it will automatically uncheck to discussion settings boxes for you when creating a new page, while still giving you the flexibility to open comments up specifically on individual pages and post types.

[Official No Page Comment Plugin Page](http://sethalling.com/plugins/no-page-comment "No Page Comment WordPress Plugin")

[Donate to Support No Page Comment Development](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5WWP2EDSCAJR4 "Donate to support the No Page Comment Plugin development")

== Installation ==

1. Unzip the `no-page-comment.zip` file and `no-page-comment` folder to your `wp-content/plugins` folder.
1. Alternatively, you can install it from the 'Add New' link in the 'Plugins' menu in WordPress.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Comments and trackbacks will be turned off by default when adding a new page.

= Settings Page = 

Click 'FAQs Settings' in the settings panel. A screen will display showing the following settings for posts, pages, and any other custom post type installed on your blog:

* Disable comments
* Disable trackbacks

Note: These settings set the default when creating a new page. Once a new post, page, or custom post type is added, comments can be enabled by modifying the Discussion settings for that page.

== Frequently Asked Questions ==

= Why aren't comments and trackbacks being disabled? =

Javascript probably isn't active on your browser. Enable javascript for the plugin to work correctly.

= Why are comments disabled in posts as well? =

This is most likely due to a setting in WordPress. Go to the Discussion settings page and make sure that comments are enabled. The plugin will only block comments on pages.

= How do I modify the comment settings on an individual post or page? =

First, you must make sure you can see the Discussion admin box. Enable this by clicking on the 'Screen Options' tab at the top right and then checking the discussion checkbox. Below the post/page editor, there will be a new admin box allowing you to specifically enable or disable comments and trackbacks for that page or post.

== Changelog ==

= 0.2 =
* UPDATE: Style Admin Settings Page to match with WordPress 
* NEW: Add support for posts
* NEW: Add support for custom post types

= 0.1 =
* NEW: Initial release.

== Upgrade Notice ==

= 0.2 =
Adds the ability to disable comments on posts and pages. All previous No Page Comment settings will remain intact with upgrade.