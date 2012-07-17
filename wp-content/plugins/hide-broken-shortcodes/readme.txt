=== Hide Broken Shortcodes ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: shortcode, shortcodes, content, post, page, coffee2code
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 2.5
Tested up to: 3.4
Stable tag: 1.5
Version: 1.5

Prevent broken shortcodes from appearing in posts and pages.


== Description ==

Prevent broken shortcodes from appearing in posts and pages.

Shortcodes are a handy feature of WordPress allowing for a simple markup-like syntax to be used within post and page content, such that a handler function will replace the shortcode with desired content.  For instance, this:
    `[youtube id="abc" width="200"]`
might be replaced by a plugin to embed a YouTube video into the post with a width of 200.  Or:
    `[make_3d]Special News[/make_3d]`
might be used to make a three-dimensional image of the text contained in the shortcode tag, 'Special News'.

By default, if the plugin that provides the functionality to handle any given shortcode tag is disabled, or if a shortcode is improperly defined in the content (such as with a typo), then the shortcode in question appears on the blog in its entirety, unprocessed by WordPress.  At best this reveals unsightly code-like text to visitors and at worst can potentially expose information not intended to be seen by visitors.

This plugin prevents unhandled shortcodes from appearing in the content of a post or page. If the shortcode is of the self-closing variety (the first example above), then the shortcode tag and its attributes are not displayed and nothing is shown in their place.  If the shortcode is of the enclosing variety (the second example above), then the text that is being enclosed will be shown, but the shortcode tag and attributes that surround the text will not be displayed (e.g. in the second example above, "Special News" will still be displayed on the site).

See the Filters section for more customization tips.

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/hide-broken-shortcodes/) | [Plugin Directory Page](http://wordpress.org/extend/plugins/hide-broken-shortcodes/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Unzip `hide-broken-shortcodes.zip` inside the `/wp-content/plugins/` directory for your site (or install via the built-in WordPress plugin installer)
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Optionally filter 'hide_broken_shortcode' or 'hide_broken_shortcodes_filters' if you want to customize the behavior of the plugin


== Frequently Asked Questions ==

= How can I type out a shortcode in a post so that it doesn't get processed by WordPress or hidden by this plugin? =

If you want want a shortcode to appear as-is in a post (for example, you are trying to provide an example of how to use a shortcode), can use the shortcode escaping syntax, which is built into WordPress, by using two opening brackets to start the shortcode, and two closing brackets to close the shortcode:

* `[[some_shortcode]]`
* `[[an_example style="yes"]some text[/an_example]]`

The shortcodes will appear in your post (but without the double brackets).

= How can I prevent certain broken shortcodes from being hidden? =

Assuming you want to allow the broken shortcodes 'abc' and 'gallery' to be ignored by this plugin (and therefore not hidden if broken), you include the following in your theme's functions.php file or in a site-specific plugin:

`
function allowed_broken_shortcodes( $display, $shortcode_name, $m ) {
	$shortcodes_not_to_hide = array( 'abc', 'gallery' );
	if ( in_array( $shortcode_name, $shortcodes_not_to_hide ) )
		$display = $m[0];
	return $display;
}
add_filter( 'hide_broken_shortcode', 'allowed_broken_shortcodes', 10, 3 );
`


== Filters ==

The plugin is further customizable via two filters. Typically, these customizations would be put into your active theme's functions.php file, or used by another plugin.

= hide_broken_shortcode =

The 'hide_broken_shortcode' filter allows you to customize what, if anything, gets displayed when a broken shortcode is encountered.  Your hooking function can be sent 3 arguments:

Arguments :

* $default (string): The default display text (what the plugin would display by default)
* $shortcode (string): The name of the shortcode
* The text bookended by opening and closing broken shortcodes, if present

Example:

`add_filter( 'hide_broken_shortcode', 'hbs_handler', 10, 3 );
function hbs_handler( $default, $shortcode, $content ) {
	return ''; // Don't show the shortcode or text bookended by the shortcode
}`

= hide_broken_shortcodes_filters =

The 'hide_broken_shortcodes_filters' filter allows you to customize what filters to hook to find text with potential broken shortcodes.  The two default filters are 'the_content' and 'widget_text'. Your hooking function will only be sent one argument: the array of filters.

Example:

`add_filter( 'hide_broken_shortcodes_filters', 'hbs_filter' );
function hbs_filter( $filters_array ) {
	$filters_array[] = 'the_title'; // Assuming you've activated shortcode support in post titles
	return $filters_array;
}`


== Changelog ==

= 1.5 =
* Recursively hide nested broken shortcodes
* Re-license as GPLv2 or later (from X11)
* Add 'License' and 'License URI' header tags to readme.txt and plugin file
* Remove ending PHP close tag
* Note compatibility through WP 3.4+
* Fix error in example code in readme.txt

= 1.4 =
* Update get_shortcode_regex() and do_shortcode_tag() to support shortcode escape syntax
* NOTE: The preg match array sent via the 'hide_broken_shortcode' filter has changed and requires you to update any code that hooks it
* Add version() to return plugin version
* Note compatibility through WP 3.3+
* Add Frequently Asked Questions section to readme.txt
* Add link to plugin directory page to readme.txt
* Update copyright date (2012)

= 1.3.1 =
* Note compatibility through WP 3.2+
* Minor code formatting changes (spacing, variable removal)
* Fix plugin homepage and author links in description in readme.txt

= 1.3 =
* Switch from object instantiation to direct class invocation
* Explicitly declare all functions public static
* Note compatibility through WP 3.1+
* Update copyright date (2011)

= 1.2 =
* Allow customization of the filters the plugin applies to via the 'hide_broken_shortcodes_filters' filter
* Change do_shortcode filter priority from 12 to 1001 (to avoid incompatibility with Preserve Code Formatting, and maybe others)
* Move registering filters into register_filters()
* Rename class from 'HideBrokenShortcodes' to 'c2c_HideBrokenShortcodes'
* Store plugin instance in global variable, $c2c_hide_broken_shortcodes, to allow for external manipulation
* Note compatibility with WP 3.0+
* Minor code reformatting (spacing)
* Add Filters and Upgrade Notice sections to readme.txt
* Remove all header documentation and instructions from plugin file (all that and more are in readme.txt)
* Remove trailing whitespace from header docs

= 1.1 =
* Create filter 'hide_broken_shortcode' to allow customization of the output for broken shortcodes
* Now also filter widget_text
* Add PHPDoc documentation
* Note compatibility with WP 2.9+
* Update copyright date

= 1.0 =
* Initial release


== Upgrade Notice ==

= 1.5 =
Recommended minor update: recursively hide nested broken shortcodes; noted compatibility through WP 3.4+; explicitly stated license

= 1.4 =
Minor update: support shortcode escaping syntax; noted compatibility through WP 3.3+. BE AWARE: An incompatible change has been made in third argument sent to 'hide_broken_shortcode' filter.

= 1.3.1 =
Trivial update: noted compatibility through WP 3.2+ and minor code formatting changes (spacing)

= 1.3 =
Minor update: slight implementation modification; updated copyright date; other minor code changes.

= 1.2 =
Minor update. Highlights: added hooks for customization; renamed class; re-prioritized hook to avoid conflict with other plugins; verified WP 3.0 compatibility.