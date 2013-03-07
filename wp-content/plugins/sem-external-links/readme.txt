=== External Links ===
Contributors: Denis-de-Bernardy, Mike_Koepke
Donate link: http://www.semiologic.com/partners/
Tags: external-links, nofollow, link-target, link-icon, semiologic
Requires at least: 2.8
Tested up to: 3.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The external links plugin for WordPress lets you process outgoing links differently from internal links.


== Description ==

The external links plugin for WordPress lets you process outgoing links differently from internal links.

Under Settings / External Links, you can configure the plugin to:

- Process all outgoing links, rather than only those within your entries' content.
- Add an external link icon to outgoing links. You can use a class="no_icon" attribute on links to override this.
- Open outgoing links in new windows. Note that this can damage your visitor's trust towards your site in that they can think your site used a pop-under.
- Add rel=nofollow to the links. You can use a rel="nofollow" attribute on links to override this.

= Help Me! =

The [Semiologic forum](http://forum.semiologic.com) is the best place to report issues. Please note, however, that while community members and I do our best to answer all queries, we're assisting you on a voluntary basis.

If you require more dedicated assistance, consider using [Semiologic Pro](http://www.getsemiologic.com).


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Change Log ==

= 4.1 =

- WP 3.5 compat

= 4.0.6 =

- WP 3.0.1 compat

= 4.0.5 =

- WP 3.0 compat

= 4.0.4 =

- Force a higher pcre.backtrack_limit and pcre.recursion_limit to avoid blank screens on large posts

= 4.0.3 =

- Improve case-insensitive handling of domains
- Improve image handling
- Switch back to using a target attribute: work around double windows getting opened in Vista/IE7
- Disable entirely in feeds

= 4.0.2 =

- Don't enforce new window pref in feeds

= 4.0.1 =

- Ignore case when comparing domains

= 4.0 =

- Allow to force a follow when the nofollow option is toggled
- Enhance escape/unescape methods
- Localization
- Code enhancements and optimizations