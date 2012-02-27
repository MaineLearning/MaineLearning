=== CryptX ===
Contributors: Ralf Weber
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4026696
Tags: encode, antispam, email, spam, spider, unicode, mailto, filter, spambot, decrypt, encrypt, mail, javascript, post, page, widget, image
Requires at least: 3.0
Tested up to: 3.3-aortic-dissection
Stable tag: 3.1.2

== Description ==

No more SPAM by spiders scanning you site for email adresses. With CryptX you can hide all your email adresses, 
with and without a mailto-link, by converting them using javascript or UNICODE. Although you can choose to add 
a mailto-link to all unlinked email adresses with only one klick at the settings. That's great, isn't it?

[Plugin Homepage](http://weber-nrw.de/wordpress/cryptx/ "Plugin Homepage")

== Screenshots ==

1. Disable Option box
2. Plugin settings

== Changelog ==

See [Plugin Homepage](http://weber-nrw.de/wordpress/cryptx/ "Plugin Homepage") for details!
= 3.1.2 =
* fixed a bug in the template function (should now work without errors)
= 3.1.1 =
* added support for subject information in the template function
* added some missing translation strings
= 3.1 =
* added support for custom fields
* removed the vertical-align for the generated image. The alignment should be done by css with the class 'cryptxImage'.
= 3.0 =
* huge parts of code rewritten to fix some problems. (Thx to Harald Bertels)
= 2.8 =
* complete code review! All errors shown with WP_DEBUG where fixed.
= 2.7.1 =
* bug fixing with some php installations (thx to Norman Rzepka)
= 2.7 =
* added the shortcode [cryptx]...[/cryptx]! The shortcode was implemented for posts and pages, where CryptX was switched off.
= 2.6.6 =
* fixed a bug in the template function. (thx to Jessica for reporting the bug)
= 2.6.5 =
* fixed a missing slash at the end of the image tag.
= 2.6.4 =
* fixed a bug with some php versions.
= 2.6.3 =
* some bugs are fixed, e.g. the non functional "add mailto checkbox" on the option page.
= 2.6.2 =
* added the option to choose where the needed javascript is loaded (header/footer)
= 2.6.1 =
* bugfix for the autolink function ( see comment: http://weber-nrw.de/wordpress/cryptx/comment-page-7/#comment-415 )
= 2.6.0 =
* Added new feature to convert email adress into an image
= 2.5.1 =
* Added Option to disabled/enable the CryptX Widget on editing a post or page.
= 2.5.0 =
* Changed the location to store the disabled per post/page option from postmeta to CryptX Options. This should keep the postmeta fields clean. 
= 2.4.6 =
* added support for ssl-secured sites
= 2.4.5 =
* added support for mailto links without email adress, like a link from "Sociable"
= 2.4.4 =
* added support for widgets
* added information how to implement CryptX in your template
= 2.4.3 =
* added support for content provided by shortcodes like "WP-Table Reloaded"
= 2.4.2 =
* missed to delete my internal Debug function :-(
= 2.4.1 =
* Changed routine in the new Option if Custom Field not exist.
= 2.4.0 =
* Add Option to disable CryptX on single post/page

== Installation ==

1. Upload "cryptX folder" to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Edit the Options under the Options Page.
4. Look at your Blog and be happy.

[Plugin Homepage](http://weber-nrw.de/wordpress/cryptx/ "Plugin Homepage")
