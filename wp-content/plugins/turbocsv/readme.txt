=== TurboCSV ===
Contributors: chrisvrichardson
Tags: excel, spreadsheet, csv, comma, import, importer, importing, data, file, datafile, upload, uploader, uploading, feed, post, posts
Requires at least: 3.2
Tested up to: 3.4
Stable tag: 2.48

TurboCSV is the easiest way to import Excel or CSV data into your WordPress blog to create posts, pages, categories and tags.

== Description ==
Just use the easy on-screen wizard to specify the upload file, create a template for the post body and title and set the other post options.  TurboCSV will import the file and automatically create posts, pages, categories and tags.  If there's an error with the upload just use the "undo" feature to reverse the changes and try again.

[Home Page](http://www.wphostreviews.com/turbocsv) |
[Documentation](http://www.wphostreviews.com/turbocsv-documentation) |
[FAQ](http://wphostreviews.com/turbocsv/turbocsv-faq)

= Features =
* Import from a PC, a URL or a server file path
* Import posts, pages, categories, tags, and custom fields
* Import custom post types and custom taxonomies
* Import to any blog/site in a WordPress 3.0 multisite network
* Create new posts/pages or update existing ones
* Create import templates with the integrated graphical editor
* Full history and logging
* UNDO capability allows you to completely reverse an import with just one click
* Set all post attributes including date, status, comment status, author, etc.
* Assign random post dates or load dates from the input file
* Import custom fields, including multi-valued fields


== Screenshots ==
1. Import from Anywhere
2. Graphical Template Editor
3. Post / Page Options
4. Categories and Tags
5. Custom Fields
6. Templates
7. History and Undo
8. Import Log and Audit Trail

== Installation ==

See [full installation intructions](http://wphostreviews.com/turbocsv/turbocsv-documentation)

1. Unzip the plugin .zip file into your wordpress plugins directory, usually `/wp-content/plugins/`
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You'll find TurboCSV under the WordPress `tools` menu

== Frequently Asked Questions ==
[FAQ](http://wphostreviews.com/turbocsv/turbocsv-faq)

== Changelog ==

= 2.48 =
* Changed: TurboCSV calls action 'mappress_update_meta' for compatibility with MapPress 2.38.6 and higher
* Changed: Map errors are now assumed to be in field 'mappress_error' for compatibility with MapPress 2.38.6 and higher

= 2.47 =
* Fixed: an error in UTF-8 detection prevented reading some UTF-8 files

= 2.46 =
* Added: a new option has been added to import thumbnails by ID, image name, or URL.  See the documentation for details.
* Changed: for hierarchical categories, the plugin now assigns only the BOTTOM category in the imported hierarchy (the WP post editor works the same way)

= 2.45 =
* Fixed: error when adding additional custom field settings in firefox, could not select value from dropdowns

= 2.44 =
* Fixed: error selecting from dropdown checkboxes in Firefox

= 2.43 =
* Fixed: inserting fields in the HTML editor not working if tinyMCE is disabled in WP user settings

= 2.42 =
* Changed: message 'unable to read headers' is now 'unable to read file'
* Fixed: harmless warning / info messages during import
* Added: there is a new 'term_description_separator' in the settings screen.  You can use to add a description to taxonomy terms (e.g. tags and categories).  The default separator is "::", so enter terms as "name::description" in your input data.
* Added: there is a new 'serialize prefix' ("s:") that forces data into a serialized array - this is useful for a few plugins that require meta data as arrays
* Added: you can set the serialize prefix in the TurboCSV settings, the default is "s:"

= 2.41 =
* Changed: if you are using WordPress < 3.3 you can only edit post content as HTML.  You *must* upgrade WP to 3.3+ to enable the visual editor again
* Fixed: dropdown checkboxes were not updating list of values when selecting all / selecting none
* Fixed: warning/notice messages if wp_debug is on
* Fixed: error in selection for list of users
* Removed script loading for older WP editor version
* Removed ajax hook

= 2.40 =
* Fixed: tinyMCE focus was not getting set for inserting field tokens
* Fixed: call to get_last_revision() was not using $this->

= 2.39 =
* Fixed: fixed visual editor broken by WP 3.2 / 3.3 changes

= 2.38 =
* Added: link to Excel smaple files
* Changed: when importing updates plugin will now create posts if the update key doesn't exist already, rather than giving an error
* Changed: removed 10,000 character line length limit for fgetcsv() in PHP < 5.0

= 2.37 =
* Added: better tooltip help
* Fixed: bug in dropdown value selection

= 2.36 =
* Changed: the default post status is now "draft" (the WordPress default).  You must set it to 'publish' if you want published posts.
* Added: all settings fields (e.g. post type, post status, etc. can now be imported using the spreadsheet)
* Added: all fields now have a default value setting in the import template
* Added: new dropdown controls are used for easier selection of default values
* Removed: AJAX is no longer used to submit imports because some users reported timeout issues

= 2.35 =
* Removed: the debug option checkbox now only logs to a file
* Fixed: a warning notice, if WP_DEBUG is on

= 2.34 =
* Added: serialized data may now be imported; the plugin will unserialize them before adding them to the WordPress metadata tables

= 2.33 =
* Fixed: a typo in one of the TCSV files prevented some non-UTF8 files from being read properly - you'll see an error that headers aren't being read instead.

= 2.32 =
* Added: better file detection for all file types, especially with BOM headers
* Fixed: UTF-16LE files were causing the plugin to fail and write corrupted lines to the history database

= 2.31 =
* Fixed: call to WP dbDelta function removed - caused problems in network activation on some sites

= 2.30 =
* Fixed: reduced WordPress memory leaking for very large imports
* Fixed: converted imported lines to database table for very large imports
* Added: you can now use escaped commas in categories, tags, taxonomies and custom columns.  For example "a,b" is treated as two categories, but "a\,b" is treated as a single category.
* Changed: previously there was an 'address separator' for custom fields.  This is no longer used.  Instead, escape any commas in your addresses.  For example: "Chicago\,IL".
* Added: you can import double quotes in any field by using two quote characters.  For example this would import 'Cat, "Dog"': "Cat, ""Dog"" ".

= 2.29 =
* Fixed: updated javascript selector quotes for compatibility with WordPress 3.2
* Fixed: removed 'select all' checkboxes from taxonomy lists

= 2.28 =
* Fixed: checkboxes not saving for new custom fields

= 2.27 =
* Fixed: some warning messages when running in WP_DEBUG mode
* Fixed: messages when importing with WP_POST_REVISIONS set false for custom post type
* Added: settings screen now includes a parameter for the maximum number of post logs to display (default is 500).

= 2.26 =
* Fixed: bug preventing matching by unique ID field with single quotes in field contents

= 2.25 =
* Added: TurboCSV now supports tab-delimited and pipe-delimited files (in addition to commad-delimited and semicolon-delimited).  Just choose your delimiter in the 'settings' tab.

= 2.24 =
* Fixed: bug in custom parser when separator appears inside enclosures
* Fixed: plugin was excluding "_thumbnail_id" from the list of existing custom fields

= 2.23 =
* Fixed: bug introduced in 2.22 that caused the 'maximum number of errors to skip' setting to be ignored.

= 2.22 =
* Added: it's now possible to network activate the plugin.  As new blogs are created, the plugin will install itself automatically.
* Added: from the main site of a multisite installation you can import into child sites.  Child sites may only import into themselves.
* Added: a single spreadsheet can now import into many sites.  Use the '!blog_id' column to specify the target site.
* Added: undo is now possible in multisite.
* Added: import results now include the blog_id and link to the correct post/taxonomy in that site
* Added: support for more character set encodings
* Added: button to reset option settings to defaults
* Fixed: an obscure bug where fgetcsv() skips first character for certain character sets, e.g. windows-1250
* Added: ability to network activate on multisite installations (normally not advisable, since this is an IMPORT program!)

= 2.21 =
* Changed: categories and tags are now treated consistently like other taxonomies.
* Added: more code to reset the WP cacheing system after import
* Changed: the routines to get import lists and template lists are now streamlined to reduce memory usage when displaying import history

= 2.20 =
* Changed: When updating, only custom fields selected in the template will be updated (previously, the plugin would delete existing values even if not selected)
* Fixed: On very large blogs the WP functions to display liss of taxonomies, tags and categories could run out of memory.  Now only the first 200 terms will be visible in the template editor.

= 2.19 =
* Added: You can now choose either Post ID or your own custom field for updates.

= 2.18 =
* Added: On the settings screen you can now specify an input file delimiter, such as semicolon (";") instead of comma (",").  This is useful for some European CSV files which use semicolon delimiters.
* Added: Code page Windows-1251 for Cyrillic files, such as Russian or Greek
* Changed: By default input columns that don't start with "!" won't be proposed as custom fields.  You must check the fields you want to create.
* Changed: if you have spaces in your column headings the plugin will now allow them.  Just be sure you also include the spaces in your post template.

= 2.17 =
* Changed: The values separator for tags and categories is now always a comma.  Changing the 'values separator' setting in the 'settings' screen only affects custom fields
* Added: You can now escape custom field values.  For example, if the separator is comma, this is two values: "a,b".  This is ONE value: "a\,b"
* Added: categories are now sorted and shown with the full category hierarchy in the template screen

= 2.16 =
* Generating maps from custom fields is now configured entirely in MapPress.  However, geocoding errors are still reported in the TurboCSV during the import.
* Removed table striping - caused slow performance in at least one installation using Google SDN version of jquery-ui

= 2.15 =
* Fixed: bug preventing address import if address field was not final column

= 2.14 =
* Fixed: Case-sensitive meta_keys were only listed once

= 2.13 =
* Fixed: increased AJAX and PHP timeout limits

= 2.12 =
* Fixed: bug prevented save/load of templates

= 2.11 =
* Added: MapPress Pro integration to automatically create maps
* Added: option to set number of rows before database commit.  This parameter will help you avoid running out of memory on large imports.
* Added: option to set the values separator and hierarchy separator characters (by default these are ',' and '|', respectively)
* Added: collapsible options sections; note that dragging and remmebering settings are not implemented
* Fixed: "Process" is now the default button
* Removed: option to turn off buffering
* Removed: demo taxonomies - users needing this feature can implement it from the documentation

= 2.10 =
* Remove incorrect column in the sample spreadsheet and CSV file

= 2.09 =
* Localized date and time using WordPress setting when using current date/time for import
* Added selection of post author from column

= 2.08 =
* Support for custom post types added
* Support for WordPress 3.0 multisite added

= 2.07 =
* Fixed encoding in SQL tables - it's now forced to utf8)
* Added option to specify import encoding or try to detect (plugin will try to 'guess' UTF-8 or ANSI)
* Updates now completely replace post metadata rather than appending

= 2.06 =
* Fixed printing for two internal error messages

= 2.05 =
* Added feature to load multiple values into custom fields: specify input value as comma-separate list, e.g. "a,b,c" to use.

= 2.04 =
* Fixed bug preventing visual editor in Firefox and Chrome

= 2.03 =
* Fixed warning message for option settings on initial activation

= 2.01, 2.02 =
* Added default column names - see documentation for details.  Now you can create a spreadsheet that will import without doing any manual mapping.
* Re-wrote the taxonomy processing.  It's now faster and the same code is used for categories, tags and custom taxonomies.
* Performance improvement for database updates during large imports.
= 2.0 =
* Completely new release with lots of new features and bug fixes
