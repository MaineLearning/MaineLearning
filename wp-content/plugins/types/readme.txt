=== Types - Custom Fields and Custom Post Types Management ===
Contributors: brucepearson, AmirHelzer, jozik, mihaimihai
Donate link: http://wp-types.com
Tags: CMS, custom field, custom fields, custom post type, custom post types, post, post type, post types, cck, taxonomy, fields, types
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 0.9.4.2

The complete solution for custom post types, custom taxonomy and custom fields. Craft your edit screens and use shortcodes to display fields.

== Description ==

Types is your one-stop solution for creating custom content with WordPress. You can define:

* Custom post types
* Custom taxonomy
* Custom fields with fancy meta boxes

Once you've defined your custom post types and custom fields, use Types shortcode to include them in your content, without resorting to PHP.

Check out this 2 minutes Types intro:

[vimeo http://vimeo.com/32661608]

= Documentation =

* [Types - Custom Post Types and Custom Fields](http://wp-types.com/home/types-manage-post-types-taxonomy-and-custom-fields/) - Official home page, with example applications
* [Types and Views User Guides](http://wp-types.com/documentation/user-guides/) - The complete set of Types manuals
* [Types API](http://wp-types.com/documentation/functions/) - PHP reference for API usage

= Custom Posts and Taxonomy =

Use the GUI to define new post types and taxonomy. You can control menu positioning, labels and all the advanced features that WordPress offers.

Choose how your custom taxonomy relates to post types and holds together related content.

= Custom Fields =

Types lets you define meta boxes with custom fields. You can assign these boxes to posts, pages or any custom type. Your meta boxes can include standard and advanced fields:

**Text (translatable) custom fields:**

* **Single-line text** - a single text line
* **Multi-line text** - a paragraph of text
* **WYSIWYG** - Visual editor in custom fields (requires WordPress 3.3 and above)

**Selector custom fields:**

* **Checkbox** - yes / no fields. You can control the text output for on and off.
* **Radio** - displays multiple text options as radio buttons
* **Select** - displays multiple text options as a drop-down select input

**File upload custom fields:**

* **File** - allows uploading files to posts and displays a download link on public pages
* **Image** - allows uploading images and displaying them at different sizes with a resize cache

**Speciality custom fields:**

* **Date** - shows a date-picker in the editor screen and lets you choose output formats when displaying
* **Email** - inputs and validates correct email format
* **Numeric** - lets writers enter number data only by validating its content
* **Phone** - validates format for phone numbers and allows to display them with special styling
* **Skype** - displays the Skype graphics, showing when you're available
* **URL** - validates URL format and displays a link on public pages

= Embed Types into Your Themes and Plugins =

If you're creating themes and plugins that require custom types and custom fields, you can use the Embedded version of Types. This is a minimal version, which doesn't include the configuration screens. It runs as a theme function and doesn't require activation, like the full plugin version.

You will export Types configuration to a file and the Embedded version will use that. Your theme (or plugin) will include the custom types and fields that you define, but users will not be able to modify the settings. Look at the 'embedded' directory for information on how to integrate with your theme.

= Fully WPML Compatible =

The same folks who wrote [WPML](http://wpml.org) have created this plugin. Types and WPML play perfectly together. You can translate all labels via WPML's String Translation, without any configuration.

When you translate content that includes custom fields, these fields will appear in WPML's Translation Editor. Also, content synchronization between different languages is fully supported.

= Display Custom Fields using Shortcodes =

Types creates shortcodes for inserting custom fields into the WordPress editor. Your new fields will be available to editors. Just click on the T menu and choose which field to insert.

You can use Types API to add custom fields to templates.

= Add Custom Fields to Templates, Query Content and Display It =

To display custom content in whatever way you choose, discover [Views](http://wp-types.com) - the perfect companion for Types. Together, Types and Views allow building complex WordPress sites, without any coding.

You'll be able to create dynamic templates for single pages and content lists using advanced query and output tools. All Views are created using plain HTML and shortcodes - no PHP or API to learn.

== Installation ==

1. Upload 'types' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= Can I import and export my data? =

Types includes its own import and export features. If you create a development site, you can easily transfer settings to the production site.

== Screenshots ==

1. Define custom post types and taxonomy
2. Define custom fields (meta-groups)
3. Editing custom fields
4. Inserting custom fields to content

== Changelog ==

= 0.9 =
* First release

= 0.9.1 =
* Added Embedded mode
* Allows to manage existing custom fields with Types
* Added a .po file for translating Types interface

= 0.9.2 =
* Added WYSIWYG custom fields
* Improved the usability for setting up custom taxonomies
* Date fields use the date format specified by WordPress
* Fixed a few bugs for WordPress 3.3
* Checks that fields cannot be created twice
* Checks that only local images are resized
* Added bulk-delete for custom fields
* Fixed a few issues with WPML support

= 0.9.3 =
* Added an import screen from Advanced Custom Fields
* Added an import screen from Custom Posts UI
* Added support for non-English character in custom field names
* Eliminated messages about how to insert custom fields in PHP
* Check if fields already exist with the same name before creating them
* Improved compatibility with WPML

= 0.9.4 =
* Added an option to display custom field groups on specific templates only
* Fixed a number of bugs with Javascript and with Windows servers

= 0.9.4.1 =
* Fixed a problem adding custom fields to a group on some servers
* Fixed so that standard tags and categories work again with custom post types
* Fixed custom field groups not being shown for some content templates

== Upgrade Notice ==

= 0.9.1 =
* The new Embedded mode allows integrating Types functionality in WordPress plugins and themes.

= 0.9.2 =
* Check out the new WYSIWYG custom fields.

= 0.9.3 =
* This version streamlines the admin screens and includes a importers from other plugins

= 0.9.4 =
* You can now enable custom field groups for content with specific templates

= 0.9.4.1 =
* Fix a few problems found in the 0.9.4 release
