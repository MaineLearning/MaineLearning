=== Blog Copier ===
Contributors: ModernTribe, peterchester
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TWM2GF6BQZGSN
Tags: copy, duplicate, replicate, blog, site, duplicator, replicator, moderntribe, tribe, wpmu, multisite, network, superadmin
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 1.0.2

Enables superusers to copy existing sub blogs to new sub blogs.

== Description ==

A simple and effective approach to copying blogs within a multisite network.

* Copy a blog including all its widgets, template settings and more.
* Option to copy or not copy files.
* GUIDs and urls in post contents are migrated automatically.

This plugin was derived from Ron Renneck's awesome WP Replicator (http://wpebooks.com/replicator/) plugin, although it's been 90% rewritten. Changes from the original include the following:

* Improved performance on large scale blogs.
* Improved file copy performance and an option in the admin to bypass copying altogether.
* Removed limit of number of blogs that can be used as a copy source.
* Encapsulated the code in a Class and renamed variables to be more readable.
* Revised UI to keep it simple and easy to use.

Sadly the WordPress file management code is not idea for handling the copying of a large folder with subdirectories so we opted to stick with exec('cp'). On the flip side, we set up a filter (copy_blog_files_command) so that you can override it with your own custom copy code.

This plugin is actively supported and we will do our best to help you. In return we simply as 3 things:

1. Help Out. If you see a question on the forum you can help with or have a great idea and want to code it up and submit a patch, that would be just plain awesome and we will shower your with praise. Might even be a good way to get to know us and lead to some paid work if you freelance.  Also, we are happy to post translations if you provide them.
1. Donate - if this is generating enough revenue to support our time it makes all the difference in the world
https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TWM2GF6BQZGSN
1. Support us by buying our Premium plugins. In particular, check out our Events Calendar Pro http://tri.be/wordpress-events-calendar-pro/

== Installation ==

= Install =

1. In your WordPress Network administration, go to the Plugins page
1. Activate this plugin and a subpage for the plugin will appear
   in your Sites menu.

Please visit the forum for questions or comments: http://wordpress.org/tags/blog-copier/

= Requirements =

* PHP 5.1 or above
* WordPress 3.0 or above
* Multisite activated with at least one sub-blog

== Documentation ==

It's pretty straight forward. Select the blog you want to copy. Set a new domain or subdomain and a title. Decide if you want to copy the files or just the data. Click "Copy Now". Done.

This DOES NOT copy blogs across networks, back up blogs off the network, or copy the master blog. This also does NOT copy users from one blog to another.

== Changelog ==

= 1.0.2 =

Added .pot file. Anyone interested in submitting a translation??? http://wordpress.org/tags/blog-copier/

= 1.0.1 =

Minor documentation updates.

= 1.0 =

Initial plugin release. Woohoo!

== Screenshots ==

1. Blog Copier Screen
1. Sites "Copy" Option

== Frequently Asked Questions ==

= Where do I go to file a bug or ask a question? =

Please visit the forum for questions or comments: http://wordpress.org/tags/blog-copier/