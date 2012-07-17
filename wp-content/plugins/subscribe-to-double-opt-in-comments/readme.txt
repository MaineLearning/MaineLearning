=== Subscribe to Double-Opt-In Comments ===
Contributors: Tobiask
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3736248
Tags: comments, subscribe, double opt in, kommentar, abonnieren, opt in, optin, kommentare, benachrichtigung
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: 6.1.2

Based on the well known Subscribe-to-Comments PlugIn, but now with double-opt-in feature to prevent spam.

== Changelog ==

= 6.1.2 =
* Tested with 3.4.1

= 6.1.1 =
* Added Romanian translation, big thanks to: Web Geek Science (<a href="http://webhostinggeeks.com/">Web Hosting Geeks</a>)

= 6.1.0 =
* Merged multisite and normal version!

= 6.0.10 =
* added new translation: Dutch. Thanks to Florian!
* minor bug fixes

= 6.0.9 =
* minor bug fixes

= 6.0.8 =
* added 3.3 support
* added multisite version, but currently disabled, you have to enable it manually if you like to

= 6.0.7 =
* fixed some minor bugs

= 6.0.6 =
* Added new language: Lithuanian (thx to Nata, webhostinghub.com)

= 6.0.5 =
* Fixed some HTML markup
* Fixed double footer problem
* Tested with 3.2

= 6.0.4 =
* Fixed headline bug 

= 6.0.3 =
* Fixed bug with lost footer (using own style)

= 6.0.2 =
* Added support for Portuguese (Brazilian) [thanks to Leandro]
* 3.1.3 compatible

= 6.0.1 =
* Fixed minor bugs

= 6.0 =
* Fixed Bugfix within deletion of subscriptions

= 5.9 =
* Changed apostrophe compatibility
* Minor bugfixes within e-mail sending function

= 5.8 =
* Minor bug fixes
* Checked WP 3.1 compatibility

= 5.7 =
* Minor bug fixes

= 5.6 =
* New css class to style the Verify-Page: verify_succeeded and verify_failed
* Higher security for generated verify-token implemented
* Improved code formatting

= 5.5 =
* Checked 3.0.4 compatibility
* Bugfix with standalone subscribe

== Description ==

= English =

Allows readers to receive notifications of new comments that are posted to an entry, with double-opt-in Feature. 
First, the user will get an e-mail with a confirmation link, after the user has confirmed the subscription, he or she will be noticed about new comments. 
Plugin based on Mark Jaquith "Subscribe to Comments".
More information on my blog: <a href="http://www.sjmp.de/internet/subscribe-to-comments-mit-double-opt-in-pruefung/">sjmp.de</a>.

Language support: English, German, Slovak, Turkish, Danish, Belorussian, Spanish (Argentina), Ukrainian, Italian, Hebrew, Arabic, Portuguese (Brazilian), Lithuanian, Dutch, Romanian.

= Deutsch =

Weiterentwicklung der bekannten Version des "Subscribe to Comments" Plugins von Mark Jaquith.
Jetzt mit Double-Opt-In Feature. Wichtig f&uuml;r deutsche Blogger. User m&uuml;ssen ein Abo eines Blogposts erst via E-Mail bestaetigen.
Danach erhalten sie erst eine Mail falls ein neuer Kommentar gepostet wurde. So werden Spameintragungen ausgeschlossen.
Mehr dazu auch auf meinem Blog: <a href="http://www.sjmp.de/internet/subscribe-to-comments-mit-double-opt-in-pruefung/">sjmp.de</a>.

Sprachunterst&uuml;tzung: Deutsch, Englisch, Slowakisch, T&uuml;rkisch, D&auml;nisch, Wei&szlig;russisch, 
Spanisch (Argentinien), Ukrainisch, Italienisch, Hebr&auml;isch, Arabisch, Portugiesisch (Brasilien), Litauisch, Niederl&auml;ndisch, Rum&auml;nisch.

== Installation ==

= English =

1. Upload all files to the "/wp-content/plugins/" directory
2. Activate the plugin through the "Plugins" menu in your WordPress Adminpanel
3. Set settings via "Settings" menu in WordPress
4. Ready, steady, go :)

Questions? Go to <a href="http://www.sjmp.de/internet/subscribe-to-comments-mit-double-opt-in-pruefung/">this page</a> and leave a comment!

= Deutsch =

1. Dateien ins "/wp-content/plugins" Verzeichnis laden
2. Im Adminbereich das Plugin aktivieren
3. Einstellungen anpassen, ebenfalls im Adminbereich!
4. Fertig, jetzt darf man sich freuen :)

Fragen? Du kannst deine Fragen <a href="http://www.sjmp.de/internet/subscribe-to-comments-mit-double-opt-in-pruefung/">hier</a> stellen! Einfach einen Kommentar hinterlassen.

== Frequently Asked Questions ==

= How can I change the place of the subscription checkbox? =

Use this code (within the loop) in your template file, to change the place of the checkbox: <code><?php show_subscription_checkbox(); ?></code>

= Can people subscribe manually without commenting? =

Yes, just place this code snippet in your template (outside the comments form): <code><?php show_manual_subscription_form(); ?></code>

= I use Hosteurope and I cannot send e-mails via this plugin =

See this (german) comment for a solution: http://www.sjmp.de/internet/subscribe-to-comments-mit-double-opt-in-pruefung/comment-page-10/#comment-4460

== Screenshots ==

1. Part of the settings menu.
2. The checkbox to subscribe to a comment thread.