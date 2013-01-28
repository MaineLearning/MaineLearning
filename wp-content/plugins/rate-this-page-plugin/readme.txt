=== Rate This Page Plugin ===
Plugin Name: Rate This Page Plugin
Contributors: Agents Of Value
Donate link: http://www.agentsofvalue.com/2011/09/wikipedia-style-rate-this-page-plugin/
Tags: plugin wordpress,wordpress blog,page rating,page plugin,page rate,feedback tool,article feedback tool,wordpress rating,custom wordpress plugin,wordpress reviews plugin,wikipedia,wikipedia style rating
Requires at least: 3.0
Tested up to: 3.3.1
Stable Tag: 2.1

== Description ==
Rate This Page is a Wikipedia Article Feedback Tool like plugin where you can rate certain posts or pages of your blog.

This plugin contains the following features: Ability to select where the plugin UI will be positioned on the content; Ability to choose insertion if either on Page, Category or Both; Admin can view reports of rated posts or pages; The label can also be customized based on the like of the admin to be its label.

Using cookies, the plugin stores a session if a guest user rates certain posts or pages and it uses AJAX to store the rating information.

PS: Please post any bugs, issues and suggestions about the plugin. This will help us to better enhance our released plugin.

Supported language: English

== About Plugin Support ==
Plug-in is provided for free. However, the support is never free. Currently available step by step tutorials on the plugin website: http://www.agentsofvalue.com/2011/09/wikipedia-style-rate-this-page-plugin/

== Installation ==
Minimal Requirements

* PHP 5.x.x
* mySQL 4.0, 4.1 or 5.x
* WordPress 3.0 or newer

Recommended Requirements

* PHP 5.2.x or newer
* mySQL 5.x
* WordPress 3.0 or newer

Please see the step by step with screenshot on this post:  http://www.agentsofvalue.com/?p=6351

* Upload 'rate-this-page-plugin' folder to the '/wp-content/plugins/' directory.
* Activate the plugin through the 'Plugins' menu in WordPress.
* Go to the admin section of the plugin at the left sidebar on wp-admin see "Rate This Page".
* then, select "All Post" if you want the plugin to appear in all of your blog articles please see the Configuration Tab and then, the Rate This  Page tool rating will be present at all article posts.
* Select "By Category" if you want the plugin to appear in selected categories of your choice.
* Enjoy! (^_^)

== Frequently Asked Questions ==

= How do I change the UI design of the plugin? =
On plugin settings page go to "Configuration" Tab and look for "Theme Selection" dropdown menu and you can select your desired UI color.

= The plugin did not display on my blog post or page. =
You need to specify the insertion type of the plugin if either "By Category" and "By Page" but you need to specify the category and page you want the plugin to display with on its specific Insertion Tab. You can also set the insertion into "By Page and Category" or set it to "All Article" to display the plugin on all article post except on pages.

= What is the use of "Database Configuration" Tab? =
On that tab you can uninstall and re-install the plugin's own table that stores the data of rated post if you don't want to go on phpMyAdmin to delete manually all the data.

= How did the Rate This Page plug-in calculate the Average Rating in the rating report tab? =
The plug-in Average Rating formula:
If there are (5) people voted for trustworthy and rated 5.0, then the average rating is 5.0 because, the article's trustworthy is weighted by the number of people's ratings.
Now, if there are (5) people voted for trustworthy that rated 5.0, and another one person voted 1.0 rating, then the average rating is 4.3.

In short:
Total number of rating's Trustworthy, Objective, Complete and Well-written (of certain article post) is divided by the number of people who rated the article = Average Rating.

Highest and Lowest Rated posts/pages will be intentified if the average is 3.5 above and 3.5 below.

= What is the syntax for shortcode? =
Use [rate_this_page] syntax if you want to use shortcode for the plugin.

= How to activate Top Rated Widget? =
Go to Appearance->Widgets and drag the widget named "RTP Top Rated" into sidebar panel. That's it it and you're done.

== Screenshots ==
1. Rate This Page Plug-in User Interface
2. Configuration Tab for plugn UI activation and Theme UI selection.
3. Settings area for custom labels activation.
4. Insertion Tab for By Page display settings.
5. Insertion Tab for By Category display settings
6. Log Reports Tab to view log data of rated posts/pages.
7. Report Tab to view ratings statistics of rated posts/pages.
8. Rate This Page Top Rated Widget User Interface.

== Upgrade Notice ==
= 2.1 =
* Added Top Rated Posts/Pages Widget. * Updated aft_plgn_save_ratings() using initialized variables on using some data. * Updated aft_plgn_calculate_summary() on some variables that used array variables instead of initialized variables.

== Changelog ==
= 2.1 =
* Added Top Rated Posts/Pages Widget.
* Updated aft_plgn_save_ratings() using initialized variables on using some data.
* Updated aft_plgn_calculate_summary() on some variables that used array variables instead of initialized variables.

= 2.0 =
* Added viewing results of current average rating on posts/pages
* Added option variables rtp_can_rate and rtp_logged_by
* Added rtp_can_rate function used to check if guests or registered users are allowed to rate.
* Added rtp_logged_cookies function used in cookie logging method
* Added rtp_logged_ip function used in ip logging method
* Added rtp_is_rated function used to check rated posts/pages if logging method is enabled.
* Added settings to specify plugin visibility to either on registered users or guests only.
* Added viewing log data on reports page allowing also admin to delete log records.
* Added rtp_count_rated_item function to be use to count # of user who rate
* Added new define variable RTP_COOKIE_EXPIRATION2 which set into 7 days cookie expiration.
* Added new define variable RTP_COOKIE_LOGNAME used as cookie name for logging methods.
* Added $is_delete on aft_plgn_calculate_summary function to be used as flag on recalculating summary data if log data is deleted.
* Added shortcode for the plugin.
* Updated jQuery Raty plugin into 2.1.0
* Updated jQuery plugin TableSorter into 2.0.25
* Updated aft_plgn_fetch_article_rate() function removed redundant sql select query string on querying list of rated posts/pages.
* Updated calculation on getting the average rating of posts/pages that has been rated.
* Updated aft_plgn_save_ratings function included ip and host values to be saved on rating data.
* Fixed table sorting not properly sorted from highest to lowest in average ratings report.
* Fixed table layout on admin settings for Insertion and Reports Tab.
* Fixed pagination on admin settings for Insertion and Reports Tab.
* Fixed bug on pagination on which it prevents getting value on checkboxes that was checked if not visible.
* Renamed do_ajax_save to rtp_process_save including rewritten codes to save posts/pages rating data.
* Removed $session_key parameter on aft_plgn_calculate_summary function which was not used ever since.
* Rewritten rtp-admin.php code split up to rtp-admin-main.php and rtp-admin-reports.php both files contains the Configuration and Reports page.
* Rewritten rtp-main.php.
* Using add_action instead of add_filter to instantiate plugin display to content
* Initialization of scripts for user and admin are now separated.
* Cookie rtp-cookie-session variable will now include registered users on its value.
* Admin settings now using cookies to store the last selected tab.

= 1.3 =
* Fixed jQuery initialization on plugin admin page. It will now load only on its specific page.

= 1.2 =
* Added Label Customization.
* Added Custom hints on settings page if custom label is selected to yes.
* Added the ability to select theme display for the plugin.
* Updated admin settings page into new layout using Cupertino jQuery UI theme.
* Updated display strings into a translation ready.
* Used JSON parsing to retrieve array data from php file to jQuery script.
* Minor clean up on initializing jquery scripts and css codes.
* Removed extra divs on plugin design which are never been used.
* Removed some unused or commented syntaxes on CSS files.
* Fixed an issue where some CSS codes was included on RSS feeds.
* Fixed some CSS codes which can be overridden by other css layout like on some template being used.
* Renamed feedback.css to rtp-style.css.
* Renamed feedback-admin.css to rtp-style-admin.css.
* Renamed feedback.js to rtp.dev.js.
* Renamed feedback-ajax.js to rtp-ajax.dev.js.
* Used minified js files on loading required jscripts of the plugin.
* Will work on latest Wordpress version 3.3.

= 1.1 =
* Fixed image of rating stars not properly loaded if site using permalinks.
* Added the ability to display the rating in Wordpress Selected Pages and selected By Category blog posts.

= 1.0 =
* First Release.