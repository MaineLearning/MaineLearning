=== Types - Custom Fields and Custom Post Types Management ===
Contributors: brucepearson, AmirHelzer, jozik, mihaimihai
Donate link: http://wp-types.com
Tags: CMS, custom field, custom fields, custom post type, custom post types, post, post type, post types, cck, taxonomy, fields, types, relationships
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 0.9.5.1

The complete solution for custom post types, custom taxonomy and custom fields. Craft your edit screens and use shortcodes to display fields.

== Description ==

**[Types](http://wp-types.com/home/types-manage-post-types-taxonomy-and-custom-fields/) is your one-stop solution for creating custom content with WordPress.**

Use Types to:

* Add **custom post types** to WordPress
* Organize content with **custom taxonomy**
* Enhance editing screens with **custom fields**
* Define **parent / child** relationship between post types

It's powerful enough to help you build any site with WordPress, but also simple and friendly for non-techies.

Once you've defined your custom post types and custom fields, you can display them using Types' PHP API or Views.

= Custom post types and taxonomy =

Types lets you define custom post types and taxonomy from within the WordPress GUI.

This video shows how to define custom post types and custom taxonomy. You're also welcome to read the complete tutorial on [WordPress custom Post Types](http://wp-types.com/documentation/user-guides/create-a-custom-post-type/).

[vimeo http://vimeo.com/37267854]

**New in Types 0.9.5 - define parent / child relationship between different post types!**

You can create complex content hierarchies in Types. For example, if you're building a site for artists, each *Artist* can have many *Show* child items. You can add and edit Shows right from within the Artist edit page in one neat table.

When you setup post relationships, different content types become interconnected. Field Tables let you bulk-edit child items when you're editing parents. This takes the concept of repeater fields to a whole new level.

= Custom fields =

Types lets you completely customize the WordPress editing interface with different kinds of custom fields. You can display the custom fields using Types API functions or with Views.

Watch this video for a quick intro to custom fields. For the complete reference, go to our [WordPress custom fields tutorial](http://wp-types.com/documentation/user-guides/using-custom-fields/).

[vimeo http://vimeo.com/37320858]

Choose which fields to display, group them together into 'meta boxes' and select on what edit pages they will display.

Types supports these custom field kinds:

* **Single-line text** - a single text line
* **Multi-line text** - a paragraph of text
* **WYSIWYG** - Visual editor in custom fields (requires WordPress 3.3 and above)
* **Checkbox** - yes / no fields. You can control the text output for on and off.
* **Radio** - displays multiple text options as radio buttons
* **Select** - displays multiple text options as a drop-down select input
* **File** - allows uploading files to posts and displays a download link on public pages
* **Image** - allows uploading images and displaying them at different sizes with a resize cache
* **Date** - shows a date-picker in the editor screen and lets you choose output formats when displaying
* **Email** - inputs and validates correct email format
* **Numeric** - lets writers enter number data only by validating its content
* **Phone** - validates format for phone numbers and allows to display them with special styling
* **Skype** - displays the Skype graphics, showing when you're available
* **URL** - validates URL format and displays a link on public pages

Besides defining custom fields, Types can also validate correct user-input. You can enable format validation for every field type and make different fields optional or required.

= Displaying custom fields in PHP templates using Types API =

If you're convenient with PHP and WordPress API, Types makes it super-easy for you to display custom fields. It includes a complete API that will display each custom field in a unique way.

For example, when you display an image, Types will output the IMG tag with all its attributes. It also includes an image resizer and cache that let you display images in any size, without loading you server.

See how to insert custom fields into WordPress template files in PHP. Types includes a comprehensive [custom fields API](http://wp-types.com/documentation/functions/), which lets you insert any field, anywhere in the site.

[vimeo http://vimeo.com/37725539]

= Display custom content types and fields with Views and no coding =

**[Views](http://wp-types.com/home/views-create-elegant-displays-for-your-content/) is the display companion for Types.** It's a commercial plugin that costs just $49 USD, which will turn weeks of programming and debug into a few hours of fun. With Views you don't need a programmer to create complex sites. It's like having your own developer, instantly building whatever you want.

Views lets you:

* Load any content from the database and display it with [Views](http://wp-types.com/documentation/user-guides/views/).
* Create templates right from within the WordPress editor using [View Templates](http://wp-types.com/documentation/user-guides/view-templates/).
* Insert custom fields anywhere.

Watch this video for a quick teaser:
[vimeo http://vimeo.com/37376736]

You're welcome to visit our [learn section](http://wp-types.com/learn/) to see complete working examples of classified sites, magazine layout, real-estate sites and other complete and functional designs we've built with Types and Views and no PHP at all.

Views is designed for both non-techies and developers alike. If you're just starting with custom content types for WordPress, you'll appreciate how quickly you can have a fully-working site that uses custom post types, taxonomy and fields.

Then, when you're more convenient with Types and Views and want to get more, you'll discover power-features such as nested output, conditional display, post relationship and other features that turn WordPress into a top-notch CMS.

= Need to build multilingual sites? =

The same folks who wrote [WPML](http://wpml.org) have created Types, so the two plugins play perfectly together. You can translate all labels via WPML's String Translation, without any configuration.

When you translate content that includes custom fields, these fields will appear in WPML's Translation Editor. Also, content synchronization between different languages is fully supported.

== Installation ==

1. Upload 'types' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= How can I display custom post types on the home-page? =

By default, WordPress will either display your blog posts or a specific page on the home-page.

To display custom post types on the home-page, you have two options:

1. If you're comfortable with PHP and WordPress API, edit the site's template files (probably index.php) and load the custom post types there. Different themes do this differently, so we can't really say what single approach works best. You should look at [get_posts](http://codex.wordpress.org/Template_Tags/get_posts), which is part of the WordPress Template Tags system.
2. If you want to build sites right away, without becoming an expert in WordPress API and you can afford $49 (USD), try [Views](http://wp-types.com/home/views-create-elegant-displays-for-your-content/). You'll be able to load whatever content you need from the database and display it anywhere and in whatever way you choose.

We're sorry, but we don't know of any third option which is both free and requires no coding.

= Can I use Types without Views? =

Sure you can! Types, by itself, replaces several other plugins that define custom types and fields. We believe that it does it much better, but it's up to you to decide.

If you also buy Views, you'll have a complete solution for both **defining** and **displaying** custom data. You can achieve everything that Views does if you're fluent in PHP and know WordPress API. When you buy Views, you're also supporting Types development, but we're not looking for donations. You should consider Views for its value and nothing else.

= I am already a ninje developer, do I really need Views? =

We honestly think so. Even if you're an expert developer, do you really enjoy doing the same stuff over and over again? With Views, you can concentrate on the unique features of every new site that you build, without wasting time on routine stuff.

Views was originally inspired by the Drupal module with the same name. Around 90% of all Drupal sites use the Drupal Views module and many consider it as one of the most powerful features in Drupal. Now, you too can enjoy the same power (and even more), but without any of the complexity of Drupal.

= Can Types display custom fields that I defined somehow else? =

Yes! You can tell Types to manage any other custom fields. For example, if you're using an e-commerce plugin, you can tell Types to manage product pricing. This will greatly help you display these fields with Types API or with Views.

Go to Custom fields control, under the Types menu. There, you can tell Types to manage existing custom fields.

= How do I migrate an existing site to use Types? =

The most important thing is to remember not to define custom post types and taxonomy in more than one place. If you previously defined them in PHP, first, remove your PHP code with the manual definition. The content will appear to temporarily vanish. Don't panic. Now, redefine the same custom post types and taxonomy with Types. Everything will return :-)

Types also includes data import from other plugins such as Custom Post UI and Advanced Custom Fields.

= Can I import and export my Types settings? =

Yes! Types includes its own import and export features, using an XML settings file. If you create a development site, you can easily transfer settings to the production site.

= What is the advantage of using Types over separate plugins for custom post types, taxonomy and fields? =

Types offers a much richer integration, which is simply impossible with separate plugins. For example, you have fine-grained control of where to display custom meta-boxes. Because Types defines both the post types and fields, we have greater control of where things can go.

Additionally, Types is the only plugin that lets you define parent/child relationships between different post types AND use that information to edit child data when editing a parent.


== Screenshots ==

1. Defining custom post types and taxonomy
2. Defining custom fields (meta-groups)
3. Editing custom fields
4. Inserting custom fields to content
5. Bulk editing child content using Field Tables

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

= 0.9.4.2 =
* Fixes a few bugs.

= 0.9.5 =
* Added support for parent/child post relationship between different types
* Added Field Tables, for bulk editing child fields from the parent editor
* Streamlined the field insert GUI

= 0.9.5.1 =
* Fixed a last-minute bug with post relationship

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

= 0.9.5 =
Try the new parent/child relationship between different post types!

= 0.9.5.1 =
Fixed a last-minute bug with post relationship
