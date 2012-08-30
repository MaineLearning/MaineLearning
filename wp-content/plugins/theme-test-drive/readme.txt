=== Theme Test Drive ===
Contributors: freediver
Donate link: https://www.networkforgood.org/donation/MakeDonation.aspx?ORGID2=920155875
Tags: theme, themes, admin, test
Requires at least: 2.3
Tested up to: 3.4.1
Stable tag: trunk

Safely test drive any theme as an administrator, while visitors use the default one. 

== Description ==

Theme Test Drive Wordpress plugin allows you to safely test drive any theme on your blog as administrator, while visitors still use the default one. 

It happens completely transparent and they will not even notice you run a different theme for yourself. 

Best part is you can even set the testing theme options (if it has them) in the Admin panel while you are testing the theme.

You can also preview any theme by adding "?theme=xxx" to your blog URL. For example http://www.myblog.com/?theme=Default

Plugin by Vladimir Prelovac. Need a <a href="http://www.prelovac.com/vladimir/services">WordPress Consultant</a>?

== Changelog ==

= 2.8.3 =
* Update for compatibility to WordPress 3.4.1 by replacing deprecated calls (thanks Lance Willett!)
* Use new WP_Theme API for loading themes and getting theme information
* Fix PHP warnings

= 2.8.2 =
* WordPress 3.2 compatibility

= 2.8.1 =
* Reverted the admin capabilities so the user can see the options panel for theme being previewed 

= 2.8 =
* Added a patch for theme and stylesheet filters that sometimes caused problems with user capabilities

= 2.7.4 =
* WordPress 2.8 compatibilty

= 2.7.3 =
* Fixed the problem with access level update

= 2.7 =
* WP 2.7 cleanup and security update


= 2.5 =
* Easy theme installation: Install your themes using a built in installer
* Ability to use folder name as well as "?theme=xxx" paramter for instant preview (thanks Michael Stewart!)


== Installation ==

1. Upload the whole plugin folder to your /wp-content/plugins/ folder.

2. Go to the Plugins page and activate the plugin.

3. Use the Options page to set the theme you want to test drive.

The selected theme will be visible only to blog administrator.  

Other visitors of the blog will always see the default theme. 

Note: if you use WP-Cache plugin, you might need to disable it (or setup to exclude pages)


== Credits ==

Some of the functions of Theme Test Drive plugin came from other plugins. So I can at least thank these people:

* [Ryan Boren](http://boren.nu/ "Ryan Boren") for his [Theme Switcher](http://dev.wp-plugins.org/wiki/ThemeSwitcher "Theme Switcher") plugin
* [Andres Santos](http://andufo.com "Andres Santos") for his [wp-websnapr](http://andufo.com/proyectos/plugins/wp-websnapr" "wp-websnapr") plugin
* [Oliver](http://www.deliciousdays.com/ "Oliver") for his [cforms II](http://www.deliciousdays.com/cforms-plugin "cforms II") plugin
* [Scott](http://www.plaintxt.org/ "Scott") for his excellent readme.txt file
* [WebSnapr](http://www.websnapr.com "WebSnapr") folks for their service

Thanks.

== License ==

This file is part of Theme Test Drive.

Theme Test Drive is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

Theme Test Drive is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Theme Test Drive. If not, see <http://www.gnu.org/licenses/>.


== Screenshots ==

1. Admin panel for installing and previewing themes



== Frequently Asked Questions ==

= How do I correctly use this plugin? =

Go to Admin Panel, Design, Theme Test Drive. Select the theme you want to preview and click enable.

Additionally you may click on any of the instant preview links, or wait for a preview thumbnail to generate.

= Can I suggest an feature for the plugin? =

Of course, visit <a href="http://www.prelovac.com/vladimir/wordpress-plugins/theme-test-drive#comments">Theme Test Drive Home Page</a>

= I love your work, are you available for hire? =

Yes I am, visit my <a href="http://www.prelovac.com/vladimir/services">WordPress Services</a> page to find out more.