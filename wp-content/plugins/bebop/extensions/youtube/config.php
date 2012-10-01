<?php

/*Config file for an extension*/

function get_youtube_config() {
	$config = array(
		'name' 				=> 'youtube',
		'display_name'		=> 'YouTube',
		'type'				=> 'youtube',
		'pages'				=> array( 'settings',  ),
		'defaultpage'		=> 'settings',
		'data_feed' 		=> 'http://gdata.youtube.com/feeds/api/users/bebop_replace_username/uploads',
		'sanitise_url'		=> array ( '&feature', '&amp;feature' ),		//remove unwanted/unneeded paremeters from a feed.
		'content_type'	 	=> __( 'YouTube video', 'bebop' ),
		'content_oembed'	=> true,
		'action_link' 		=> 'http://www.youtube.com/watch?v=',
	);
	return $config;
}
?>