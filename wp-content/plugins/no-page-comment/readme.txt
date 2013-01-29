=== No Page Comment ===

Contributors: sethta
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5WWP2EDSCAJR4
Tags: admin, comments, custom post type, javascript, page, pages, post, posts, plugin, settings, tools, trackbacks
Requires at least: 3.1
Tested up to: 3.5
Stable tag: trunk

Disable comments by default on new pages and custom post types, while still giving you the ability to individually set them on a page or post basis.

== Description ==

By default, WordPress gives you two options. You can either disable comments and trackbacks by default for all pages and posts, or you can have them active by default. Unfortunately, there is no specific WordPress setting that allows comments and trackbacks to be active by default for posts, while disabling them on pages or any other post type.

There have been workarounds created by disabling comments site-wide on all pages and/or posts, but what if you actually want to have comments on a page or two? The difference between this plugin and others is that it will automatically uncheck to discussion settings boxes for you when creating a new page, while still giving you the flexibility to open comments up specifically on individual pages and post types.

Also, if you are looking for a quick way to disable all comments or pingbacks for a specific custom post type, that feature has now been built into the plugin. It directly interacts with your database to modify the status, so it is highly recommended that you backup your database first. There shouldn't be any issues with this feature, but it's always good to play it safe.

[Official No Page Comment Plugin Page](http://sethalling.com/plugins/wordpress/no-page-comment "No Page Comment WordPress Plugin")

[Donate to Support No Page Comment Development](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5WWP2EDSCAJR4 "Donate to support the No Page Comment Plugin development")

== Installation ==

1. Unzip the `no-page-comment.zip` file and `no-page-comment` folder to your `wp-content/plugins` folder.
1. Alternatively, you can install it from the 'Add New' link in the 'Plugins' menu in WordPress.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Comments and trackbacks will be turned off by default when adding a new page.

= Settings Page =

Click 'No Page Comment' in the settings panel. A screen will display showing the following settings for posts, pages, and any other custom post type installed on your blog:

* Disable comments
* Disable trackbacks

Note: These settings set the default when creating a new page. Once a new post, page, or custom post type is added, comments can be enabled by modifying the Discussion settings for that page.

Also, there is now the option to globally enable/disable comments or pingbacks of a specific post type.

== Frequently Asked Questions ==

= Why aren't comments and trackbacks being disabled? =

Javascript probably isn't active on your browser. Enable javascript for the plugin to work correctly.

= Why are comments disabled in posts as well? =

This is most likely due to a setting in WordPress. Go to the 'Discussion' settings page and make sure that comments are enabled. This is required for the plugin to work properly.

= Why can't I enable comments of custom post type: "X" =

Depending on your theme or plugin that created custom post type "X", that post type may not have comments set up. If this is the case, this plugin cannot help you and you will have to talk to that theme/plugin author.

= How do I modify the comment settings on an individual post or page? =

First, you must make sure you can see the Discussion admin box. Enable this by clicking on the 'Screen Options' tab at the top right and then checking the discussion checkbox. Below the post/page editor, there will be a new admin box allowing you to specifically enable or disable comments and trackbacks for that page or post.

= I want to quickly disable all trackbacks throughout my blog posts. Is this possible? =

Of course, although *it is highly recommended that you backup your blog's database prior to completing this step*. Go to the 'No Page Comment' settings page and scroll to the bottom of the page. There is an area that will allow you to either enable or disble both comments and trackbacks for any post type you have installed on your blog.

== Screenshots ==

1. The Settings page on a fresh WordPress 3.5 installation

== Changelog ==

= 0.3 =
* NEW: Add ability to enable/disable all comments or trackbacks on any specific custom post type. It is highly recommended that you backup your blog's database prior to doing this.

= 0.2 =
* UPDATE: Style Admin Settings Page to match with WordPress
* NEW: Add support for posts
* NEW: Add support for custom post types

= 0.1 =
* NEW: Initial release.

== Upgrade Notice ==

= 0.3 =
Adds the ability to enable/disable all comments or trackbacks on any specific custom post type. All previous No Page Comment settings will remain intact with upgrade.

= 0.2 =
Adds the ability to disable comments on posts, pages, and custom post types. All previous No Page Comment settings will remain intact with upgrade.