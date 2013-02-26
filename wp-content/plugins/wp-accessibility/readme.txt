=== WP Accessibility ===
Contributors: joedolson
Donate link: http://www.joedolson.com/donate.php
Tags: title, accessibility, accessible, navigation, wcag, a11y
Requires at least: 3.4.2
Tested up to: 3.5.0
Stable tag: trunk
License: GPLv2 or later

WP Accessibility provides fixes for common accessibility issues in your WordPress site.

== Description ==

This plug-in is for correcting a variety of common accessibility issues encountered in WordPress themes. While most accessibility issues can't be easily addressed using abstracted code, WP Accessibility can provide a number of accessibility features with a minimum mount of setup and expert knowledge.

All features can be disabled according to your theme's needs. All of the functions based on modifying stylesheets can be customized using your own custom styles by placing the appropriate stylesheet in your theme directory.

At the moment, the plug-in can:

* Remove redundant title attributes from page lists, category lists, and archive menus. 
* Enable skip links with WebKit support by enqueuing JavaScript support for moving keyboard focus.
* Add skip links with user-defined targets. (Customizable targets and appearance.)
* Add language and text direction attributes to your HTML attribute
* Remove the target attribute from links.
* Force a search page error when a search is made with an empty text string. (If your theme has a search.php template.)
* Remove tabindex from elements that are focusable.
* Strip title attributes from images inserted into content. 
* Add post titles to standard "read more" links.
* Add an outline to the keyboard focus state for focusable elements. 
* Add a toolbar toggling between high contrast, large print, and desaturated (grayscale) views of your theme.
* Fix certain accessibility issues in the WordPress admin styles
* Show the color contrast between two provided hexadecimal color values.
* Read more about <a href="http://make.wordpress.org/accessibility/wp-accessibility-plugin/">the accessibility problems corrected</a>

The plug-in is intended to make up for some deficiencies commonly found in themes. It can't correct every problem (by a long shot), but can provide some assistance.

Translating my plug-ins is always appreciated. Visit <a href="http://translate.joedolson.com">my translations site</a> to start getting your language into shape!


== Installation ==

1. Download the plugin's zip file, extract the contents, and upload them to your wp-content/plugins folder.
2. Login to your WordPress dashboard, click "Plugins", and activate WP Accessibility.
2. Customise your settings on the Settings > WP Accessibility page.

== Changelog ==

= 1.2.1 =

* Disabled grayscale toggle in Accessibility toolbar by default due to poor browser support and low functional value. (Can still be enabled by user.)
* Removed php notice in title-free recent posts widget
* Updated German and added Polish translations

= 1.2.0 =

* Added space between content output and continue reading text in excerpt context.
* Added German translation
* Added Accessibility Toolbar (<a href="http://www.usableinteractions.com/2012/11/accessibility-toolbar/">Source</a>)
* Added WP admin stylesheet:
* Some contrast improvements.
* Placed post row action links (Edit, Quick Edit, Trash, View) into screen reader visible and keyboard usable position.
* Added underlines to links on hover
* Supports your own custom wp-admin stylesheet via your Theme directory. 

= 1.1.2 =

* Update support statement to WP 3.5.0
* Add role='navigation' to skiplinks container.

= 1.1.1 =

* Bug fix: extra template loaded when search template is inserted.
* Bug fix: jQuery not always loaded when required.

= 1.1.0 =

* Added ability to add focus outline in :focus pseudo class.
* Added color contrast tool.
* Added settings link to plugins listing.
* Added link to translations site for this plug-in. 
* Improved response for forcing search error on empty search submission.
* Bug fix for adding custom skip link.

= 1.0.0 =

* Initial release!

== Frequently Asked Questions ==

= WP Accessibility is inserting some information via javascript. Is this really accessible? =

Yes. It does require that the user is operating a device that has javascript support, but that encompasses the vast majority of devices and browsers today, including screen readers.

= I installed WP Accessibility and ran some tests, but I'm still getting errors WP Accessibility is supposed to correct. =

Even if WP Accessibility is running correctly, not all accessibility testing tools will be aware of the fixes. Here's a resource for more information: [Mother Effing Tool Confuser](http://mothereffingtoolconfuser.com/).

== Screenshots ==

1. Settings Page

== Upgrade Notice ==

* Bug fix for custom skiplink target; Added focus outline option; Added color contrast tool.