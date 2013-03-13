=== Autolink URI ===
Contributors: Denis-de-Bernardy, Mike_Koepke
Donate link: http://www.semiologic.com/partners/
Tags: autolink, link, auto-link, semiologic
Requires at least: 3.1
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Autolink URI plugin for WordPress automatically converts urls to hyperlinked urls.


== Description ==

The Autolink URI plugin for WordPress automatically converts urls to hyperlinked urls.

Before:

> www.semiologic.com

After:

> [www.semiologic.com](http://www.semiologic.com)

= Help Me! =

The [Semiologic forum](http://forum.semiologic.com) is the best place to report issues. Please note, however, that while community members and I do our best to answer all queries, we're assisting you on a voluntary basis.

If you require more dedicated assistance, consider using [Semiologic Pro](http://www.getsemiologic.com).


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Change Log ==

= 2.1 =

- Added support for port numbers in url
- Fix localhost urls

= 2.0.4 =

- Allow colons (:) in url matching

= 2.0.3 = 

- Don't check inside of quotes.  Some shortcodes have urls as parameters

= 2.0.2 =

- Avoid auto-linking background attributes

= 2.0.1 =

- Also catch urls with a parameter and no trailing slash

= 2.0 =

- Enhance escape/unescape methods
- Localization
- Code enhancements and optimizations