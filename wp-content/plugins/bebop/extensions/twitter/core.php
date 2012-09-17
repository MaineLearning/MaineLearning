<?php 
/*
 * Use this page to store and additional functions or filters which you may require for your plugin to work as expected.
 * For example, the code below is used to switch out a image url in the text with the html for a clickable image. in twitter.
 */
 
add_filter( 'bp_get_activity_content','bebop_twitter_photos',5 );
add_filter( 'bp_get_activity_content_body','bebop_twitter_photos',5 );

function bebop_twitter_photos( $text ) {
	if ( bp_get_activity_type() == 'twitter' ) {
		$text = preg_replace( '#http://twitpic.com/([a-z0-9_]+)#i', '<a href="http://twitpic.com/\\1" target="_blank" rel="external"><img width="60" src="http://twitpic.com/show/mini/\\1" /></a>', $text );
		$text = preg_replace( '#http://yfrog.com/([a-z0-9_]+)#i', '<a href="http://yfrog.com/\\1" target="_blank" rel="external"><img width="60" src="http://yfrog.com/\\1.th.jpg" /></a>', $text );
		$text = preg_replace( '#http://yfrog.us/([a-z0-9_]+)#i', '<a href="http://yfrog.us/\\1" target="_blank" rel="external"><img width="60" src="http://yfrog.us/\\1:frame" /></a>', $text );
	}
	return $text;
}

?>