=== TablePress ===
Contributors: TobiasBg
Donate link: http://tablepress.org/donate/
Tags: html,table,data,editor,csv,excel,import,export
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: 0.9-RC
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

TablePress enables you to create and manage tables, without having to write HTML code, and it adds valuable functions for your visitors.

== Description ==

TablePress enables you to create and manage tables on your WordPress site. No HTML knowledge is needed, as a comfortable interface allows to easily edit table data. Tables can contain any type of data, even formulas that will be evaluated. An additional JavaScript library can be used to add features like sorting, pagination, filtering, and more for site visitors. You can include the tables into your posts, on your pages, or in text widgets with ease. Tables can be imported and exported from/to CSV files (e.g. from Excel), HTML files, and JSON.

= More information =
Please visit the plugin website at http://tablepress.org/ for more information.

= Supporting future development =
If you like the TablePress plugin, please rate and review it here in the WordPress Plugin Directory, support it with your [donation](http://tablepress.org/donate/), or [flattr it](https://flattr.com/thing/783658/TablePress). Thank you!

= Migration from WP-Table Reloaded =
TablePress is the official successor of the WP-Table Reloaded plugin. It has been rewritten from the ground up and uses an entirely new internal structure. This fixes some major flaws of WP-Table Reloaded and prepares the plugin for easier, safer, and better future development.
If you are currently using WP-Table Reloaded, it is recommended that you switch to TablePress. WP-Table Reloaded will no longer be maintained or developed. For further information on how to switch from WP-Table Reloaded to TablePress, please see the [migration guide](http://tablepress.org/migration-from-wp-table-reloaded/) on the plugin website.

== Screenshots ==

1. "All Tables" screen
2. "Edit" screen
3. "Add new Table" screen
4. "Import" screen
5. "Export" screen
6. "Plugin Options" screen
7. "About" screen
8. Two example tables (as they can be seen on the [TablePress website](http://tablepress.org/demo/))

== Installation ==

The easiest way to install TablePress is via your WordPress Dashboard. Go to the "Plugins" page and search for "TablePress" in the WordPress Plugin Directory. Then click "Install Now" and the following steps will be done for you automatically. You'll just have to activate TablePress.

Manual installation works just as for other WordPress plugins:

1. Download and extract the ZIP file and move the folder "tablepress" into the "wp-content/plugins/" directory of your WordPress installation.
1. Activate the plugin "TablePress" on the "Plugins" page of your WordPress Dashboard.
1. Create and manage tables by going to the "TablePress" section in the admin menu.
1. Add a table to a page, post, or text widget, by adding the Shortcode `[table id=<your-table's-ID> /]` to its content, or by using the "Table" button in the editor toolbar.
1. You can change the table styling by using CSS code, which can be entered into the "Custom CSS" textarea on the "Plugin Options" screen.

== Frequently Asked Questions ==

= Where can I find answers to Frequently Asked Questions? =

Many questions, regarding different features or styling, have been answered on the [FAQ page](http://tablepress.org/faq/) on the plugin website.

= Support? =

For support questions, bug reports, or feature requests, please use the [WordPress Support Forums](http://wordpress.org/support/plugin/tablepress). Please [search](http://wordpress.org/support/) through the forums first, and only [open a new thread](http://wordpress.org/support/plugin/tablepress) if you don't find an existing answer. Thank you!

= Requirements? =

In short: WordPress 3.5 or higher, while the latest version of WordPress is always recommended.

= Languages and Localization? =

The plugin currently includes the following languages:
Chinese (Simplified), English, French, German, Slovak, and Spanish.

I'd really appreciate it, if you would translate the plugin into your language! Using Heiko Rabe's WordPress plugin [Codestyling Localization](http://wordpress.org/extend/plugins/codestyling-localization/) that really is as easy as pie. Just install the plugin, add your language, create the .po-file, translate the strings in the comfortable editor and create the .mo-file. It will automatically be saved in TablePress's plugin folder. If you send me the .mo- and .po-file, I will gladly include them into future plugin releases.
There is also a .pot-file available to use in the "i18n" subfolder. Of course you can also use [poEdit](http://www.poedit.net/) as your editor, which also works nicely.

= Development =

You can follow the development of TablePress more closely in its official GitHub repository at https://github.com/TobiasBg/TablePress.

= Switch from WP-Table Reloaded to TablePress =

For further information on how to switch from WP-Table Reloaded to TablePress, please see the [migration guide](http://tablepress.org/migration-from-wp-table-reloaded/) on the plugin website.

= Where can I get more information? =

Please visit the [official plugin website](http://tablepress.org/) for the latest information on this plugin.

== Usage ==

After installing the plugin, you can add, import, export, edit, copy, delete, ... tables on the "TablePress" screen in the WordPress Dashboard.
Everything should be self-explaining there.

To show one of your tables in a post, on a page, or in a text widget, just include the Shortcode `[table id=<the-ID> /]` to your post/page/text widget, where `<the-ID>` is the ID of your table (can be found on the left side of the "All Tables" screen.)
Alternatively, you can also insert tables by clicking the "Table" button in the editor toolbar, and then selecting the desired table.

After that you might want to change the styling of the table. You can do this by entering CSS commands into the "Custom CSS" textarea on the "Plugin Options" screen.
You may also add certain features (like sorting, pagination, filtering, alternating row colors, row highlighting, print name and/or description, ...) by checking the appropriate options on a table's "Edit" screen.

== Acknowledgements ==

Special thanks go to [Allan Jardine](http://www.sprymedia.co.uk/) for the [DataTables JavaScript library](http://www.datatables.net/).
Thanks to all language file translators!
Thanks to every donor, supporter and bug reporter!

== License ==

This plugin is Free Software, released and licensed under the GPL, version 2 (http://www.gnu.org/licenses/gpl-2.0.html).
You may use it free of charge for any purpose.
I kindly ask you for link somewhere on your website to http://tablepress.org/. This is not required!
I'm also happy about [donations](http://tablepress.org/donate/) or something from my [amazon.de](http://tablepress.org/wishlist-de/) or [amazon.com wishlist](http://tablepress.org/wishlist-us/)! Thanks!

== Changelog ==

= Version 0.8.1-beta =
Initial version where most features are ready and pretty stable.

= Version 0.9-RC =
Release candidate in which all intended features are included and very stable.

== Upgrade Notice ==

= 0.9-RC =
This release contains many enhancements and bug fixes.