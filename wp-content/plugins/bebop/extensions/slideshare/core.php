<?php 
/*
 * Use this page to store and additional functions or filters which you may require for your plugin to work as expected.
 * For example, the code below adds oembed support for slideshare to the main bp activity feed and the custom Open Educational Resources tab.
 */

// Add Slideshare oEmbed
function add_oembed_slideshare() {
	wp_oembed_add_provider( 'http://www.slideshare.net/*', 'http://www.slideshare.net/api/oembed/2' );
}
//hook into the activity loop to add our function above.
add_action( 'bp_before_activity_loop','add_oembed_slideshare' );
?>