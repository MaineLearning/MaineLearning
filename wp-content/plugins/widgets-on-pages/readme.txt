=== Widgets on Pages ===
Contributors: toddhalfpenny
Donate link: http://gingerbreaddesign.co.uk/wordpress/plugins/plugins.php
Tags: widgets, sidebar, pages, post, shortcode, inline
Requires at least: 2.8
Tested up to: 3.4.2
Stable tag: 0.0.12

The easy way to Add Widgets or Sidebars to Posts and Pages by shortcodes or template tags.

== Description ==

''NOTE'' Apologies but you may lose the widgets in your customised sidebars if upgrading from pre 0.0.8 version. The cause of this loss is required to enhance functionality and reduce further possible loss of config when changing/modifying themes. The choice to to do this was not easy but hopefully will make the plugin more stable going forward.

The easy way to Add Widgets to Posts and/or Pages.  Allows 'in-page' widget areas so widgets can be defined via shortcut straight into page/post content.
There is one default widget area that can be used or you can add more from the settings menu. You can currently have an unlimited number of sidebars.
Each sidebar can be called indepentenly by  a shortcode and you can call more than one per post/page.
Sidebars can be included in the post/page by using a shortcode like `[widgets_on_pages id=x]` where `x` is the number of the sidebar.
Sidebars can also be named via the Widgets on Pages options page and that name can be used instead of the `x` id.
''NOTE'' : see changelog for use if using the named sidebars



== Installation ==


''NOTE'' Apologies but you may lose the widgets in your customised sidebars if upgrading from pre 0.0.8 version. The cause of this loss is required to enhance functionality and reduce further possible loss of config when changing/modifying themes. The choice to to do this was not easy but hopefully will make the plugin more stable going forward.

1. Install the plugin from within the Dashboard or upload the directory `widgets-on-pages` and all its contents to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the widgets you want to the `Widgets on Pages` widget area in the admin screens
1. Add the shortcut `[widgets_on_pages id=x]` to the page or post in the place where you'd like your widgets to appear (where 'x' = the id of the sidebar to use (or the name if you have named it such as `[widgets_on_pages id=TagCloud]`). If using only the default sidebar then no 'id' argument is needed (i.e. `[widgets_on_pages]`).
1. To add a sidebar into a theme you can add the following type of code to the relevant PHP theme file. `<?php widgets_on_template("wop_1"); ?>`
1. If you see bullet points/images next to the widget titles when using this plugin use the 'Enable Styling' setting in the options page
1. For further info check out these ace videos put together by Jessica Barnard 
[youtube http://www.youtube.com/watch?v=h957U96SFYE]

== Frequently Asked Questions ==

= How can I remove the bullet points which appear next to each widget? =

Simply select the 'Enable Styling' setting in the Widgets on Pages options page.

= I did the above but the bullets still show, what now =

Your theme's CSS is probably overriding your setting... you could try using your browsers ''inspect element" function to see what part of the CSS is setting the list-style.

= Can I have more than one defined sidebar area =

Yes... you can have an unlimited number of sidebars defined. The number available can be administered via the settings menu.

== Screenshots ==

1. Setting up the sidebars.

2. The 'options' page.


== Changelog ==

= 0.0.12 = 

1. Updated intermals to "re-hide" options screen from non Administrators (thanks to fran klin for spotting this)
1. Removed some potential name conflicts

= 0.0.11 = 

1. Replaced all short PHP tags with long ones to ensure the plugin worked as expected even on sites where PHP short tags were disabled. Props to drdanchaz over at the WordPress.org forums for the tip-off.
1. Added more specific selectors to the wop.css to target ul>li as well as ul.

= 0.0.10 = 

1. Added option to add CSS file to auto remove bullets... this has been the biggest cause of support mails/forum posts. 

= 0.0.9 = 

1. Corrected shortcode tags show in Widget admin page.

= 0.0.8 =

1. Resolve potential conflicts with other plugins (contextual help callback).
1. Fixed bug so that Widgets settings are not lost when switching themes. Credit to wesleong over at WordPress.org forums for getting this fix on the right track!
1. Add settings link on main dashboard plugins page


= 0.0.7 =

1. Resolve conflict with YouTube Lyte plugin (thanks to Massa P for the tip off)
1. Can now add sidebars via template tags so extra sidebars can be added to themes very quickly.
1. Added contextual help.

= 0.0.6 = 

Sidebars can now be named via the options page. These names can be used in place of the numerical id used in older versions. Note that if you change the name you will need to manually update any shortcodes where this is being used.

= 0.0.5 = 

Fix for activation errors. Looks like it might've been the use of php short open tags or line ending chars.

= 0.0.4 = 

There is now no longer a limit on the number of sidebars that can be defined. Each sidebar can be called independently.

= 0.0.3 = 

The number of sidebars can now be defined via the settings menu. There can be up to 5 defined. Each sidebar can be called independently.

= 0.0.2 = 

Minor update so that the functions.php code is not needed anymore... makes like easier.

= 0.0.1 = 

1st release - only supports one defined in-post/page widget area
