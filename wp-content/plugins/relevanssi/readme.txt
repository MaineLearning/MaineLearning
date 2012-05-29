=== Relevanssi - A Better Search ===
Contributors: msaari, comprock
Donate link: http://www.relevanssi.com/buy-premium/
Tags: search, relevance, better search
Requires at least: 2.7
Tested up to: 3.3.1
Stable tag: 2.9.14

Relevanssi replaces the default search with a partial-match search that sorts results by relevance. It also indexes comments and shortcode content.

== Description ==

Relevanssi replaces the standard WordPress search with a better search engine, with lots of features
and configurable options. You'll get better results, better presentation of results - your users
will thank you.

This is the free version of Relevanssi. There's also Relevanssi Premium, which has added features.
For more information about Premium, see [Relevanssi.com](http://www.relevanssi.com/).

= Key features =
* Search results sorted in the order of relevance, not by date.
* Fuzzy matching: match partial words, if complete words don't match.
* Find documents matching either just one search term (OR query) or require all words to appear (AND query).
* Search for phrases with quotes, for example "search phrase".
* Create custom excerpts that show where the hit was made, with the search terms highlighted.
* Highlight search terms in the documents when user clicks through search results.
* Search comments, tags, categories and custom fields.

= Advanced features =
* Adjust the weighting for titles, tags and comments.
* Log queries, show most popular queries and recent queries with no hits.
* Restrict searches to categories and tags using a hidden variable or plugin settings.
* Index custom post types and custom taxonomies.
* Index the contents of shortcodes.
* Google-style "Did you mean?" suggestions based on successful user searches.
* Automatic support for [WPML multi-language plugin](http://wpml.org/).
* Automatic support for [s2member membership plugin](http://www.s2member.com/).
* Advanced filtering to help hacking the search results the way you want.

Relevanssi is available in two versions, regular and Premium. Regular Relevanssi is and will remain
free to download and use. Relevanssi Premium comes with a cost, but will get all the new features.
Standard Relevanssi will be updated to fix bugs, but new features will mostly appear in Premium.
Also, support for standard Relevanssi depends very much on my mood and available time. Premium
pricing includes support.

= Premium features (only in Relevanssi Premium) =
* Search result throttling to improve performance on large databases.
* Improved spelling correction in "Did you mean?" suggestions.
* Multisite support.
* Search and index user profiles.
* Search and index taxonomy term pages (categories, tags, custom taxonomies).
* Assign weights to post types.
* Adjust weights manually with a filter hook.
* Highlighting search terms for visitors from external search engines.
* Export and import settings.
* Disable indexing of post content and post titles with a simple filter hook.

= Relevanssi in Facebook =
You can find [Relevanssi in Facebook](http://www.facebook.com/relevanssi).
Become a fan to follow the development of the plugin, I'll post updates on bugs, new features and
new versions to the Facebook page.

= Other search plugins =
Relevanssi owes a lot to [wpSearch](http://wordpress.org/extend/plugins/wpsearch/) by Kenny
Katzgrau. Relevanssi was built to replace wpSearch, when it started to fail.

Search Unleashed is a popular search plugin, but it hasn't been updated since 2010. Relevanssi
is in active development and does what Search Unleashed does.



== Installation ==

1. Extract all files from the ZIP file, and then upload the plugin's folder to /wp-content/plugins/.
1. If your blog is in English, skip to the next step. If your blog is in other language, rename the file *stopwords* in the plugin directory as something else or remove it. If there is *stopwords.yourlanguage*, rename it to *stopwords*.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to the plugin settings and build the index following the instructions there.

To update your installation, simply overwrite the old files with the new, activate the new
version and if the new version has changes in the indexing, rebuild the index.

= Changes to templates =
None necessary! Relevanssi uses the standard search form and doesn't usually need any changes in
the search results template.

= How to index =
Check the options to make sure they're to your liking, then click "Save indexing options and
build the index". If everything's fine, you'll see the Relevanssi options screen again with a 
message "Indexing successful!"

If something fails, usually the result is a blank screen. The most common problem is a timeout:
server ran out of time while indexing. The solution to that is simple: just return to Relevanssi
screen (do not just try to reload the blank page) and click "Continue indexing". Indexing will
continue. Most databases will get indexed in just few clicks of "Continue indexing". You can
follow the process in the "State of the Index": if the amount of documents is growing, the 
indexing is moving along.

If the indexing gets stuck, something's wrong. I've had trouble with some plugins, for example
Flowplayer video player stopped indexing. I had to disable the plugin, index and then activate
the plugin again. Try disabling plugins, especially those that use shortcodes, to see if that
helps. Relevanssi shows the highest post ID in the index - start troubleshooting from the post
or page with the next highest ID. Server error logs may be useful, too.

= Using custom search results =
If you want to use the custom search results, make sure your search results template uses `the_excerpt()`
to display the entries, because the plugin creates the custom snippet by replacing the post excerpt.

If you're using a plugin that affects excerpts (like Advanced Excerpt), you may run into some
problems. For those cases, I've included the function `relevanssi_the_excerpt()`, which you can
use instead of `the_excerpt()`. It prints out the excerpt, but doesn't apply `wp_trim_excerpt()`
filters (it does apply `the_content()`, `the_excerpt()`, and `get_the_excerpt()` filters).

To avoid trouble, use the function like this:

`<?php if (function_exists('relevanssi_the_excerpt')) { relevanssi_the_excerpt(); }; ?>`

See Frequently Asked Questions for more instructions on what you can do with
Relevanssi.

= The advanced hacker option =
If you're doing something unusual with your search and Relevanssi doesn't work, try
using `relevanssi_do_query()`. See [Knowledge Base](http://www.relevanssi.com/knowledge-base/relevanssi_do_query/).

= Uninstalling =
To uninstall the plugin, first click the "Remove plugin data" button on the plugin settins page
to remove options and database tables, then remove the plugin using the normal WordPress
plugin management tools.

= Combining with other plugins =
Relevanssi doesn't work with plugins that rely on standard WP search. Those plugins want to
access the MySQL queries, for example. That won't do with Relevanssi. [Search Light](http://wordpress.org/extend/plugins/search-light/),
for example, won't work with Relevanssi.

[ThreeWP Ajax Search](http://wordpress.org/extend/plugins/threewp-ajax-search/) is
an AJAX instant search plugin that works with Relevanssi.

Some plugins cause problems when indexing documents. These are generally plugins that use shortcodes
to do something somewhat complicated. One such plugin is [MapPress Easy Google Maps](http://wordpress.org/extend/plugins/mappress-google-maps-for-wordpress/).
When indexing, you'll get a white screen. To fix the problem, disable either the offending plugin 
or shortcode expansion in Relevanssi while indexing. After indexing, you can activate the plugin
again.

== Frequently Asked Questions ==

= Knowledge Base =
You can find solutions and answers at the [Relevanssi Knowledge Base](http://www.relevanssi.com/category/knowledge-base/).

= Relevanssi doesn't work =
If you the results don't change after installing and activating Relevanssi, the most likely 
reason is that you have a call to `query_posts()` on your search results template. This confuses
Relevanssi. Try removing the query_posts call and see what happens.

= Where are the user search logs? =
See the top of the admin menu. There's 'User searches'. There. If the logs are empty, please note
showing the results needs at least MySQL 5.

= Displaying the number of search results found =

The typical solution to showing the number of search results found does not work with Relevanssi.
However, there's a solution that's much easier: the number of search results is stored in a
variable within $wp_query. Just add the following code to your search results template:

`<?php echo 'Relevanssi found ' . $wp_query->found_posts . ' hits'; ?>`

= Advanced search result filtering =

If you want to add extra filters to the search results, you can add them using a hook.
Relevanssi searches for results in the _relevanssi table, where terms and post_ids are listed.
The various filtering methods work by listing either allowed or forbidden post ids in the
query WHERE clause. Using the `relevanssi_where` hook you can add your own restrictions to
the WHERE clause.

These restrictions must be in the general format of 
` AND doc IN (' . {a list of post ids, which could be a subquery} . ')`

For more details, see where the filter is applied in the `relevanssi_search()` function. This
is stricly an advanced hacker option for those people who're used to using filters and MySQL
WHERE clauses and it is possible to break the search results completely by doing something wrong
here.

There's another filter hook, `relevanssi_hits_filter`, which lets you modify the hits directly.
The filter passes an array, where index 0 gives the list of hits in the form of an array of 
post objects and index 1 has the search query as a string. The filter expects you to return an
array containing the array of post objects in index 0 (`return array($your_processed_hit_array)`).

= Direct access to query engine =
Relevanssi can't be used in any situation, because it checks the presence of search with
the `is_search()` function. This causes some unfortunate limitations and reduces the general usability
of the plugin.

You can now access the query engine directly. There's a new function `relevanssi_do_query()`,
which can be used to do search queries just about anywhere. The function takes a WP_Query object
as a parameter, so you need to store all the search parameters in the object (for example, put the
search terms in `$your_query_object->query_vars['s']`). Then just pass the WP_Query object to
Relevanssi with `relevanssi_do_query($your_wp_query_object);`.

Relevanssi will process the query and insert the found posts as `$your_query_object->posts`. The
query object is passed as reference and modified directly, so there's no return value. The posts
array will contain all results that are found.

= Sorting search results =
If you want something else than relevancy ranking, you can use orderby and order parameters. Orderby
accepts $post variable attributes and order can be "asc" or "desc". The most relevant attributes
here are most likely "post_date" and "comment_count".

If you want to give your users the ability to sort search results by date, you can just add a link
to http://www.yourblogdomain.com/?s=search-term&orderby=post_date&order=desc to your search result
page.

Order by relevance is either orderby=relevance or no orderby parameter at all.

= Filtering results by date =
You can specify date limits on searches with `by_date` search parameter. You can use it your
search result page like this: http://www.yourblogdomain.com/?s=search-term&by_date=1d to offer
your visitor the ability to restrict their search to certain time limit (see
[RAPLIQ](http://www.rapliq.org/) for a working example).

The date range is always back from the current date and time. Possible units are hour (h), day (d),
week (w), month (m) and year (y). So, to see only posts from past week, you could use by_date=7d
or by_date=1w.

Using wrong letters for units or impossible date ranges will lead to either defaulting to date
or no results at all, depending on case.

Thanks to Charles St-Pierre for the idea.

= Caching =
Relevanssi has an included cache feature that'll store search results and
post excerpts in the database for reuse. It's something of an experimental 
feature right now, but should work and if there are lots of repeat queries,
it'll give some actual boost in performance.

= Displaying the relevance score =
Relevanssi stores the relevance score it uses to sort results in the $post variable. Just add
something like

`echo $post->relevance_score`

to your search results template inside a PHP code block to display the relevance score.

= Did you mean? suggestions =
To use Google-style "did you mean?" suggestions, first enable search query logging. The
suggestions are based on logged queries, so without good base of logged queries, the
suggestions will be odd and not very useful.

To use the suggestions, add the following line to your search result template, preferably
before the have_posts() check:

`<?php if (function_exists('relevanssi_didyoumean')) { relevanssi_didyoumean(get_search_query(), "<p>Did you mean: ", "?</p>", 5); }?>`

The first parameter passes the search term, the second is the text before the result,
the third is the text after the result and the number is the amount of search results
necessary to not show suggestions. With the default value of 5, suggestions are not
shown if the search returns more than 5 hits.

= Search shortcode =
Relevanssi also adds a shortcode to help making links to search results. That way users
can easily find more information about a given subject from your blog. The syntax is
simple:

`[search]John Doe[/search]`

This will make the text John Doe a link to search results for John Doe. In case you
want to link to some other search term than the anchor text (necessary in languages
like Finnish), you can use:

`[search term="John Doe"]Mr. John Doe[/search]`

Now the search will be for John Doe, but the anchor says Mr. John Doe.

One more parameter: setting `[search phrase="on"]` will wrap the search term in
quotation marks, making it a phrase. This can be useful in some cases.

= Restricting searches to categories and tags =
Relevanssi supports the hidden input field `cat` to restrict searches to certain categories (or
tags, since those are pretty much the same). Just add a hidden input field named `cat` in your
search form and list the desired category or tag IDs in the `value` field - positive numbers
include those categories and tags, negative numbers exclude them.

This input field can only take one category or tag id (a restriction caused by WordPress, not
Relevanssi). If you need more, use `cats` and use a comma-separated list of category IDs.

The same works with post types. The input fields are called `post_type` and `post_types`.

You can also set the restriction from general plugin settings (and then override it in individual
search forms with the special field). This works with custom taxonomies as well, just replace `cat`
with the name of your taxonomy.

If you want to restrict the search to categories using a dropdown box on the search form, use
a code like this:

`<form method="get" action="<?php bloginfo('url'); ?>">
	<div><label class="screen-reader-text" for="s">Search</label>
	<input type="text" value="<?php the_search_query(); ?>" name="s" id="s" />
<?php
	wp_dropdown_categories(array('show_option_all' => 'All categories'));
?>
	<input type="submit" id="searchsubmit" value="Search" />
	</div>
</form>`

This produces a search form with a dropdown box for categories. Do note that this code won't work
when placed in a Text widget: either place it directly in the template or use a PHP widget plugin
to get a widget that can execute PHP code.

= Restricting searches with taxonomies =

You can use taxonomies to restrict search results to posts and pages tagged with a certain 
taxonomy term. If you have a custom taxonomy of "People" and want to search entries tagged
"John" in this taxonomy, just use `?s=keyword&people=John` in the URL. You should be able to use
an input field in the search form to do this, as well - just name the input field with the name
of the taxonomy you want to use.

It's also possible to do a dropdown for custom taxonomies, using the same function. Just adjust
the arguments like this:

`wp_dropdown_categories(array('show_option_all' => 'All people', 'name' => 'people', 'taxonomy' => 'people'));`

This would do a dropdown box for the "People" taxonomy. The 'name' must be the keyword used in
the URL, while 'taxonomy' has the name of the taxonomy.

= Automatic indexing =
Relevanssi indexes changes in documents as soon as they happen. However, changes in shortcoded
content won't be registered automatically. If you use lots of shortcodes and dynamic content, you
may want to add extra indexing. Here's how to do it:

`if (!wp_next_scheduled('relevanssi_build_index')) {
	wp_schedule_event( time(), 'daily', 'relevanssi_build_index' );
}`

Add the code above in your theme functions.php file so it gets executed. This will cause
WordPress to build the index once a day. This is an untested and unsupported feature that may
cause trouble and corrupt index if your database is large, so use at your own risk. This was
presented at [forum](http://wordpress.org/support/topic/plugin-relevanssi-a-better-search-relevanssi-chron-indexing?replies=2).

= Highlighting terms =
Relevanssi search term highlighting can be used outside search results. You can access the search
term highlighting function directly. This can be used for example to highlight search terms in
structured search result data that comes from custom fields and isn't normally highlighted by
Relevanssi.

Just pass the content you want highlighted through `relevanssi_highlight_terms()` function. The
content to highlight is the first parameter, the search query the second. The content with
highlights is then returned by the function. Use it like this:

`if (function_exists('relevanssi_highlight_terms')) {
    echo relevanssi_highlight_terms($content, get_search_query());
}
else { echo $content; }`

= What is tf * idf weighing? =

It's the basic weighing scheme used in information retrieval. Tf stands for *term frequency*
while idf is *inverted document frequency*. Term frequency is simply the number of times the term
appears in a document, while document frequency is the number of documents in the database where
the term appears.

Thus, the weight of the word for a document increases the more often it appears in the document and
the less often it appears in other documents.

= What are stop words? =

Each document database is full of useless words. All the little words that appear in just about
every document are completely useless for information retrieval purposes. Basically, their
inverted document frequency is really low, so they never have much power in matching. Also,
removing those words helps to make the index smaller and searching faster.

== Known issues and To-do's ==
* Known issue: The most common cause of blank screens when indexing is the lack of the mbstring extension. Make sure it's installed.
* Known issue: In general, multiple Loops on the search page may cause surprising results. Please make sure the actual search results are the first loop.
* Known issue: Relevanssi doesn't necessarily play nice with plugins that modify the excerpt. If you're having problems, try using relevanssi_the_excerpt() instead of the_excerpt().
* Known issue: I know the plugin works with WP 2.5, but it loses some non-essential functionality. The shortcode stuff doesn't work with WP 2.5, which doesn't support shortcodes. Compatibility with older versions of WP hasn't been tested.
* Known issue: Custom post types and private posts is problematic - I'm using default 'read_private_*s' capability, which might not always work.
* Known issue: There are reported problems with custom posts combined with custom taxonomies, the taxonomy restriction doesn't necessarily work.
* Known issue: Phrase matching is only done to post content; phrases don't match to category titles and other content.
* Known issue: User searches page requires MySQL 5.
* For more features to come, see [Feature list](http://www.relevanssi.com/features/).

== Thanks ==
* Cristian Damm for tag indexing, comment indexing, post/page exclusion and general helpfulness.
* Marcus Dalgren for UTF-8 fixing.
* Warren Tape for 2.5.5 fixes.
* Mohib Ebrahim for relentless bug hunting.

== Changelog ==

= 2.9.14 =
* Relevanssi will now index pending and future posts. These posts are only shown in the admin search.

= 2.9.13 =
* Stripping shortcodes from excerpts didn't work properly. Should work now.
* Fixed a mistake in the FAQ: correct post date parameter is `post_date`, not `date`.
* New filter `relevanssi_results` added. This filter will process an array with (post->ID => document weight) pairs.
* Private and draft posts were deleted from the index when they were edited. This bug has been fixed. (Thanks to comprock.)
* When continuing indexing, Relevanssi now tells if there's more to index. (Thanks to mrose17.)
* Fixed problems with searching attachments. Indexing attachments still has some problems. When you build the index, attachments are indexed properly.
* Improved WPML support.
* The `relevanssi_index_doc()` function has a new parameter that allows you to bypass global $post and force the function to index the document given as a parameter (see 2.9.13 release notes at Relevanssi.com for more details).

= 2.9.12 =
* Scheduled cache truncate wasn't scheduled properly. It is now.
* Added support for 'author' query variable.
* Fixed a bug with indexing custom post types.

= 2.9.11 =
* Plugin now works properly without multibyte string functions.
* Fixed s2member support for s2member versions 110912 and above. (Thanks to Jason Caldwell.)
* Added support for 'tag' query variable.

= 2.9.10 =
* AND search failed, when search query included terms that are shorter than the minimum word length.
* Improved s2member support.
* Fixed errors about deprecated ereg_replace.
* Small fix to Did you mean suggestions.

= 2.9.9 =
* Removed warnings about undefined functions and missing $wpdb.
* Fixed a bug that removed 'à' from search terms.
* Phrases are recognized from custom field searches.

= 2.9.8 =
* Support for s2member membership plugin. Search won't show posts that the current user isn't allowed to see.
* New filter `relevanssi_post_ok` can be used to add support for other membership plugins.
* Post meta fields that contain arrays are now indexed properly, expanding all the arrays.

= 2.9.7 =
* Fixed a bug that causes problems when paging search results.
* Taxonomy term restrictions didn't work most of the time.
* the_content filters didn't run on excerpts.
* Style data and other extra elements created by short codes are now stripped.

= 2.9.6 =
* Fixed a problem causing "Attempt to modify property of non-object" errors.
* Fixed a warning message.

= 2.9.5 =
* Searching for private posts caused an error message.

= 2.9.4 =
* Relevanssi should now be much lighter on server.
* Post date selection didn't work properly. Fixed that.
* Stopwords can be exported.
* Restricting indexing on custom post types works better.
* Minimum word length is properly enforced in indexing.
* Punctuation removal is more efficient.
* Fixed a MySQL error that was triggered by a media upload.
* Fixed a bug that caused an error when quick editing a post.

= 2.9.3 =
* A call to a non-existing function in 2.9.2 made all sorts of mess. This release fixes all problems with broken loops. I'm sorry about the bug.

= 2.9.2 =
* It's now possible to adjust the number of search results per page. See [Changing posts_per_page](http://www.relevanssi.com/knowledge-base/posts-per-page/) for instructions.
* Somebody reported revisions appearing in the search results. Added an extra check to prevent that.
* Improved the indexing procedure to prevent MySQL errors from appearing and to streamline the process.
* Improved the way custom post types can be handled in indexing.
* Improved the method of removing nested highlights.

= 2.9.1 =
* It is now possible to change the default result order from relevance to post date.
* Fixed a bug that caused wrong $post object to be set in indexing.
* Added a new hook `relevanssi_excerpt_content`; see [Knowledge Base](http://www.relevanssi.com/category/knowledge-base/) for details.

= 2.9 =
* Fixed a bug that caused Cyrillic searches in the log to get corrupted.
* Punctuation removal function is now triggered with a filter call and can thus be replaced.
* Google Adsense caused double hits to the user search logs. That's now fixed thanks to Justin Klein.
* User search log is available to user with `edit_post` capabilities (editor role). Thanks to John Blackbourn.
* A proper database collation is now set. Thanks to John Blackbourn.
* UI looks better. Thanks to John Blackbourn.
* Lots of small fixes here and there.

= 2.8.2 =
* The `order` parameter was case sensitive. It isn't anymore.
* WordPress didn't support searching for multiple categories with the `cat` query variable. There's now new `cats` which can take multiple categories.
* Similar to `cats` vs `cat`, you can use `post_types` to restrict the search to multiple post types.

= 2.8.1 =
* Fixed two small mistakes that caused error notices.
* Custom post types, particularly those created by More Types plugin, were causing problems.

= 2.8 =
* There's now a way to truncate the cache (sorry it took so long). Expired cache data is now automatically removed from the database every day. There's also an option to clear the caches.
* Highlights didn't work properly with non-ASCII alphabets. Now there's an option to make them work.
* Title highlight option now affects external search term highlights as well.
* There were some bugs on the options page.

= 2.7.5 =
* There was a bug that caused shortcodes to fail in 2.7.4. That's fixed now.
* Category search will now include subcategories as well, both when including and excluding.

= 2.7.4 =
* Improved the fallback to fuzzy search if no hits are found with regular search.
* AND searches sometimes failed to work properly, causing unnecessary fallback to OR search. Fixed.
* When using WPML, it's now possible to choose if the searches are limited to current language.
* Adding stopwords from the list of 25 common words didn't work. It works now.
* The instructions to add a category dropdown to search form weren't quite correct. They are now.
* Small fix that makes shortcodes in posts more compatible with Relevanssi.

= 2.7.3 =
* IMPORTANT SECURITY UPDATE: Earlier versions of Relevanssi have a cross-site scripting (XSS) vulnerability. Please install this update as soon as possible.
* Added instructions of doing a category dropdown in the search form in the FAQ.

= 2.7.2 =
* A silly typo caused the caching not to work. That's fixed now.
* A new filter: `relevanssi_didyoumean_query` lets you modify the query used for 'Did you mean?' searches.

= 2.7.1 =
* Thanks to a bug in the code, the WPML support didn't work. It's fixed now.

= 2.7 =
* Caching search results is possible. If you have lots of repeated queries, caching will provide extra speed and less wear on server.
* Multilanguage plugin WPML is now supported. If WPML is active, Relevanssi will automatically restrict search results to current language.
* New filter: `relevanssi_search_filter` lets you adjust search query variables. See source code for further details. Thanks to Sam Hotchkiss.
* Got a report of synonyms not working; hopefully fixed it now.
* It is now possible to set the minimum word length to index. Default is now 3 instead of 2.
* You can now add several stopwords at one go and remove all stopwords.
* Author search didn't work properly. It works now.
* Search result highlighting functions properly now, there might've been some problems with it.

= 2.6 =
* New setting allows user to define how `exclude_from_search` is handled. It's now possible to exclude a custom post type from general searches and search for it specifically by defining post_type.
* New filter: `relevanssi_hits_filter` lets you process hits found by Relevanssi. See FAQ.

= 2.5.6 =
* Attachments are no longer automatically indexed; there's an option for it now.
* You can now exclude custom post types from index.
* When AND search fails, it falls back to OR search. It's now possible to disable this fallback.

= 2.5.5 =
* The stopword management created empty stopwords. It won't anymore.
* Faulty HTML code in the admin page has been fixed.
* Indexing shortcodes that need the global $post context is now possible.
* Relevanssi is now aware of attachments and manages post_status of "inherit".
* These fixes were provided by Warren Tape, thanks!

= 2.5.4 =
* Small bugfix relating to post types.
* Added stopword management tools: way to remove and add stopwords.
* Custom excerpts can now be created from post excerpts as well, if those are indexed.
* Added answers to some frequently asked questions to the documentation.

= 2.5.3 =
* Very small bugfix fixing the error on line 1192.

= 2.5.2 =
* Fixed a bug about `mysql_real_escape_string()` expecting a string.
* Added documentation about compatibility issues.

= 2.5.1 =
* Option to highlight search terms in comment text as well.
* Fixed a small problem in highlighting search terms.

= 2.5 =
* Better support for other search plugins like [Dave's WordPress Live Search](http://wordpress.org/extend/plugins/daves-wordpress-live-search/).
* New User searches screen that shows more data about user searches.
* Search logs can now be emptied.
* Custom fields weren't indexed on updated posts. That is now fixed.
* Once again improved the highlighting: now the highlighting will look for word boundaries and won't highlight terms inside words.
* Relevanssi query engine can now be accessed directly, making all sorts of advanced hacking easier. See FAQ.

= 2.4.1 =
* Fixed a problem where search term highlighting was changing terms to lowercase.
* Fixed a problem with highlighting breaking stuff in shortcodes.
* Made some changes to the admin interface - there's more to come here, as the admin page is a bit of a mess right now.

= 2.4 =
* Highlighting post content won't highlight inside HTML tags anymore.
* Soft hyphens inside words are now removed in indexing. They still confuse the highlighting.
* Matching engine is now able to match category titles that contain apostrophes.

= 2.3.3.1 =
* Suppressed the error messages on the correct mb_strpos() function call. If you still get mb_strpos() errors, update.
* Added a FAQ note on getting the number of search results found.

= 2.3.3 =
* Suppressed notices on one mb_strpos() call.
* Added a search variable "by_date" to filter search results, see FAQ for details.

= 2.3.2 =
* Fixed a serious bug related to taxonomy term searches that could cause strange search results. Thanks to Charles St-Pierre for finding and killing the bug.
* Spanish stopwords are now included (thanks to Miguel Mariano).

= 2.3.1 =
* I fixed the highlighting logic a bit, the highlighting didn't work properly before.

= 2.3 =
* New highlighting option: HTML5 mark tag. Thanks to Jeff Byrnes.
* Relevanssi can now highlight search term hits in the posts user views from search. Highlighting for search term hits from external searches will be added later.
* It is now possible to add custom filtering to search results, see FAQ for details. Thanks to Charles St-Pierre.
* Removed search result highlighting from admin search, where it wasn't very useful.

= 2.2 =
* Relevanssi used to index navigation menu items. It won't, anymore.
* Translation and stopwords in Brazilian Portuguese added, thanks to Pedro Padron.

= 2.1.9 =
* No changes, I'm just trying to resurrect the broken Relevanssi plugin page.

= 2.1.8 =
* Including the popular microtime_float function caused conflicts with several other plugins (whose authors are just as sloppy as I am!). Fixed that.

= 2.1.7 =
* The index categories option wasn't saved properly. Now it is.
* Fixed the %terms% breakdown option to show correct counts and added %total% to show total hit count.
* Phrases are now matched also in post titles and category titles (before they were only matched against post content).
* Post excerpts can now be indexed and searched. I would appreciate feedback from people who use this feature: do you use the excerpts in search results? If you use custom snippets created by Relevanssi, what you want them to display?
* Set the constant TIMER to true to enable timing of the search process for debugging reasons.

= 2.1.6 =
* Title highlighting caused an error. That is now fixed. I also streamlined the highlighting code a bit.

= 2.1.5 =
* You can now enter synonyms, expanding queries with synonyms when doing an OR search. This is useful to expand acronyms and abbreviations, for example.
* When doing a phrase search, highlighting will only highlight phrase hits.
* New breakdown variable %terms% will list hits by term.
* Some users reported error messages about unexpected T_OBJECT_OPERATOR. Those shouldn't happen, please let me know if they still do.
* Highlighting will now highlight only complete words.

= 2.1.4 =
* Fixed a small bug that could cause all queries by anonymous users to go unlogged.

= 2.1.3 =
* OR operator makes a comeback! The default operator is now an option, and if you choose AND and search gets no results, an OR search is also run.
* You can now give a list of user ids - any searches by those users will not be logged. List your admin user id, so your test searches won't clutter the log.

= 2.1.2 =
* Removing punctuation didn't work properly, making phrase search impossible. I'd thought I'd fix it, but for some reason I made a mistake and the fix didn't appear in the released versions.
* Search has now an implicit AND operator, which means that every search term must appear in all result documents. Please let me know if you'd prefer an implicit OR operator, like Relevanssi had before.
* Relevanssi options page now shows the amount of indexed documents, making troubleshooting indexing easier.

= 2.1.1 =
* "Did you mean" suggestions now work in blogs that are not in root directory.
* Early 2.1 downloads had faulty encodings. Update to make sure you've got a good file.

= 2.1 =
* An experimental "Did you mean" suggestion feature. Feedback is most welcome.
* Added a short code to facilitate adding links to search results.
* Fixed a small bug that in some cases caused MySQL errors.

= 2.0.3 =
* Fixed problems relating to the orderby parameter.

= 2.0.2 =
* Small bug fix: with private posts, sometimes correct amount of posts weren't displayed.

= 2.0.1 =
* Exclude posts/pages option wasn't saved on the options page. It works now.
* 2.0 included an unnecessary function that broke Relevanssi in WP 2.8.5. Fixed that.

= 2.0 =
* Post authors can now be indexed and searched. Author are indexed by their display name.
* In search results, $post->relevance_score variable will now contain the score of the search result.
* Comment authors are now included in the index, if comments are indexed.
* Search results can be sorted by any $post field and in any order, in addition of sorting them by relevancy.
* Private posts are indexed and displayed to the users capable of seeing them. This uses Role-Scoper plugin, if it's available, otherwise it goes by WordPress capabilities.
* Searches can be restricted with a taxonomy term (see FAQ for details).

= 1.9 =
* Excerpts are now better and will contain more search terms and not just the first hit.
* Fixed an error relating to shortcodes in excerpts.
* If comments are indexed, custom excerpts will show text from comments as well as post content.
* Custom post type posts are now indexed as they are edited. That didn't work before.
* Cleaned out more error notices.

= 1.8.1 =
* Sometimes empty ghost entries would appear in search results. No more.
* Added support for the WordPress' post_type argument to restrict search results to single post type.
* Relevanssi will now check for the presence of multibyte string functions and warn if they're missing.
* The category indexing option checkbox didn't work. It's now fixed.
* Small fix in the way punctuation is removed.
* Added a new indexing option to index all public post types.

= 1.8 =
* Fixed lots of error notices that popped up when E_NOTICE was on. Sorry about those.
* Custom post types can now be indexed if wanted. Default behaviour is to index all post types (posts, pages and custom types).
* Custom taxonomies can also be indexed in addition to standard post tags. Default behaviour is to index nothing. If somebody knows a way to list all custom taxonomies, that information would be appreciated.

= 1.7.3 =
* Small bug fix: code that created database indexes was broken. Say "ALTER TABLE `wp_relevanssi` ADD INDEX (doc)" and "ALTER TABLE `wp_relevanssi` ADD INDEX (term)" to your MySQL db to fix this for an existing installation.

= 1.7.2 =
* Small bug fix: public posts that are changed to private are now removed from index (password protected posts remain in index).
* An Italian translation is now included (thanks to Alessandro Fiorotto).

= 1.7.1 =
* Small fix: the hidden variable cat now accepts negative category and tag ids. Negative categories and tags are excluded in search. Mixing inclusion and exclusion is possible.

= 1.7 =
* Major bug fix: Relevanssi doesn't kill other post loops on the search result page anymore. Please let me know if Relevanssi feels too slow after the update.
* Post categories can now be indexed.

= 1.6 =
* Relevanssi is now able to expand shortcodes before indexing to include shortcode content to the index.
* Fixed a bug related to indexing, where tag stripping didn't work quite as expected.

= 1.5.3 =
* Added a way to uninstall the plugin.
* A French translation is now included (thanks to Jean-Michel Meyer).

= 1.5.2 =
* Fixed a small typo in the code, tag and comment hit count didn't work in the breakdown. If you don't use the breakdown feature, updating is not necessary.

= 1.5.1 =
* User interface update, small changes to make the plugin easier to use.
* Fixed a small bug that sometimes causes "Empty haystack" warnings.

= 1.5 =
* Comments can now be indexed and searched (thanks to Cristian Damm).
* Tags can also be indexed (thanks to Cristian Damm).
* Search term hits in the titles can be highlighted in search results (thanks to Cristian Damm).
* When using custom excerpts, it's possible to add extra information on where the hits were made.
* Fuzzy matching is now user-adjustable.
* UTF-8 support is now better (thanks to Marcus Dalgren).

= 1.4.4 =
* Added an option to exclude posts or pages from search results. This feature was requested and provided by Cristian Damm.

= 1.4.3 =
* Indexing of custom fields is now possible. Just add a list of custom field names you want to include in the index on the settings page and re-index.

= 1.4.2 =
* Users can search for specific phrases by wrapping the phase with "quotes".
* Fixed a bug that caused broken HTML in some cases of highlighted search results (search term matches in highlighting HTML tags were being highlighted).
* Improved punctuation removal. This change requires reindexing the whole database.

= 1.4.1 =
* Fixed a bug that caused empty search snippets when using word-based snippets.
* Improved support for WP 2.5.
* Added an option to exclude categories and tags from search results.
* Added an option to index only posts or pages.
* Added French stopwords.

= 1.4 =
* Added an option to restrict searches to certain categories or tags, either by plugin option or hidden input field in the search form.
* The contents of `<script>` and other such tags are now removed from excerpts.
* When indexing, HTML tags and `[shortcodes]` are removed.
* Digits are no longer removed from terms. Re-index database to get them indexed.
* Wrapped the output of `relevanssi_the_excerpt()` in <p> tags.
* Stopwords are no longer removed from search queries.
* Search result snippet length can now be determined in characters or whole words.

= 1.3.3 =
* Small bug fixes, removed the error message caused by a query that is all stop words.
* Content and excerpt filters are now applied to excerpts created by Relevanssi.
* Default highlight CSS class has a unique name, `search-results` was already used by WordPress.

= 1.3.2 =
* Quicktags are now stripped from custom-created excerpts.
* Added a function `relevanssi_the_excerpt()', which prints out the excerpt without triggering `wp_trim_excerpt()` filters.

= 1.3.1 =
* Another bug fix release.

= 1.3 =
* New query logging feature. Any feedback on query log display features would be welcome: what information you want to see?
* Added a CSS class option for search term highlighting.
* Fixed a bug in the search result excerpt generation code that caused endless loops with certain search terms.

= 1.2 =
* Added new features to display custom search result snippets and highlight the search terms in the results.

= 1.1.3 =
* Fixed a small bug, made internationalization possible (translations are welcome!).

= 1.1.2 =
* English stopword file had a problem, which is now fixed.

= 1.1.1 =
* Fixed a stupid bug introduced in the previous update. Remember always to test your code before sending files to repository!

= 1.1 =
* Fixes the problem with pages in search results.

= 1.0 =
* First published version.