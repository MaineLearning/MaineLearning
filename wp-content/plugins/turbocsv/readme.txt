=== TurboCSV ===

Contributors: chrisvrichardson

Tags: excel, spreadsheet, csv, comma, import, importer, importing, data, file, datafile, upload, uploader, uploading, feed, post, posts

Requires at least: 3.0

Tested up to: 3.0

Stable tag: 2.13



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

