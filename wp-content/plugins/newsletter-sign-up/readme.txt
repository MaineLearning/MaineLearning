=== Plugin Name ===
Contributors: DvanKooten
Donate link: http://dannyvankooten.com/donate/
Tags: newsletter,sign-up,mailchimp,aweber,newsletter signup,checkbox,ymlp,email,phplist,icontact,mailinglist,checkbox,form widget,widget,newsletter widget,subscribe widget,form shortcode,mailchimp api
Requires at least: 2.7
Tested up to: 3.5
Stable tag: 1.7.9

The ultimate Newsletter plugin! Works with third-party newsletter services like MailChimp. Sign-up checkboxes, widget forms, shortcodes, it's all in there.

== Description ==

= Newsletter Sign-Up =

Boost your mailinglist size with this newsletter plugin! This plugin adds various ways for your visitors to subscribe to your third-party newsletter. Newsletter Sign-Up is most known for it's "Sign-up to our newsletter" checkbox at the WordPress comment form. 

This plugin currently supports the following newsletter providers but is not limited to those: MailChimp, YMLP, Aweber, iContact, PHPList, Feedblitz.
You can practically use the plugin for EVERY newsletter provider that's around if you use the right configuration settings.

**Features:**

* Add a "sign-up to our newsletter" checkbox to your comment form or register form (including BP and MS)
* Easy customizable Newsletter Sign-Up Widget
* Embed a sign-up form in your posts with a simple shortcode `[newsletter-sign-up-form]`.
* Embed a sign-up form in your template files by calling `nsu_signup_form();`
* Use the MailChimp or YMLP API or any other third party newsletter provider.
* Works with most major mailinglist services because of the form mimicing feature.
* Compatible with [WYSIWYG Widgets](http://dannyvankooten.com/wordpress-plugins/wysiwyg-widgets/) to allow easy widget text editing.

**More info:**

* [Newsletter Sign-Up for WordPress](http://dannyvankooten.com/wordpress-plugins/newsletter-sign-up/)
* Check out more [WordPress plugins](http://dannyvankooten.com/wordpress-plugins/) by Danny van Kooten
* You should follow [Danny on Twitter](http://twitter.com/DannyvanKooten) for lightning fast support and updates.

= "Sign me up" checkbox =
One of the things NSU does is adding a "Sign me up to your newsletter checkbox" to your comment and registration forms. Most visitors who care to comment are willing to subscribe to your newsletter too.
Signing up to your newsletter is as easy as ticking a checkbox! 

= Sign-up forms =
Another strength of NSU is the ability to create sign-up forms and easily embed those in multiple places. Newsletter Sign-Up comes with a sign-up form widget, a shortcode to use in your posts and/or pages and a function call
to use in your template files. You can even choose to redirect the visitor to a certain page after signing-up, offering them exclusive content or a "thank you for signing up" message.

= Using MailChimp or YMLP? Use their API's! =
If you're using MailChimp or YMLP then you're in luck. Configuring is as easy as providing your API credentials so that Newsletter Sign-Up can work with the API of your newsletter provider.

= Customizable =
All generated forms come with common CSS classes and unique CSS identifiers so that you can easily style them to your likings. 

= Easy to setup =
Configuring Newsletter Sign-Up has been made as easy as possible. With the Configuration Extractor that comes with Newsletter Sign-Up all you have to is provide your sign-up form HTML code, the configuration extractor
tool will then analyze it and try to extract the right values for you. 


== Installation ==

1. Upload the contents of newsletter-sign-up.zip to your plugins directory.
1. Activate the plugin
1. Specify your newsletter service settings. For more info head over to: [Newsletter Sign-Up for WordPress](http://dannyvankooten.com/wordpress-plugins/newsletter-sign-up/)
1. That's all. Watch your list grow!
1. Optional: Install [WYSIWYG Widgets](http://dannyvankooten.com/wordpress-plugins/wysiwyg-widgets/) if you want to be able to easily edit the widget's form text.

== Frequently Asked Questions ==

= What does this plugin do? =

This plugin adds various way to your WP blog for visitors to subscribe to your third party newsletter service. What once started out as a simple 'Sign me up to your newsletter' checkbox at your comment form is now a 
superb e-mail address gatherer. This plugin respects double opt-in rules, it's all legit.

= What is the shortcode to embed a sign-up form in my posts? =

Its `[newsletter-sign-up-form]`.

= Why does the checkbox not show up? =

You're theme probably does not support the comment hook this plugin uses to add the checkbox to your comment form. You can manually place the checkbox
by calling `<?php if(function_exists('nsu_checkbox')) nsu_checkbox(); ?>` inside the form tags of your comment form. Usually this file can be found in your theme folder as `comments.php`.

= Where can I get the form action of my sign-up form? =

Look at the source code of your sign-up form and check for `<form action="http://www.yourmailinglist.com/signup?a=asd123"`....
The action attribute is what you need here.

= Where can I get the email identifier of my sign-up form? =

Take a look at the source code of your sign-up form and look for the input field that holds the email address. You'll need the NAME attribute of this input field, eg: `<input type="text" name="emailid"....` (in this case emailid is what you need)

= Can I let my users subscribe with their name too? =

Yes, it's possible. Just provide your name identifier (finding it is much like the email identifier) and the plugin will try to submit the user's name along with the request.

= Can I also show a checkbox at the BuddyPress sign-up form? =

Yes.

= Can I show a sign-up form by calling a function in my template files? =

Yes, use the following code snippet in your theme files to embed a sign-up form: `if(function_exists('nsu_signup_form')) nsu_signup_form();`

For more questions and answers go have a look at my website regarding [Newsletter Sign-Up](http://dannyvankooten.com/wordpress-plugins/newsletter-sign-up/)

== Screenshots ==

1. The mailinglist configuration page of Newsletter Sign-Up in the WordPress admin panel.
2. The form configuration page in the WP Admin panel.
3. The "sign-up" checkbox in action @ Twenty Eleven

== Changelog ==

= 1.7.9 =
* Improved CSS Reset for comment checkbox

= 1.7.8 =
* Improved enqueue call to load stylesheet on frontend
* Fixed notice after submitting widget form (undefined variable $name)
* Fixed %%IP%% value in widget form
* Added debugging option. When `_nsu_debug` is in the POST or GET data it will echo the result of the sign-up request.

= 1.7.7 =
* Improved Improved HTML output for forms
* Improved code indentation
* Added OnBlur attribute to form input's. Default value now reappears after losing focus (while empty).
* Added replacement value's for additional data (`%%NAME%%` and `%%IP%%`)

= 1.7.6 =
* Fixed: The plugin now works with PHPList again. Thanks ryanjlaw.

= 1.7.5 =
* Fixed: Hidden inputs are now wrapped by a block element too, so the form output validates as XHTML 1.0 STRICT.

= 1.7.4 =
* Added: Ability to turn off double opt-in (MailChimp API users only).
* Improved: Various CSS improvements

= 1.7.3 =
* Fixed: Actual fix for previous two plugin updates. My bad, sorry everone.

= 1.7.2 =
* Fixed: Bug after submitting comment or registration form.

= 1.7.1 =
* Fixed: Bug where you coudln't configure mailinglist specific settings (like MC API).

= 1.7 =
* Added: add subscribers to certain interest group(s) (limited to 1 grouping at the moment). (MailChimp API users only)
* Improvement: Slightly better code readability

= 1.6.1 =
* Fixed notice on frontend when e-mail field not filled in
* Fixed provided values for First and Lastname field for MailChimp when using both.

= 1.6 =
* Improvement: Huge backend changes. Everything is split up for increased maintainability.
* Improvement: Better code documenting
* Improvement: Consistent function names (with backwards compatibility for old function names)
* Improvement: Only load frontend CSS file if actually needed / asked to.
* Added: Added CSS class to text after signing up
* Added: Added option to automatically add paragraph's to text after signing up.
* Added: Added option to set default value for e-mail and name field.
* Added: Option to redirect to a given url after signing-up
* Added: More elegant error handling.
* Fix: "Hide checkbox for subscribers" did not work after version 1.5.1

= 1.5.2 =
* Fix: Fixed widget, it was broken after v1.5.1.

= 1.5.1 =
* Improvement: Minor code improvements
* Improvement: Minor backend changes

= 1.5.0 =
* Added: Config Extractor, a tool that helps you extract the right configuration settings when manually configuring.
* Improvement: Some code refactoring, more to come..

= 1.4.3 =
* Improvement: Added CSS classes to the form's label's and input fields.
* Improvement: Added unique ID's to each form and input field
* Added: Compatibilty with [WYSIWYG Widgets Plugin](http://dannyvankooten.com/wordpress-plugins/wysiwyg-widgets/) . Install that plugin if you want to be able 
to easily edit the widget's text. :)

= 1.4.2 =
* Improvement: Made the label at comment form and registration forms clickable so it checks the checkbox.
* Improvement: Made 'email' a required field when submitting the sign-up form.
* Improvement: Made 'name' an optionally required field when submitting the sign-up form.

= 1.4.1 =
* Added: the function `nsu_signup_form()` which you can call from your theme files to output a sign-up form, just like the shortcode.

= 1.4 =
* Improvement: Hide metaboxes in the NSU configuration screen
* Improvement: Edit all widget labels in NSU configuration screen instead of widget options. (You might have to reconfigure some of your settings, sorry!)
* Added: Ability to add a sign-up form to your posts or pages using the shortcode `[newsletter-sign-up-form]`
* Some more restructuring of the code.

= 1.3.3 =
* Improvement: Users can now edit the widget labels for the email and name input fields.
* Improvement: You can now use some common HTML-codes in the widget text's
* Improvement: Linebreaks (\n) are now converted to HTML linebreaks in frontend.
* Fixed: Widget typo in the label for the email input field.

= 1.3.2 =
* Fixed bug: not loading the widget's default CSS after submitting option page.
* Fixed bug: 404 error after submitting the widget using API and 'subscribe with name'.
* Improvement: Added id's to the input fields in the widget.

= 1.3.1 =
* Fixed: parse error, unexpected T_FUNCTION for older versions of PHP which do not support anonymous functions.

= 1.3 =
* Added a widget: adds a sign-up form to your widget areas

= 1.2 =
* Fixed critical bug causing all custom form requests to fail (iow no sign-up request was made). Sorry!
* Fixed bug in backend: empty aweber list id field

= 1.1.2 =
* Re-added the predefined form values for Aweber, iContact and MailChimp
* Fixed PHPList fatal error
* Added additional data support when using YMLP API

= 1.1.1 =
* Fixed small bug for YMLP or MailChimp API users

= 1.1 =
* Changed the backend for different newsletters
* Added YMLP API support
* Added MailChimp API support
* Now uses the WordPress HTTP API
* Removed the ReadOnly attribute of prefilled fields
* Now works with MultiSite registration forms too
* Fixed inline CSS, now uses optional stylesheet
* Better documentation

= 1.0.6 =
* Fixed a missing argument error.

= 1.0.5 =
* Fixed some undefined indexes notices in the frontend

= 1.0.4 =
* Small change in seconds before timeout when making the POST request.
* Fixed bug with addititional data not being properly saved.

= 1.0.3 =
* Changed the plugin's backend structure
* Added the <a href="http://dannyvankooten.com">DannyvanKooten.com</a> dashboard widget.

= 1.0.2 =
* Added option to send custom data along with the sign-up request. 

= 1.0.1 =
* Improved script and stylesheet loading - now only loads on NS options page.
* Added option to show checkbox at the BuddyPress register form

= 1.0 =
* Stable release
* Added CURL support
* Added option to show a checkbox at WP registration form
* Added option to subscribe commenters with their name

= 0.1 =
Beta release