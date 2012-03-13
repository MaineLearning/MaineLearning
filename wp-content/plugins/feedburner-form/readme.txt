=== Feedburner Form ===
Contributors: Dianakc
Tags: feedburner, feed, subscription, form, form, subscribers, rss
Donate link: https://dianakcury.com/dev/feedburner-form
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: trunk

Feedburner Form let you insert a Feedburner e-mail subscription form in any widgetized area of your site.

== Description ==

Feedburner Form is a simple plugin that allows you insert Google Feedburner subscription forms in widgetized areas of you site. By using this form, you allow visitors to subscribe to your feed and receive an e-mail sent by Google Feedburner, everytime you publish content. Also, you don't have to worry about manage subscriptions: the visitors can subscribe and unscribe themselves through Google Feedburner service.
Rate this plugin! And let me know if you have any issue.
Google FeedBurner is a registered trademark and service owned by Google Inc.

== Installation ==

1. Unzip and upload Feedburner Form plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin.
1. Add the Feedburner form widget.
1. Inform your Feedburner username.
1. Choose to display or not a counter, with options.

== Frequently Asked Questions ==

= Feed URL name? Or Google account username? =
Every feed you add to a Google FeedBurner account will create a new feed URL (just like the URL shortners) so every time you add a feed URL you must set this new name.
For instance, you have feed  such <code>http://site.com/feed/category</code>, after add this feed URL to your Google FeedBurner account, you can set something like <code>MyCategory</code>, then your new feed URL will be: <code>http://feeds2.feedburner.com/MyCategory</code>.
When using the FeedBurner Form widget, you must supply the URL name you chose, this allow us to add forms for evey added feed, how many feed we have and also third feeds.

==That means I can add a form for third site feeds? ==
Yes, as long as you add the feed to n Google FeedBurner and active the subscription service.

= Why my counter is not showing? =
You must activate the counter service for every added feed under your Google Feedburner account. Log in to your Google FeedBruner account, click a feed, then click Publicize Tab. Within Publicize screen you can activate services for your feed such subscription and counter.

= Which feed should I add in Feedburner service? =
In WordPress, there are feeds for every category, tag name, author etc, but by default, you must add the main feed to Feedburner. This feed, will notifiy subscribers about everything you post.
Note that you can add any valid URL feed within your Google FeedBurner account, even third ones, but the added feed must have the services activated.

= How to customize?=
Append the fbstyle.css content to your stlye.css theme. 

= I don't want some categories in my main feed. Is that possible? =
Yes. In this case, you have to use a different feed URL in FeedBurner:
`http://domain.com/feed?cat=-315&-320`
Where you inform categories for exclude. Don't forget to append the `minus`.
This will prevent categories from being added to your main feed, even for browser readers etc.

= How to set a newsletter with Feedburner? =
The following uses posts as newsletters, so you can create special content, just like newsletter are:
1. Create a category called i.e. `Newsletter`.
1. Grab the `Newsletter` category feed (Refer to <a href="http://codex.wordpress.org/WordPress_Feeds">WordPress Feeds</a>).
1. Add the feed to Google Feedburner, choosing a feed URL name.
1. Add the widget and inform the feed URL name.
1. The posts in `Newsletter` category are the newsletters itself.

= How to donate? =
Please check out the <a href="http://arquivo.tk/dev/feedburner-form" target="_blank" >plugin site</a>.

== Changelog ==

=1.3=
* New option: enter an image or icon url
* Cleared out the style, append the fbstyle.css content to your style.css  and customize everything

=1.2.1=
* Solved credits issue (Silly me).
* Removed css from button, input field and icon.
* Now the counter does stay centered.

= 1.2 =
* New explanations and cleaner css.
* Removed css from button, input field and icon.

= 1.0 =
* Feedburner Form first release.

== Screenshots ==

1. Feedburner form widget
1. How will looks
1. Different styles