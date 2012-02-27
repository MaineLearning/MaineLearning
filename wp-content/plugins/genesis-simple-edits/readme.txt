=== Plugin Name ===
Contributors: nathanrice, studiopress
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5553118
Tags: shortcodes, genesis, genesiswp, studiopress
Requires at least: 3.2
Tested up to: 3.2.1
Stable tag: 1.7.1

This plugin lets you edit the three most commonly modified areas in any Genesis theme: the post-info (byline), the post-meta, and the footer area.

== Description ==

This plugin creates a new Genesis settings page that allows you to modify the post-info (byline), post-meta, and footer area on any Genesis theme. Using text, shortcodes, and HTML in the textboxes provided in the admin screen, these three commonly modified areas are easily editable, without having to learn PHP or write functions, filters, or mess with hooks.

== Installation ==

1. Upload the entire `genesis-simple-edits` folder to the `/wp-content/plugins/` directory
1. DO NOT change the name of the `genesis-simple-edits` folder
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to `Genesis > Simple Edits`
1. Edit the areas you would like to change, using text, HTML, and the provided shortcode references.
1. Save the changes

== Frequently Asked Questions ==

= What are Shortcodes? =

Check out the [Shortcodes API](http://codex.wordpress.org/Shortcode_API) for an explanation, and our [Shortcode Reference](http://dev.studiopress.com/shortcode-reference) for a list of available Genesis-specific shortcodes.

= My PHP isn't working =

This plugin is not designed to work with PHP code.

= The plugin won't activate =

You must have Genesis (1.3+) or a Genesis child theme installed and activated on your site.

== Changelog ==

= 1.0 =
* Initial Release

= 1.7.1 =
* Increased installation requirement to Genesis 1.7.1
* Removed PHP4 constructor
* Whitespace, standards, and documentation