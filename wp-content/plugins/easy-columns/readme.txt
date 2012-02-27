=== WP Easy Columns ===
Contributors: Pat Friedl
Donate link: http://www.affiliatetechhelp.com/wordpress/easy-columns
Tags: columns, column, grid layout, layout, magazine, page, posts, magazine columns, magazine layout, float div

Requires at least: 2.7
Tested up to: 3.2
Stable tag: 2.0

Easy Columns provides the shortcodes to create a grid system or magazine style columns for laying out your pages just the way you need them.

== Description ==

Easy Columns provides the shortcodes to create a grid system or magazine style columns for laying out your pages just the way you need them.

Using shortcodes for 1/4, 1/2, 1/3, 2/3, 3/4, 1/5, 2/5, 3/5 and 4/5 columns, you can insert <strong>at least thirty</strong> unique variations of columns on any page or post.

Quickly add columns to your pages from the editor with an easy to use "pick n' click" interface!

For usage and more information, visit <a href="http://www.affiliatetechhelp.com" target="_blank">affiliatetechhelp.com</a>.

<b>Example</b>
To create content with 3 columns, you would use the shortcodes like this:

[wpcol_1third]this is column 1[/wpcol_1third]

[wpcol_1third]this is column 2[/wpcol_1third]

[wpcol_1third_end]this is column 3[/wpcol_1third_end]

== Installation ==

1. Upload `wp-ez-columns` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use any of the following shortcodes in your posts or pages:
   Available Shortcodes:

   1/4 columns
   [wpcol_1quarter id="" class="" style=""][/wpcol_1quarter]
   [wpcol_1quarter_end id="" class="" style=""][/wpcol_1quarter_end]

   1/2 columns
   [wpcol_1half id="" class="" style=""][/wpcol_1half]
   [wpcol_1half_end id="" class="" style=""][/wpcol_1half_end]

   3/4 columns
   [wpcol_3quarter id="" class="" style=""][/wpcol_3quarter]
   [wpcol_3quarter_end id="" class="" style=""][/wpcol_3quarter_end]

   1/3 columns
   [wpcol_1third id="" class="" style=""][/wpcol_1third]
   [wpcol_1third_end id="" class="" style=""][/wpcol_1third_end]

   2/3 columns
   [wpcol_2third id="" class="" style=""][/wpcol_2third]
   [wpcol_2third_end id="" class="" style=""][/wpcol_2third_end]

   1/5 columns
   [wpcol_1fifth id="" class="" style=""][/wpcol_1fifth]
   [wpcol_1fifth_end id="" class="" style=""][/wpcol_1fifth_end]

   2/5 columns
   [wpcol_2fifth id="" class="" style=""][/wpcol_2fifth]
   [wpcol_2fifth_end id="" class="" style=""][/wpcol_2fifth_end]

   3/5 columns
   [wpcol_3fifth id="" class="" style=""][/wpcol_3fifth]
   [wpcol_3fifth_end id="" class="" style=""][/wpcol_3fifth_end]

   4/5 columns
   [wpcol_4fifth id="" class="" style=""][/wpcol_4fifth]
   [wpcol_4fifth_end id="" class="" style=""][/wpcol_4fifth_end]

   special columns
   [wpdiv id="" class="" style=""][/wpdiv]
   (easily create DIVs in your content without editing HTML)

   [wpcol_divider] (clears all floats and creates a 2px high, 100% width div)
   [wpcol_end_left] (clears left float)
   [wpcol_end_right] (clears right float)
   [wpcol_end_both] (clears both)

   ** Be sure to insert the "_end" column shortcode for your last column! **

== Frequently Asked Questions ==

= Is This Plugin Supported? =
Yes. Just send an email, and I'd be happy to help

= How can I customize the columns? =
You can edit the wp-ez-columns.css file to customize the layouts. Be sure to back up the CSS before testing!

= Can I put anything in the columns? =
Yes, as long as the content isn't larger than the column, otherwise the CSS will break and cause line breaks.

= What good are columns? =
Columns can be used in CMS layouts, magazine layouts and squeeze page layouts - it's only limited by your imagination.

== Upgrade Notice ==
* Additional shortcodes and fixes - please update!

= 2.0 =
* Upgraded visual editor window to WP 3.2 compliance
* Completely reworked visual editor interface
* Added additional "pick n click" column combinations

= 1.2.1 =
* Added 4/5 column support
* Added custom CSS logic for the 1/5-4/5 columns
* Updated and simplified the visual editor

= 1.2 =
* Additional shortcodes added. Please upgrade!

== Changelog ==
= 1.2 =
* Added Support for 1/5, 2/5 and 3/5 columns.

= 1.1 =
* Updated the shortcodes to clean up the WordPress &quot;Auto P&quot; functions that cause gaps, etc.

= 1.0 =
* New code