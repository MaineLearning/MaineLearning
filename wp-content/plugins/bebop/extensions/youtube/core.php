<?php
/*
 * Use this page to store and additional functions or filters which you may require for your plugin to work as expected.
 * For example, the code below is used to swap the watch parameter of a youtube video url so itcan be embedded into the activity stream.
 * This code is actually not needed to make oembed work, but it is a good example of how this page can be used.
 */

add_filter( 'bp_get_activity_content', 'bebop_youtube_embed', 8 );
add_filter( 'bp_get_activity_content_body', 'bebop_youtube_embed', 8 );

function bebop_youtube_embed( $text ) {
	return str_replace( 'watch/?v=', 'embed/', $text ); 
}