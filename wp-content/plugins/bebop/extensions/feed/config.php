<?php

/*Config file for an extension*/

function get_feed_config() {
	$config = array(
		'name' 				=> 'feed',
		'display_name'		=> 'Feed',
		'type'				=> 'Feed',
		'pages'				=> array( 'settings', ),
		'defaultpage'		=> 'settings',
		'content_type' 			=> 'RSS link',
	);
	return $config;
}
?>